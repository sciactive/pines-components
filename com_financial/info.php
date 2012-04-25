<?php
/**
 * com_financial's information.
 *
 * @package Components
 * @subpackage financial
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Financial Functions',
	'author' => 'SciActive (Component), Enrique Garcia (Library)',
	'version' => '1.0.0',
	'license' => 'http://www.gnu.org/licenses/gpl.html',
	'website' => 'http://www.sciactive.com',
	'short_description' => 'Financial function library',
	'description' => 'A library of financial functions with identical names and arguments as those used in Microsoft Excel. Entirely based on work by Enrique Garcia.',
	'depend' => array(
		'pines' => '<2'
	),
);

?>