<?php
/**
 * Select a user.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/listemployees') )
	punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'forms/userselect'));

$pines->com_hrm->user_select_form($REQUEST['all'] == 'true');

?>