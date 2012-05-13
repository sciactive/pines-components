<?php
/**
 * com_modules_module class.
 *
 * @package Components\modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A module.
 *
 * @package Components\modules
 */
class com_modules_module extends entity {
	/**
	 * Load a module.
	 * @param int $id The ID of the module to load, 0 for a new module.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_modules', 'module');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->enabled = true;
		$this->show_title = true;
		$this->options = array();
		$this->conditions = array();
	}

	/**
	 * Create a new instance.
	 * @return com_modules_module The new instance.
	 */
	public static function factory() {
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
				return $this->name;
			case 'type':
				return 'module';
			case 'types':
				return 'modules';
			case 'url_edit':
				if (gatekeeper('com_modules/editmodule'))
					return pines_url('com_modules', 'module/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_modules/listmodules'))
					return pines_url('com_modules', 'module/list');
				break;
			case 'icon':
				return 'picon-view-file-columns';
		}
		return null;
	}

	/**
	 * Check the module's conditions.
	 *
	 * @return bool True if conditions are met, false otherwise.
	 */
	public function check_conditions() {
		global $pines;
		if (!$this->conditions)
			return true;
		// Check that all conditions are met.
		$pass = true;
		foreach ($this->conditions as $cur_type => $cur_value) {
			if (!$pines->depend->check($cur_type, $cur_value)) {
				$pass = false;
				break;
			}
		}
		return $pass;
	}

	/**
	 * Delete the module.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted module $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the module.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Print a form to edit the module.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_modules', 'module/form', 'content');
		$module->entity = $this;
		$module->modules = $pines->com_modules->module_types();

		return $module;
	}

	/**
	 * Print the module.
	 * @return module|null The printed module, or null on failure.
	 */
	public function print_module() {
		list ($component, $modname) = explode('/', $this->type, 2);
		$component = clean_filename($component);
		if (!file_exists("components/$component/modules.php"))
			return null;
		$include = include("components/$component/modules.php");
		$view = $include[$modname]['view'];
		$view_callback = $include[$modname]['view_callback'];
		if (!isset($view) && !isset($view_callback))
			return null;
		if (isset($include[$modname]['type']) && !preg_match('/\bmodule\b/', $include[$modname]['type']))
			return null;
		unset($include);
		if (isset($view))
			$module = new module($component, $view, $this->position, $this->order);
		else {
			$module = call_user_func($view_callback, $this->position, $this->order, $this->options);
			if (!$module)
				return null;
		}
		$module->title = $this->name;
		$module->show_title = $this->show_title;
		foreach ($this->options as $cur_option) {
			switch ($cur_option['name']) {
				case 'muid':
				case 'title':
				case 'note':
				case 'classes':
				case 'content':
				case 'component':
				case 'view':
				case 'position':
				case 'order':
				case 'show_title':
				case 'is_rendered':
				case 'data_container':
					break;
				default:
					$name = $cur_option['name'];
					if (substr($name, -2) == '[]') {
						$name = substr($name, 0, -2);
						if ((array) $module->$name !== $module->$name)
							$module->$name = array();
						array_push($module->$name, $cur_option['value']);
					} else
						$module->$name = $cur_option['value'];
					break;
			}
		}

		return $module;
	}
}

?>