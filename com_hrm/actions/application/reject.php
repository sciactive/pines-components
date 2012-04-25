<?php
/**
 * Reject an applicant.
 *
 * @package Components\hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/addemployee') )
	punt_user(null, pines_url('com_hrm', 'application/list'));

$list = explode(',', $_REQUEST['items']);

foreach ($list as $cur_application_id) {
	$cur_application = com_hrm_application::factory((int) $cur_application_id);
	if ($cur_application->status == 'hired' || $cur_application->status == 'rejected')
		continue;
	$cur_application->status = 'rejected';
	if (!$cur_application->save())
		$failed_adds .= (empty($failed_adds) ? '' : ', ').$cur_application_id;
}
if (empty($failed_adds)) {
	pines_notice('Selected applicant(s) rejected successfully.');
} else {
	pines_error('Could not reject the applicants with the following application IDs: '.$failed_adds);
}

pines_redirect(pines_url('com_hrm', 'application/list'));

?>