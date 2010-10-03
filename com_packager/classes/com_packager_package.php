<?php
/**
 * com_packager_package class.
 *
 * @package Pines
 * @subpackage com_packager
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
 * @subpackage com_packager
 */
class com_packager_package extends entity {
	/**
	 * Load a package.
	 * @param int $id The ID of the package to load, 0 for a new package.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_packager', 'package');
		// Defaults.
		$this->type = 'component';
		$this->meta = array();
		$this->additional_files = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data(), $entity->get_sdata());
		}
	}

	/**
	 * Create a new instance.
	 * @return com_packager_package The new instance.
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
	 * Delete the package.
	 * @return bool True on success, false on failure.
	 */
	public function delete() {
		if (!parent::delete())
			return false;
		pines_log("Deleted package $this->name.", 'notice');
		return true;
	}

	/**
	 * Save the package.
	 * @return bool True on success, false on failure.
	 */
	public function save() {
		if (!isset($this->name))
			return false;
		return parent::save();
	}

	/**
	 * Retrieve the package's filename.
	 * @return string The filename.
	 */
	public function get_filename() {
		if (!empty($this->filename))
			return clean_filename($this->filename);
		switch ($this->type) {
			case 'component':
			case 'template':
				global $pines;
				$component = $this->component;
				return clean_filename("{$this->name}-{$pines->info->$component->version}");
			case 'system':
				global $pines;
				return clean_filename("{$this->name}-{$pines->info->version}");
			case 'meta':
				return clean_filename("{$this->name}-{$this->meta['version']}");
			default:
				return 'unknown';
		}
	}

	/**
	 * Write the package to a Slim archive.
	 *
	 * @param string $filename The filename to write to.
	 * @return bool True on success, false on failure.
	 */
	public function package($filename) {
		global $pines;
		$arc = new slim;
		//$arc->compression = '';
		//$arc->header_compression = false;
		switch ($this->type) {
			case 'component':
			case 'template':
				$component = $this->component;
				$info = $pines->info->$component;
				// Select only needed info from the info object.
				$arc->ext = array(
					'package' => $this->name,
					'type' => $this->type,
					'name' => $info->name,
					'author' => $info->author,
					'version' => $info->version,
					'license' => $info->license,
					'short_description' => $info->short_description,
					'description' => $info->description,
					'depend' => $info->depend,
					'recommend' => $info->recommend,
					'conflict' => $info->conflict
				);
				$arc->working_directory = $this->type == 'template' ? 'templates/' : 'components/';
				$arc->add_directory($component);
				break;
			case 'system':
				$info = $pines->info;
				// Select only needed info from the info object.
				$arc->ext = array(
					'package' => $this->name,
					'type' => $this->type,
					'name' => $info->name,
					'author' => $info->author,
					'version' => $info->version,
					'license' => $info->license,
					'short_description' => $info->short_description,
					'description' => $info->description,
					'depend' => $info->depend,
					'recommend' => $info->recommend,
					'conflict' => $info->conflict
				);
				$arc->add_directory('', true, true, '/^(components\/com_.*|templates\/tpl_.*|media\/.*)$/');
				$arc->add_directory('media/images');
				$arc->add_directory('media/logos');
				$arc->add_file('media/index.html');
				break;
			case 'meta':
				$arc->ext = array(
					'package' => $this->name,
					'type' => $this->type,
					'name' => $this->meta['name'],
					'author' => $this->meta['author'],
					'version' => $this->meta['version'],
					'license' => $this->meta['license'],
					'short_description' => $this->meta['short_description'],
					'description' => $this->meta['description'],
					'depend' => $this->meta['depend'],
					'recommend' => $this->meta['recommend'],
					'conflict' => $this->meta['conflict']
				);
				foreach ($this->additional_files as $cur_file) {
					if (!file_exists($cur_file))
						continue;
					if (is_dir($cur_file)) {
						$arc->add_directory($cur_file);
					} else {
						$arc->add_file($cur_file);
					}
				}
				break;
			default:
				return false;
		}

		return $arc->write($filename);
	}

	/**
	 * Print a form to edit the package.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_packager', 'form_package', 'content');
		$module->entity = $this;
		$module->components = $pines->all_components;

		return $module;
	}
}

?>