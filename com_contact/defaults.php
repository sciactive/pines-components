<?php
/**
 * com_contact's configuration defaults.
 *
 * @package Pines
 * @subpackage com_contact
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright Smart Industries, LLC
 * @link http://smart108.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'contact_email',
		'cname' => 'Contact Address',
		'description' => 'The email address for users to contact',
		'value' => 'root@localhost',
		'peruser' => true,
	),
);

?>