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
			$root = $_SERVER['DOCUMENT_ROOT'].htmlspecialchars($pines->config->location);
			foreach ($files as &$cur_url) {
				$regex = '/'.preg_quote($root).'/';
				if (preg_match('/^htt/', $cur_url))
                    continue;
				if (preg_match('/'.preg_quote($root, '/').'/', $cur_url)) {
					$cur_url = preg_replace('/'.preg_quote($root, '/').'/', '', $cur_url);
				}
				$mtime = filemtime($cur_url);
				$last_mod = ($mtime > $last_mod) ? $mtime : $last_mod;
            }
			unset($cur_url);
			
			// Fix the root if location starts with htt
			if (preg_match('/^htt/', $pines->config->location))
				$root = $_SERVER['DOCUMENT_ROOT'];
			
            $links = urlencode(implode('%%%', $files));
            $url = htmlspecialchars($pines->config->compressed_url_root).'templates/tpl_simple/buildcss.php?mtime='.$last_mod.'&root='.$root.'&css='.$links;
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
			$root = $_SERVER['DOCUMENT_ROOT'].htmlspecialchars($pines->config->location);
			$full_location = 'http'.(($_SERVER['HTTPS'] == "on") ? 's://' : '://').$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], P_INDEX));
			$rela_location = substr($_SERVER['PHP_SELF'], 0, strripos($_SERVER['PHP_SELF'], P_INDEX));
            foreach ($files as &$cur_url) {
				if (preg_match('/^htt/', $cur_url))
                    continue;
				if (preg_match('/'.preg_quote($root, '/').'/', $cur_url)) {
					$cur_url = preg_replace('/'.preg_quote($root, '/').'/', '', $cur_url);
				}
				$mtime = filemtime($cur_url);
				$last_mod = ($mtime > $last_mod) ? $mtime : $last_mod;
            }
			unset($cur_url);
			
			// Fix the root if location starts with htt
			if (preg_match('/^htt/', $pines->config->location))
				$root = $_SERVER['DOCUMENT_ROOT'];
			
            $system = $root.'system/includes/js.php';
            $system_mod = filemtime($system);
            $last_mod = ($system_mod > $last_mod) ? $system_mod : $last_mod;
            $links = urlencode(implode('%%%', $files));
            $url = htmlspecialchars($pines->config->compressed_url_root).'templates/tpl_simple/buildjs.php?mtime='.$last_mod.'&root='.$root.'&full='.$full_location.'&rela='.$rela_location.'&js='.$links;
            return $url;
	}
}