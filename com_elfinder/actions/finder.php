<?php
/**
 * Provide a file manager.
 *
 * @package Components\elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_elfinder/finder') && !gatekeeper('com_elfinder/finderself') )
	punt_user(null, pines_url('com_elfinder', 'finder'));

$module = new module('com_elfinder', 'finder', 'content');
$module->ckeditor = ($_REQUEST['ckeditor'] == 'true');
$module->absolute = ($_REQUEST['absolute'] == 'true');

?>