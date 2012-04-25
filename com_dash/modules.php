<?php
/**
 * com_dash's modules.
 *
 * @package Components\dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

global $pines;
return array(
	'quick_dash' => array(
		'cname' => 'Quick Dash',
		'description' => 'Show the quick dash.',
		'view_callback' => array($pines->com_dash, 'quick_dash_module'),
		'type' => 'module',
	),
);

?>