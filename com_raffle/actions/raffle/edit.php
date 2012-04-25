<?php
/**
 * Provide a form to edit a raffle.
 *
 * @package Components
 * @subpackage raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if (!empty($_REQUEST['id'])) {
	if ( !gatekeeper('com_raffle/editraffle') )
		punt_user(null, pines_url('com_raffle', 'raffle/edit', array('id' => $_REQUEST['id'])));
} else {
	if ( !gatekeeper('com_raffle/newraffle') )
		punt_user(null, pines_url('com_raffle', 'raffle/edit'));
}

$entity = com_raffle_raffle::factory((int) $_REQUEST['id']);
$entity->print_form();

?>