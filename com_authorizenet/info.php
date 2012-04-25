<?php
/**
 * com_authorizenet's information.
 *
 * @package Components
 * @subpackage authorizenet
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Authorize.Net Interface',
	'author' => 'SciActive',
	'version' => '1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Payment gateway interface',
	'description' => 'Processes credit transactions through the Authorize.Net payment gateway.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_sales&com_jquery&com_bootstrap&com_pform'
	),
);

?>