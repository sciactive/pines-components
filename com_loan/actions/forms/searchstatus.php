<?php
/**
 * Select a status to search loans by.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_loan/viewarchived') )
	punt_user(null, pines_url('com_loan', 'loan/list'));

$pines->com_loan->search_status_form($_REQUEST['cur_state']);
?>
