<?php
/**
 * com_configure_component class.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A configurable component.
 *
 * @package Pines
 * @subpackage com_configure
 */
class com_configure_component extends p_base {
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
	 * Load a component's configuration.
	 * @param string $component The name of the component to load.
	 */
	public function __construct($component) {
		global $pines;
		if (!key_exists($component, $pines->com_configure->config_files))
			return;
		$this->component = $component;
		$this->name = $component;
		$this->defaults_file = $pines->com_configure->config_files[$component]['defaults'];
		$this->config_file = $pines->com_configure->config_files[$component]['config'];
		if (file_exists($this->defaults_file))
			$this->defaults = include($this->defaults_file);
		if (file_exists($this->config_file)) {
			$this->config = include($this->config_file);
			foreach ($this->config as $cur_val) {
				$this->config_keys[$cur_val['name']] = $cur_val['value'];
			}
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
	 * Print a form to edit the configuration.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$pines->com_ptags->load();
		$module = new module('com_configure', 'edit', 'content');
		$module->comp = $this;

		return $module;
	}

	/**
	 * Print a view of the configuration.
	 * @return module The view's module.
	 */
	public function print_view() {
		global $pines;
		$module = new module('com_configure', 'view', 'content');
		$module->comp = $this;

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
}

?>