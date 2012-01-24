<?php
/**
 * Save changes to a thread.
 *
 * @package Pines
 * @subpackage com_notes
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_notes/editthread') )
	punt_user(null, pines_url('com_notes', 'thread/list'));
$thread = com_notes_thread::factory((int) $_REQUEST['id']);
if (!isset($thread->guid)) {
	pines_error('Requested thread id is not accessible.');
	return;
}

$thread->hidden = ($_REQUEST['hidden'] == 'ON');
switch ($_REQUEST['privacy']) {
	case 'only-me':
		$thread->ac = (object) array('user' => 3, 'group' => 0, 'other' => 0);
		break;
	case 'my-group':
		$thread->ac = (object) array('user' => 3, 'group' => 2, 'other' => 0);
		break;
	case 'everyone':
	default:
		$thread->ac = (object) array('user' => 3, 'group' => 2, 'other' => 2);
		break;
}
foreach ((array) $_REQUEST['delete_note'] as $cur_key) {
	if (isset($thread->notes[$cur_key]))
		unset($thread->notes[$cur_key]);
}

if ($thread->save()) {
	pines_notice('Saved thread ['.$thread->guid.']');
} else {
	pines_error('Error saving thread. Do you have permission?');
}

pines_redirect(pines_url('com_notes', 'thread/list'));

?>