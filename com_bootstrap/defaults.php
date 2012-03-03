<?php
/**
 * com_bootstrap's configuration defaults.
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
	array(
		'name' => 'theme',
		'cname' => 'Theme',
		'description' => 'Bootstrap theme. To use your own, put the files in the "custom" folder under com_bootstrap/includes/themes.',
		'value' => 'normal',
		'options' => pines_scandir('components/com_bootstrap/includes/themes/'),
		'peruser' => true,
	),
	array(
		'name' => 'grid_columns',
		'cname' => 'Total Grid Columns',
		'description' => 'You must put the total number of columns in your grid layout here. This is used by other components to style their content.',
		'value' => 12,
		'peruser' => true,
	),
	array(
		'name' => 'responsive',
		'cname' => 'Include Responsive Stylesheet',
		'description' => 'Include the responsive stylesheet for mobile browsers. Don\'t set this if you\'re using a custom theme and there is no bootstrap-responsive.min.css file in the "css" folder.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'always_load',
		'cname' => 'Always Load',
		'description' => 'Always load Bootstrap. (Even if no part of the page says it needs it.) HIGHLY RECOMMENDED!',
		'value' => true,
		'peruser' => true,
	),
);

?>