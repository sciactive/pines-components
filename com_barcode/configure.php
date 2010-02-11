<?php
/**
 * com_barcode's configuration.
 *
 * @package Pines
 * @subpackage com_barcode
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 =>
  array (
	'name' => 'type',
	'cname' => 'Default Code Type',
	'description' => 'The type of barcode.',
	'value' => 'C39',
	'options' => array(
		'I25',
		'C39',
		'C128A',
		'C128B',
		'C128C'
	)
  ),
  1 =>
  array (
	'name' => 'output_type',
	'cname' => 'Default Image Type',
	'description' => 'The format of the generated image.',
	'value' => 'png',
	'options' => array(
		'png',
		'gif',
		'jpg'
	)
  ),
  2 =>
  array (
	'name' => 'width',
	'cname' => 'Default Width',
	'description' => 'The width of the image the barcoded will be generated in.',
	'value' => 200,
  ),
  3 =>
  array (
	'name' => 'height',
	'cname' => 'Default Height',
	'description' => 'The height of the actual barcode.',
	'value' => 50,
  ),
  4 =>
  array (
	'name' => 'xres',
	'cname' => 'Default x-Resolution',
	'description' => 'Thickness of the bars.',
	'value' => 1,
	'options' => array(
		1,
		2,
		3
	)
  ),
  5 =>
  array (
	'name' => 'font',
	'cname' => 'Default Font',
	'description' => 'The font of the text generated underneath the barcode. (Text is not enabled by default.)',
	'value' => 2,
	'options' => array(
		1,
		2,
		3,
		4,
		5
	)
  ),
  6 =>
  array (
	'name' => 'bgcolor',
	'cname' => 'Default Background Color',
	'description' => 'Use comma separated RGB color values, or HTML color codes/names.',
	'value' => 'white',
  ),
  7 =>
  array (
	'name' => 'color',
	'cname' => 'Default Barcode and Text Color',
	'description' => 'Use comma separated RGB color values, or HTML color codes/names.',
	'value' => 'black',
  ),
);

?>