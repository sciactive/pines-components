<?php
/**
 * com_customertimer's buttons.
 *
 * @package Components\customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'floors' => array(
		'description' => 'Customer timer floors.',
		'text' => 'Floors',
		'class' => 'picon-chronometer',
		'href' => pines_url('com_customertimer', 'floor/list'),
		'default' => false,
		'depends' => array(
			'ability' => 'com_customertimer/listfloors',
		),
	),
);

?>