<?php
/**
 * com_packager_package class.
 *
 * @package Components\packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * A package.
 *
 * @package Components\packager
 */
class com_packager_package extends entity {
	/**
	 * Load a package.
	 * @param int $id The ID of the package to load, 0 for a new package.
	 */
	public function __construct($id = 0) {
		parent::__construct();
		$this->add_tag('com_packager', 'package');
		if ($id > 0) {
			global $pines;
			$entity = $pines->entity_manager->get_entity(array('class' => get_class($this)), array('&', 'guid' => $id, 'tag' => $this->tags));
			if (isset($entity)) {
				$this->guid = $entity->guid;
				$this->tags = $entity->tags;
				$this->put_data($entity->get_data(), $entity->get_sdata());
				return;
			}
		}
		// Defaults.
		$this->type = 'component';
		$this->meta = array();
		$this->additional_files = array();
		$this->exclude_files = array();
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

	public function info($type) {
		switch ($type) {
			case 'name':
				return $this->name;
			case 'type':
				return 'package';
			case 'types':
				return 'packages';
			case 'url_edit':
				if (gatekeeper('com_packager/editpackage'))
					return pines_url('com_packager', 'package/edit', array('id' => $this->guid));
				break;
			case 'url_list':
				if (gatekeeper('com_packager/listpackages'))
					return pines_url('com_packager', 'package/list');
				break;
			case 'icon':
				return 'picon-package-x-generic';
		}
		return null;
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
		$arc->file_integrity = true;
		$arc->preserve_mode = true;
		$arc->follow_links = true;
		// Use these to turn off compression.
		//$arc->compression = '';
		//$arc->header_compression = false;

		// Filename prefix.
		$prefix = ($this->type == 'component' || $this->type == 'template') ? "{$this->component}/" : '';

		// Make a regex to exclude files.
		$re_files = array();
		foreach ((array) $this->exclude_files as $cur_file) {
			// Append a $ to only match the whole string if it's a file.
			$re_files[] = preg_quote($prefix.$cur_file, '/').(substr($cur_file, -1) == '/' ? '' : '$');
		}
		if ($re_files)
			$re_exclude = '/^('.implode('|', $re_files).')/';

		// Build the archive.
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
					'website' => $info->website,
					'services' => $info->services,
					'short_description' => $info->short_description,
					'description' => $info->description,
					'depend' => $info->depend,
					'recommend' => $info->recommend,
					'conflict' => $info->conflict
				);
				$arc->working_directory = $this->type == 'template' ? 'templates/' : 'components/';
				$arc->add_directory($component, true, true, $re_exclude);
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
					'website' => $info->website,
					'services' => $info->services,
					'short_description' => $info->short_description,
					'description' => $info->description,
					'depend' => $info->depend,
					'recommend' => $info->recommend,
					'conflict' => $info->conflict
				);
				$arc->add_directory('', true, true, '/^(components\/com_|templates\/tpl_|media\/'.($re_files ? '|'.implode('|', $re_files) : '').')/');
				$arc->add_directory('media/images/', true, true, $re_exclude);
				$arc->add_directory('media/logos/', true, true, $re_exclude);
				if (!isset($re_exclude) || !preg_match($re_exclude, 'media/index.html'))
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
					'website' => $info->meta['website'],
					'short_description' => $this->meta['short_description'],
					'description' => $this->meta['description'],
					'depend' => $this->meta['depend'],
					'recommend' => $this->meta['recommend'],
					'conflict' => $this->meta['conflict']
				);
				break;
			default:
				return false;
		}

		// Add additional files.
		foreach ((array) $this->additional_files as $cur_file) {
			if (!file_exists($arc->working_directory.$prefix.$cur_file))
				continue;
			if (is_dir($arc->working_directory.$prefix.$cur_file)) {
				$arc->add_directory($prefix.$cur_file, true, true, $re_exclude);
			} else {
				if (!isset($re_exclude) || !preg_match($re_exclude, $prefix.$cur_file))
					$arc->add_file($prefix.$cur_file);
			}
		}

		// Include icon and screenshots in a directory called "_MEDIA".
		$arc->add_entry(array(
			'type' => 'dir',
			'path' => '_MEDIA'
		));
		if (!empty($this->icon)) {
			$file = $pines->uploader->real($this->icon);
			if (file_exists($file))
				$data = file_get_contents($file);
			if (!empty($data)) {
				$name = basename($file);
				$arc->add_entry(array(
					'type' => 'file',
					'path' => "_MEDIA/$name",
					'data' => $data
				));
				$arc->ext['icon'] = $name;
			}
		}
		if (!empty($this->screenshots)) {
			$arc->ext['screens'] = array();
			foreach ($this->screenshots as $cur_screen) {
				$file = $pines->uploader->real($cur_screen['file']);
				if (file_exists($file))
					$data = file_get_contents($file);
				if (!empty($data)) {
					$name = basename($file);
					$arc->add_entry(array(
						'type' => 'file',
						'path' => "_MEDIA/$name",
						'data' => $data
					));
					$arc->ext['screens'][] = array(
						'alt' => $cur_screen['alt'],
						'file' => $name
					);
				}
			}
		}

		return $arc->write($filename);
	}

	/**
	 * Print a form to edit the package.
	 * @return module The form's module.
	 */
	public function print_form() {
		global $pines;
		$module = new module('com_packager', 'package/form', 'content');
		$module->entity = $this;
		$module->components = $pines->all_components;

		return $module;
	}
}

?>