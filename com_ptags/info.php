<?php
/**
 * com_ptags' information.
 *
 * @package Components\ptags
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Pines Tags',
	'author' => 'SciActive',
	'version' => '1.1.2-1.0.1beta2',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Pines Tags jQuery plugin',
	'description' => 'A JavaScript tag editor jQuery component. Supports many features, and fully themeable using jQuery UI.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery'
	),
);

?>