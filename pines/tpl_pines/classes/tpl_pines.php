<?php
/**
 * tpl_pines class.
 *
 * @package Pines
 * @subpackage tpl_pines
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * tpl_pines main class.
 *
 * A nice looking, fluid width template based on jQuery UI.
 *
 * @package Pines
 * @subpackage tpl_pines
 */
class tpl_pines extends template {
	/**
	 * The template format.
	 * @var string $format
	 */
	var $format = 'xhtml-1.0-strict-desktop';
	/**
	 * The editor CSS location, relative to Pines' directory.
	 * @var string $editor_css
	 */
	var $editor_css = 'templates/tpl_pines/css/editor.css';

	/**
	 * Format a menu in HTML.
	 * 
	 * @param array $menu The menu.
	 * @return string The menu's HTML.
	 */
	public function menu($menu) {
		if (count($menu) == 1)
			return '';
		$return = $menu[0]['position'] == 'main_menu' ? '<ul class="dropdown dropdown-horizontal">' : '<ul class="dropdown dropdown-vertical">';
		foreach ($menu as $key => $value) {
			if (is_int($key)) continue;
			$return .= $this->sub_menu($value);
		}
		$return .= '</ul>';
		return $return;
	}

	/**
	 * Format a sub menu in HTML.
	 * 
	 * @param array $menu The menu.
	 * @return string The menu's HTML.
	 */
	public function sub_menu($menu) {
		//$return = '<li class="ui-state-default"><a href="'.
		//	(isset($menu[0]['href']) ? $menu[0]['href'] : '#').
		//	(isset($menu[0]['onclick']) ? "\" onclick=\"{$menu[0]['onclick']}\">" : '">').
		//	htmlentities($menu[0]['text']).
		//	(count($menu) > 1 ? '<span class="ui-icon ui-icon-carat-1-s"></span>' : '').'</a>';
		$return = '<li class="ui-state-default"><a'.
			(count($menu) > 1 ? ' class="dir" href="' : ' href="').
			(isset($menu[0]['href']) ? $menu[0]['href'] : '#').
			(isset($menu[0]['onclick']) ? "\" onclick=\"{$menu[0]['onclick']}\">" : '">').
			htmlentities($menu[0]['text']).'</a>';
		if (count($menu) > 1) {
			$return .= '<ul>';
			foreach ($menu as $key => $value) {
				if (is_int($key)) continue;
				$return .= $this->sub_menu($value);
			}
			$return .= '</ul>';
		}
		$return .= '</li>';
		return $return;
	}
}

?>