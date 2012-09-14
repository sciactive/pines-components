<?php
/**
 * com_entityhelper's information.
 *
 * @package Components\entityhelper
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Entity Helper',
	'author' => 'SciActive',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Entity interaction helper.',
	'description' => "Provides a helpful dialog when you click on links to certain entities.\n\nUse the classcheck action to see whether classes have custom helpers.",
	'depend' => array(
		'pines' => '<3',
		'component' => 'com_jquery&com_bootstrap&com_pform'
	),
);

?>