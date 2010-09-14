<?php
/**
 * Process an issue.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/resolveissue') )
	punt_user(null, pines_url('com_hrm', 'employee/list'));

$pines->page->override = true;

$issue = com_hrm_issue::factory((int) $_REQUEST['id']);
if (!isset($issue->guid) || !isset($issue->employee->guid)) {
	$pines->page->override_doc('Error');
	return;
}
// Either delete an employee issue or mark it's status.
switch ($_REQUEST['status']) {
	case 'delete':
		$issue->delete();
		break;
	default:
		if (isset($_REQUEST['status'])) {
			$issue->status = $_REQUEST['status'];
			if (!empty($_REQUEST['comments']))
				$issue->comments[] = $_REQUEST['comments'];
			$issue->save();
		}
		break;
}
$pines->page->override_doc();

?>