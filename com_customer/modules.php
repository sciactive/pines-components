<?php
/**
 * com_customer's modules.
 *
 * @package Components
 * @subpackage customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'addcustomerinteraction' => array(
		'cname' => 'Add Customer Interaction',
		'description' => 'Add an interaction to a customer.',
		'image' => 'includes/add_interaction_widget_screen.png',
		'view' => 'modules/interaction',
		'type' => 'module widget',
		'widget' => array(
			'default' => false,
			'depends' => array(
				'ability' => 'com_customer/newinteraction',
				'component' => 'com_calendar',
			),
		),
	),
);

?>