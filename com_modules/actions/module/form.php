<?php
/**
 * Provide a form to edit a module's options.
 *
 * @package Pines
 * @subpackage com_modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_modules/editmodule') && !gatekeeper('com_modules/newmodule') )
	punt_user('You don\'t have necessary permission.', pines_url('com_modules', 'module/edit', array('id' => $_REQUEST['id'])));

$pines->page->override = true;

list($component, $modname) = explode('/', $_REQUEST['type'], 2);
$component = clean_filename($component);
$modules = include("components/$component/modules.php");
$form = $modules[$modname]['form'];

$module = new module($component, $form);
$pines->page->override_doc($module->render());

?>