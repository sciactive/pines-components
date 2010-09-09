<?php
/**
 * Return a JSON object of package info.
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
	punt_user(null, pines_url('com_plaza', 'package/list'));

$pines->page->override = true;
$package = $pines->com_package->db['packages'][$_REQUEST['name']];
if (isset($package))
	$pines->page->override_doc(json_encode($package));

?>