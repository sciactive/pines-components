<?php
/**
 * com_about's configuration defaults.
 *
 * @package Components\about
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'description',
		'cname' => 'Description',
		'description' => 'Description of your installation.',
		'value' => 'Please configure a description of your installation.',
		'peruser' => true,
	),
	array(
		'name' => 'describe_system',
		'cname' => 'Describe System',
		'description' => 'Whether to show the system\'s description underneath yours.',
		'value' => true,
		'peruser' => true,
	),
);

?>