<?php
/**
 * com_popeye's information.
 *
 * @package Components\popeye
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'jQuery.popeye',
	'author' => 'SciActive (Component), Christoph Schuessler (JavaScript)',
	'version' => '1.0.1dev',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'jQuery.popeye jQuery plugin',
	'description' => 'A JavaScript image slideshow jQuery component.',
	'depend' => array(
		'pines' => '<2',
		'component' => 'com_jquery'
	),
);

?>