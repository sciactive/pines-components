<?php
/**
 * com_package_package class.
 *
 * @package Pines
 * @subpackage com_package
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A package.
 *
 * @package Pines
 * @subpackage com_package
 */
class com_package_package extends p_base {
	/**
	 * The name of the package.
	 * @var string
	 */
	public $name = '';
	/**
	 * Whether the package is installed.
	 * @var bool
	 * @access private
	 */
	private $installed = false;
	/**
	 * The package's info.
	 * @var array
	 * @access private
	 */
	private $info = array();
	/**
	 * The package's Slim archive.
	 * @var slim
	 * @access private
	 */
	private $slim = null;

	/**
	 * Load a package.
	 *
	 * @param string $package The name of the package or filename of the Slim archive.
	 * @param bool $is_file Whether to load a Slim archive package.
	 */
	public function __construct($package, $is_file = false) {
		global $pines;
		if ($is_file) {
			if (!is_readable($package))
				return;
			$this->slim = new slim;
			if (!$this->slim->read($package))
				return;
			$this->info = $this->slim->ext;
			$this->name = $this->info['package'];
			unset($this->info['package']);
		} else {
			$info = $pines->com_package->db['packages'][$package];
			if (isset($info)) {
				$this->name = $package;
				$this->installed = true;
				$this->info = $info;
			}
		}
	}

	/**
	 * Create a new instance.
	 *
	 * This checks that the package has been loaded correctly and hooks the
	 * object.
	 *
	 * @return com_package_package The new instance.
	 */
	public static function factory() {
		global $pines;
		$class = get_class();
		$args = func_get_args();
		$object = new $class($args[0], $args[1]);
		if (empty($object->name))
			return null;
		$pines->hook->hook_object($object, $class.'->', false);
		return $object;
	}

	/**
	 * Check whether the package is installed.
	 * @return bool True or false.
	 */
	public function is_installed() {
		return $this->installed;
	}

	/**
	 * Check whether the package is ready to install/upgrade.
	 * @return bool True or false.
	 */
	public function is_ready() {
		global $pines;
		// Check if a newer version is installed.
		if (
				isset($pines->com_package->db['packages'][$this->name]) &&
				version_compare(
					$this->info['version'],
					$pines->com_package->db['packages'][$this->name]['version'],
					'<'
				)
			)
			return false;
		// Check if any services this component provides are already provided.
		if ($this->info['type'] == 'component' && isset($this->info['services'])) {
			foreach ($this->info['services'] as $cur_service) {
				// If the service is provided, it may just be because this
				// package is already installed. Check if the component is the
				// same.
				if (isset($pines->com_package->db['services'][$cur_service]) && !in_array($this->name, $pines->com_package->db['services'][$cur_service]))
					return false;
			}
		}
		// Check that all dependencies are met.
		if (isset($this->info['depend'])) {
			foreach ($this->info['depend'] as $cur_type => $cur_value) {
				if (!$pines->depend->check($cur_type, $cur_value))
					return false;
			}
		}
		// Check that no conflicts exists.
		if (isset($this->info['conflict'])) {
			foreach ($this->info['conflict'] as $cur_type => $cur_value) {
				if ($pines->depend->check($cur_type, $cur_value))
					return false;
			}
		}
		return true;
	}

	/**
	 * Check whether the package can be installed/upgraded.
	 * @return bool True or false.
	 */
	public function is_installable() {
		if ($this->installed)
			return false;
		return (!empty($this->name) && isset($this->slim) && in_array($this->info['type'], array('component', 'template', 'system', 'meta')));
	}

	/**
	 * Install the package.
	 * @param bool $force Install the package even if there is a newer version already installed, services are already provided, or the dependencies aren't met.
	 * @return bool True on success, false on failure.
	 */
	public function install($force = false) {
		global $pines;
		if (!$this->is_installable())
			return false;
		if (!$this->is_ready()) {
			if (!$force)
				return false;
			pines_log("Forced package installation requested for package \"{$this->name}\". A newer version is installed, services are already provided, or the dependencies aren't met.", 'warning');
		}
		if (isset($pines->com_package->db['packages'][$this->name])) {
			pines_log("Replacing existing package \"{$this->name}\" version {$pines->com_package->db['packages'][$this->name]['version']} with new version {$this->info['version']}.", 'notice');
			// Todo: remove old version.
		} else {
			pines_log("Installing new package \"{$this->name}\" version {$this->info['version']}.", 'notice');
		}
		switch ($this->info['type']) {
			case 'component':
			case 'template':
				$this->slim->working_directory = $this->info['type'] == 'template' ? 'templates/' : 'components/';
				if (!$this->slim->extract())
					return false;
				$pines->components[] = $this->name;
				break;
			case 'system':
				if (!is_writable("components/com_package/includes/cache/sys_{$this->name}.php"))
					return false;
				$sys_files = glob('components/com_package/includes/cache/sys_*.php');
				if ($sys_files) {
					foreach ($sys_files as $cur_sys_file) {
						if (!unlink($cur_sys_file))
							return false;
					}
				}
				if (!$this->slim->extract())
					return false;
				if (!file_put_contents("components/com_package/includes/cache/sys_{$this->name}.php", "<?php\ndefined('P_RUN') or die('Direct access prohibited');\nreturn ".var_export($this->info, true).";\n?>"))
					return false;
				break;
			case 'meta':
				if (!file_put_contents("components/com_package/includes/cache/met_{$this->name}.php", "<?php\ndefined('P_RUN') or die('Direct access prohibited');\nreturn ".var_export($this->info, true).";\n?>"))
					return false;
				break;
			default:
				return false;
		}
		pines_log("Successfully installed new package \"{$this->name}\" version {$this->info['version']}. Rebuilding package database.", 'notice');
		$pines->com_package->rebuild_db();
		return true;
	}
}

?>