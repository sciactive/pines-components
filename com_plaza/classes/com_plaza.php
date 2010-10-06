<?php
/**
 * com_plaza class.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_plaza main class.
 *
 * @package Pines
 * @subpackage com_plaza
 */
class com_plaza extends component {
	/**
	 * The last component checked.
	 * @access private
	 * @var string
	 */
	private $last_component = '';
	/**
	 * The last component comparison checked.
	 * @access private
	 * @var string
	 */
	private $last_ccompare = '';
	/**
	 * The last component version checked.
	 * @access private
	 * @var string
	 */
	private $last_crequired = '';
	/**
	 * The last package checked.
	 * @access private
	 * @var string
	 */
	private $last_package = '';
	/**
	 * The last package comparison checked.
	 * @access private
	 * @var string
	 */
	private $last_pcompare = '';
	/**
	 * The last package version checked.
	 * @access private
	 * @var string
	 */
	private $last_prequired = '';
	/**
	 * The last service checked.
	 * @access private
	 * @var string
	 */
	private $last_service = '';
	/**
	 * The list of packages to add.
	 * @access private
	 * @var array
	 */
	private $add_package = array();
	/**
	 * The list of services to add.
	 * @access private
	 * @var array
	 */
	private $add_service = array();
	/**
	 * The list of packages to remove.
	 * @access private
	 * @var array
	 */
	private $rem_package = array();
	/**
	 * The list of services to remove.
	 * @access private
	 * @var array
	 */
	private $rem_service = array();

	/**
	 * Local copy of components array.
	 * @access private
	 * @var array
	 */
	private $pines_components;
	/**
	 * Local copy of info object.
	 * @access private
	 * @var object
	 */
	private $pines_info;
	/**
	 * Local copy of services array.
	 * @access private
	 * @var array
	 */
	private $pines_services;
	/**
	 * Local copy of package db.
	 * @access private
	 * @var array
	 */
	private $pines_com_package_db;
	/**
	 * The method of fetching files from the repositories.
	 *
	 * - pecl - Use PECL's HttpRequest Class.
	 * - curl - Use cURL.
	 * - fopen - Use fopen(), file_get_contents().
	 *
	 * @access private
	 * @var string
	 */
	private $fetch = 'fopen';

	/**
	 * Set the fetch value.
	 */
	public function __construct() {
		global $pines;
		switch ($pines->config->com_plaza->fetch_method) {
			case 'auto':
				if (class_exists('HttpRequest')) {
					$this->fetch = 'pecl';
				} elseif (function_exists('curl_init')) {
					$this->fetch = 'curl';
				}
				break;
			case 'pecl':
				$this->fetch = 'pecl';
				break;
			case 'curl':
				$this->fetch = 'curl';
				break;
			case 'fopen':
			default:
				$this->fetch = 'fopen';
				break;
		}
	}

	/**
	 * Add a package in the local environment.
	 *
	 * @access private
	 * @param array $package The package to add.
	 */
	private function calculate_add($package) {
		$name = $package['package'];
		$this->pines_components = array_merge($this->pines_components, array($name));
		$this->pines_info->$name = (object) $package;
		if (isset($package['services'])) {
			foreach ($package['services'] as $cur_service) {
				$this->pines_services[$cur_service] = $name;
			}
		}
		$this->pines_com_package_db['packages'][$name] = $package;
	}

	/**
	 * Calculate the changes required for a package action.
	 *
	 * The return array will contain:
	 *
	 * - possible - Whether it is possible.
	 * - install - What packages need to be installed.
	 * - remove - What packages need to be removed.
	 * - service - What services need to be installed.
	 *
	 * @param array $package The package the action is happening on.
	 * @param string $do The action. ("install", "upgrade", or "remove")
	 * @param bool $clear_local_first Whether to clear the local environment first. Use this feature to keep calculating more changes with the same environment.
	 * @return array The result.
	 */
	public function calculate_changes($package, $do, $clear_local_first = true) {
		global $pines;

		// Override the default checkers.
		$old_checkers = $pines->depend->checkers;
		$pines->depend->checkers['component'] = array($this, 'check_component');
		$pines->depend->checkers['service'] = array($this, 'check_service');

		if ($clear_local_first) {
			// Set up local objects, so we can change things.
			$this->add_package = $this->add_service = $this->rem_package = $this->rem_service = array();
			$this->pines_components = $pines->components;
			$this->pines_info = clone $pines->info;
			$this->pines_services = $pines->services;
			$this->pines_com_package_db = $pines->com_package->db;
		}

		$possible = true;

		// Get an index of available packages.
		$index = $this->get_index();

		switch ($do) {
			case 'install':
				// Check if it's installed.
				if (isset($this->pines_com_package_db['packages'][$package['package']]))
					$possible = false;
			case 'upgrade':
				if ($possible) {
					// Check that all dependencies are met.
					if (isset($package['depend'])) {
						foreach ($package['depend'] as $cur_type => $cur_value) {
							do {
								if (!($pass = $pines->depend->check($cur_type, $cur_value))) {
									switch ($cur_type) {
										case 'pines':
											// TODO: Look for system package.
											$possible = false;
											break;
										case 'component':
											// When dependencies aren't met, see if they can be by adding packages.
											if (isset($index['packages'][$this->last_component]) && (empty($this->last_ccompare) || version_compare($index['packages'][$this->last_component]['version'], $this->last_crequired, $this->last_ccompare))) {
												$this->calculate_add($index['packages'][$this->last_component]);
												$this->add_package[] = $this->last_component;
											} else {
												$possible = false;
											}
											break;
										case 'package':
											if (isset($index['packages'][$this->last_package]) && (empty($this->last_pcompare) || version_compare($index['packages'][$this->last_package]['version'], $this->last_prequired, $this->last_pcompare))) {
												$this->calculate_add($index['packages'][$this->last_package]);
												$this->add_package[] = $this->last_package;
											} else {
												$possible = false;
											}
											break;
										case 'service':
											if (isset($index['services'][$this->last_service])) {
												$this->pines_services[$this->last_service] = 'available';
												$this->add_service[] = $this->last_service;
											} else {
												$possible = false;
											}
											break;
										default:
											$possible = false;
											break;
									}
								}
							} while($possible && !$pass);
						}
					}
					// Check if any services this component provides are already provided.
					if ($package['type'] == 'component' && isset($package['services'])) {
						foreach ($package['services'] as $cur_service) {
							// If the service is provided, it may just be because this
							// package is already installed. Check if the component is the
							// same.
							if (isset($this->pines_com_package_db['services'][$cur_service]) && !in_array($package['package'], $this->pines_com_package_db['services'][$cur_service])) {
								$name = $this->pines_com_package_db['services'][$cur_service][0];
								$this->calculate_remove($name);
								$this->rem_package[] = $name;
							}
						}
					}
					// Check that no conflicts exists.
					if (isset($package['conflict'])) {
						foreach ($package['conflict'] as $cur_type => $cur_value) {
							do {
								if (!($pass = !$pines->depend->check($cur_type, $cur_value))) {
									switch ($cur_type) {
										case 'pines':
											// TODO: Look for system package.
											$possible = false;
											break;
										case 'component':
											$this->calculate_remove($this->last_component);
											$this->rem_package[] = $this->last_component;
											break;
										case 'package':
											$this->calculate_remove($this->last_package);
											$this->rem_package[] = $this->last_package;
											break;
										case 'service':
											$this->calculate_remove_service($this->last_service);
											$this->rem_service[] = $this->last_service;
											break;
										default:
											$possible = false;
											break;
									}
								}
							} while($possible && !$pass);
						}
					}
				}
				// Remove any existing version from the environment.
				$this->calculate_remove($package['package']);
				// Add the new package to the environment.
				$this->calculate_add($package);
				// Check other packages if they depend on something we removed or conflict with something new.
				do {
					$changed = false;
					foreach ($this->pines_components as $cur_component) {
						if ($cur_component == $package['package'] || in_array($cur_component, $this->add_package))
							continue;
						if (isset($this->pines_info->$cur_component->depend)) {
							foreach ($this->pines_info->$cur_component->depend as $cur_type => $cur_value) {
								if (!$pines->depend->check($cur_type, $cur_value)) {
									$this->rem_package[] = $cur_component;
									$this->calculate_remove($cur_component);
									$changed = true;
									break 2;
								}
							}
						}
						if (isset($this->pines_info->$cur_component->conflict)) {
							foreach ($this->pines_info->$cur_component->conflict as $cur_type => $cur_value) {
								if ($pines->depend->check($cur_type, $cur_value)) {
									$this->rem_package[] = $cur_component;
									$this->calculate_remove($cur_component);
									$changed = true;
									break 2;
								}
							}
						}
					}
				} while ($changed);
				break;
			case 'remove':
				$this->calculate_remove($package['package']);
				// Check other packages if they depend on something we removed.
				do {
					$changed = false;
					foreach ($this->pines_components as $cur_component) {
						if (isset($this->pines_info->$cur_component->depend)) {
							foreach ($this->pines_info->$cur_component->depend as $cur_type => $cur_value) {
								if (!$pines->depend->check($cur_type, $cur_value)) {
									$this->rem_package[] = $cur_component;
									$this->calculate_remove($cur_component);
									$changed = true;
									break 2;
								}
							}
						}
						if (isset($this->pines_info->$cur_component->conflict)) {
							foreach ($this->pines_info->$cur_component->conflict as $cur_type => $cur_value) {
								if ($pines->depend->check($cur_type, $cur_value)) {
									$this->rem_package[] = $cur_component;
									$this->calculate_remove($cur_component);
									$changed = true;
									break 2;
								}
							}
						}
					}
				} while ($changed);
				break;
		}
		
		// Restore the default checkers.
		$pines->depend->checkers = $old_checkers;
		
		return array('possible' => $possible, 'install' => $this->add_package, 'remove' => $this->rem_package, 'service' => $this->add_service);
	}

	/**
	 * Calculate all changes required for a package action.
	 *
	 * This function will calculate the changes the package requires, and the
	 * changes those changes require, and so on.
	 *
	 * @uses com_plaza::calculate_changes()
	 * @param array $package The package the action is happening on.
	 * @param string $do The action. ("install", "upgrade", or "remove")
	 * @return array The result.
	 */
	public function calculate_changes_full($package, $do) {
		global $pines;
		// Calculate immediate changes.
		$return = $this->calculate_changes($package, $do);

		// Calculate all the changes required to do it all.
		$checked = array(
			'install' => array(),
			'remove' => array()
		);

		if ($return['possible']) {
			do {
				$changed = false;
				foreach (array('install', 'remove') as $cur_do) {
					foreach ((array) $return[$cur_do] as $cur_package_name) {
						if (!in_array($cur_package_name, $checked[$cur_do])) {
							// Get the package.
							switch ($cur_do) {
								case 'install':
									$cur_package = $index['packages'][$cur_package_name];
									break;
								case 'remove':
									$cur_package = $pines->com_package->db['packages'][$cur_package_name];
									break;
							}
							if (!isset($cur_package))
								continue;
							// Calculate its changes, in the same environment.
							$more = $this->calculate_changes($cur_package, $cur_do, false);
							// Is it still possible?
							$return['possible'] = $return['possible'] && $more['possible'];
							// Mark that this is already checked.
							$checked[$cur_do][] = $cur_package_name;
							// Combine all changes.
							$return['install'] = array_merge($return['install'], (array) $more['install']);
							$return['remove'] = array_merge($return['remove'], (array) $more['remove']);
							$return['service'] = array_merge($return['service'], (array) $more['service']);
							$changed = true;
						}
					}
				}
			} while ($return['possible'] && $changed);
		}
		
		if ($return['possible']) {
			// Check if the same package is in both install and remove.
			if (array_intersect((array) $return['install'], (array) $return['remove']))
				$return['possible'] = false;
		}

		return $return;
	}

	/**
	 * Remove a package in the local environment.
	 *
	 * @access private
	 * @param string $name The name of the package to remove.
	 */
	private function calculate_remove($name) {
		$this->pines_components = array_diff($this->pines_components, array($name));
		if (isset($this->pines_info->$name)) {
			if (isset($this->pines_info->$name->services)) {
				foreach ($this->pines_info->$name->services as $cur_service) {
					if ($cur_service == 'template')
						continue;
					unset($this->pines_services[$cur_service]);
				}
			}
			unset($this->pines_info->$name);
		}
		unset($this->pines_com_package_db['packages'][$name]);
	}

	/**
	 * Remove a service in the local environment.
	 *
	 * @access private
	 * @param string $name The name of the service to remove.
	 */
	private function calculate_remove_service($name) {
		foreach ($this->pines_components as $cur_component) {
			$services = $this->pines_info->$cur_component->services;
			if (in_array($name, $services)) {
				$this->rem_package[] = $cur_component;
				$this->calculate_remove($cur_component);
			}
		}
	}

	/**
	 * Override component checker.
	 *
	 * @uses pines::components
	 * @param string $value The value to check.
	 * @return bool The result of the component check.
	 */
	public function check_component($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $pines->depend->simple_parse($value, array($this, 'check_component'));

		// Set the last value.
		$this->last_component = $component = preg_replace('/([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$1', $value);
		$this->last_ccompare = '';
		$this->last_crequired = '';
		if ($component == $value) {
			return in_array($value, $this->pines_components);
		} else {
			if (!isset($this->pines_info->$component))
				return false;
			$this->last_ccompare = $compare = preg_replace('/([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$2', $value);
			$this->last_crequired = $required = preg_replace(' /([a-z0-9_]+)([<>=]{1,2})(.+)/S', '$3', $value);
			return version_compare($this->pines_info->$component->version, $required, $compare);
		}
	}

	/**
	 * Override package checker.
	 *
	 * @param string $value The value to check.
	 * @return bool The result of the package check.
	 */
	public function check_package($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $this->simple_parse($value, array($this, 'check_package'));

		// Set the last value.
		$this->last_package = $package_name = preg_replace('/([a-z0-9_-]+)([<>=]{1,2})(.+)/S', '$1', $value);
		$this->last_pcompare = '';
		$this->last_prequired = '';

		$package = $this->pines_com_package_db['packages'][$package_name];
		if (!isset($package))
			return false;
		if ($package_name != $value) {
			$this->last_pcompare = $compare = preg_replace('/([a-z0-9_-]+)([<>=]{1,2})(.+)/S', '$2', $value);
			$this->last_prequired = $required = preg_replace(' /([a-z0-9_-]+)([<>=]{1,2})(.+)/S', '$3', $value);
			return version_compare($package['version'], $required, $compare);
		}
		return true;
	}

	/**
	 * Override service checker.
	 *
	 * @uses pines::services
	 * @param string $value The value to check.
	 * @return bool The result of the service check.
	 */
	public function check_service($value) {
		global $pines;
		if (
				strpos($value, '&') !== false ||
				strpos($value, '|') !== false ||
				strpos($value, '!') !== false ||
				strpos($value, '(') !== false ||
				strpos($value, ')') !== false
			)
			return $pines->depend->simple_parse($value, array($this, 'check_service'));

		// Set the last value.
		$this->last_service = $value;

		return key_exists($value, $this->pines_services);
	}

	/**
	 * Get an index of packages.
	 *
	 * @param string $repository Only retrieve from this repository.
	 * @param string $publisher Only retrieve from this publisher.
	 * @return array An array of packages.
	 */
	public function get_index($repository = null, $publisher = null) {
		global $pines;
		if (isset($repository)) {
			$files = array('components/com_plaza/includes/cache/indices/'.md5($repository).'.index');
		} else {
			$files = array();
			foreach ($pines->config->com_plaza->repositories as $cur_repository) {
				$files[] = 'components/com_plaza/includes/cache/indices/'.md5($cur_repository).'.index';
			}
		}

		// Build the index of package.
		$index = array();
		foreach ($files as $cur_file) {
			if (!file_exists($cur_file))
				continue;
			$cur_index = (array) json_decode(file_get_contents($cur_file), true);
			if (isset($publisher)) {
				$index = array_merge((array) $cur_index[$publisher], $index);
			} else {
				foreach ($cur_index as $cur_pub_index) {
					$index = array_merge((array) $cur_pub_index, $index);
				}
			}
		}

		// Build the list of services.
		$services = array();
		foreach ($index as $cur_package) {
			if (!isset($cur_package['services']))
				continue;
			foreach ($cur_package['services'] as $cur_service) {
				$services[$cur_service][] = $cur_package['package'];
			}
		}

		return array('packages' => $index, 'services' => $services);
	}

	/**
	 * Creates and attaches a module which lists packages.
	 */
	public function list_packages() {
		global $pines;

		$module = new module('com_plaza', 'package/list', 'content');

		$module->db = $pines->com_package->db;
	}

	/**
	 * Creates and attaches a module which lists repository packages.
	 */
	public function list_repository() {
		global $pines;

		$module = new module('com_plaza', 'package/repository', 'content');

		$module->db = $pines->com_package->db;
		$module->index = $this->get_index();
		if (empty($module->index['packages'])) {
			$this->reload_packages();
			$module->index = $this->get_index();
		}
	}

	public function package_download($package) {
		global $pines;
		$file = "components/com_plaza/includes/cache/packages/{$package['package']}-{$package['version']}.slm";
		if (!file_exists($file)) {
			// Figure out which repository it's in.
			foreach ($pines->config->com_plaza->repositories as $cur_repository) {
				$index = $this->get_index($cur_repository, $package['publisher']);
				if (isset($index['packages'][$package['package']]) && $index['packages'][$package['package']]['version'] == $package['version']) {
					if (!isset($package['publisher']))
						$package['publisher'] = $index['packages'][$package['package']]['publisher'];
					$repository = $cur_repository;
					break;
				}
			}
			if (!isset($repository))
				return false;
			// Download it.
			$cur_url = $repository . (strpos($repository, '?') === false ? '?' : '&') . 'option=com_repository&action=getpackage&pub='.urlencode($package['publisher']).'&p='.urlencode($package['package']).'&v='.urlencode($package['version']);
			switch ($this->fetch) {
				case 'pecl':
					$hr = new HttpRequest($cur_url, HTTP_METH_GET, array('redirect' => 2));
					try {
						$hr->send();
					} catch (Exception $e) {
						$return = false;
						continue;
					}
					file_put_contents($file, $hr->getResponseBody());
					break;
				case 'curl':
					// Get the index.
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $cur_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					file_put_contents($file, curl_exec($ch));
					curl_close($ch);
					break;
				case 'fopen':
					$fw = fopen($file, 'w');
					$fr = fopen($cur_url, 'r');
					if (!$fr || !$fw)
						return false;
					stream_copy_to_stream($fr, $fw);
					fclose($fr);
					fclose($fW);
					break;
			}
		}
		$slim = new slim;
		if (!$slim->read($file)) {
			unset($slim);
			unlink($file);
			return false;
		}
		return true;
	}

	public function package_install($package) {
		if (!$this->package_download($package))
			return false;
		$file = "components/com_plaza/includes/cache/packages/{$package['package']}-{$package['version']}.slm";
		if (!file_exists($file))
			return false;
		// Load the package.
		$pack = com_package_package::factory($file, true);
		if (!isset($pack))
			return false;
		// Make sure it's installable.
		if (!$pack->is_installable())
			return false;
		// Check its dependencies, etc.
		if (!$pack->is_ready())
			return false;
		// Install it.
		if (!$pack->install())
			return false;
		return true;
	}

	public function package_reinstall($package) {
		$pack = com_package_package::factory($package['package']);
		if (!isset($pack) || !$pack->is_installed())
			return false;
		return $this->package_install($package);
	}

	public function package_remove($package) {
		// Check that it won't disrupt other packages.
		$changes = $this->calculate_changes($package, 'remove');
		if (!$changes['possible'] || $changes['install'] || $changes['remove'] || $changes['service'])
			return false;
		// Load the package.
		$pack = com_package_package::factory($package['package']);
		if (!isset($pack))
			return false;
		// Remove it.
		if (!$pack->remove())
			return false;
		return true;
	}

	public function package_upgrade($package) {
		// TODO: Upgrade.
	}

	/**
	 * Reload the package indices from the repositories.
	 *
	 * @return bool True on success, false on failure.
	 * @todo Remove old indicies after the repository is removed.
	 */
	public function reload_packages() {
		global $pines;
		$return = true;
		foreach ($pines->config->com_plaza->repositories as $cur_repository) {
			$cache_file = 'components/com_plaza/includes/cache/indices/'.md5($cur_repository);
			$cur_url = $cur_repository . (strpos($cur_repository, '?') === false ? '?' : '&') . 'option=com_repository&action=getindex';
			switch ($this->fetch) {
				case 'pecl':
					$hr = new HttpRequest($cur_url, HTTP_METH_GET, array('redirect' => 2));
					try {
						$hr->send();
					} catch (Exception $e) {
						$return = false;
						continue;
					}
					$output = $hr->getResponseBody();
					break;
				case 'curl':
					// Get the index.
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $cur_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$output = curl_exec($ch);
					curl_close($ch);
					break;
				case 'fopen':
					$output = file_get_contents($cur_url);
					break;
			}
			if (empty($output)) {
				$return = false;
				continue;
			}
			$index = (array) json_decode($output);
			if (empty($index)) {
				$return = false;
				continue;
			}
			if (!file_put_contents("{$cache_file}.tmp", $output)) {
				$return = false;
				continue;
			}
			if (!rename("{$cache_file}.tmp", "{$cache_file}.index")) {
				$return = false;
				continue;
			}
		}
		return $return;
	}
}

?>