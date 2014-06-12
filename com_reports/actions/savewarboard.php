<?php
/**
 * Save changes to a warboard.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_reports/editwarboard') )
	punt_user(null, pines_url('com_reports', 'warboard'));

$warboard = com_reports_warboard::factory((int) $_REQUEST['id']);
if (!isset($warboard->guid)) {
	pines_error('Requested Warboard id is not accessible.');
	return;
}

$warboard->company_name = $_REQUEST['company_name'];
$warboard->positions = !empty($_REQUEST['titles']) ? $_REQUEST['titles'] : array();
$warboard->columns = (int) $_REQUEST['columns'];
$warboard->locations = array();
$warboard->important = array();

$locations = array_map('intval', (array) $_REQUEST['locations']);
foreach ($locations as $cur_location) {
	$location = group::factory((int) $cur_location);
	if (isset($location->guid))
		$warboard->locations[] = $location;
}

$stacks = array();
$importants = (array) $_REQUEST['important'];
foreach($importants as $cur_imp) {
	if (preg_match('#_stack#', $cur_imp)) {
		$cur_imp = intval(preg_replace('#_stack#', '', $cur_imp));
		$stacks[] = $cur_imp;
	}
	$important = group::factory((int) $cur_imp);
	if (isset($important->guid))
		$warboard->important[] = $important;
}
$warboard->stack = $stacks;

$warboard->hq = group::factory((int) $_REQUEST['hq']);
if (!isset($warboard->hq->guid))
	$warboard->hq = $_SESSION['user']->group;

$warboard->ac = (object) array(
	'user' => 3,
	'group' => 2,
	'other' => 2
);

// Make a warboard array for the view with all the columns determined already:
$cols = $warboard->columns;
//$cols_remaining = (isset($warboard->hq)) ? $cols - 1 : $cols;
$loc_remaining = count($warboard->locations);
$imp_remaining = count($warboard->important);
$total_loc = $loc_remaining;
$total_imp = $imp_remaining;
// Build the warboard array now?
$fill_warboard = array();

//$first_row = true;
while (($loc_remaining > 0) || ($imp_remaining > 0)) {
	$row = array();
	// This handles filling the row...
	for ($c = 0; $c < $cols; $c++) {
		$block = array();
		if ($loc_remaining > 0) {
			// Do locations first.
			$loc_c = $total_loc - $loc_remaining;
			$block['location'] = $warboard->locations[$loc_c];
			$block['type'] = 'location';
			$loc_remaining--;
			$row[] = $block;
		} else if ($imp_remaining > 0) {
			// Do important next.
			$imp_c = $total_imp - $imp_remaining;
			// Check if it is a stack?
			if (in_array($warboard->important[$imp_c]->guid, $stacks)) {
				$block['type'] = 'stack';
				// The type is also ALWAYS important for stacks,
				// Since the locations have already been done
				// and only important numbers can stack...
				if ($imp_remaining < 2 && isset($warboard->hq)) {
					$block['stack_hq'] = true;
					$block['important'] = $warboard->important[$imp_c];
					$imp_remaining--;
					$row[] = $block;
				} else if ($imp_remaining < 2) {
					// There's nothing to stack it with...
					$block['type'] = 'important'; // overrides the stack
					$block['important'] = $warboard->important[$imp_c];
					$imp_remaining--;
					$row[] = $block;
				} else {
					// There's another one to stack with. (stacking is limited to 2)
					$block['stack1'] = $warboard->important[$imp_c];
					$block['stack2'] = $warboard->important[$imp_c + 1];
					$imp_remaining--;
					$imp_remaining--;
					if ($imp_remaining == 0 && isset($warboard->hq)) {
						$block['make_hq'] = true;
					}
					// Since I subtracted the remaining by two, there's no
					// danger of repeating that 2nd stacked location.
					$row[] = $block;
				}
			} else {
				// Regular full blocks.
				$block['important'] = $warboard->important[$imp_c];
				$block['type'] = 'important';
				$imp_remaining--;
				$row[] = $block;
			}
		}
	}
	$fill_warboard[] = $row;
}

$warboard->rows = $fill_warboard;

if ($warboard->save()) {
	pines_notice('Saved Warboard');
} else {
	$warboard->print_form();
	pines_error('Error saving Warboard. Do you have permission?');
	return;
}

pines_redirect(pines_url('com_reports', 'warboard'));

?>