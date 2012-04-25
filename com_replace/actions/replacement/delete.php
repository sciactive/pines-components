<?php
/**
 * Delete a replacement.
 *
 * @package Components
 * @subpackage replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_replace/deletereplacement') )
	punt_user(null, pines_url('com_replace', 'replacement/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_replacement) {
	$cur_entity = com_replace_replacement::factory((int) $cur_replacement);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_replacement;
}
if (empty($failed_deletes)) {
	pines_notice('Selected replacement(s) deleted successfully.');
} else {
	pines_error('Could not delete replacements with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_replace', 'replacement/list'));

?>