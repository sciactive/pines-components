<?php
/**
 * com_cache's information.
 *
 * @package Components\cache
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Cache',
	'author' => 'SciActive',
	'version' => '1.1.1dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Manage Caching',
	'description' => 'Manage how PHP caching is leveraged.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery&com_bootstrap&com_pnotify&com_jstree&com_timeago'
	),
	'abilities' => array(
		array('managecache', 'Manage Cache', 'User can manage all of caching'),
	),
);

?>