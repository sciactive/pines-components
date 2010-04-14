<?php
/**
 * configurator_component class.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A configurable component.
 *
 * @package Pines
 * @subpackage com_configure
 */
class configurator_component extends p_base implements configurator_component_interface {
	/**
	 * The configuration defaults.
	 * @var array
	 */
	public $defaults = array();
	/**
	 * The current configuration.
	 * @var array
	 */
	public $config = array();
	/**
	 * The current configuration in an array with key => values.
	 * @var array
	 */
	public $config_keys = array();
	/**
	 * The info object of the component.
	 * @var object
	 */
	public $info;
	/**
	 * The component.
	 * @var string
	 */
	public $name;
	/**
	 * The component.
	 * @var string
	 */
	protected $component;
	/**
	 * The defaults file.
	 * @var string
	 */
	protected $defaults_file;
	/**
	 * The config file.
	 * @var string
	 */
	protected $config_file;
	/**
	 * The info file.
	 * @var string
	 */
	protected $info_file;

	/**
	 * Load a component's configuration and info.
	 * @param string $component The component to load.
	 */
	public function __construct($component) {
		global $pines;
		if (!key_exists($component, $pines->com_configure->component_files))
			return;
		$this->component = $component;
		$this->name = $component;
		$this->defaults_file = $pines->com_configure->component_files[$component]['defaults'];
		$this->config_file = $pines->com_configure->component_files[$component]['config'];
		$this->info_file = $pines->com_configure->component_files[$component]['info'];
		if (file_exists($this->defaults_file))
			$this->defaults = include($this->defaults_file);
		if (file_exists($this->config_file)) {
			$this->config = include($this->config_file);
			foreach ($this->config as $cur_val) {
				$this->config_keys[$cur_val['name']] = $cur_val['value'];
			}
		}
		if (file_exists($this->info_file))
			$this->info = (object) include($this->info_file);
	}

	/**
	 * Create a new instance.
	 * @param string $component The component to load.
	 */
	public static function factory($component) {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$object = new $class($args[0]);
		$pines->hook->hook_object($object, $class.'->', false);
		return $object;
	}

	/**
	 * Get a full config array. (With defaults replaced.)
	 * @return array The array.
	 */
	public function get_full_config_array() {
		$array = $this->defaults;
		foreach ($array as &$cur_val) {
			if (key_exists($cur_val['name'], $this->config_keys))
				$cur_val['value'] = $this->config_keys[$cur_val['name']];
		}
		return $array;
	}

	/**
	 * Check if a component is configurable.
	 * @return bool True or false.
	 */
	public function is_configurable() {
		return !empty($this->defaults);
	}

	/**
	 * Check if a component is disabled.
	 * @return bool True or false.
	 */
	public function is_disabled() {
		global $pines;
		return ($this->component != 'system' && in_array($this->component, array_diff($pines->all_components, $pines->components)));
	}

	/**
	 * Print a form to edit the configuration.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->com_ptags->load();
		$module = new module('com_configure', 'edit', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Print a view of the configuration.
	 * @return module The view's module.
	 */
	public function print_view() {
		global $pines;
		$module = new module('com_configure', 'view', 'content');
		$module->entity = $this;

		return $module;
	}

	/**
	 * Write the configuration to the config file.
	 * @return bool True on success, false on failure.
	 */
	public function save_config() {
		$file_contents = sprintf(
			"<?php\ndefined('P_RUN') or die('Direct access prohibited');\nreturn %s;\n?>",
			var_export($this->config, true)
		);
		return file_put_contents($this->config_file, $file_contents);
	}

	/**
	 * Configure this component load only user configurable settings.
	 *
	 * The current settings will be updated to reflect the settings of
	 * $usergroup.
	 *
	 * @var user|group $usergroup The user or group which is being configured.
	 */
	public function set_peruser($usergroup = null) {
		if (!isset($usergroup) && isset($_SESSION['user']))
			$usergroup = $_SESSION['user'];
		$this->peruser = true;
		foreach ($this->defaults as $key => &$cur_entry) {
			if (!$cur_entry['peruser']) {
				unset($this->defaults[$key]);
			} else {
				if (isset($this->config_keys[$cur_entry['name']]))
					$cur_entry['value'] = $this->config_keys[$cur_entry['name']];
			}
		}
		if (!isset($usergroup)) {
			$this->config = array();
			$this->config_keys = array();
			return;
		}
		// Load the config for the user/group.
		$this->config = array();
		$this->config_keys = array();
		$this->user = $usergroup;
	}
}

?>