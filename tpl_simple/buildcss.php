<?php
header('Content-Type: text/css');
header('Vary: Accept-Encoding');
header('Pragma: ');
header('X-Powered-By: ');
header("Access-Control-Allow-Origin: *");

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

require('compresscss.php');
$files = $_REQUEST['css'];
$css = explode('%%%', $files);
array_walk($css, function( &$val) {
    $val = file_get_contents( $val );
});
$css = implode($css);
$C = new CSS_Compress($css);
$output_css = $C->get_css();
ob_start();
echo $output_css;
$content = ob_get_clean();

header('Last-Modified: '.gmdate('r', $mod_date));
header('Cache-Control: max-age=604800, public');
header('Expires: '.gmdate('r', time()+604800));
header('ETag: "'.$etag.'"');

echo $content;
