<?php
/**
 * com_pinlock's configuration defaults.
 *
 * @package Pines
 * @subpackage com_pinlock
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'actions',
		'cname' => 'Protected Actions',
		'description' => 'These actions will require the user to enter their PIN before access.',
		'value' => array('com_pinlock/example'),
	),
);

?>