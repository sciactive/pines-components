<?php
/**
 * com_dash_dashboard class.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A dashboard.
 *
 * @package Pines
 * @subpackage com_dash
 */
class com_dash_dashboard extends entity {
	/**
	 * Load an dashboard.
	 * @param int $id The ID of the dashboard to load, 0 for a new dashboard.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		global $pines;
		$this->add_tag('com_dash', 'dashboard');
		if ($id > 0) {
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		$button_types = $pines->com_dash->button_types();
		$default_buttons = array();
		foreach ($button_types as $cur_component => $cur_button_set) {
			$added_this_component = false;
			foreach ($cur_button_set as $cur_button_name => $cur_button) {
				if (!$cur_button['default'])
					continue;
				// Check its conditions.
				foreach ((array) $cur_button['depends'] as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value))
						continue 2;
				}
				// Add a separator for this component's buttons.
				if (!$added_this_component)
					$default_buttons[] = 'separator';
				$added_this_component = true;
				// Add it to the buttons.
				$default_buttons[] = array(
					'component' => $cur_component,
					'button' => $cur_button_name
				);
			}
		}
		// Remove the first separator, since it doesn't separate anything.
		if ($default_buttons)
			$default_buttons = array_slice($default_buttons, 1);
		// Defaults.
		$widget_types = $pines->com_dash->widget_types();
		$default_widgets = array(
			0 => array(),
			1 => array(),
			2 => array()
		);
		// Use a counter to put default widgets into separate columns.
		$counter = 0;
		foreach ($widget_types as $cur_component => $cur_widget_set) {
			foreach ($cur_widget_set as $cur_widget_name => $cur_widget) {
				if (!$cur_widget['widget']['default'])
					continue;
				// Check its conditions.
				foreach ((array) $cur_widget['widget']['depends'] as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value))
						continue 2;
				}
				// Add it to one of the 3 columns.
				$default_widgets[$counter % 3][uniqid()] = array(
					'component' => $cur_component,
					'widget' => $cur_widget_name,
					'options' => array()
				);
				// Increase the counter.
				$counter++;
			}
		}
		// Build the default tabs.
		$this->tabs = array(
			uniqid() => array(
				'name' => 'Home',
				'buttons' => $default_buttons,
				'columns' => array(
					uniqid() => array(
						'size' => 1/2,
						'widgets' => $default_widgets[0]
					),
					uniqid() => array(
						'size' => 1/4,
						'widgets' => $default_widgets[1]
					),
					uniqid() => array(
						'size' => 1/4,
						'widgets' => $default_widgets[2]
					)
				)
			),
		);
	}

	/**
	 * Create a new instance.
	 * @return com_dash_dashboard The new instance.
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
	 * Print a form to edit the dashboard.
	 * @return module The form's module.
	 */
	public function print_form() {
		$module = new module('com_dash', 'dashboard/form', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print the dashboard view.
	 * @param string $tab The key of the tab to show by default.
	 * @return module The dashboard's module.
	 */
	public function print_dashboard($tab = null) {
		$module = new module('com_dash', 'dashboard/view', 'content');
		$module->entity = $this;
		if ($tab)
			$module->selected_tab = $tab;

		return $module;
	}

	/**
	 * Print a dashboard tab.
	 * @param string $key The key of the tab.
	 * @return module The tab's module.
	 */
	public function print_tab($key) {
		if (!isset($this->tabs[$key]))
			return false;
		$module = new module('com_dash', 'dashboard/tab', 'content');
		$module->key = $key;
		$module->tab = $this->tabs[$key];

		return $module;
	}

	/**
	 * Print a dashboard tab editor.
	 * @param string $key The key of the tab.
	 * @return module The tab's module.
	 */
	public function edit_tab($key = '') {
		if (!isset($this->tabs[$key]))
			$key = '';
		$module = new module('com_dash', 'dashboard/edittab', 'content');
		$module->key = $key;
		$module->tab = $this->tabs[$key];

		return $module;
	}

	/**
	 * Get a widget's entry in whatever tab/column it's in.
	 * @param string $key The widget's key.
	 * @return array The widget's entry array.
	 */
	public function &widget($key) {
		foreach ($this->tabs as &$cur_tab) {
			foreach ($cur_tab['columns'] as &$cur_col) {
				if (!isset($cur_col['widgets'][$key]))
					continue;
				return $cur_col['widgets'][$key];
			}
			unset($cur_col);
		}
		unset($cur_tab);
	}

	/**
	 * Get a widget's location in tab/column keys.
	 * @param string $key The widget's key.
	 * @return array An array with two entries 'tab' and 'column' containing the respective keys.
	 */
	public function widget_location($key) {
		foreach ($this->tabs as $tab_key => $cur_tab) {
			foreach ($cur_tab['columns'] as $col_key => $cur_col) {
				if (!isset($cur_col['widgets'][$key]))
					continue;
				return array('tab' => $tab_key, 'column' => $col_key);
			}
		}
		return array();
	}
}

?>