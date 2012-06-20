<?php
/**
 * com_example's mails.
 *
 * @package Components\example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'save_foobar' => array(
		'cname' => 'Saved Foobar',
		'description' => 'This email is sent when a foobar is saved.',
		'view' => 'mails/save_foobar',
		'has_recipient' => false, // Does the email already have a place to be sent?
		'macros' => array(
			'foobar_name' => 'Name of the foobar that has been saved.',
		),
	),
);

?>