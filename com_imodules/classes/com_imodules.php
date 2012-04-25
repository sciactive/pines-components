<?php
/**
 * com_imodules class.
 *
 * @package Components
 * @subpackage imodules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_imodules main class.
 *
 * An inline module parser. It allows placement of various modules directly in
 * content.
 *
 * @package Components
 * @subpackage imodules
 */
class com_imodules extends component {
	/**
	 * Parse and replace inline modules in content.
	 *
	 * Inline module syntax:
	 *
	 * <pre>
	 * [com_component/type attribute="value" second="second value"]inline content[/com_component/type]
	 * or
	 * [com_component/type attribute='value' second='second value']inline content[/com_component/type]
	 * or
	 * [com_component/type attribute=`value` second=`second value`]inline content[/com_component/type]
	 * </pre>
	 *
	 * Without attributes:
	 *
	 * <pre>
	 * [com_component/type]inline content[/com_component/type]
	 * </pre>
	 *
	 * Without inline content:
	 *
	 * <pre>
	 * [com_component/type attribute="value" second="second value" /]
	 * </pre>
	 *
	 * Without attributes or inline content:
	 *
	 * <pre>
	 * [com_component/type /]
	 * </pre>
	 *
	 * The attributes will be set as properties on the module. The inline
	 * content will be placed in the property "icontent".
	 *
	 * @param string &$content The content to parse.
	 */
	public function parse_imodules(&$content) {
		$pattern = '/\[([\w\d_\/]+)( [^\]]*)?\/\]|\[([\w\d_\/]+)( [^\]]*)?\](.*?)\[\/\3\]/sS';
		$matches = array();
		$offset = 0;
		preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE);
		while ($matches) {
			// Read the imodule entry.
			$short = empty($matches[1][0]) ? false : true;
			$type = clean_filename($short ? $matches[1][0] : $matches[3][0]);

			// Determine the module.
			list ($component, $modname) = explode('/', $type, 2);
			$component = clean_filename($component);
			if (!file_exists("components/$component/modules.php")) {
				// If the module doesn't exist, skip it.
				$offset = $matches[0][1] + strlen($matches[0][0]);
				preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset);
				continue;
			}
			$include = include("components/$component/modules.php");
			$view = $include[$modname]['view'];
			$view_callback = $include[$modname]['view_callback'];
			if (!isset($view) && !isset($view_callback))
				continue;
			if (isset($include[$modname]['type']) && !preg_match('/\bimodule\b/', $include[$modname]['type'])) {
				// If the module doesn't exist or isn't an imodule, skip it.
				$offset = $matches[0][1] + strlen($matches[0][0]);
				preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset);
				continue;
			}
			unset($include);

			// Read the attributes.
			$attrs = ($short ? $matches[2][0] : $matches[4][0]);
			$attr_matches = array();
			if (preg_match_all('/(\w+)=("|\'|`)(.*?)\2/', $attrs, $attr_matches))
				$attrs = array_combine($attr_matches[1], $attr_matches[3]);
			else
				$attrs = array();
			$icontent = ($short ? '' : $matches[5][0]);

			// Build a module.
			if (isset($view))
				$module = new module($component, $view);
			else {
				$module = call_user_func($view_callback, null, null, $attrs);
				if (!$module)
					continue;
			}
			$module->show_title = false;
			foreach ($attrs as $name => $value) {
				switch ($name) {
					case 'muid':
					case 'title':
					case 'note':
					case 'classes':
					case 'content':
					case 'component':
					case 'view':
					case 'position':
					case 'order':
					case 'show_title':
					case 'is_rendered':
					case 'data_container':
						break;
					default:
						$module->$name = $value;
						break;
				}
			}
			$module->icontent = $icontent;

			// Replace the content.
			$new_value = $module->render();
			$content = substr_replace($content, $new_value, $matches[0][1], strlen($matches[0][0]));

			// Find the next match.
			preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE, $offset);
		}
	}
}

?>