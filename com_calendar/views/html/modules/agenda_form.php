<?php
/**
 * Provides a form for the user to choose agenda options.
 *
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<div class="pf-form">
	<div class="pf-element">
		<label><span class="pf-label">View</span>
			<select class="pf-field" name="view_type">
				<option value="basicDay">Day</option>
				<option value="basicWeek">Week</option>
			</select></label>
	</div>
</div>