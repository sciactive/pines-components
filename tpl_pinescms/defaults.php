<?php
/**
 * tpl_pinescms' configuration.
 *
 * @package Pines
 * @subpackage tpl_pinescms
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'variant',
		'cname' => 'Page Variant/Layout',
		'description' => 'The layout of the page. On two column layouts, the sidebars are combined into one. On full page, the sidebars are not available.',
		'value' => 'framework',
		'options' => array(
			'Pines CMS (cms)' => 'cms',
			'Pines Framework (framework)' => 'framework',
		),
		'peruser' => true,
	),
);

?>