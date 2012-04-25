<?php
/**
 * com_configure's configuration defaults.
 *
 * @package Components
 * @subpackage configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'percondition',
		'cname' => 'Per Condition Settings',
		'description' => 'Allow per condition configuration.',
		'value' => true,
	),
	array(
		'name' => 'peruser',
		'cname' => 'Per User/Group Settings',
		'description' => 'Allow per user and per group configuration.',
		'value' => true,
	),
	array(
		'name' => 'conditional_groups',
		'cname' => 'Obey Conditional Groups',
		'description' => 'Obey conditions on groups when loading per group configuration. Settings below this one are just for reference.',
		'value' => true,
	),
	array(
		'name' => 'boolean',
		'cname' => 'Boolean',
		'description' => 'True or false.',
		'value' => true,
	),
	array(
		'name' => 'integer',
		'cname' => 'Integer',
		'description' => 'Whole number.',
		'value' => 1,
	),
	array(
		'name' => 'float',
		'cname' => 'Float',
		'description' => 'Floating point number.',
		'value' => 1.01,
	),
	array(
		'name' => 'string',
		'cname' => 'String',
		'description' => 'Text data.',
		'value' => 'Just text.',
	),
	array(
		'name' => 'array_integer',
		'cname' => 'Integer Array',
		'description' => 'An array of whole numbers.',
		'value' => array(
			1,
			2,
			4,
			8,
			16,
			32
		),
	),
	array(
		'name' => 'array_float',
		'cname' => 'Float Array',
		'description' => 'An array of floating point numbers.',
		'value' => array(
			1.00,
			1.5,
			9.33,
			8.4,
			23.4
		),
	),
	array(
		'name' => 'array_string',
		'cname' => 'String Array',
		'description' => 'An array of text data.',
		'value' => array(
			'this',
			'and',
			'that'
		),
	),
	array(
		'name' => 'option_integer',
		'cname' => 'Integer Option',
		'description' => 'A choice of whole numbers.',
		'value' => 1,
		'options' => array(
			1,
			2,
			4,
			8,
			16,
			'Thirty-two' => 32
		),
	),
	array(
		'name' => 'option_float',
		'cname' => 'Float Option',
		'description' => 'A choice of floating point numbers.',
		'value' => 1.5,
		'options' => array(
			1.00,
			1.5,
			9.33,
			8.4
		),
	),
	array(
		'name' => 'option_string',
		'cname' => 'String Option',
		'description' => 'A choice of text data.',
		'value' => 'this',
		'options' => array(
			'this',
			'and',
			'that'
		),
	),
	array(
		'name' => 'multi_integer',
		'cname' => 'Integer Selection',
		'description' => 'A selection of whole numbers.',
		'value' => array('Thirty-Two' => 32),
		'options' => array(
			1,
			2,
			4,
			8,
			16,
			'Thirty-Two' => 32
		),
	),
	array(
		'name' => 'multi_float',
		'cname' => 'Float Selection',
		'description' => 'A selection of floating point numbers.',
		'value' => array(9.33, 8.4),
		'options' => array(
			1.00,
			1.5,
			9.33,
			8.4
		),
	),
	array(
		'name' => 'multi_string',
		'cname' => 'String Selection',
		'description' => 'A selection of text data.',
		'value' => array(
			'this',
			'that',
		),
		'options' => array(
			'this',
			'and',
			'that'
		),
	),
);

?>
<?php
/*
 * Sample config.php file.
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'boolean',
		'value' => false,
	),
	array(
		'name' => 'integer',
		'value' => 3,
	),
	array(
		'name' => 'array_string',
		'value' => array(
			'this',
			'and',
			'that'
		),
	),
	array(
		'name' => 'multi_string',
		'value' => array(
			'and',
		),
	),
);

?>