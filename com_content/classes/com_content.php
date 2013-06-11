<?php
/**
 * com_content class.
 *
 * @package Components\content
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
 * @package Components\content
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
	
	/**
	 * A recursive function to get children categories and pages from a children on a category
	 * 
	 * @param array The children on a category in which to search for more children or pages.
	 * 
	 * return object menu item
	 */
	public function get_menu_children($children) {
		global $pines;
		$menu_children = array();
		foreach ($children as $cur_child) {
			if (!$cur_child->enabled)
				continue; // if not enabled, skip it.
			// If there are no pages in it, who cares?
			if (empty($cur_child->pages))
				continue;
			// Do things..
			$child_item = (object) stdClass;
			$child_item->menu_item_type = 'category';
			$child_item->menu_item_guid = $cur_child->guid;
			$child_item->menu_item_name = $cur_child->name;
			$child_item->menu_item_alias = $cur_child->alias;
			// Set up Children
			if (!empty($cur_child->children))
				$child_item->menu_item_children = $pines->com_content->get_menu_children($cur_child->children);

			// The non empty children array could have contained all disabled children, so..
			if (empty($child_item->menu_item_children))
				unset($child_item->menu_item_chilren);

			// Set up Pages
			if (!empty($cur_child->pages)) {
					$pages = array();
				foreach ($cur_child->pages as $cur_page) {
					if (!$cur_page->enabled)
						continue; // If the page is not enabled, skip it.
					$menu_page = (object) stdClass;
					$menu_page->menu_item_guid = $cur_page->guid;
					$menu_page->menu_item_link = pines_url('com_content','page', array('a' => $cur_page->alias));
					// Pages have no children
					$menu_page->menu_item_name = $cur_page->name;
					$menu_page->menu_item_alias = $cur_page->alias;
					$pages[] = $menu_page;
				}
				$child_item->menu_item_pages = $pages;
			}
			$menu_children[] = $child_item;
		}

		return $menu_children;
	}
	
	/**
	 * Create a recursive sidemenu based on a specific tag from pages and categories.
	 * The result is considered "natural" because its top level is sorted to the natural order
	 * in which the GUIDs are called.
	 * 
	 * @param string $tag The tag to look for to compose the natural side menu top level
	 * 
	 * return array of natural menu objects that starts with top level and gets deeper.
	 */
	public function create_sidemenu($tag) {
		global $pines;
		// This function is what will determine how sub categories and pages are laid out
		// within the entire menu. Save Sidemenu only saves an order later, based on the information
		// generated from THIS function. Get Sidemenu just choose which menu to get - basically if there
		// is a sorted one or not.
		
		// This function will get a natural sidemenu. Natural means there is no special sort
		// for top level menu items other than the order in which their guids surface. The
		// children pages within categories will maintain the way they are sorted already.
		// The only editable sort order from the sidemenu is in the options for the sidemenu module
		// and it is done in the save function.
		// This function will create a single sidemenu entity that will contain two main arrays:
		// natural_menu
		// and
		// sorted_menu (only if there is one).
		// This function will create the entity if it doesn't exist, and then it will create the natural_menu array.
		
		// Get sidemenu entity or create one
		$sidemenu = $pines->entity_manager->get_entity(
			array('class' => entity),
			array('&',
				'tag' => array('com_content', 'sidemenu')
			)
		);
		if (!isset($sidemenu->guid)) {
			// create the sidemenu then
			$sidemenu = com_content_sidemenu::factory();
		}
		
		// Begin natural_menu creation.
		// Get all the categories and pages where the tag array contains the tag.
		$menu_entities = $pines->entity_manager->get_entities(
			array('class' => entity),
			array('&',
				'tag' => array('com_content'),
				'array' => array(
					array('content_tags', $tag)
				)
			),
			array('!&',
				'tag' => array('sidemenu')
			)
		);
		
		$top_level = array();
		
		foreach ($menu_entities as $cur_entity) {
			// First Check if the Item is ENABLED, if not, continue.
			if (!$cur_entity->enabled)
				continue;
			
			$menu_item = (object) stdClass;
			
			if (in_array('page', $cur_entity->tags)) {
				// This entity is a page
				$menu_item->menu_item_type = 'page';
			} else if (in_array('category', $cur_entity->tags)) {
				// This entity is a category
				$menu_item->menu_item_type = 'category';
			}
			
			// Check if CATEGORY has a parent category
			if ($menu_item->menu_item_type == 'category' && !isset($cur_entity->parent)) {
				$menu_item->menu_item_guid = $cur_entity->guid;
				// There won't be a link for categories.
				
				// Deal with children...this part is very annoying and may need to be a function...
				// that is recursive. 
				// It needs to figure out if it has children, and if those children have pages. If they have
				// no pages, do not include that child item.
				
				// Get Children
				if (!empty($cur_entity->children)) {
					$menu_item->menu_item_children = $pines->com_content->get_menu_children($cur_entity->children);
					// The non empty children array could have contained all disabled children, so..
					if (empty($menu_item->menu_item_children))
						unset($menu_item->menu_item_children);
				}
				// Get Pages
				if (!empty($cur_entity->pages)) {
					$pages = array();
					foreach ($cur_entity->pages as $cur_page) {
						if (!$cur_page->enabled)
							continue; // If the page is not enabled, skip it.
						if (!in_array($tag, $cur_page->content_tags))
							continue; // The page must have the tag
						$menu_page = (object) stdClass;
						$menu_page->menu_item_guid = $cur_page->guid;
						$menu_page->menu_item_link = pines_url('com_content','page', array('a' => $cur_page->alias));
						// Pages have no children
						$menu_page->menu_item_name = $cur_page->name;
						$menu_page->menu_item_alias = $cur_page->alias;
						$pages[] = $menu_page;
					}
					$menu_item->menu_item_pages = $pages;
				}
				$menu_item->menu_item_name = $cur_entity->name;
				$menu_item->menu_item_alias = $cur_entity->alias;
				
				// If children is not empty OR there are pages, show this category
				if (!empty($menu_item->menu_item_children) || !empty($menu_item->menu_item_pages))
					$top_level[] = $menu_item;
			}
			
			// Find out if a PAGE has a category (we dont want it..top level only)
			if ($menu_item->menu_item_type == 'page') {
				$all_categories = $pines->entity_manager->get_entities(
					array('class' => entity),
					array('&',
						'tag' => array('com_content'),
						'tag' => array('category'),
						'array' => array(
							array('content_tags', $tag)
						)
					)
				);
				$category_that_page_is_in = false;
				foreach ($all_categories as $cur_category) {
					if ($cur_entity->in_array($cur_category->pages)) {
						$category_that_page_is_in = true;
						// Once it's true, it doesn't matter how many categories it applies to
						// I don't want it
						break;
					}
				}
				
				if ($category_that_page_is_in) {
					// This page belongs to a category...
					// So we don't want it..
					continue;
				} else {
					$menu_item->menu_item_guid = $cur_entity->guid;
					$menu_item->menu_item_link = pines_url('com_content','page', array('a' => $cur_entity->alias));
					// Pages have no children
					$menu_item->menu_item_name = $cur_entity->name;
					$menu_item->menu_item_alias = $cur_entity->alias;
					$top_level[] = $menu_item;
				}
			}
		}
		
		// This function always re-creates the natural top level.
		// Use get_sidemenu to retrieve the rest of what's needed I suppose...
		// I think we SHOULD save a natural menu here, but it can be updated and saved when
		// Updating is clicked.. that way if someone forgets to update the menu,
		// the natural menu is still saved.
		$sidemenu->natural_menu = $top_level;
		$sidemenu->save(); // Save before just to save this one single entity..
		return $top_level;
	}
	
	/**
	 * Save the side menu sort order and update with new pages
	 * 
	 * 
	 * return
	 */
	public function save_sidemenu($guid_order, $tag) {
		global $pines;
		// We are going to need to re create the natural menu, because the old
		// one might not contain new pages/categories in the children.
		
		// Get the newly saved entity
		$sidemenu = $pines->entity_manager->get_entity(
			array('class' => entity),
			array('&',
				'tag' => array('com_content', 'sidemenu')
			)
		);
		
		// This will save into the entity sidemenu, so we can pull the natural menu
		$sidemenu->natural_menu = $pines->com_content->create_sidemenu($tag);
		
		$sorted_menu = array();
		// count the guid order [guid, guid, etc]
		$order_count = count($guid_order);
		
		for ($i = 0; $i < $order_count; $i++) {
			// Get the already saved natural menu and we just care about top level right now
			foreach ($sidemenu->natural_menu as $cur_nat_top_menu_item) {
				if ($cur_nat_top_menu_item->menu_item_guid == $guid_order[$i]) {
					// This matches the first one in the order, and therefore 
					// we should generate the sorted_menu with this cur_top_menu_item.
					// Remember that cur_top_menu_item contains children arrays of other menu items, so
					// we don't need to recalculate that order. We also just recreated the menu too, so 
					// those children are completely accurate.
					$sorted_menu[] = $cur_nat_top_menu_item;
					// Now that we found it, we can get the hell out of this foreach and move to the next
					// guid in the guid_order.
					break;
				}
			}
		}
		
		if ($sorted_menu == $sidemenu->natural_menu) {
			// The sorted/updated menu above is still the same as the natural menu. Don't do anything
			// 
			// You may need to unset the sorted menu, or it will keep trying to grab the sorted instead
			// of using the natural, if a sorted was used and then a natural was chosen later.
			unset($sidemenu->sorted_menu);
			$sidemenu->save();
			return true; // return true for success...
		} else {
			$sidemenu->sorted_menu = $sorted_menu;
			return $sidemenu->save();
		}
	}
	
	/**
	 * Get the sorted menu
	 * 
	 * @param $type The default menu to attempt to get. Force natural menu if you want the natural.
	 * 
	 * return false if no menu exists. Return the natural or sorted menu if they exist. Force natural if type is natural.
	 * 
	 */
	public function get_sidemenu($type = 'sorted') {
		global $pines;
		// Get the saved sidemenu entity if it exists
		$sidemenu = $pines->entity_manager->get_entity(
			array('class' => entity),
			array('&',
				'tag' => array('com_content', 'sidemenu')
			)
		);
		
		if (!isset($sidemenu->guid))
			return false;
		
		if ($type == 'sorted' && !isset($sidemenu->sorted_menu))
			return $sidemenu->natural_menu; // sorted was requested  but doesn't exist.
		else if ($type == 'sorted')
			return $sidemenu->sorted_menu; // if sorted was requested and it does exist.
		else
			return $sidemenu->natural_menu; // if natural was selected.
		
	}
}

?>