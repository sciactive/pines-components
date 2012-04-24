<?php
/**
 * com_content class.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_content main class.
 *
 * Manage content pages.
 *
 * @package Pines
 * @subpackage com_content
 */
class com_content extends component {
	/**
	 * A cache of the custom CSS files array.
	 * @access private
	 * @var mixed
	 */
	private $custom_css;

	/**
	 * Get an array of custom CSS files to use.
	 * @return string[] An array of CSS file names.
	 */
	public function get_custom_css() {
		if (!isset($this->custom_css)) {
			global $pines;
			foreach ((array) $pines->config->com_content->custom_css as $cur_glob) {
				if (strtolower(substr($cur_glob, -4)) != '.css')
					$cur_glob .= '.css';
				$this->custom_css = array_merge((array) $this->custom_css, glob($cur_glob));
			}
			$this->custom_css = array_unique($this->custom_css);
		}
		return $this->custom_css;
	}

	/**
	 * Check that a page variant is valid for a template.
	 * 
	 * @param string $variant The variant to check.
	 * @param string $template The template to use for the check. If left null, the current template is used.
	 * @return bool Whether the variant is valid for the specified template.
	 */
	public function is_variant_valid($variant, $template = null) {
		global $pines;
		if (isset($template))
			$cur_template = clean_filename($template);
		else
			$cur_template = clean_filename($pines->current_template);
		// Is there even a variant option?
		if (!isset($pines->config->$cur_template->variant))
			return false;
		// Find the defaults file.
		if (file_exists("templates/$cur_template/defaults.php"))
			$file = "templates/$cur_template/defaults.php";
		elseif (file_exists("templates/.$cur_template/defaults.php"))
			$file = "templates/.$cur_template/defaults.php";
		else
			return false;
		/**
		 * Get the template defaults to determine if the variant is valid.
		 */
		$template_options = (array) include($file);
		$variant_valid = false;
		foreach ($template_options as $cur_option) {
			if ($cur_option['name'] != 'variant')
				continue;
			$variant_valid = in_array($variant, $cur_option['options']);
			break;
		}
		return $variant_valid;
	}

	/**
	 * Creates and attaches a module which lists categories.
	 * @return module The module.
	 */
	public function list_categories() {
		global $pines;

		$module = new module('com_content', 'category/list', 'content');

		$module->categories = $pines->entity_manager->get_entities(array('class' => com_content_category), array('&', 'tag' => array('com_content', 'category')));

		if ( empty($module->categories) )
			pines_notice('No categories found.');

		return $module;
	}

	/**
	 * Creates and attaches a module which lists pages.
	 *
	 * @param com_content_category $category The category to list pages from. If null, all pages will be listed.
	 * @return module The module.
	 */
	public function list_pages($category = null) {
		global $pines;

		$module = new module('com_content', 'page/list', 'content');

		if (isset($category)) {
			$module->pages = $category->pages;
			$module->category = $category;
		} else {
			$module->pages = $pines->entity_manager->get_entities(array('class' => com_content_page), array('&', 'tag' => array('com_content', 'page')));
		}

		if ( empty($module->pages) )
			pines_notice('No pages found.');

		return $module;
	}

	/**
	 * Load the custom CSS files into the page head.
	 */
	public function load_custom_css() {
		static $loaded = false;
		if ($loaded)
			return;
		$module = new module('com_content', 'custom_css', 'head');
		$loaded = true;
	}
}

?>