<?php
/**
 * com_timeoutnotice's information.
 *
 * @package Pines
 * @subpackage com_timeoutnotice
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Timeout Notice',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Display a session timeout notice',
	'description' => "Present a notice to users after a specified period of inactivity. They can then extend their session, or be redirected to a login page or the homepage.\n\nThis component changes the way user sessions time out, so you must set your PHP session timeout to be longer than the timeout you setup for this component.",
	'depend' => array(
		'pines' => '<2',
		'service' => 'user_manager',
		'component' => 'com_jquery&com_pnotify&com_pform'
	),
);

?>