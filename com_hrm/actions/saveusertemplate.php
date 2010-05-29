<?php
/**
 * Save changes to a user template.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_hrm/editusertemplate') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'listusertemplates'));
	$user_template = com_hrm_user_template::factory((int) $_REQUEST['id']);
	if (!isset($user_template->guid)) {
		pines_error('Requested user template id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_hrm/newusertemplate') )
		punt_user('You don\'t have necessary permission.', pines_url('com_hrm', 'listusertemplates'));
	$user_template = com_hrm_user_template::factory();
}

// General
$user_template->name = $pines->com_hrm->title_case($_REQUEST['name']);
$user_template->default_component = $_REQUEST['default_component'];
$user_template->group = group::factory((int) $_REQUEST['group']);
if (!isset($user_template->group->guid))
	$user_template->group = null;
$user_template->groups = array();
if (is_array($_REQUEST['groups'])) {
	foreach ($_REQUEST['groups'] as $cur_group) {
		$cur_entity = group::factory((int) $cur_group);
		if (isset($cur_entity->guid))
			$user_template->groups[] = $cur_entity;
		unset($cur_entity);
	}
}

if (empty($user_template->name)) {
	$user_template->print_form();
	pines_notice('Please specify a name.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_hrm_user_template), array('&', 'data' => array('name', $user_template->name), 'tag' => array('com_hrm', 'user_template')));
if (isset($test) && !$user_template->is($test)) {
	$user_template->print_form();
	pines_notice('There is already a user template with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_hrm->global_user_templates)
	$user_template->ac->other = 1;

if ($user_template->save()) {
	pines_notice('Saved user template ['.$user_template->name.']');
} else {
	pines_error('Error saving user template. Do you have permission?');
}

redirect(pines_url('com_hrm', 'listusertemplates'));

?>