<?php
/**
 * Provide a form to edit a module's options.
 *
 * @package Components\modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_modules/editmodule') && !gatekeeper('com_modules/newmodule') )
	punt_user(null, pines_url('com_modules', 'module/edit', array('id' => $_REQUEST['id'])));

$pines->page->override = true;
header('Content-Type: application/json');

list($component, $modname) = explode('/', $_REQUEST['type'], 2);
$component = clean_filename($component);
/**
 * Retrieve module list.
 */
$modules = include("components/$component/modules.php");
$def = $modules[$modname];
$view = $def['form'];
$view_callback = $def['form_callback'];
if (!isset($view) && !isset($view_callback))
	throw new HttpServerException(null, 500);

$options = (array) json_decode($_REQUEST['data'], true);

if (isset($view))
	$module = new module($component, $view);
else {
	$module = call_user_func($view_callback, null, null, $options);
	if (!$module)
		throw new HttpServerException(null, 500);
}

// Include the options.
foreach ($options as $cur_option) {
	switch ($cur_option['name']) {
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
			$name = $cur_option['name'];
			if (substr($name, -2) == '[]') {
				$name = substr($name, 0, -2);
				if ((array) $module->$name !== $module->$name)
					$module->$name = array();
				array_push($module->$name, $cur_option['value']);
			} else
				$module->$name = $cur_option['value'];
			break;
	}
}

$pines->page->modules['head'] = array();
$content = $module->render();
// Render any modules placed into the head. (In case they add more.)
foreach ($pines->page->modules['head'] as $cur_module)
	$cur_module->render();
// Now get their content.
$head = '';
foreach ($pines->page->modules['head'] as $cur_module)
	$head .= $cur_module->render();

$pines->page->override_doc(json_encode(array('content' => $content, 'head' => $head)));

?>