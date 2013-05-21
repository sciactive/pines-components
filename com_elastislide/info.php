<?php
/**
 * com_elastislide's information.
 *
 * @package Components\elastislide
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Elastislide',
	'author' => 'SciActive (Component), Codrops (JavaScript)',
	'version' => '1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'An image carousel jQuery plugin.',
	'description' => 'Ideal for Galleries and Iconic Scrolling Menus.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery'
	),
);

?>