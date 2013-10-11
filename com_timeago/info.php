<?php
/**
 * com_timeago's information.
 *
 * @package Components\timeago
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Timeago',
	'author' => 'SciActive (Component), http://ryan.mcgeary.org/ (JavaScript)',
	'version' => '1.0.1',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Time Ago jQuery Plugin',
	'description' => 'A JavaScript jQuery component. Use to convert timestamps to "time ago" strings.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery'
	),
);

?>