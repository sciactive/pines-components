<?php
/**
 * Load default group selector.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_customer/defaultgroups') )
	punt_user(null, pines_url('com_customer', 'defaultgroups'));

$module = new module('com_customer', 'customer_group_select', 'content');
$module->groups = $pines->user_manager->get_groups();

?>