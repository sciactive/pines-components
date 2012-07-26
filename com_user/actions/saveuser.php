<?php
/**
 * Save changes to a user.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_user/edituser') && (!gatekeeper('com_user/self') || ($_REQUEST['id'] != $_SESSION['user_id'])) )
		punt_user(null, pines_url('com_user', 'listusers'));
	$user = user::factory((int) $_REQUEST['id']);
	if (!isset($user->guid)) {
		pines_error('Requested user id is not accessible.');
		return;
	}
	if ( !empty($_REQUEST['password']) )
		$user->password($_REQUEST['password']);
} else {
	if ( !gatekeeper('com_user/newuser') )
		punt_user(null, pines_url('com_user', 'listusers'));
	$user = user::factory();
	$user->password($_REQUEST['password']);
}

if (gatekeeper('com_user/usernames'))
	$user->username = $_REQUEST['username'];
if (in_array('name', $pines->config->com_user->user_fields)) {
	$user->name_first = $_REQUEST['name_first'];
	$user->name_middle = $_REQUEST['name_middle'];
	$user->name_last = $_REQUEST['name_last'];
	$user->name = $user->name_first.(!empty($user->name_middle) ? ' '.$user->name_middle : '').(!empty($user->name_last) ? ' '.$user->name_last : '');
}
if (gatekeeper('com_user/enabling')) {
	if ($_REQUEST['enabled'] == 'ON')
		$user->add_tag('enabled');
	else
		$user->remove_tag('enabled');
}
if (in_array('email', $pines->config->com_user->user_fields)) {
	// Only send an email if they don't have the ability to edit all users.
	if ($pines->config->com_user->confirm_email && !gatekeeper('com_user/edituser')) {
		if (isset($user->guid) && $user->email != $_REQUEST['email']) {
			if (isset($user->email_change_date) && $user->email_change_date > strtotime('-1 week'))
				pines_notice('You already changed your email address recently. Please wait until '.format_date(strtotime('+1 week', $user->email_change_date), 'full_short').' to change your email address again.');
			else {
				if (isset($user->secret)) {
					// The user hasn't verified their previous email, so just update it.
					$user->email = $_REQUEST['email'];
					// This will cause a new verification email to be sent when
					// the user is saved. We need to change the secret, so the
					// old link doesn't verify the new address.
					$user->secret = uniqid('', true);
				} else {
					$user->new_email_address = $_REQUEST['email'];
					$user->new_email_secret = uniqid('', true);
					// Save the old email in case the verification link is clicked.
					$user->cancel_email_address = $user->email;
					$user->cancel_email_secret = uniqid('', true);
					$user->email_change_date = time();
					$confirm_email = true;
				}
			}
		}
	} else
		$user->email = $_REQUEST['email'];
	if ($user->email && $_REQUEST['mailing_list'] != 'ON' && !$pines->com_mailer->unsubscribe_query($user->email)) {
		if (!$pines->com_mailer->unsubscribe_add($user->email))
			pines_error('Your email could not be removed from the mailing list. Please try again, and if the problem persists, contact an administrator.');
	} elseif ($user->email && $_REQUEST['mailing_list'] == 'ON' && $pines->com_mailer->unsubscribe_query($user->email)) {
		if (!$pines->com_mailer->unsubscribe_remove($user->email))
			pines_error('Your email could not be added to the mailing list. Please try again, and if the problem persists, contact an administrator.');
	}
}
if (in_array('phone', $pines->config->com_user->user_fields))
	$user->phone = preg_replace('/\D/', '', $_REQUEST['phone']);
if (in_array('fax', $pines->config->com_user->user_fields))
	$user->fax = preg_replace('/\D/', '', $_REQUEST['fax']);
if ($pines->config->com_user->referral_codes)
	$user->referral_code = $_REQUEST['referral_code'];
if (in_array('timezone', $pines->config->com_user->user_fields))
	$user->timezone = $_REQUEST['timezone'];

// Location
if (in_array('address', $pines->config->com_user->user_fields)) {
	$user->address_type = $_REQUEST['address_type'];
	$user->address_1 = $_REQUEST['address_1'];
	$user->address_2 = $_REQUEST['address_2'];
	$user->city = $_REQUEST['city'];
	$user->state = $_REQUEST['state'];
	$user->zip = $_REQUEST['zip'];
	$user->address_international = $_REQUEST['address_international'];
}
if (in_array('additional_addresses', $pines->config->com_user->user_fields)) {
	$user->addresses = (array) json_decode($_REQUEST['addresses']);
	foreach ($user->addresses as &$cur_address) {
		$array = array(
			'type' => $cur_address->values[0],
			'address_1' => $cur_address->values[1],
			'address_2' => $cur_address->values[2],
			'city' => $cur_address->values[3],
			'state' => $cur_address->values[4],
			'zip' => $cur_address->values[5]
		);
		$cur_address = $array;
	}
	unset($cur_address);
}

if (in_array('pin', $pines->config->com_user->user_fields) && gatekeeper('com_user/assignpin'))
	$user->pin = $_REQUEST['pin'];

// Attributes
if (in_array('attributes', $pines->config->com_user->user_fields)) {
	$user->attributes = (array) json_decode($_REQUEST['attributes']);
	foreach ($user->attributes as &$cur_attribute) {
		$array = array(
			'name' => $cur_attribute->values[0],
			'value' => $cur_attribute->values[1]
		);
		$cur_attribute = $array;
	}
	unset($cur_attribute);
}

// Go through a list of all groups, and assign them if they're selected.
// Groups that the user does not have access to will not be received from the
// entity manager after com_user filters the result, and thus will not be
// assigned.
if ( gatekeeper('com_user/assigngroup') ) {
	$highest_primary_parent = $pines->config->com_user->highest_primary;
	$highest_secondary_parent = $pines->config->com_user->highest_secondary;
	$primary_groups = $secondary_groups = array();
	if ($highest_primary_parent == 0) {
		$primary_groups = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group')));
	} else {
		if ($highest_primary_parent > 0) {
			$highest_primary_parent = group::factory($highest_primary_parent);
			if (isset($highest_primary_parent->guid))
				$primary_groups = $highest_primary_parent->get_descendants();
		}
	}
	if ($highest_secondary_parent == 0) {
		$secondary_groups = $pines->entity_manager->get_entities(array('class' => group), array('&', 'tag' => array('com_user', 'group')));
	} else {
		if ($highest_secondary_parent > 0) {
			$highest_secondary_parent = group::factory($highest_secondary_parent);
			if (isset($highest_secondary_parent->guid))
				$secondary_groups = $highest_secondary_parent->get_descendants();
		}
	}
	$group = group::factory((int) $_REQUEST['group']);
	$groups = array_map('intval', (array) $_REQUEST['groups']);
	foreach ($primary_groups as $cur_group) {
		if ($cur_group->is($group)) {
			$user->group = $group;
			break;
		}
	}
	foreach ($secondary_groups as $cur_group) {
		if (in_array($cur_group->guid, $groups))
			$user->add_group($cur_group);
		else
			$user->del_group($cur_group);
	}
	// What if the user can't assign the current primary group, so it defaults to null?
	//if ($_REQUEST['group'] == 'null' || $_REQUEST['no_primary_group'] == 'ON' )
	if ($_REQUEST['group'] == 'null')
		unset($user->group);
}

if ( gatekeeper('com_user/abilities') ) {
	$user->inherit_abilities = ($_REQUEST['inherit_abilities'] == 'ON');
	$sections = array('system');
	foreach ($pines->components as $cur_component)
		$sections[] = $cur_component;
	foreach ($sections as $cur_section) {
		if ($cur_section == 'system')
			$section_abilities = (array) $pines->info->abilities;
		else
			$section_abilities = (array) $pines->info->$cur_section->abilities;
		foreach ($section_abilities as $cur_ability) {
			if ( isset($_REQUEST[$cur_section]) && (array_search($cur_ability[0], $_REQUEST[$cur_section]) !== false) )
				$user->grant($cur_section.'/'.$cur_ability[0]);
			else
				$user->revoke($cur_section.'/'.$cur_ability[0]);
		}
	}
}

if (empty($user->username)) {
	$user->print_form();
	pines_notice('Please specify a username.');
	return;
}
if ($pines->config->com_user->max_username_length > 0 && strlen($user->username) > $pines->config->com_user->max_username_length) {
	$user->print_form();
	pines_notice("Usernames must not exceed {$pines->config->com_user->max_username_length} characters.");
	return;
}
$test = $pines->entity_manager->get_entity(
		array('class' => user, 'skip_ac' => true),
		array('&',
			'tag' => array('com_user', 'user'),
			'match' => array('username', '/^'.preg_quote($_REQUEST['username'], '/').'$/i')
		)
	);
if (isset($test->guid) && !$user->is($test)) {
	$user->print_form();
	pines_notice('There is already a user with that username. Please choose a different username.');
	return;
}
if (in_array('email', $pines->config->com_user->user_fields)) {
	$test = $pines->entity_manager->get_entity(
			array('class' => user, 'skip_ac' => true),
			array('&',
				'tag' => array('com_user', 'user'),
				'match' => array('email', '/^'.preg_quote($user->email, '/').'$/i')
			)
		);
	if (isset($test) && !$user->is($test)) {
		$user->print_form();
		pines_notice('There is already a user with that email address. Please use a different email.');
		return;
	}
}
if (array_diff(str_split($user->username), str_split($pines->config->com_user->valid_chars))) {
	$user->print_form();
	pines_notice($pines->config->com_user->valid_chars_notice);
	return;
}
if (!preg_match($pines->config->com_user->valid_regex, $user->username)) {
	$user->print_form();
	pines_notice($pines->config->com_user->valid_regex_notice);
	return;
}
if (empty($user->password) && !$pines->config->com_user->pw_empty) {
	$user->print_form();
	pines_notice('Please specify a password.');
	return;
}
if (in_array('pin', $pines->config->com_user->user_fields) && gatekeeper('com_user/assignpin') && !empty($user->pin)) {
	$test = $pines->entity_manager->get_entity(array('class' => user), array('&', 'tag' => array('com_user', 'user'), 'data' => array('pin', $user->pin)));
	if (isset($test) && !$user->is($test)) {
		$user->print_form();
		pines_notice('This PIN is already in use.');
		return;
	}

	if ($pines->config->com_user->min_pin_length > 0 && strlen($user->pin) < $pines->config->com_user->min_pin_length) {
		$user->print_form();
		pines_notice("User PINs must be at least {$pines->config->com_user->min_pin_length} characters.");
		return;
	}
}
if ($user->save()) {
	pines_notice('Saved user ['.$user->username.']');
	pines_log('Saved user ['.$user->username.']');
	if ($pines->config->com_user->confirm_email && $confirm_email) {
		// Send the verification email.
		$link = htmlspecialchars(pines_url('com_user', 'verifyuser', array('id' => $user->guid, 'type' => 'change', 'secret' => $user->new_email_secret), true));
		$link2 = htmlspecialchars(pines_url('com_user', 'verifyuser', array('id' => $user->guid, 'type' => 'cancelchange', 'secret' => $user->cancel_email_secret), true));
		$macros = array(
			'old_email' => htmlspecialchars($user->email),
			'new_email' => htmlspecialchars($user->new_email_address),
			'to_phone' => htmlspecialchars(format_phone($user->phone)),
			'to_fax' => htmlspecialchars(format_phone($user->fax)),
			'to_timezone' => htmlspecialchars($user->timezone),
			'to_address' => $user->address_type == 'us' ? htmlspecialchars("{$user->address_1} {$user->address_2}").'<br />'.htmlspecialchars("{$user->city}, {$user->state} {$user->zip}") : '<pre>'.htmlspecialchars($user->address_international).'</pre>'
		);
		$macros2 = $macros;
		$macros['verify_link'] = $link;
		$macros2['cancel_link'] = $link2;
		// Two emails, first goes to the new address for verification.
		// Second goes to the old email address to cancel the change.
		$recipient = (object) array(
			'email' => $user->new_email_address,
			'username' => $user->username,
			'name' => $user->name,
			'name_first' => $user->name_first,
			'name_last' => $user->name_last,
		);
		if ($pines->com_mailer->send_mail('com_user/verify_email_change', $macros, $recipient) && $pines->com_mailer->send_mail('com_user/cancel_email_change', $macros2, $user))
			pines_notice('A confirmation has been sent to your new email address. Please click the link provided to verify your address.');
		else
			pines_error('Couldn\'t send confirmation email.');
	}
} else
	pines_error('Error saving user. Do you have permission?');

if (gatekeeper('com_user/listusers')) {
	if ($user->has_tag('enabled'))
		pines_redirect(pines_url('com_user', 'listusers'));
	else
		pines_redirect(pines_url('com_user', 'listusers', array('enabled' => 'false')));
} else
	pines_redirect(pines_url());

?>