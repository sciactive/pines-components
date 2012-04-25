<?php
/**
 * com_menueditor's configuration defaults.
 *
 * @package Components
 * @subpackage menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'global_entries',
		'cname' => 'Globalize Entries',
		'description' => 'Ensure that every user can access all entries by setting the "other" access control to read.',
		'value' => true,
		'peruser' => true,
	),
);

?>