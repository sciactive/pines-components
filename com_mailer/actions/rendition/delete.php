<?php
/**
 * Delete a set of renditions.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_mailer/deleterendition') )
	punt_user(null, pines_url('com_mailer', 'rendition/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_rendition) {
	$cur_entity = com_mailer_rendition::factory((int) $cur_rendition);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_rendition;
}
if (empty($failed_deletes)) {
	pines_notice('Selected rendition(s) deleted successfully.');
} else {
	pines_error('Could not delete renditions with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_mailer', 'rendition/list'));

?>