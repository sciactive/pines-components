<?php
/**
 * View a package.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/listpackages') )
	punt_user('You don\'t have necessary permission.', pines_url('com_plaza', 'listpackages'));

$module = new module('com_plaza', 'view_package', 'content');
$module->name = $_REQUEST['name'];
$module->package = $pines->com_package->db['packages'][$_REQUEST['name']];
if (!isset($module->package)) {
	$module->detach();
	pines_notice('Requested package could not be found.');
}

?>