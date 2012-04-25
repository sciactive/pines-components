<?php
/**
 * com_dash class.
 *
 * @package Components
 * @subpackage dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_dash main class.
 *
 * @package Components
 * @subpackage dash
 */
class com_dash extends component {
	/**
	 * A cache of the included buttons.php files.
	 * @var array
	 * @access private
	 */
	private $button_include_cache = array();
	/**
	 * A cache of the included modules.php files.
	 * @var array
	 * @access private
	 */
	private $module_include_cache = array();

	/**
	 * Get a button's definition array.
	 * @param array $button The button entry array.
	 * @return array The button definition.
	 */
	public function get_button_def($button) {
		$component = clean_filename($button['component']);
		$name = $button['button'];
		if (isset($this->button_include_cache[$component])) {
			$include = $this->button_include_cache[$component];
		} else {
			if (!file_exists("components/$component/buttons.php"))
				return null;
			$include = include("components/$component/buttons.php");
			$this->button_include_cache[$component] = $include;
		}
		return $include[$name];
	}

	/**
	 * Get a widget's definition array.
	 * @param array $widget The widget entry array.
	 * @return array The widget definition.
	 */
	public function get_widget_def($widget) {
		$component = clean_filename($widget['component']);
		$name = $widget['widget'];
		if (isset($this->module_include_cache[$component])) {
			$include = $this->module_include_cache[$component];
		} else {
			if (!file_exists("components/$component/modules.php"))
				return null;
			$include = include("components/$component/modules.php");
			$this->module_include_cache[$component] = $include;
		}
		return $include[$name];
	}

	/**
	 * Show the dashboard manager.
	 * @return module The module.
	 */
	public function manage() {
		global $pines;

		$module = new module('com_dash', 'manage/list', 'content');
		$module->dashboards = $pines->entity_manager->get_entities(
				array('class' => com_dash_dashboard),
				array('&',
					'tag' => array('com_dash', 'dashboard')
				)
			);

		if ( empty($module->dashboards) )
			pines_notice('No dashboards found.');

		return $module;
	}

	/**
	 * Get an array of all the widget types.
	 * 
	 * Goes through each component's modules.php file.
	 *
	 * @return array Widget types.
	 */
	public function widget_types() {
		global $pines;
		$return = array();
		foreach ($pines->components as $cur_component) {
			if (strpos($cur_component, 'tpl_') === 0)
				continue;
			if (!file_exists("components/$cur_component/modules.php"))
				continue;
			$modules = include("components/$cur_component/modules.php");
			if (!$modules || (array) $modules !== $modules)
				continue;
			foreach ($modules as $key => $cur_module) {
				if (!isset($cur_module['type']) || !preg_match('/\bwidget\b/', $cur_module['type']))
					unset($modules[$key]);
			}
			if ($modules)
				$return[$cur_component] = $modules;
		}
		return $return;
	}

	/**
	 * Get an array of all the button types.
	 * 
	 * Goes through each component's buttons.php file.
	 *
	 * @return array Button types.
	 */
	public function button_types() {
		global $pines;
		$return = array();
		foreach ($pines->components as $cur_component) {
			if (strpos($cur_component, 'tpl_') === 0)
				continue;
			if (!file_exists("components/$cur_component/buttons.php"))
				continue;
			$buttons = include("components/$cur_component/buttons.php");
			if (!$buttons || (array) $buttons !== $buttons)
				continue;
			if ($buttons)
				$return[$cur_component] = $buttons;
		}
		return $return;
	}

	/**
	 * Return the quick dash module.
	 * @param string $position The module's position.
	 * @param int $order The module's order.
	 * @param array $options The module's options.
	 * @return module|bool The module on success, false on failure.
	 */
	public function quick_dash_module($position, $order, $options) {
		if (!gatekeeper('com_dash/dash') || !isset($_SESSION['user']->dashboard->guid))
			return false;
		$module = new module('com_dash', 'modules/quick_dash', $position, $order);
		$module->entity = $_SESSION['user']->dashboard;
		return $module;
	}
}

?>