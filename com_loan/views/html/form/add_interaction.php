<?php
/**
 * The view for adding a customer interaction on com_loan.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Add Customer Interaction';
$pines->icons->load();
?>
<div id="p_muid_form">
	<style type="text/css" scoped="scoped">
		#p_muid_form label, #p_muid_form label input {
			display: inline;
		}
		#p_muid_results .status {
			margin-top: 1em;
		}
		#p_muid_results .well {
			padding-bottom: 1px;
		}
	</style>
	<div class="interaction_status_bar"></div>
	<div id="p_muid_controls" class="clearfix pf-form">
		<div class="pf-element" style="clear:left;max-width:100%">
			<label><span class="pf-label">Interaction Type</span>
				<select class="interaction_type" name="interaction_type" style="width:140px; max-width:90%;">
					<?php foreach ($pines->config->com_customer->interaction_types as $cur_type) {
						$cur_type = explode(':', $cur_type);
						echo '<option value="'.htmlspecialchars($cur_type[1]).'">'.htmlspecialchars($cur_type[1]).'</option>';
					} ?>
				</select></label>
		</div>
		<div class="pf-element" style="clear:left;max-width:100%;">
			<label><span class="pf-label">Interaction Status</span>
				<select class="interaction_status" name="interaction_status" style="width:140px; max-width:90%;">
					<option value="">Status</option>
					<option value="open">Open</option>
					<option value="closed">Closed</option>
				</select>
			</label>
		</div>
		<div class="pf-element pf-full-width">
			<textarea rows="3" cols="30" style="width:95%;" class="interaction_comments" name="interaction_comments"></textarea>
		</div>
		<input type="hidden" name="interaction_success" value="" />
	</div>
</div>