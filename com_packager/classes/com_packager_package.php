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
		$this->attributes = $this->meta = array();
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('guid' => $id, 'tags' => $this->tags, 'class' => get_class($this)));
			if (!isset($entity))
				return;
			$this->guid = $entity->guid;
			$this->tags = $entity->tags;
			$this->put_data($entity->get_data());
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
	 * @todo Once testing is complete, enable compression of the archive.
	 */
	public function package($filename) {
		global $pines;
		$arc = new slim;
		$arc->compression = '';
		$arc->header_compression = false;
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
					'description' => $info->description
				);
				$arc->working_directory = $this->type == 'template' ? 'templates/' : 'components/';
				$arc->add_directory($component);
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
					'description' => $this->meta['description']
				);
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
		$pines->editor->load();
		$pines->com_pgrid->load();
		$module = new module('com_packager', 'form_package', 'content');
		$module->entity = $this;
		$module->components = $pines->all_components;

		return $module;
	}
}

?>