<?php
/**
 * Save the state of a Pines Grid.
 *
 * @package Components
 * @subpackage pgrid
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper())
	punt_user();

$cur_state = $_REQUEST['state'];
$cur_view = $_REQUEST['view'];
if (isset($_SESSION['user'])) {
	pines_session('write');
	if ((array) $_SESSION['user']->pgrid_saved_states !== $_SESSION['user']->pgrid_saved_states)
		$_SESSION['user']->pgrid_saved_states = array();
	// Re-encode the state, for extra protection.
	$cur_state = json_encode(json_decode($cur_state));
	$_SESSION['user']->pgrid_saved_states[$cur_view] = $cur_state;
	$_SESSION['user']->save();
	pines_session('close');
}
$pines->page->override = true;

?>