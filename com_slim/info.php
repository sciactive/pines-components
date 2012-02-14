<?php
/**
 * com_slim's information.
 *
 * @package Pines
 * @subpackage com_slim
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Slim Archive',
	'author' => 'SciActive',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Slim archiver and extracter',
	'description' => 'A library for archiving files to and extracting files from the Slim archive format. Slim archives are designed to easily work with PHP programs.',
	'depend' => array(
		'pines' => '<2',
		'function' => 'gzdeflate&gzinflate&stream_filter_append'
	),
);

?>