<?php
/**
 * com_messenger's information.
 *
 * @package Components\messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Pines Messenger',
	'author' => 'SciActive (Component, Pines Chat), Jack Moffitt (Strophe.js)',
	'version' => '0.10.2dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'An instant messenger',
	'description' => 'An instant messenger that works within the website. Designed to use an XMPP (Jabber) server. Includes a script to authenticate users for the ejabberd server.',
	'depend' => array(
		'pines' => '<2',
		'service' => 'user_manager&entity_manager&icons',
		'component' => 'com_jquery&com_bootstrap&com_pform&com_soundmanager',
	),
);

?>