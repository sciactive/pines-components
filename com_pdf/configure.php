<?php
/**
 * com_pdf's configuration.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array (
  0 =>
  array (
	'name' => 'author',
	'cname' => 'Default Author',
	'description' => 'The default author of PDFs created by com_pdf.',
	'value' => 'Hunter Perrin',
  ),
  1 =>
  array (
	'name' => 'pdf_path',
	'cname' => 'PDF Library Path',
	'description' => 'The relative path of the directory containing the PDFs. End this path with a slash!',
	'value' => $config->setting_upload.'pdf/',
  ),
);

?>