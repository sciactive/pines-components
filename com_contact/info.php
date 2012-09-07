<?php
/**
 * com_contact's information.
 *
 * @package Components\contact
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Contact Form',
	'author' => 'SciActive',
	'version' => '0.02.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'A simple contact form',
	'description' => 'A contact form used to send messages from the website.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_bootstrap&com_pform'
	),
);

?>