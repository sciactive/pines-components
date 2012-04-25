<?php
/**
 * com_jquery's information.
 *
 * @package Components
 * @subpackage jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'jQuery',
	'author' => 'SciActive',
	'version' => '1.7.1-1.8.17-1.1.0dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'jQuery JavaScript library',
	'description' => 'Provides the jQuery JavaScript library and the jQuery UI JavaScript and CSS framework.',
	'depend' => array(
		'pines' => '<2'
	),
);

?>