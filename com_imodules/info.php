<?php
/**
 * com_imodules' information.
 *
 * @package Pines
 * @subpackage com_imodules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'IModule Parser',
	'author' => 'SciActive',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'An inline module parsing system',
	'description' => 'An inline module system, which allows you to place modules directly in content.',
	'depend' => array(
		'pines' => '<2'
	),
);

?>