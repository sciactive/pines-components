<?php
/**
 * com_barcode's configuration defaults.
 *
 * @package Pines
 * @subpackage com_barcode
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'type',
		'cname' => 'Default Code Type',
		'description' => 'The type of barcode.',
		'value' => 'C39',
		'options' => array(
			'I25' => 'I25',
			'Code 39' => 'C39',
			'Code 128A' => 'C128A',
			'Code 128B' => 'C128B',
			'Code 128C' => 'C128C'
		)
	),
	array(
		'name' => 'output_type',
		'cname' => 'Default Image Type',
		'description' => 'The format of the generated image.',
		'value' => 'png',
		'options' => array(
			'PNG' => 'png',
			'GIF' => 'gif',
			'JPG (Does not support transparency.)' => 'jpg'
		)
	),
	array(
		'name' => 'width',
		'cname' => 'Default Width',
		'description' => 'The width of the image the barcoded will be generated in.',
		'value' => 250,
	),
	array(
		'name' => 'height',
		'cname' => 'Default Height',
		'description' => 'The height of the actual barcode.',
		'value' => 50,
	),
	array(
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
	array(
		'name' => 'font',
		'cname' => 'Default Font',
		'description' => 'The font of the text generated underneath the barcode. (Text is not enabled by default.)',
		'value' => 2,
		'options' => array(
			'Small' => 1,
			'Medium' => 2,
			'Medium Bold' => 3,
			'Large' => 4,
			'Large Bold' => 5
		)
	),
	array(
		'name' => 'bgcolor',
		'cname' => 'Default Background Color',
		'description' => 'Use comma separated RGB color values, or HTML color codes/names.',
		'value' => 'white',
	),
	array(
		'name' => 'color',
		'cname' => 'Default Barcode and Text Color',
		'description' => 'Use comma separated RGB color values, or HTML color codes/names.',
		'value' => 'black',
	),
);

?>