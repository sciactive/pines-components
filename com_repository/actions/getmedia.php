<?php
/**
 * Get a package's media.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

$publisher = $_REQUEST['pub'];
$package = $_REQUEST['p'];
$version = $_REQUEST['v'];
$media = $_REQUEST['m'];

if (empty($publisher) || empty($package) || empty($version) || empty($media))
	return;

$user = user::factory($publisher);
if (!isset($user->guid))
	throw new HttpClientException(null, 404);

$file = clean_filename("{$pines->config->com_repository->repository_path}{$user->guid}/{$package}/{$version}/_MEDIA/{$media}");
if (!file_exists($file))
	throw new HttpClientException(null, 404);

$ext = strtolower(end(explode(".", $file)));

switch ($ext) {
	case 'bmp':
		$content_type = 'image/bmp';
		break;
	case 'cod':
		$content_type = 'image/cis-cod';
		break;
	case 'gif':
		$content_type = 'image/gif';
		break;
	case 'ief':
		$content_type = 'image/ief';
		break;
	case 'jpe':
	case 'jpeg':
	case 'jpg':
		$content_type = 'image/jpeg';
		break;
	case 'jfif':
		$content_type = 'image/pipeg';
		break;
	case 'png':
		$content_type = 'image/png';
		break;
	case 'svg':
		$content_type = 'image/svg+xml';
		break;
	case 'tif':
	case 'tiff':
		$content_type = 'image/tiff';
		break;
	case 'ras':
		$content_type = 'image/x-cmu-raster';
		break;
	case 'cmx':
		$content_type = 'image/x-cmx';
		break;
	case 'ico':
		$content_type = 'image/x-icon';
		break;
	case 'pnm':
		$content_type = 'image/x-portable-anymap';
		break;
	case 'pbm':
		$content_type = 'image/x-portable-bitmap';
		break;
	case 'pgm':
		$content_type = 'image/x-portable-graymap';
		break;
	case 'ppm':
		$content_type = 'image/x-portable-pixmap';
		break;
	case 'rgb':
		$content_type = 'image/x-rgb';
		break;
	case 'xbm':
		$content_type = 'image/x-xbitmap';
		break;
	case 'xpm':
		$content_type = 'image/x-xpixmap';
		break;
	case 'xwd':
		$content_type = 'image/x-xwindowdump';
		break;
	default:
		$content_type = 'application/octet-stream';
		break;
}

header('Content-Type: '.$content_type);

$pines->page->override_doc(file_get_contents($file));

?>