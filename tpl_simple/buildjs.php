<?php
header('Content-Type: text/javascript');
header('Vary: Accept-Encoding');
header('Pragma: ');
header('X-Powered-By: ');

$mod_date = $_REQUEST['mtime'];
$etag = dechex(crc32($mod_date));

if (
		(array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER) && strpos($_SERVER['HTTP_IF_NONE_MATCH'], $etag) !== false ) ||
		(array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER) && $mod_date <= strtotime(preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE'])))
	) {
	header('Content-Type: ');
	header('ETag: "'.$etag.'"');
	header('HTTP/1.1 304 Not Modified');
	exit;
}

$files = $_REQUEST['js'];
$root = $_REQUEST['root'];
$files = explode('%%%', $files);
$output_js = "";
$system_js =
file_get_contents($root.'/system/includes/pines.min.js')."\n".
'pines.full_location = "http'.(($_SERVER['HTTPS'] == 'on') ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/includes/js.php'))."\"\n".
'pines.rela_location = "'.substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], 'system/includes/js.php'))."\"\n".
"var JSON;JSON||pines.loadjs(pines.rela_location+\"system/includes/json2.min.js\");\n";
foreach($files as $file) {
	if (preg_match('/^htt/', $file)) {
		$output_js .= file_get_contents($file).";\n";
    } else {
		$output_js .= file_get_contents($root.$file).";\n";
	}
    
}

$length = strlen($system_js) + strlen($output_js);
header('Content-Length: '.$length);
header('Last-Modified: '.gmdate('r', $mod_date));
header('Cache-Control: max-age=604800, public');
header('Expires: '.gmdate('r', time()+604800));
header('ETag: "'.$etag.'"');

ob_start();
echo $system_js;
ob_flush();
echo $output_js;
$content = ob_get_flush();



