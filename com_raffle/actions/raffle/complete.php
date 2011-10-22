<?php
/**
 * Complete a raffle.
 *
 * @package Pines
 * @subpackage com_raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_raffle/completeraffle') )
	punt_user(null, pines_url('com_raffle', 'raffle/complete', array('id' => $_REQUEST['id'])));

$entity = com_raffle_raffle::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested raffle id is not accessible.');
	return;
}

if (!$entity->complete()) {
	pines_error('Couldn\'t complete raffle. Do you have permission?');
	return;
}
$entity->print_complete();

?>