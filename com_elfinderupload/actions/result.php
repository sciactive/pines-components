<?php
/**
 * Show the results of the file uploader test.
 *
 * @package Components
 * @subpackage elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_elfinder/finder') && !gatekeeper('com_elfinder/finderself') )
	punt_user(null, pines_url('com_elfinderupload', 'test'));

$module = new module('com_elfinderupload', 'result', 'content');
$module->file = $_REQUEST['file'];
$module->folder = $_REQUEST['folder'];
$module->files = explode('//', $_REQUEST['files']);
$module->tmpfile = $_REQUEST['tmpfile'];

?>