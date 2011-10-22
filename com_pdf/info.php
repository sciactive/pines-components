<?php
/**
 * com_pdf's information.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'PDF Generator',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Generate PDFs from templates',
	'description' => 'Easily insert information into a PDF template. Also allows users to format their own PDFs.',
	'depend' => array(
		'pines' => '<2',
		'class' => 'Imagick',
		'component' => 'com_jquery&com_pform'
	),
);

?>