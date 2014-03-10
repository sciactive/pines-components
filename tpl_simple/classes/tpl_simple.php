<?php
/**
 * tpl_simple class.
 *
 * @package Templates\simple
 * @license http://opensource.org/licenses/MIT
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright Angela Murrell
 * @link http://verticolabs.com
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * tpl_simple main class.
 *
 * A nice looking, fluid width template based on Bootstrap.
 *
 * @package Templates\bootstrap
 */
class tpl_simple extends template {
	/**
	 * The template format.
	 * @var string $format
	 */
	public $format = 'html-desktop-5';
	
	/**
	 * Format a menu in HTML.
	 * 
	 * @param array $menu The menu.
	 * @return string The menu's HTML.
	 */
	public function menu($menu) {
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
			default:
				$return = '<ul class="nav">';
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
	 * @param bool $top_level Whether this menu is a top-level menu.
	 * @return string The menu's HTML.
	 */
	public function sub_menu(&$menu, $top_level = true) {
		$count = count($menu);
		$return = '<li class="'.(($count > 1) ? ($top_level ? 'dropdown' : 'dropdown-submenu') : '').($menu[0]['current_page'] || $menu[0]['current_page_parent'] ? ' active' : '').'"><a href="'.
			(isset($menu[0]['href']) ? htmlspecialchars($menu[0]['href']) : 'javascript:void(0);').'"'.
			(isset($menu[0]['onclick']) ? " onclick=\"{$menu[0]['onclick']}\"" : '').
			(isset($menu[0]['target']) ? " target=\"{$menu[0]['target']}\"" : '').
			(($count > 1) ? ' data-toggle="dropdown" class="dropdown-toggle"' : '')
			.'>'.htmlspecialchars($menu[0]['text']).
			(($count > 1 && $top_level) ? '<b class="caret"></b>' : '').'</a>';
		if ($count > 1) {
			$return .= '<ul class="dropdown-menu">';
			foreach ($menu as $key => &$value) {
				if ((int) $key === $key) continue;
				$return .= $this->sub_menu($value, false);
			}
			$return .= '</ul>';
		}
		$return .= '</li>';
		return $return;
	}
        
        /**
	 * Load Template CSS
         * 
         * Will send array to compression function.
         * Also sends last modified date based on files sent.
         * 
         * @param files Array An array of css paths.
	 * @return string url of stylesheet.
	 */
	public function load_template_css($files) {
            global $pines;
            $last_mod = 0;
            foreach ($files as $cur_url) {
               if (preg_match('/^htt/', $cur_url))
                    continue;
               $mtime = filemtime($cur_url);
               $last_mod = ($mtime > $last_mod) ? $mtime : $last_mod;
            }
            $links = urlencode(implode('%%%', $files));
            $url = htmlspecialchars($pines->config->compressed_url_root)."templates/tpl_simple/buildcss.php?mtime=".$last_mod."&css=" . $links;
            return $url;
	}
        
        /**
	 * Load Template JS
         * 
         * Will send array to compression function.
         * Also sends last modified date based on files sent.
         * 
         * @param files Array An array of css paths.
	 * @return string url of stylesheet.
	 */
	public function load_template_js($files) {
            global $pines;
            $last_mod = 0;
            foreach ($files as $cur_url) {
               $mtime = filemtime($cur_url);
               $last_mod = ($mtime > $last_mod) ? $mtime : $last_mod;
            }
            $system = $_SERVER['DOCUMENT_ROOT'].htmlspecialchars($pines->config->location).'system/includes/js.php';
            $system_mod = filemtime($system);
            $last_mod = ($system_mod > $last_mod) ? $system_mod : $last_mod;
            $links = urlencode(implode('%%%', $files));
            $loc = htmlspecialchars($pines->config->location);
            $url = htmlspecialchars($pines->config->compressed_url_root)."templates/tpl_simple/buildjs.php?loc=".$loc."&mtime=".$last_mod."&js=" . $links;
            return $url;
	}
}