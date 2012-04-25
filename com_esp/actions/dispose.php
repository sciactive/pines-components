<?php
/**
 * Display a disposal form for an ESP.
 *
 * @package Components
 * @subpackage esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_esp/disposeplans') )
	punt_user(null, pines_url('com_esp', 'dispose', array('items' => $_REQUEST['items'], 'dispose' => $_REQUEST['dispose'])));

$list = explode(',', $_REQUEST['items']);
foreach ($list as $cur_item) {
    $cur_entity = com_esp_plan::factory((int) $cur_item);
    if (isset($cur_entity->guid)) {
		$cur_entity->status = $_REQUEST['dispose'];
        $cur_entity->disposed = $_REQUEST['dispose'];
		if ($cur_entity->save()) {
			pines_log("Disposed ESP: $cur_item", 'notice');
		} else {
			pines_log("GUID \"$cur_item\" could not be saved. Cannot dispose.", 'error');
			$failed_disposals .= (empty($failed_disposals) ? '' : ', ').$cur_item;
		}
    } else {
        pines_log("GUID \"$cur_item\" is not a valid ESP. Cannot dispose.", 'error');
        $failed_disposals .= (empty($failed_disposals) ? '' : ', ').$cur_item;
    }
    unset($cur_entity);
}
if (empty($failed_disposals)) {
    pines_notice('Selected ESP(s) disposed successfully.');
} else {
    pines_error('Could not dispose ESPs with given IDs: '.$failed_disposals);
}

pines_redirect(pines_url('com_esp', 'list'));

?>