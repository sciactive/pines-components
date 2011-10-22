<?php
/**
 * Create a barcode.
 *
 * You can use this action to create an image of a barcode. Parameters are
 * passed as POST, GET, or COOKIE data, and the image is generated and printed
 * to the browser. Therefore, you can use this action as the SRC of an IMG tag.
 * Or you can have the image created as a temporary file.
 * The following parameters can be given:
 *
 * - code - The text of the barcode. (Required)
 * - type - The type of barcode to generate.
 * - style - See below for information about this parameter.
 * - width - The width of the image to generate.
 * - height - The height of the barcode.
 * - xres - The width of the bars (1, 2, or 3).
 * - font - The font to use for any barcode text. (1-5)
 * - bgcolor - The background color.
 * - color - The color of the barcode and text.
 * - tmpfile - Create a tmp file and echo its location instead. ("ON") (in development, dont use)
 *
 * If a parameter is not given, the default will be used.
 *
 * The style parameter defines various aspects of the style of the generated
 * image. It is a number made by adding the numbers of the styles you want. The
 * available styles are:
 *
 * - 1 - Draw a border around the barcode.
 * - 2 - Make the background transparent.
 * - 4 - Align the barcode in the center of the image.
 * - 8 - Align the barcode in the left of the image.
 * - 16 - Align the barcode in the right of the image.
 * - 32 - Output the image in JPEG format. (Does not support transparency.)
 * - 64 - Output the image in PNG format.
 * - 128 - Output the image in GIF format.
 * - 256 - Print the text beneath the barcode.
 * - 512 - Stretch the text across the image.
 *
 * For example, if you want your barcode to be a centered, transparent PNG, with
 * stretched text beneath it, this is how you would calculate the style value:
 *
 * 2 + 4 + 64 + 256 + 512 = 838
 *
 * Style defaults to 2 + 4 + (32, 64, or 128, depending on configuration).
 *
 * Built using the Barcode Render Class for PHP Created by Karim Mribti
 * http://www.mribti.com/barcode/
 *
 * @package Pines
 * @subpackage com_barcode
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

if (!isset($_REQUEST['code'])) {
	$pines->page->override_doc("\0");
	return;
}

/**
 * Require the barcode base class.
 */
require_once('components/com_barcode/includes/barcode.php');
$style = 0;

//if ($_REQUEST['tmpfile'] == 'ON')
//	$filename = tempnam('dummy', 'pin');
// Todo: Find a safer way for creating a file with the barcode generator.
//$filename = $_REQUEST['filename'];
$code = strtoupper($_REQUEST['code']);
$type = strtoupper($_REQUEST['type']);
$style = (int) $_REQUEST['style'];
$width = $_REQUEST['width'];
$height = $_REQUEST['height'];
$xres = $_REQUEST['xres'];
$font = $_REQUEST['font'];
$bgcolor = $_REQUEST['bgcolor'];
$color = $_REQUEST['color'];

if (!isset($type))
	$type = $pines->config->com_barcode->type;
if (!isset($width))
	$width = $pines->config->com_barcode->width;
if (!isset($height))
	$height = $pines->config->com_barcode->height;
if (!isset($xres))
	$xres = $pines->config->com_barcode->xres;
if (!isset($font))
	$font = $pines->config->com_barcode->font;
if (!isset($bgcolor))
	$bgcolor = $pines->config->com_barcode->bgcolor;
if (!isset($color))
	$color = $pines->config->com_barcode->color;
if (!$style)
	$style = $pines->config->com_barcode->output_type == 'jpg' ? 36 : ($pines->config->com_barcode->output_type == 'gif' ? 134 : 70);

switch ($type) {
	case 'I25':
		/**
		 * I25 barcode class.
		 */
		require_once('components/com_barcode/includes/i25object.php');
		$obj = new I25Object($width, $height, $style, $code);
		break;
	case 'C128A':
		/**
		 * C128A barcode class.
		 */
		require_once('components/com_barcode/includes/c128aobject.php');
		$obj = new C128AObject($width, $height, $style, $code);
		break;
	case 'C128B':
		/**
		 * C128B barcode class.
		 */
		require_once('components/com_barcode/includes/c128bobject.php');
		$obj = new C128BObject($width, $height, $style, $code);
		break;
	case 'C128C':
		/**
		 * C128C barcode class.
		 */
		require_once('components/com_barcode/includes/c128cobject.php');
		$obj = new C128CObject($width, $height, $style, $code);
		break;
	case 'C39':
	default:
		/**
		 * C39 barcode class.
		 */
		require_once('components/com_barcode/includes/c39object.php');
		$obj = new C39Object($width, $height, $style, $code);
		break;
}

if ($obj) {
	$obj->ColorObject($bgcolor, $color);
	$obj->SetFont($font);
	$obj->DrawObject($xres);
	if (isset($obj->mError)) {
		pines_log("Barcode generation error: {$obj->mError}", 'error');
		echo $obj->mError;
	} else {
		if (isset($filename))
			$obj->filename = $filename;
		$obj->FlushObject();
		$obj->DestroyObject();
	}
	unset($obj);
}

?>