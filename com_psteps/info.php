<?php
/**
 * com_psteps' information.
 *
 * @package Components\psteps
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Pines Steps',
	'author' => 'SciActive',
	'version' => '0.0.1alpha-0.0.1alpha',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Pines Steps jQuery plugin',
	'description' => 'A JavaScript Steps jQuery component. Allows for linear navigation similar to tabs, but with progress indication and validation.',
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery&com_bootstrap'
	),
);

?>