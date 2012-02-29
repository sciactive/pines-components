<?php
/**
 * Provides a form for the user to edit a loan.
 *
 * @package Pines
 * @subpackage com_loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Loan' : 'Editing Loan ['.htmlspecialchars($this->entity->id).'] for '.htmlspecialchars($this->entity->customer->name);
$this->note = 'Provide loan details in this form.';
$pines->com_customer->load_customer_select();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			// Creation date Date Picker.
			$("#p_muid_creation_date").datepicker({
				changeMonth: true,
				changeYear: true,
				yearRange: '-50',
				dateFormat: 'yy-mm-dd'
			});

			// Customer Autocomplete.
			$("#p_muid_customer").customerselect();

			// Principal Amount Checker - adds preceding $ and checks for non-numeric entries.
			// Also, if a "bad" value is entered in, it reverts back to the last "good" value, if there was one.
			var prin_inp = $("#p_muid_prin");
			if(typeof(orig_prin_value) == 'undefined') {
				var orig_prin_value = prin_inp.val();
			}
			prin_inp.change(function() {
				var prin_value = prin_inp.val();
				if(prin_value.match(/^\$/)) {
					prin_value = prin_value.replace("$","");
				}
				var parse_val = parseFloat(prin_value);
				if(isNaN(parse_val)) {
					if(orig_prin_value == 0 || !orig_prin_value.match(/^[0-9]/)) {
						prin_inp.val(orig_prin_value);
					} else {
						prin_inp.val('$' + orig_prin_value);
					}
				}
				if(prin_value.match(/^[0-9]/)) {
					prin_inp.val('$' + prin_value);
				}
				orig_prin_value = prin_inp.val();
			});

			// APR checker - adds %, checks for non-numeric entries, and hints user to make sure it's correct.
			// Also, if a "bad" value is entered in, it reverts back to the last "good" value, if there was one.
			var apr_inp = $("#p_muid_apr");
			if(typeof(orig_apr_value) == 'undefined') {
				var orig_apr_value = apr_inp.val();
			}
			<?php if(isset($this->entity->apr_correct) && !$this->entity->apr_correct) { ?>
					apr_inp.after(' <label id="is_this_correct" style="font-size:.9em;">is this correct? <input type="checkbox" name="apr_correct" value="ON" <?php echo ($this->entity->apr_correct) ? ' checked="checked"' : ''; ?> /></label>');
				<?php } elseif(isset($this->entity->apr_correct) && $this->entity->apr_correct) { ?>
					apr_inp.after(' <label id="is_this_correct"><input type="hidden" name="apr_correct" value="ON" <?php echo ($this->entity->apr_correct) ? ' checked="checked"' : ''; ?> /></label>');
				<?php } ?>
			apr_inp.change(function() {
				var apr_value = apr_inp.val();
				if(apr_value.match(/^\./)) {
					apr_inp.val('0' + apr_value);
				}
				var parse_val = parseFloat(apr_value)
				if(isNaN(parse_val)) {
					apr_inp.val(orig_apr_value);
				} else {
					apr_inp.val(parse_val + '%');
					$("#is_this_correct").remove();
					apr_inp.after(' <label id="is_this_correct" style="font-size:.9em;">is this correct? <input type="checkbox" name="apr_correct" value="ON" /></label>');
				}
				orig_apr_value = apr_inp.val();
			});

			// Check that term is an integer.
			var term_inp = $("#p_muid_term");
			var orig_term_value = term_inp.val();
			term_inp.change(function() {
				var term_value = term_inp.val();
				var parse_val = parseInt(term_value)
					if(isNaN(parse_val)) {
						term_inp.val(orig_term_value);
					}
			});

			// First Payment Date Date-Picker.
			$("#p_muid_first_payment_date").datepicker({
				changeMonth: true,
				changeYear: true,
				yearRange: '-50:+3',
				dateFormat: 'yy-mm-dd'
			});
		});
	</script>
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
		<div>Modified: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Customer</h3>
	</div>
	<div class="pf-element">
		<span class="pf-note">Enter part of a name, company, email, or phone # to search.</span>
		<input id="p_muid_customer" class="pf-field" type="text" name="customer" value="<?php echo (isset($this->entity->customer->guid) ? htmlspecialchars("{$this->entity->customer->guid}: {$this->entity->customer->name}") : ''); ?>"/>
	</div>
	<div class="pf-element pf-heading">
		<h3>Loan Information</h3>
	</div>
	<div class="pf-element">
		<span class="pf-label">Creation Date</span>
		<input id="p_muid_creation_date" class="pf-field" type="text" name="creation_date" value="<?php echo isset($this->entity->creation_date) ? htmlspecialchars(format_date(strtotime($this->entity->creation_date), 'date_sort')) : ''; ?>"/>
	</div>
	<div class="pf-element">
		<span class="pf-label">Principal Amount</span>
		<input id="p_muid_prin" class="pf-field" type="text" name="principal" value="<?php echo (!empty($this->entity->principal) ? htmlspecialchars("\${$this->entity->principal}") : ''); ?>"/>
	</div>
	<div class="pf-element">
		<span class="pf-label">APR</span>
		<span class="pf-note">Annual Percentage Rate</span>
		<input id="p_muid_apr" class="pf-field" type="text" name="apr" value="<?php echo (!empty($this->entity->apr) ? htmlspecialchars("{$this->entity->apr}%") : ''); ?>"/>
	</div>
	<div class="pf-element">
		<label for="p_muid_term">
			<span class="pf-label">Term</span>
			<span class="pf-note">Length of loan in years or months.</span>
		</label>
		<div class="pf-group">
			<input id="p_muid_term" class="pf-field" type="text" name="term" value="<?php echo (!empty($this->entity->term) ? htmlspecialchars($this->entity->term) : ''); ?>"/>
			<select class="pf-field" name="term_type">
				<option value="months"<?php echo $this->entity->term_type == 'months' ? ' selected="selected"' : ''; ?>>Months</option>
				<option value="years"<?php echo $this->entity->term_type == 'years' ? ' selected="selected"' : ''; ?>>Years</option>
			</select>
		</div>
	</div>
	<div class="pf-element">
		<span class="pf-label">First Payment Date</span>
		<input id="p_muid_first_payment_date" class="pf-field" type="text" name="first_payment_date" value="<?php echo isset($this->entity->first_payment_date) ? htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), 'date_sort')) : ''; ?>"/>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Payment Frequency</span>
			<select class="pf-field" name="payment_frequency">
				<option value="12"<?php echo $this->entity->payment_frequency == '12' ? ' selected="selected"' : ''; ?>>Monthly</option>
				<option value="1"<?php echo $this->entity->payment_frequency == '1' ? ' selected="selected"' : ''; ?>>Annually</option>
				<option value="2"<?php echo $this->entity->payment_frequency == '2' ? ' selected="selected"' : ''; ?>>Semi-Annually</option>
				<option value="4"<?php echo $this->entity->payment_frequency == '4' ? ' selected="selected"' : ''; ?>>Quarterly</option>
				<option value="6"<?php echo $this->entity->payment_frequency == '6' ? ' selected="selected"' : ''; ?>>Bi-Monthly</option>
				<option value="24"<?php echo $this->entity->payment_frequency == '24' ? ' selected="selected"' : ''; ?>>Semi-Monthly</option>
				<option value="26"<?php echo $this->entity->payment_frequency == '26' ? ' selected="selected"' : ''; ?>>Bi-Weekly</option>
				<option value="52"<?php echo $this->entity->payment_frequency == '52' ? ' selected="selected"' : ''; ?>>Weekly</option>
			</select>
		</label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Compound Frequency</span>
			<select class="pf-field" name="compound_frequency">
				<option value="12"<?php echo $this->entity->compound_frequency == '12' ? ' selected="selected"' : ''; ?>>Monthly</option>
				<option value="1"<?php echo $this->entity->compound_frequency == '1' ? ' selected="selected"' : ''; ?>>Annually</option>
				<option value="2"<?php echo $this->entity->compound_frequency == '2' ? ' selected="selected"' : ''; ?>>Semi-Annually</option>
				<option value="4"<?php echo $this->entity->compound_frequency == '4' ? ' selected="selected"' : ''; ?>>Quarterly</option>
				<option value="6"<?php echo $this->entity->compound_frequency == '6' ? ' selected="selected"' : ''; ?>>Bi-Monthly</option>
				<option value="24"<?php echo $this->entity->compound_frequency == '24' ? ' selected="selected"' : ''; ?>>Semi-Monthly</option>
				<option value="26"<?php echo $this->entity->compound_frequency == '26' ? ' selected="selected"' : ''; ?>>Bi-Weekly</option>
				<option value="52"<?php echo $this->entity->compound_frequency == '52' ? ' selected="selected"' : ''; ?>>Weekly</option>
				<option value="360"<?php echo $this->entity->compound_frequency == '360' ? ' selected="selected"' : ''; ?>>Daily (360)</option>
			</select>
		</label>
	</div>
	<div class="pf-element">
		<label>
			<span class="pf-label">Payment Type</span>
			<span class="pf-note">Default is at the end of the period.</span>
			<select class="pf-field" name="payment_type">
				<option value="ending"<?php echo $this->entity->payment_type == 0 ? ' selected="selected"' : ''; ?>>End of Period</option>
				<option value="beginning"<?php echo $this->entity->payment_type == 1 ? ' selected="selected"' : ''; ?>>Beginning of Period</option>
			</select>
		</label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_loan', 'loan/list'))); ?>);" value="Cancel" />
	</div>
</form>