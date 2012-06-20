<?php
/**
 * com_su's information.
 *
 * @package Components\su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Switch User',
	'author' => 'SciActive',
	'version' => '1.1.0beta',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Switch to a different user',
	'description' => 'Allow users to login as a different user quickly, without having to logout first.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'user_manager',
		'component' => 'com_jquery&com_pnotify&com_pform'
	),
	'abilities' => array(
		array('switch', 'User Switcher', 'User can see and use the user switcher.'),
		array('nopassword', 'Switch Without Password', 'User can switch to any other user without providing a password.')
	),
);

?>