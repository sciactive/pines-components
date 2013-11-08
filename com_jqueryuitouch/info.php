<?php
/**
 * com_jqueryuitouch's information.
 *
 * @package Components\jqueryuitouch
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'jQueryUI Touch',
	'author' => 'SciActive (Component), Dave Furfero (JavaScript)',
	'version' => '1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'jQuery UI Touch plugin',
	'description' => 'A JavaScript Plugin that enhances the jQuery UI Library ideally for mobile.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery'
	),
);

?>