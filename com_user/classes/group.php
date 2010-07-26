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
		$this->conditions = array();
		$this->address_type = 'us';
		$this->attributes = array();
		if ($id > 0 || (string) $id === $id) {
			global $pines;
			if ((int) $id === $id) {
				$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			} else {
				$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'data' => array('groupname', $id), 'tag' => $this->tags));
			}
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data(), $entity->get_sdata());
		}
	}

	/**
	 * Create a new instance.
	 *
	 * @param int $id The ID of the group to load, 0 for a new group.
	 * @return group A group instance.
	 */
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

	public function delete() {
		global $pines;
		$entities = $pines->entity_manager->get_entities(
				array('class' => group),
				array('&',
					'ref' => array('parent', $this),
					'tag' => array('com_user', 'group')
				)
			);
		foreach ($entities as $cur_group) {
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

	public function get_children() {
		global $pines;
		$return = (array) $pines->entity_manager->get_entities(
				array('class' => group),
				array('&',
					'data' => array('enabled', true),
					'ref' => array('parent', $this),
					'tag' => array('com_user', 'group')
				)
			);
		return $return;
	}

	public function get_descendents() {
		global $pines;
		$return = array();
		$entities = $pines->entity_manager->get_entities(
				array('class' => group),
				array('&',
					'data' => array('enabled', true),
					'ref' => array('parent', $this),
					'tag' => array('com_user', 'group')
				)
			);
		foreach ($entities as $entity) {
			$child_array = $entity->get_descendents();
			$return = array_merge($return, array($entity), $child_array);
		}
		return $return;
	}

	public function get_level() {
		$group = $this;
		$level = 0;
		while (isset($group->parent) && $group->parent->enabled) {
			$level++;
			$group = $group->parent;
		}
		return $level;
	}

	public function get_logo($full = false) {
		global $pines;
		if (isset($this->logo))
			return $full ? $pines->uploader->url($pines->uploader->real($this->logo), true) : $this->logo;
		if (isset($this->parent) && $this->parent->enabled)
			return $this->parent->get_logo($full);
		return ($full ? $pines->config->full_location : $pines->config->rela_location)."{$pines->config->upload_location}logos/default_logo.png";
	}

	public function get_users($descendents = false) {
		global $pines;
		if ($descendents) {
			$groups = $this->get_descendents();
		} else {
			$groups = array();
		}
		$groups[] = $this;
		$return = $pines->entity_manager->get_entities(
				array('class' => user),
				array('|',
					'ref' => array(
						array('group', $groups),
						array('groups', $groups)
					)
				),
				array('&',
					'data' => array('enabled', true),
					'tag' => array('com_user', 'user')
				)
			);
		return $return;
	}

	public function print_form() {
		global $pines;
		$module = new module('com_user', 'form_group', 'content');
		$module->entity = $this;
		$module->display_default = gatekeeper('com_user/defaultgroups');
		$module->display_abilities = gatekeeper('com_user/abilities');
		$module->display_conditions = gatekeeper('com_user/conditions');
		$module->sections = array('system');
		$module->group_array = $pines->user_manager->get_groups();
		foreach ($pines->components as $cur_component) {
			$module->sections[] = $cur_component;
		}

		return $module;
	}
}

?>