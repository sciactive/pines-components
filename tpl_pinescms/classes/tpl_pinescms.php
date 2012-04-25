<?php
/**
 * tpl_pinescms class.
 *
 * @package Templates
 * @subpackage pinescms
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * tpl_pinescms main class.
 *
 * A nice default template for websites.
 *
 * @package Templates
 * @subpackage pinescms
 */
class tpl_pinescms extends template {
	/**
	 * The template format.
	 * @var string $format
	 */
	public $format = 'html-desktop-5';
	/**
	 * The editor CSS location, relative to Pines' directory.
	 * @var string $editor_css
	 */
	public $editor_css = 'templates/tpl_pinescms/css/editor.css';

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
				$return = '<ul class="main_menu dropdown dropdown-horizontal">';
				break;
			default:
				$return = '<ul class="dropdown dropdown-horizontal">';
				break;
		}
		foreach ($menu as $key => &$value) {
			if ((int) $key === $key) continue;
			$return .= $this->sub_menu($value);
		}
		$return .= '</ul>';
		return $return;
	}

	/**
	 * Format a sub menu in HTML.
	 *
	 * @param array &$menu The menu.
	 * @return string The menu's HTML.
	 */
	public function sub_menu(&$menu) {
		$count = count($menu);
		$return = '<li class="'.($menu[0]['current_page'] || $menu[0]['current_page_parent'] ? 'current_page' : '').'"><a'.($count > 1 ? ' class="dir"' : '').' href="'.
			(isset($menu[0]['href']) ? htmlspecialchars($menu[0]['href']) : 'javascript:void(0);').'"'.
			(isset($menu[0]['onclick']) ? " onclick=\"{$menu[0]['onclick']}\"" : '').
			(isset($menu[0]['target']) ? " target=\"{$menu[0]['target']}\"" : '')
			.'>'.htmlspecialchars($menu[0]['text']).'</a>';
		if ($count > 0) {
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