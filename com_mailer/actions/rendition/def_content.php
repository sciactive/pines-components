<?php
/**
 * Get a rendition's default content.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_mailer/editrendition') && !gatekeeper('com_mailer/newrendition') )
	punt_user(null, pines_url('com_mailer', 'rendition/edit', array('id' => $_REQUEST['id'])));

$pines->page->override = true;
header('Content-Type: application/json');

list($component, $defname) = explode('/', $_REQUEST['type'], 2);
$component = clean_filename($component);
/**
 * Retrieve module list.
 */
$mails = include("components/$component/mails.php");
$def = $mails[$defname];
$view = $def['view'];
$view_callback = $def['view_callback'];
if (!isset($view) && !isset($view_callback))
	throw new HttpServerException(null, 500);

if (isset($view))
	$module = new module($component, $view);
else {
	$module = call_user_func($view_callback, null, null, $options);
	if (!$module)
		throw new HttpServerException(null, 500);
}

$content = $module->render();

$pines->page->override_doc(json_encode(array('content' => $content, 'subject' => $module->title)));

?>