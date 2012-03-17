<?php
/**
 * Enter a public raffle.
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

$entity = com_raffle_raffle::factory((int) $_REQUEST['id']);
if (!isset($entity->guid) || !$entity->public)
	throw new HttpClientException(null, 404);

if ($entity->complete) {
	pines_notice('This raffle has been completed.');
	pines_redirect(pines_url());
	return;
}

$phone = preg_replace('/\D/', '', $_REQUEST['phone']);
if (empty($_REQUEST['first_name']) || empty($_REQUEST['last_name']) || empty($_REQUEST['email']) || empty($phone)) {
	pines_notice('Please provide all requested information.');
	pines_redirect(pines_url('com_raffle', 'enter', array('id' => $entity->guid)));
	return;
}

$email_check = strtolower($_REQUEST['email']);
foreach ($entity->public_contestants as $cur_contestant) {
	if ($email_check == strtolower($cur_contestant['email'])) {
		pines_notice('You have been entered into the raffle.');
		pines_redirect($entity->back_to_form ? pines_url('com_raffle', 'enter', array('id' => $entity->guid)) : pines_url());
		return;
	}
}

$entity->public_contestants[] = array(
	'first_name' => $_REQUEST['first_name'],
	'last_name' => $_REQUEST['last_name'],
	'email' => $_REQUEST['email'],
	'phone' => $phone
);

if ($entity->save()) {
	pines_notice('You have been entered into the raffle.');
	pines_redirect($entity->back_to_form ? pines_url('com_raffle', 'enter', array('id' => $entity->guid)) : pines_url());
} else {
	pines_error('Your entry could not be saved. Please try again.');
	pines_redirect(pines_url('com_raffle', 'enter', array('id' => $entity->guid)));
}

?>