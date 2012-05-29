<?php
/**
 * Proxy requests to the real BOSH server.
 *
 * @package Components\messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;
set_time_limit($pines->config->com_messenger->proxy_timeout * 2);

$headers = array();
foreach ($_SERVER as $name => $value) {
	if (substr($name, 0, 5) == 'HTTP_') {
		$name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
		$headers[] = $name.': '.$value;
	} elseif ($name == 'CONTENT_TYPE') {
		$headers[] = 'Content-Type: '.$value;
	} elseif ($name == 'CONTENT_LENGTH') {
		$headers[] = 'Content-Length: '.$value;
	}
}
if (!empty($_SERVER['HTTP_COOKIE']))
	$headers[] = 'Cookie: '.preg_replace('/XMPP_proxy_cookie_(.+=)?/', '$1', $_SERVER['HTTP_COOKIE']);

// Set up the cURL request.
$request = curl_init();
$opts = array(
	CURLOPT_URL				=> $pines->config->com_messenger->xmpp_bosh_url,
	CURLOPT_FOLLOWLOCATION	=> true,
	CURLOPT_USERAGENT		=> $pines->info->com_messenger->name.' '.$pines->info->com_messenger->version,
	CURLOPT_HEADER			=> true,
	CURLINFO_HEADER_OUT		=> true,
	CURLOPT_HTTPHEADER		=> $headers,
	CURLOPT_RETURNTRANSFER	=> true,
	CURLOPT_CONNECTTIMEOUT	=> 30,
	CURLOPT_TIMEOUT			=> $pines->config->com_messenger->proxy_timeout,
	CURLOPT_MAXCONNECTS		=> 1000,
);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$opts[CURLOPT_POST] = true;
	$opts[CURLOPT_POSTFIELDS] = !empty($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : file_get_contents("php://input");
}
curl_setopt_array($request, $opts);

// Send the request.
$response = curl_exec($request);
if ($response === false) {
	header('X-Curl-ErrNo: '.curl_errno($request));
	header('X-Curl-Error: '.curl_error($request));
	curl_close($request);
	throw new HttpServerException(null, 500);
}
list($response_headers, $response_body) = explode("\r\n\r\n", $response, 2);

// Return headers.
foreach (explode("\n", $response_headers) as $cur_header) {
	if (strpos($cur_header, 'Set-Cookie:') === 0) {
		$cookies = explode("\n", substr($cur_header, 12)); // Get rid of "Set-Cookie: ".
		foreach ($cookies as &$value)
			$value = 'XMPP_proxy_cookie_'.$value;
		unset($value);
		header('Set-Cookie: '.implode("\n", $cookies));
	} else
		header($cur_header);
}

//if (strpos($response_body, 'body') === false)
//	print_r($response);

echo $response_body;

curl_close($request);

?>