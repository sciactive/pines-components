<?php
/**
 * com_inuitcss' configuration defaults.
 *
 * @package Pines
 * @subpackage com_inuitcss
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'grid_layout',
		'cname' => 'Grid Layout',
		'description' => 'The grid layout. Fluid layouts will resize with the browser window. If you choose custom (generate at http://csswizardry.com/inuitcss/), place a file called grid_custom.inuit.css in the com_inuitcss includes directory with your custom CSS.',
		'value' => 'grid_12-75-20-fluid.inuit.css',
		'options' => array(
			'Fixed, 12 cols, 75px width, 20px gutters, 1140px total' => 'grid_12-75-20-fixed.inuit.css',
			'Fluid, 12 cols, 75px width, 20px gutters, 1140px total' => 'grid_12-75-20-fluid.inuit.css',
			'Fixed, 16 cols, 40px width, 20px gutters, 960px total' => 'grid_16-40-20-fixed.inuit.css',
			'Fluid, 16 cols, 40px width, 20px gutters, 960px total' => 'grid_16-40-20-fluid.inuit.css',
			'Custom ' => 'grid_custom.inuit.css'
		),
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
		'name' => 'always_load',
		'cname' => 'Always Load',
		'description' => 'Always load Inuit CSS. (Even if no part of the page says it needs it.) HIGHLY RECOMMENDED!',
		'value' => true,
		'peruser' => true,
	),
);

?>