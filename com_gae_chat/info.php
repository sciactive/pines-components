<?php
/**
 * com_gae_chat's information.
 *
 * @package Components\gae_chat
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'GAE Chat',
	'author' => 'Mohammed Ahmed',
	'version' => '1.0.0',
	'license' => 'none',
	'website' => 'https://108way.com',
	'short_description' => 'A Chat client that uses Google App Engine',
	'description' => 'A chat client hosted on Google\'s App Engine. There is an employee side and a customer side.
            Uses the Channels API in App Engine to facilate chat between employees and customers.',
	'depend' => array(
		'pines' => '<3',
                'component' => 'com_jquery&com_bootstrap&com_timeago'
	),
        'abilities' => array(
            array('employeechat', 'Employee Can Chat', 'Employee has ability to chat with customers'),
        ),
);

?>