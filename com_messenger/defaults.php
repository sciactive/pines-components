<?php
/**
 * com_messenger's configuration defaults.
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
	array(
		'name' => 'xmpp_server',
		'cname' => 'XMPP Hostname',
		'description' => 'The hostname of the XMPP (Jabber) server.',
		'value' => 'megatron.smart108.com',
		'peruser' => true,
	),
	array(
		'name' => 'xmpp_bosh_url',
		'cname' => 'XMPP BOSH URL',
		'description' => 'The BOSH URL of the XMPP server. If not on this server, the remote server must have a crossdomain.xml file to allow this server to communicate with it. You can use the ejabberd web root folder at '.dirname(__FILE__).'/includes/ejabberd-web-root/',
		//'value' => '/http-bind',
		'value' => 'http://megatron.smart108.com:5280/http-bind',
		'peruser' => true,
	),
	array(
		'name' => 'use_proxy',
		'cname' => 'Use Built In Proxy',
		'description' => 'Use the built in proxy to request the BOSH URL. Must have cURL installed to use this.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'proxy_timeout',
		'cname' => 'Proxy Time Limit',
		'description' => 'Time limit (in seconds) for the proxy script to execute. Should be greater than the wait value of your BOSH server. (Like, 2x greater.)',
		'value' => 120,
		'peruser' => true,
	),
	/*array(
		'name' => 'guest_access',
		'cname' => 'Guest Access',
		'description' => 'Allow guests to login using a fictional guest account. (Requires use of the ejabberd_auth.php script.)',
		'value' => true,
		'peruser' => true,
	),*/
);

?>