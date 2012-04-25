<?php
/**
 * com_contact's configuration defaults.
 *
 * @package Components
 * @subpackage contact
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
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