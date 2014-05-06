<?php

/*
 * com_cache's refresh action to clear cache depending on criteria.
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
	if (isset($_REQUEST['domain'])) {
		$domain = $_REQUEST['domain'];
		$file_name = (isset($_REQUEST['file_name'])) ? $_REQUEST['file_name'] : null;
		// Either equals specific domain or all
		$result = $pines->com_cache->refreshconfig($domain, $file_name);
	} else {
		$result = false;
	}
}

$pines->page->override_doc(json_encode($result));
?>