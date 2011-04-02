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
	 * Scan a directory recursively. Excludes '.' and '..'.
	 *
	 * @param string $directory The directory to scan.
	 * @return array An array of filenames.
	 */
	private function dir_find($directory) {
		$directory = rtrim($directory, '/').'/';
		if (!is_dir($directory))
			return false;
		if (!is_readable($directory))
			return false;
		if (!($dh = opendir($directory)))
			return false;
		$files = array();
		while ($cur_file = readdir($dh)) {
			if ($cur_file == '.' || $cur_file == '..')
				continue;
			if (is_dir($directory.$cur_file)) {
				$cur_file = rtrim($cur_file, '/').'/';
				$contents = $this->dir_find($directory.$cur_file);
				if ($contents === false)
					return false;
				$files = array_merge($files, $contents);
			}
			$files[] = $directory.$cur_file;
		}
		closedir($dh);
		return $files;
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
	 * Install/upgrade the package.
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
		if ($this->info['type'] == 'system') {
			// Should this require $force?
			pines_log("Replacing existing system \"{$pines->info->name}\" version {$pines->info->version} with new system \"{$this->name}\" {$this->info['version']}.", 'notice');
			$old_package = $pines->com_package->get_system();
			if (!isset($old_package) || !$old_package->is_installed() || !$old_package->remove(false, true)) {
				pines_log("Could not remove \"{$old_package->name}\" version {$old_package->info['version']} for replacement.", 'error');
				return false;
			}
		} else {
			if (isset($pines->com_package->db['packages'][$this->name])) {
				pines_log("Replacing existing package \"{$this->name}\" version {$pines->com_package->db['packages'][$this->name]['version']} with new version {$this->info['version']}.", 'notice');
				$old_package = com_package_package::factory($this->name);
				if (!isset($old_package) || !$old_package->is_installed() || !$old_package->remove(false, true)) {
					pines_log("Could not remove \"{$old_package->name}\" version {$old_package->info['version']} for replacement.", 'error');
					return false;
				}
			} else {
				pines_log("Installing new package \"{$this->name}\" version {$this->info['version']}.", 'notice');
			}
		}
		switch ($this->info['type']) {
			case 'component':
			case 'template':
				$this->slim->working_directory = $this->info['type'] == 'template' ? 'templates/' : 'components/';
				if (!$this->slim->extract('', true, '/^_MEDIA\//'))
					return false;
				$pines->components[] = $this->name;
				$pines->all_components[] = $this->name;
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
				if (!$this->slim->extract('', true, '/^_MEDIA\//'))
					return false;
				if (!file_put_contents("components/com_package/includes/cache/sys_{$this->name}.php", "<?php\ndefined('P_RUN') or die('Direct access prohibited');\nreturn ".var_export($this->info, true).";\n?>"))
					return false;
				break;
			case 'meta':
				$this->info['files'] = array();
				foreach ((array) $this->slim->get_current_files() as $cur_file) {
					$this->info['files'][] = $cur_file['path'];
				}
				if ($this->info['files'] && !$this->slim->extract('', true, '/^_MEDIA\//'))
					return false;
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

	/**
	 * Remove the package.
	 * @param bool $force Try to force removal of the package.
	 * @param bool $for_upgrade Don't remove configuration and cache, and don't rebuild the database.
	 * @return bool True on success, false on failure.
	 */
	public function remove($force = false, $for_upgrade = false) {
		global $pines;
		if (!$this->installed)
			return false;
		if (!$for_upgrade)
			pines_log("Removing package \"{$this->name}\" version {$this->info['version']}.", 'notice');
		$return = true;
		switch ($this->info['type']) {
			case 'component':
			case 'template':
				$dir = $this->info['type'] == 'template' ? 'templates/' : 'components/';
				if (is_dir($dir.$this->name)) {
					$dir .= $this->name.'/';
				} elseif (is_dir($dir.'.'.$this->name)) {
					$dir .= '.'.$this->name.'/';
				} else {
					return false;
				}
				if (in_array('user_manager', (array) $this->info['services'])) {
					// If we are removing the user manager, we need to log the user out.
					$pines->user_manager->logout();
				}
				$files = $this->dir_find($dir);
				$files[] = $dir;
				foreach ($files as $cur_file) {
					if (!file_exists($cur_file))
						continue;
					if (
							$for_upgrade &&
							(
								in_array($cur_file, array(
									"{$dir}",
									"{$dir}index.html",
									"{$dir}config.php",
									"{$dir}includes/",
									"{$dir}includes/index.html",
									"{$dir}includes/cache/"
								)) ||
								(strpos($cur_file, "{$dir}includes/cache/") === 0)
							)
						)
						continue;
					if (is_file($cur_file)) {
						$return = $return && unlink($cur_file);
					} elseif (is_dir($cur_file)) {
						$return = $return && rmdir($cur_file);
					}
				}
				if ($return) {
					$pines->components = array_diff($pines->components, array($this->name));
					$pines->all_components = array_diff($pines->all_components, array($this->name));
				}
				break;
			case 'system':
				if (!is_writable("components/com_package/includes/cache/sys_{$this->name}.php"))
					return false;
				// Remove system files.
				$files = $this->dir_find('system/');
				$files[] = 'system/';
				$files[] = P_INDEX;
				$files[] = 'INSTALL';
				$files[] = 'LICENSE';
				$files[] = 'README';
				foreach ($files as $cur_file) {
					if (!file_exists($cur_file))
						continue;
					if (
							$for_upgrade &&
							in_array($cur_file, array(
								"system/",
								"system/index.html",
								"system/config.php"
							))
						)
						continue;
					if (is_file($cur_file)) {
						$return = $return && unlink($cur_file);
					} elseif (is_dir($cur_file)) {
						$return = $return && rmdir($cur_file);
					}
				}
				break;
			case 'meta':
				$files = $this->info['files'];
				usort($files, array($this, 'sort_files'));
				foreach ($files as $cur_file) {
					if (!file_exists($cur_file))
						continue;
					if (is_file($cur_file)) {
						$return = $return && unlink($cur_file);
					} elseif (is_dir($cur_file)) {
						// If the user put files in this dir, then rmdir will fail, but it's not an error.
						$still_files = array_diff((array) scandir($cur_file), array('.', '..'));
						if ($still_files) {
							pines_log("Directory {$cur_file} is not empty, so it is not being removed.", 'notice');
						} else {
							$return = $return && rmdir($cur_file);
						}
					}
				}
				if ($return && !unlink("components/com_package/includes/cache/met_{$this->name}.php"))
					$return = false;
				break;
			default:
				return false;
		}
		if (!$for_upgrade) {
			if ($return) {
				pines_log("Successfully removed package \"{$this->name}\" version {$this->info['version']}. Rebuilding package database.", 'notice');
			} else {
				pines_log("Error removing package \"{$this->name}\" version {$this->info['version']}. Check that all files were removed correctly. Rebuilding package database.", 'error');
			}
			$pines->com_package->rebuild_db();
		}
		return $return;
	}

	/**
	 * Sort files first.
	 *
	 * @param string $a First filename.
	 * @param string $b Second filename.
	 *
	 */
	private function sort_files($a, $b) {
		if (is_file($a) && is_dir($b))
			return -1;
		if (is_dir($a) && is_file($b))
			return 1;
		return 0;
	}
}

?>