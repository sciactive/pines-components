<?php
/**
 * Delete a set of templates.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_mailer/deletetemplate') )
	punt_user(null, pines_url('com_mailer', 'template/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_template) {
	$cur_entity = com_mailer_template::factory((int) $cur_template);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_template;
}
if (empty($failed_deletes)) {
	pines_notice('Selected template(s) deleted successfully.');
} else {
	pines_error('Could not delete templates with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_mailer', 'template/list'));

?>