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
	/**
	 * Load a user.
	 * @param int|string $id The ID or username of the user to load, 0 for a new user.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_user', 'user');
		// Defaults.
		$this->abilities = array();
		$this->groups = array();
		$this->inherit_abilities = true;
		$this->default_component = 'com_user';
		$this->address_type = 'us';
		if ($id > 0 || is_string($id)) {
			global $pines;
			if (is_int($id)) {
				$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			} else {
				$entity = $pines->entity_manager->get_entity(array('data' => array('username' => $id), 'tags' => $this->tags, 'class' => get_class($this)));
			}
			if (is_null($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	/**
	 * Create a new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	/**
	 * Delete the user.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted user $this->name [$this->username].", 'notice');
		return true;
	}

	/**
	 * Save the user.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->username))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the user.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_user', 'form_user', 'content');
		$module->entity = $this;
		$module->display_groups = gatekeeper("com_user/assigngroup");
		$module->display_abilities = gatekeeper("com_user/abilities");
		$module->display_default_components = gatekeeper("com_user/default_component");
		$module->sections = array('system');
		$module->group_array = $pines->user_manager->get_group_array();
		$module->default_components = $pines->user_manager->get_default_component_array();
		foreach ($pines->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}

	/**
	 * Add the user to a (secondary) group.
	 *
	 * @param group $group The group.
	 * @return mixed True if the user is already in the group. The resulting array of groups if the user was not.
	 */
	public function addgroup($group) {
		if ( !$group->in_array($this->groups) ) {
			$this->groups[] = $group;
			return $this->groups;
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
	 * @param group $group The group.
	 * @return mixed True if the user wasn't in the group. The resulting array of groups if the user was.
	 */
	public function delgroup($group) {
		if ( $group->in_array($this->groups) ) {
			foreach ($this->groups as $key => $cur_group) {
				if ($group->is($cur_group))
					unset($this->groups[$key]);
			}
			return $this->groups;
		} else {
			return true;
		}
	}

	/**
	 * Check whether the user is in a (primary or secondary) group.
	 *
	 * @param mixed $group The group, or the group's GUID.
	 * @return bool True or false.
	 */
	public function ingroup($group = null) {
		if (is_numeric($group))
			$group = group::factory((int) $group);
		if (is_null($group->guid))
			return false;
		return ($group->in_array($this->groups) || $group->is($this->group));
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
		if (!isset($this->salt))
			$this->salt = md5(rand());
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
		global $pines;
		if (!empty($this->timezone))
			return $return_date_time_zone_object ? new DateTimeZone($this->timezone) : $this->timezone;
		if (isset($this->group)) {
			if (!empty($this->group->timezone))
				return $return_date_time_zone_object ? new DateTimeZone($this->group->timezone) : $this->group->timezone;
		}
		foreach($this->groups as $cur_group) {
			if (!empty($cur_group->timezone))
				return $return_date_time_zone_object ? new DateTimeZone($cur_group->timezone) : $cur_group->timezone;
		}
		return $return_date_time_zone_object ? new DateTimeZone($pines->config->timezone) : $pines->config->timezone;
	}
}

?>