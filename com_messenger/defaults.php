<?php
/**
 * com_messenger's configuration defaults.
 *
 * @package Pines
 * @subpackage com_messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'xmpp_server',
		'cname' => 'XMPP Hostname',
		'description' => 'The hostname of the xmpp server.',
		'value' => 'localhost',
		'peruser' => true,
	),
	array(
		'name' => 'xmpp_support_user',
		'cname' => 'XMPP Support User',
		'description' => 'The username of the xmpp support user.',
		'value' => 'support',
		'peruser' => true,
	),
);

?>