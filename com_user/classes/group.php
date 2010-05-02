<?php
/**
 * group class.
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
 * Pines system groups.
 *
 * @package Pines
 * @subpackage com_user
 */
class group extends able_object implements group_interface {
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_user', 'group');
		// Defaults.
		$this->enabled = true;
		$this->abilities = array();
		$this->address_type = 'us';
		$this->attributes = array();
		if ($id > 0 || is_string($id)) {
			global $pines;
			if (is_int($id)) {
				$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			} else {
				$entity = $pines->entity_manager->get_entity(array('data' => array('groupname' => $id), 'tags' => $this->tags, 'class' => get_class($this)));
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

	public function is_descendent($group = null) {
		if (is_numeric($group))
			$group = group::factory((int) $group);
		if (!isset($group->guid))
			return false;
		// Check to see if the group is a descendent of the given group.
		if (!isset($this->parent))
			return false;
		if ($this->parent->is($group))
			return true;
		if ($this->parent->is_descendent($group))
			return true;
		return false;
	}

	/**
	 * @todo Fix this to delete only its children, who will delete their children.
	 */
	public function delete() {
		global $pines;
		$descendents = $pines->user_manager->get_group_descendents($this);
		foreach ($descendents as $cur_group) {
			if ( !$cur_group->delete() )
				return false;
		}
		if (!parent::delete())
			return false;
		pines_log("Deleted group $this->name [$this->groupname].", 'notice');
		return true;
	}

	public function save() {
		if (!isset($this->groupname))
			return false;
		return parent::save();
	}

	public function get_logo($rela_location = false) {
		global $pines;
		if (isset($this->logo))
			return ($rela_location ? $pines->config->rela_location : $pines->config->full_location)."{$pines->config->setting_upload}logos/{$this->logo}";
		if (isset($this->parent))
			return $this->parent->get_logo($rela_location);
		return ($rela_location ? $pines->config->rela_location : $pines->config->full_location)."{$pines->config->setting_upload}logos/default_logo.png";
	}

	public function print_form() {
		global $pines;
		$pines->com_pgrid->load();
		$module = new module('com_user', 'form_group', 'content');
		$module->entity = $this;
		$module->display_abilities = gatekeeper('com_user/abilities');
		$module->sections = array('system');
		$module->group_array = $pines->user_manager->get_group_array();
		foreach ($pines->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}
}

?>