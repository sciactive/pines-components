<?php
/**
 * com_reports' buttons.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'rankings' => array(
		'description' => 'Latest sales rankings.',
		'text' => 'Ranking',
		'class' => 'picon-office-chart-area-percentage',
		'href' => pines_url('com_reports', 'viewsalesranking', array('id' => 'latest')),
		'default' => true,
		'depends' => array(
			'ability' => 'com_reports/viewsalesranking',
		),
	),
);

?>