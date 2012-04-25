<?php
/**
 * Delete a set of foobars.
 *
 * @package Components\example
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_example/deletefoobar') )
	punt_user(null, pines_url('com_example', 'foobar/list'));

$list = explode(',', $_REQUEST['id']);
foreach ($list as $cur_foobar) {
	$cur_entity = com_example_foobar::factory((int) $cur_foobar);
	if ( !isset($cur_entity->guid) || !$cur_entity->delete() )
		$failed_deletes .= (empty($failed_deletes) ? '' : ', ').$cur_foobar;
}
if (empty($failed_deletes)) {
	pines_notice('Selected foobar(s) deleted successfully.');
} else {
	pines_error('Could not delete foobars with given IDs: '.$failed_deletes);
}

pines_redirect(pines_url('com_example', 'foobar/list'));

?>