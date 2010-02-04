<?php
/**
 * Create a barcode.
 *
 * @package Pines
 * @subpackage com_barcode
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 *
 * Built using the Barcode Render Class for PHP Created by Karim Mribti
 * http://www.mribti.com/barcode/
 */
defined('P_RUN') or die('Direct access prohibited');

$page->override = true;

if (is_null($_REQUEST['code'])) {
	$page->override_doc("\0");
	return;
}

/**
 * Require the barcode base class.
 */
require_once('components/com_barcode/includes/barcode.php');
$style = 0;

$code = strtoupper($_REQUEST['code']);
$type = $_REQUEST['type'];
$style = (int) $_REQUEST['style'];
$width = $_REQUEST['width'];
$height = $_REQUEST['height'];
$xres = $_REQUEST['xres'];
$font = $_REQUEST['font'];
$color = $_REQUEST['color'];

if (!isset($type))
	$type = $config->com_barcode->type;
if (!isset($width))
	$width = $config->com_barcode->width;
if (!isset($height))
	$height = $config->com_barcode->height;
if (!isset($xres))
	$xres = $config->com_barcode->xres;
if (!isset($font))
	$font = $config->com_barcode->font;
if (!isset($color))
	$color = $config->com_barcode->color;
if (!$style)
	$style = $config->com_barcode->output_type == 'jpg' ? 36 : ($config->com_barcode->output_type == 'gif' ? 4100 : 68);

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
	$obj->ColorObject($color);
	$obj->SetFont($font);
	$obj->DrawObject($xres);
	$obj->FlushObject();
	$obj->DestroyObject();
	unset($obj);
}

?>