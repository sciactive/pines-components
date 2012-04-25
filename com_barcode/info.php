<?php
/**
 * com_barcode's information.
 *
 * @package Components\barcode
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Barcode Creator',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Creates various types of barcodes.',
	'description' => 'Creates and displays barcode images using a variety of formats.',
	'depend' => array(
		'pines' => '<2',
		'function' => 'imagecreate'
	),
);

?>