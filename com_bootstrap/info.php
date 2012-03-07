<?php
/**
 * com_bootstrap's information.
 *
 * @package Pines
 * @subpackage com_bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Bootstrap',
	'author' => 'SciActive (Component), Twitter (Bootstrap)',
	'version' => '2.0.1-1.0.1dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Bootstrap CSS and JS components',
	'description' => "A collection of CSS and components, featuring mobile and tablet support, grid layout, typography enhancements, and plugin support.\n\nSee the Bootstrap website at http://twitter.github.com/bootstrap/",
	'depend' => array(
		'pines' => '<2',
	),
);

?>