<?php
/**
 * tpl_joomlatemplates class.
 *
 * @package Templates\joomlatemplates
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * tpl_joomlatemplates main class.
 *
 * @package Templates\joomlatemplates
 */
class tpl_joomlatemplates extends template {
	/**
	 * The template format.
	 * @var string $format
	 */
	public $format = 'html-desktop-5';
	/**
	 * The editor CSS location, relative to Pines' directory.
	 * @var string $editor_css
	 */
	public $editor_css = 'templates/tpl_joomlatemplates/css/editor.css';

	/**
	 * Format a menu in HTML.
	 * 
	 * @param array $menu The menu.
	 * @return string The menu's HTML.
	 */
	public function menu($menu) {
		global $pines;
		if (count($menu) == 1)
			return '';
		$top_class = '';
		switch ($menu[0]['position']) {
			case 'left':
			case 'right':
			case 'content':
			case 'user1':
			case 'user2':
			case 'user3':
			case 'user4':
				$return = '<ul class="dropdown dropdown-vertical">';
				break;
			case 'main_menu':
				$return = '<ul id="mainlevel-nav" class="dropdown dropdown-horizontal">';
				$top_class = 'mainlevel-nav';
				break;
			default:
				$return = '<ul class="dropdown dropdown-horizontal">';
				break;
		}
		foreach ($menu as $key => &$value) {
			if ((int) $key === $key) continue;
			$return .= $this->sub_menu($value, $top_class);
		}
		$return .= '</ul>';
		return $return;
	}

	/**
	 * Format a sub menu in HTML.
	 * 
	 * @param array &$menu The menu.
	 * @param string $top_class A class to apply to top-level elements.
	 * @return string The menu's HTML.
	 */
	public function sub_menu(&$menu, $top_class = 'ui-state-default') {
		$count = count($menu);
		// TODO: Remove target attribute. It's not XHTML 1.0 Strict.
		$return = '<li><a class="'.$top_class.($count > 1 ? ' dir' : '').'" href="'.
			(isset($menu[0]['href']) ? htmlspecialchars($menu[0]['href']) : 'javascript:void(0);').'"'.
			(isset($menu[0]['onclick']) ? " onclick=\"{$menu[0]['onclick']}\"" : '').
			(isset($menu[0]['target']) ? " target=\"{$menu[0]['target']}\"" : '')
			.'>'.htmlspecialchars($menu[0]['text']).'</a>';
		if ($count > 1) {
			$return .= '<ul>';
			foreach ($menu as $key => &$value) {
				if ((int) $key === $key) continue;
				$return .= $this->sub_menu($value);
			}
			$return .= '</ul>';
		}
		$return .= '</li>';
		return $return;
	}
}

?>