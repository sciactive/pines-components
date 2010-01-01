<?php
/**
 * user class.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Pines system users.
 *
 * @package Pines
 * @subpackage com_user
 */
class user extends able_entity {
	public function __construct() {
		parent::__construct();
		$this->add_tag('com_user', 'user');
		$this->abilities = array();
		$this->groups = array();
		$this->inherit_abilities = true;
		$this->default_component = 'com_user';
	}

	/**
	 * Add the user to a (secondary) group.
	 *
	 * @param int $id The GUID of the group.
	 * @return mixed True if the user is already in the group. The resulting array of group IDs if the user was not.
	 */
	public function addgroup($id) {
		if ( !in_array($id, $this->groups) ) {
			return $this->groups = array_merge(array($id), $this->groups);
		} else {
			return true;
		}
	}

	/**
	 * Check if the password given is the correct password for the user's
	 * account.
	 *
	 * @param string $password The password in question.
	 * @return bool True or false.
	 */
	public function check_password($password) {
		return ($this->password == md5($password.$this->salt));
	}

	/**
	 * Remove the user from a (secondary) group.
	 *
	 * @param int $id The GUID of the group.
	 * @return mixed True if the user wasn't in the group. The resulting array of group IDs if the user was.
	 */
	public function delgroup($id) {
		if ( in_array($id, $this->groups) ) {
			return $this->groups = array_values(array_diff($this->groups, array($id)));
		} else {
			return true;
		}
	}

	/**
	 * Check whether the user is in a (primary or secondary) group.
	 *
	 * @param int $id The GUID of the group.
	 * @return bool True or false.
	 */
	public function ingroup($id) {
		return (in_array($id, $this->groups) || ($id == $this->gid));
	}

	/**
	 * Change the user's password.
	 *
	 * This function first checks to see if the user already has a salt. If not,
	 * one will be generated.
	 *
	 * @param string $password The new password.
	 * @return string The resulting MD5 sum which is stored in the entity.
	 */
	public function password($password) {
		if (!isset($this->salt)) $this->salt = md5(rand());
		return $this->password = md5($password.$this->salt);
	}

	/**
	 * Return the user's timezone.
	 *
	 * First checks if the user has a timezone set, then the primary group, then
	 * the secondary groups, then the system default. The first timezone found
	 * is returned.
	 *
	 * @param bool $return_date_time_zone_object Whether to return an object of the DateTimeZone class, instead of an identifier string.
	 * @return string|DateTimeZone The timezone identifier or the DateTimeZone object.
	 */
	public function get_timezone($return_date_time_zone_object = false) {
		global $config;
		if (!empty($this->timezone))
			return $return_date_time_zone_object ? new DateTimeZone($this->timezone) : $this->timezone;
		if (isset($this->gid)) {
			$group = $config->user_manager->get_group($this->gid);
			if (!empty($group->timezone))
				return $return_date_time_zone_object ? new DateTimeZone($group->timezone) : $group->timezone;
		}
		if (is_array($this->groups)) {
			foreach($this->groups as $cur_group_id) {
				$group = $config->user_manager->get_group($cur_group_id);
				if (!empty($group->timezone))
					return $return_date_time_zone_object ? new DateTimeZone($group->timezone) : $group->timezone;
			}
		}
		return $return_date_time_zone_object ? new DateTimeZone($config->timezone) : $config->timezone;
	}
}

?>