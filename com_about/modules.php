<?php
/**
 * com_about's modules.
 *
 * @package Components\about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'pinesfeed' => array(
		'cname' => 'Pines News Feed',
		'description' => 'Pines Framework news feed from Twitter.',
		'view' => 'modules/pinesfeed',
		'type' => 'widget',
		'widget' => array(
			'default' => true,
			'depends' => array(
				'ability' => 'com_about/pinesfeed',
			),
		),
	),
);

?>