<?php
/**
 * user class.
 *
 * @package Components\user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Pines system users.
 *
 * @package Components\user
 * @property int $guid The GUID of the user.
 * @property string $username The user's username.
 * @property string $name_first The user's first name.
 * @property string $name_middle The user's middle name.
 * @property string $name_last The user's last name.
 * @property string $name The user's full name.
 * @property string $email The user's email address.
 * @property string $phone The user's telephone number.
 * @property string $address_type The user's address type. "us" or "international".
 * @property string $address_1 The user's address line 1 for US addresses.
 * @property string $address_2 The user's address line 2 for US addresses.
 * @property string $city The user's city for US addresses.
 * @property string $state The user's state abbreviation for US addresses.
 * @property string $zip The user's ZIP code for US addresses.
 * @property string $address_international The user's full address for international addresses.
 * @property string $pin The user's PIN.
 * @property group $group The user's primary group.
 * @property array $groups The user's secondary groups.
 * @property bool $inherit_abilities Whether the user should inherit the abilities of his groups.
 */
class user extends able_object implements user_interface {
	/**
	 * Used to save the current email to resend verification if it changes.
	 * @access protected
	 * @var string $verify_email
	 */
	protected $verify_email = '';

	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_user', 'user', 'enabled');
		if ($id > 0 || (string) $id === $id) {
			global $pines;
			if ((int) $id === $id)
				$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => array('com_user', 'user')));
			else
				$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'tag' => array('com_user', 'user'), 'strict' => array('username', (string) $id)));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				if (isset($this->secret))
					$this->verify_email = $this->email;
				return;
			}
		}
		// Defaults.
		$this->abilities = array();
		$this->groups = array();
		$this->inherit_abilities = true;
		$this->address_type = 'us';
		$this->addresses = array();
		$this->attributes = array();
	}

	/**
	 * Create a new instance.
	 *
	 * @param int|string $id The ID or username of the user to load, 0 for a new user.
	 * @return user A user instance.
	 */
	public static function factory($id = 0) {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	public function info($type) {
		switch ($type) {
			case 'name':
				return "$this->name [$this->username]";
			case 'type':
				return 'user';
			case 'types':
				return 'users';
			case 'url_edit':
				if (gatekeeper('com_user/edituser'))
					return pines_url('com_user', 'edituser', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_user/listusers'))
					return pines_url('com_user', 'listusers');
				break;
			case 'icon':
				return 'picon-user-identity';
			case 'avatar':
				$proto = $_SERVER['HTTPS'] ? 'https' : 'http';
				if (!isset($this->email) || empty($this->email))
					return $proto.'://secure.gravatar.com/avatar/?d=mm&s=40';
				return $proto.'://secure.gravatar.com/avatar/'.md5(strtolower(trim($this->email))).'?d=identicon&s=40';
			default:
				return parent::info($type);
		}
		return null;
	}

	/**
	 * Return the entity helper module.
	 * @return module Entity helper module.
	 */
	public function helper() {
		return new module('com_user', 'helper_user');
	}

	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted user $this->name [$this->username].", 'notice');
		return true;
	}

	public function disable() {
		$this->remove_tag('enabled');
	}

	public function enable() {
		$this->add_tag('enabled');
	}

	public function save() {
		if (!isset($this->username))
			return false;
		if (isset($this->guid) && isset($this->secret) && !empty($this->verify_email) && $this->verify_email != $this->email)
			$send_verification = true;
		$return = parent::save();
		if ($return && $send_verification) {
			// The email has changed, so send a new verification email.
			if ($this->send_email_verification())
				pines_notice('New verification email sent to the new email address.');
			else
				pines_error('Couldn\'t send verification email to new email address.');
		}
		return $return;
	}

	/**
	 * Send the user an email verification link.
	 * 
	 * The user must be a new user, with a GUID and a secret.
	 * 
	 * @param string $url The URL that the user is taken to after verification.
	 * @return bool True on success, false on failure.
	 */
	public function send_email_verification($url = '') {
		global $pines;
		if (!isset($this->guid) || !isset($this->secret))
			return false;
		$params = array('id' => $this->guid, 'type' => 'register', 'secret' => $this->secret);
		if (!empty($url))
			$params['url'] = $url;
		$link = htmlspecialchars(pines_url('com_user', 'verifyuser', $params, true));
		$macros = array(
			'verify_link' => $link,
			'to_phone' => htmlspecialchars(format_phone($this->phone)),
			'to_fax' => htmlspecialchars(format_phone($this->fax)),
			'to_timezone' => htmlspecialchars($this->timezone),
			'to_address' => $this->address_type == 'us' ? htmlspecialchars("{$this->address_1} {$this->address_2}").'<br />'.htmlspecialchars("{$this->city}, {$this->state} {$this->zip}") : '<pre>'.htmlspecialchars($this->address_international).'</pre>'
		);
		return $pines->com_mailer->send_mail('com_user/verify_email', $macros, $this);
	}

	public function print_form() {
		global $pines;
		$module = new module('com_user', 'form_user', 'content');
		$module->entity = $this;
		$module->display_username = gatekeeper('com_user/usernames');
		$module->display_enable = gatekeeper('com_user/enabling');
		$module->display_email_verified = gatekeeper('com_user/edituser');
		$module->display_password = gatekeeper('com_user/passwords');
		$module->display_pin = gatekeeper('com_user/assignpin');
		$module->display_groups = gatekeeper('com_user/assigngroup');
		$module->display_abilities = gatekeeper('com_user/abilities');
		$module->sections = array('system');
		$highest_parent = $pines->config->com_user->highest_primary;
		if ($highest_parent == 0)
			$module->group_array_primary = $pines->user_manager->get_groups();
		elseif ($highest_parent < 0)
			$module->group_array_primary = array();
		else {
			$highest_parent = group::factory($highest_parent);
			if (!isset($highest_parent->guid))
				$module->group_array_primary = array();
			else
				$module->group_array_primary = $highest_parent->get_descendants();
		}
		$highest_parent = $pines->config->com_user->highest_secondary;
		if ($highest_parent == 0)
			$module->group_array_secondary = $pines->user_manager->get_groups();
		elseif ($highest_parent < 0)
			$module->group_array_secondary = array();
		else {
			$highest_parent = group::factory($highest_parent);
			if (!isset($highest_parent->guid))
				$module->group_array_secondary = array();
			else
				$module->group_array_secondary = $highest_parent->get_descendants();
		}
		foreach ($pines->components as $cur_component)
			$module->sections[] = $cur_component;

		return $module;
	}

	/**
	 * Print a form to change the user's password.
	 * 
	 * @return module The form's module.
	 */
	public function print_form_password() {
		$module = new module('com_user', 'form_password', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a registration form for the user to fill out.
	 *
	 * @return module The form's module.
	 */
	public function print_register() {
		global $pines;
		$module = new module('com_user', 'form_register', 'content');
		$module->entity = $this;
		foreach ($pines->components as $cur_component)
			$module->sections[] = $cur_component;

		return $module;
	}
	
	public function add_group($group) {
		if ( !$group->in_array((array) $this->groups) ) {
			$this->groups[] = $group;
			return $this->groups;
		} else
			return true;
	}

	public function check_password($password) {
		global $pines;
		if (!isset($this->salt)) {
			$pass = ($this->password == $password);
			$cur_type = 'salt';
		} elseif ($this->salt == '7d5bc9dc81c200444e53d1d10ecc420a') {
			$pass = ($this->password == md5($password.$this->salt));
			$cur_type = 'digest';
		} else {
			$pass = ($this->password == md5($password.$this->salt));
			$cur_type = 'salt';
		}
		if ($pass && $cur_type != $pines->config->com_user->pw_method) {
			switch ($pines->config->com_user->pw_method) {
				case 'plain':
					unset($this->salt);
					$this->password = $password;
					break;
				case 'salt':
					$this->salt = md5(rand());
					$this->password = md5($password.$this->salt);
					break;
				case 'digest':
				default:
					$this->salt = '7d5bc9dc81c200444e53d1d10ecc420a';
					$this->password = md5($password.$this->salt);
					break;
			}
			$this->save();
		}
		return $pass;
	}

	/**
	 * Check the given client hash and server challenge using SAWASC.
	 *
	 * @param string $ClientHash The hash provided by the client.
	 * @param string $ServerCB The challenge block generated by the server.
	 * @param string $algo Hash algorithm. Check hash_algos().
	 * @return bool True if the hashes match, otherwise false.
	 */
	public function check_sawasc($ClientHash, $ServerCB, $algo) {
		if ($this->salt == '7d5bc9dc81c200444e53d1d10ecc420a')
			$input = $this->password;
		else
			$input = md5($this->password.'7d5bc9dc81c200444e53d1d10ecc420a');
		$ServerComb = $ServerCB.$input;
		$ServerHash = hash($algo, $ServerComb);
		return ($ClientHash === $ServerHash);
	}

	public function del_group($group) {
		if ( $group->in_array((array) $this->groups) ) {
			foreach ((array) $this->groups as $key => $cur_group) {
				if ($group->is($cur_group))
					unset($this->groups[$key]);
			}
			return $this->groups;
		} else
			return true;
	}

	public function in_group($group = null) {
		if (is_numeric($group))
			$group = group::factory((int) $group);
		if (!isset($group->guid))
			return false;
		return ($group->in_array((array) $this->groups) || $group->is($this->group));
	}

	public function is_descendant($group = null) {
		if (is_numeric($group))
			$group = group::factory((int) $group);
		if (!isset($group->guid))
			return false;
		// Check to see if the user is in a descendant group of the given group.
		if (isset($this->group->guid) && $this->group->is_descendant($group))
			return true;
		foreach ((array) $this->groups as $cur_group) {
			if ($cur_group->is_descendant($group))
				return true;
		}
		return false;
	}

	public function is_descendent($group = null) {
		return $this->is_descendant($group);
	}

	public function password($password) {
		global $pines;
		switch ($pines->config->com_user->pw_method) {
			case 'plain':
				unset($this->salt);
				return $this->password = $password;
				break;
			case 'salt':
				$this->salt = md5(rand());
				return $this->password = md5($password.$this->salt);
				break;
			case 'digest':
			default:
				$this->salt = '7d5bc9dc81c200444e53d1d10ecc420a';
				return $this->password = md5($password.$this->salt);
				break;
		}
	}

	/**
	 * First checks if the user has a timezone set, then the primary group, then
	 * the secondary groups, then the system default. The first timezone found
	 * is returned.
	 */
	public function get_timezone($return_date_time_zone_object = false) {
		global $pines;
		if (!empty($this->timezone))
			return $return_date_time_zone_object ? new DateTimeZone($this->timezone) : $this->timezone;
		if (isset($this->group->guid) && !empty($this->group->timezone))
			return $return_date_time_zone_object ? new DateTimeZone($this->group->timezone) : $this->group->timezone;
		foreach((array) $this->groups as $cur_group) {
			if (!empty($cur_group->timezone))
				return $return_date_time_zone_object ? new DateTimeZone($cur_group->timezone) : $cur_group->timezone;
		}
		return $return_date_time_zone_object ? new DateTimeZone($pines->config->timezone) : $pines->config->timezone;
	}
}

?>