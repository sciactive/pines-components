<?php
/**
 * Makes a side menu navigation of certain pages based on tags.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Grey Vugrin <greyvugrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->sub_position;

// It gets the sidemenu entity, checks for a sorted_menu or uses the natural_menu.
$menu = $pines->com_content->get_sidemenu(); // by default gets sorted or returns natural or returns false if not ready yet.
?>
<style type="text/css">
.side-menu-list .top-category, .top-category+i, .top-sidemenu-item {
	cursor:	pointer;
}
.side-menu-list .top-category+i {
	clear: right;
}
ul.side-menu-list {
	margin-left: 0;
}
.side-menu-list li {
	list-style: none;
}
.side-menu-list {
    margin-bottom: 0;
    padding: 15px 20px;
	box-sizing: border-box;	
}
.sub-menu ul {
	list-style-type: none;
}
.sub-sub-cat+ul {
	margin-left: 10px !important;
}
</style>
<div>
<?php
	// Build submenu function.
	$sub_menu = '<div class="sub-menu-container">';
	
	function make_sub_menu($cur_category, $level = 0) {
		// Start sub-menu div for this category
		$add_sub_menu = '';
		// Construct Pages
		if (is_array($cur_category->menu_item_pages)) {
			$page_margin = ($level < 1) ? 'style="margin-left: 0px;"' : '';
			$add_sub_menu .= '<ul class="sub-cat-level'.$level.' sub-page" '.$page_margin.'>';
			foreach ($cur_category->menu_item_pages as $cur_page) {
				$add_sub_menu .= '<li class="sidemenu-page level'.$level.'">';
				$add_sub_menu .= '<a href="'.$cur_page->menu_item_link.'" class="sub-page">'.$cur_page->menu_item_name.'</a>';
				$add_sub_menu .= '</li>';
			}
			$add_sub_menu .= '</ul>';
		}
//		// Construct Categories if there are any...
		if (is_array($cur_category->menu_item_children)) {
			$add_sub_menu .= '<ul class="sub-cat-level'.$level.' sub-cat" style="margin-left: '.($level * 5).'px">';
			foreach ($cur_category->menu_item_children as $cur_child) {
				$has_cats_class = ($level > 0) ? 'cat-has-pages' : '';
				$add_sub_menu .= '<li class="sidemenu-category level'.$level.' '.$has_cats_class.'">';
				// If it's a sub sub or more add the chevron down things
				if ($level > 0)
					$add_sub_menu .= '<i class="icon-chevron-down"></i> ';
				$add_sub_menu .= '<a class="sub-category">'.$cur_child->menu_item_name.'</a>';
				// Right here we send it back to this function, it has to have children
				$new_level = $level + 1;
				$add_sub_menu .= make_sub_menu($cur_child, $new_level);
				$add_sub_menu .= '</li>';
			}
			$add_sub_menu .= '</ul>';
		}
		return $add_sub_menu;
	}

// Prepare from that array the html we need
if ($menu === false || empty($this->sub_position) || empty($this->tag)) { ?>
	<p>Please setup the module options for the side menu!</p>
<?php } else { 
	// Put the top_level stuff below.
	echo '<ul class="side-menu-list">';
	foreach ($menu as $cur_top_menu_item) { 
		if ($cur_top_menu_item->menu_item_type == 'category') {
			echo '<li class="top-sidemenu-item"><a class="top-category" data-id="'.$cur_top_menu_item->menu_item_guid.'">'.$cur_top_menu_item->menu_item_name.'</a><i class="icon-chevron-right pull-right"></i></li>';
			// It has to have children...
			$sub_menu .= '<div class="sub-menu" data-id="'.$cur_top_menu_item->menu_item_guid.'"><div class="top-category-title"><h4 style="margin: 0;">'.$cur_top_menu_item->menu_item_name.'</h4></div>';
			$sub_menu .= make_sub_menu($cur_top_menu_item, 0);
			$sub_menu .= '</div>';
		} else { 
			echo '<li class="top-sidemenu-item"><a href="'.$cur_top_menu_item->menu_item_link.'" class="top-page">'.$cur_top_menu_item->menu_item_name.'</a></li>';
		}
	}
	echo '</ul>';
	$sub_menu .= '</div>';
	$sub_module = new module('system', 'null', $this->sub_position);
	$sub_module->content($sub_menu);
} ?>
</div>
