<?php
/**
 * user class.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Pines system users.
 *
 * @package Pines
 * @subpackage com_user
 */
class user extends able_object implements user_interface {
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_user', 'user');
		// Defaults.
		$this->enabled = true;
		$this->abilities = array();
		$this->groups = array();
		$this->inherit_abilities = true;
		$this->default_component = 'com_user';
		$this->address_type = 'us';
		$this->attributes = array();
		if ($id > 0 || is_string($id)) {
			global $pines;
			if (is_int($id)) {
				$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			} else {
				$entity = $pines->entity_manager->get_entity(array('data' => array('username' => $id), 'tags' => $this->tags, 'class' => get_class($this)));
			}
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
		}
	}

	public static function factory($id = 0) {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$entity = new $class($args[0]);
		$pines->hook->hook_object($entity, $class.'->', false);
		return $entity;
	}

	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted user $this->name [$this->username].", 'notice');
		return true;
	}

	public function save() {
		if (!isset($this->username))
			return false;
		return parent::save();
	}

	public function print_form() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_user', 'form_user', 'content');
		$module->entity = $this;
		$module->display_pin = gatekeeper('com_user/assignpin');
		$module->display_groups = gatekeeper('com_user/assigngroup');
		$module->display_abilities = gatekeeper('com_user/abilities');
		$module->display_default_components = gatekeeper('com_user/default_component');
		$module->sections = array('system');
		$module->group_array = $pines->user_manager->get_group_array();
		$module->default_components = $pines->user_manager->get_default_component_array();
		foreach ($pines->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}
	
	public function add_group($group) {
		if ( !$group->in_array($this->groups) ) {
			$this->groups[] = $group;
			return $this->groups;
		} else {
			return true;
		}
	}

	public function check_password($password) {
		return ($this->password == md5($password.$this->salt));
	}

	public function del_group($group) {
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

	public function in_group($group = null) {
		if (is_numeric($group))
			$group = group::factory((int) $group);
		if (!isset($group->guid))
			return false;
		return ($group->in_array($this->groups) || $group->is($this->group));
	}

	public function is_descendent($group = null) {
		if (is_numeric($group))
			$group = group::factory((int) $group);
		if (!isset($group->guid))
			return false;
		// Check to see if the user is in a descendent group of the given group.
		if (isset($this->group) && $this->group->is_descendent($group))
			return true;
		foreach ($this->groups as $cur_group) {
			if ($cur_group->is_descendent($group))
				return true;
		}
		return false;
	}

	/**
	 * This function first checks to see if the user already has a salt. If not,
	 * one will be generated.
	 */
	public function password($password) {
		if (!isset($this->salt))
			$this->salt = md5(rand());
		return $this->password = md5($password.$this->salt);
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
		if (isset($this->group) && !empty($this->group->timezone))
			return $return_date_time_zone_object ? new DateTimeZone($this->group->timezone) : $this->group->timezone;
		foreach($this->groups as $cur_group) {
			if (!empty($cur_group->timezone))
				return $return_date_time_zone_object ? new DateTimeZone($cur_group->timezone) : $cur_group->timezone;
		}
		return $return_date_time_zone_object ? new DateTimeZone($pines->config->timezone) : $pines->config->timezone;
	}

	public function register() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_user', 'form_register', 'content');
		$module->entity = $this;
		foreach ($pines->components as $cur_component)
			$module->sections[] = $cur_component;

		return $module;
	}

	// This will display a notice telling the user to verify their e-mail.
	public function registered() {
		global $pines;
		$notice = new module('com_user', 'note_register', left);
		return $notice; // $pines->com_user->print_login();
	}
}

?>