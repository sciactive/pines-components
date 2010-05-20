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
	public $package = '';
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
			$this->package = $this->info['package'];
			unset($this->info['package']);
		} else {
			$info = $pines->com_package->db['packages'][$package];
			if (isset($info)) {
				$this->package = $package;
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
		if (empty($object->package))
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
		if (
				isset($pines->com_package->db['packages'][$this->package]) &&
				version_compare(
					$this->info['version'],
					$pines->com_package->db['packages'][$this->package]['version'],
					'<'
				)
			)
			return false;
		if (isset($this->info['depend'])) {
			foreach ($this->info['depend'] as $cur_type => $cur_value) {
				if (!$pines->depend->check($cur_type, $cur_value))
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
		return (!empty($this->package) && isset($this->slim) && in_array($this->slim->ext['type'], array('component', 'template', 'system', 'meta')));
	}

	/**
	 * Install the package.
	 * @param bool $force Install the package even if there is a newer version already installed or the dependencies aren't met.
	 * @return bool True on success, false on failure.
	 */
	public function install($force = false) {
		global $pines;
		if (!$this->is_installable())
			return false;
		if (!$this->is_ready()) {
			if (!$force)
				return false;
			pines_log("Forced package installation requested for package \"{$this->package}\". A newer version is installed or the dependencies aren't met.", 'warning');
		}
		if (isset($pines->com_package->db['packages'][$this->package])) {
			pines_log("Replacing existing package \"{$this->package}\" version {$pines->com_package->db['packages'][$this->package]['version']} with new version {$this->info['version']}.", 'notice');
		} else {
			pines_log("Installing new package \"{$this->package}\" version {$this->info['version']}.", 'notice');
		}
		// Todo: finish installing.
		return true;
	}
}

?>