<?php
/**
 * Request time off from work.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/clock') )
	punt_user(null, pines_url('com_hrm', 'timeoff/request'));

$rto = com_hrm_rto::factory((int)$_REQUEST['id']);
if (!isset($rto->guid) || !$rto->user->is($_SESSION['user']))
	$rto = com_hrm_rto::factory();

$rto->print_form();

?>