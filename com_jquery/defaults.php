<?php
/**
 * com_jquery' configuration.
 *
 * @package Pines
 * @subpackage com_jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'theme',
		'cname' => 'Theme',
		'description' => 'jQuery UI theme to use.',
		'value' => 'aristo',
		'options' => pines_scandir('components/com_jquery/includes/jquery-ui/'),
		'peruser' => true,
	),
);

?>