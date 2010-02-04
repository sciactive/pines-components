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
    'cname' => 'Code Type',
    'description' => 'The type of barcode. (I25, C39, C128A, C128B, C128C)',
    'value' => 'C39',
  ),
  1 =>
  array (
    'name' => 'output_type',
    'cname' => 'Image Type',
    'description' => 'The format of the generated image (png, gif, or jpg).',
    'value' => 'png',
  ),
  2 =>
  array (
    'name' => 'width',
    'cname' => 'Width',
    'description' => 'The width of the image the barcoded will be generated in.',
    'value' => 200,
  ),
  3 =>
  array (
    'name' => 'height',
    'cname' => 'Height',
    'description' => 'The height of the actual barcode.',
    'value' => 50,
  ),
  4 =>
  array (
    'name' => 'xres',
    'cname' => 'x-Resolution',
    'description' => 'Thickness of the bars (1, 2, or 3).',
    'value' => 1,
  ),
  5 =>
  array (
    'name' => 'font',
    'cname' => 'Font',
    'description' => 'The font of the text generated underneath the barcode. (1-5) (Text is not enabled by default.)',
    'value' => 2,
  ),
  6 =>
  array (
    'name' => 'bgcolor',
    'cname' => 'Background Color',
    'description' => 'Use comma separated RGB color values, or HTML color codes/names.',
    'value' => 'white',
  ),
  7 =>
  array (
    'name' => 'color',
    'cname' => 'Barcode and Text Color',
    'description' => 'Use comma separated RGB color values, or HTML color codes/names.',
    'value' => 'black',
  ),
);

?>