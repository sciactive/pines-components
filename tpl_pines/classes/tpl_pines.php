<?php
/**
 * tpl_pines class.
 *
 * @package Templates\pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * tpl_pines main class.
 *
 * A nice looking, fluid width template based on jQuery UI.
 *
 * @package Templates\pines
 */
class tpl_pines extends template {
	/**
	 * The template format.
	 * @var string $format
	 */
	public $format = 'html-desktop-5';
	/**
	 * The editor CSS location, relative to Pines' directory.
	 * @var string $editor_css
	 */
	public $editor_css = 'templates/tpl_pines/css/editor.css';

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
				if (!$pines->config->tpl_pines->buttonized_menu)
					$header_style = true;
			default:
				$return = '<ul class="dropdown dropdown-horizontal">';
				break;
		}
		foreach ($menu as $key => &$value) {
			if ((int) $key === $key) continue;
			$return .= $this->sub_menu($value, $header_style);
		}
		$return .= '</ul>';
		return $return;
	}

	/**
	 * Format a sub menu in HTML.
	 * 
	 * @param array &$menu The menu.
	 * @param bool $header_style Whether the menu buttons should use a header style.
	 * @param bool $top_level Whether this menu is a top-level menu.
	 * @return string The menu's HTML.
	 */
	public function sub_menu(&$menu, $header_style = false, $top_level = true) {
		$count = count($menu);
		$return = '<li><a class="'.($header_style ? 'ui-widget-header' : 'ui-state-default').($menu[0]['current_page'] ? ($top_level ? '' : ' ui-priority-primary') : '').($menu[0]['current_page_parent'] ? ($top_level ? '' : ' ui-priority-primary') : '').'" href="'.
			(isset($menu[0]['href']) ? htmlspecialchars($menu[0]['href']) : 'javascript:void(0);').'"'.
			(isset($menu[0]['onclick']) ? " onclick=\"{$menu[0]['onclick']}\"" : '').
			(isset($menu[0]['target']) ? " target=\"{$menu[0]['target']}\"" : '')
			.'>'.
			($top_level && ($menu[0]['current_page'] || $menu[0]['current_page_parent']) ? '<span class="ui-icon ui-icon-triangle-1-s"></span>' : '').
			htmlspecialchars($menu[0]['text']).
			($count > 1 ? '<span class="ui-icon ui-icon-triangle-1-se"></span>' : '').'</a>';
		if ($count > 1) {
			$return .= '<ul>';
			foreach ($menu as $key => &$value) {
				if ((int) $key === $key) continue;
				$return .= $this->sub_menu($value, false, false);
			}
			$return .= '</ul>';
		}
		$return .= '</li>';
		return $return;
	}

	public function url($component = null, $action = null, $params = array(), $full_location = false) {
		// Was needed for redirection, but not anymore. ?? Or is it??
		if ($_REQUEST['tpl_pines_ajax'] == 1)
			$params['tpl_pines_ajax'] = 1;
		return parent::url($component, $action, $params, $full_location);
	}
}

?>