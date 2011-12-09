<?php
/**
 * Main page of the Joomla template adapter.
 *
 * @package Pines
 * @subpackage tpl_joomlatemplates
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
header('Content-Type: text/html');

// Get the current Joomla! template.
$jtemplate = $pines->config->tpl_joomlatemplates->template;

// Get the directory for the template.
$jtdir = "templates/tpl_joomlatemplates/templates/$jtemplate/";
if (!file_exists($jtdir))
	die('Required Joomla! template is missing!');

/**
 * Joomla! template adapter. This class will act as the Joomla! template class.
 */
include('templates/tpl_joomlatemplates/classes/jtemplate_adapter.php');

$jtclass = new jtemplate_adapter($jtemplate, $jtdir);
$jtclass->render();

?>