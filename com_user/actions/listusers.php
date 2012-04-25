<?php
/**
 * Manage the system users.
 *
 * @package Components
 * @subpackage user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_user/listusers') )
	punt_user(null, pines_url('com_user', 'listusers'));

$pines->user_manager->list_users($_REQUEST['enabled'] != 'false');
?>