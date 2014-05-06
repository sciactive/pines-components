<?php

/*
 * com_cache's manager.
 * 
 * Generates the cacheoptions.php in core/system
 * to be utilized by index.php.
 * 
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_cache/managecache') )
	punt_user(null, pines_url());

$pines->com_cache->loadconfig(($_REQUEST['use_generic'] == 'true'), $_REQUEST['import'], ($_REQUEST['getfilecount'] == 'true'));

?>