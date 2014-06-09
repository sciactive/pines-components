<?php

/*
 * com_cache's action to look up a user's hash.
 * 
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');


$pines->page->override = true;

if ( !gatekeeper('com_cache/managecache') )
	$result = false;

if ($result !== false) { 
	if (isset($_REQUEST['username'])) {
		$username = $_REQUEST['username'];
		$result = $pines->com_cache->lookup($username);
	} else {
		$result = false;
	}
}

$pines->page->override_doc(json_encode($result));
?>