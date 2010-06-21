<?php
/**
 * Provides a printable payment form.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<form id="p_muid_form" class="pf-form" method="post" action="">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var form = $("#p_muid_form");
			var card_swipe = form.find("#p_muid_card_swipe");
			var name_first = form.find("#p_muid_name_first");
			var name_last = form.find("#p_muid_name_last");
			var card_number = form.find("#p_muid_card_number");
			var card_exp_month = form.find("#p_muid_card_exp_month");
			var card_exp_year = form.find("#p_muid_card_exp_year");
			card_swipe.focus();
			card_swipe.keydown(function(e){
				if (e.keyCode == 13) {
					var card_data = card_swipe.val();
					var swipe_name_first = card_data.replace(/.*\^[^/]*\/([^\^]*)\^.*/, '$1');
					var swipe_name_last = card_data.replace(/.*\^([^/]*)\/[^\^]*\^.*/, '$1');
					var swipe_card_number = card_data.replace(/\%B(\d+).*/, '$1');
					var swipe_card_month = card_data.replace(/.*\^\d{2}(\d{2}).*/, '$1');
					var swipe_card_year = card_data.replace(/.*\^(\d{2})\d{2}.*/, '$1');

					// Simple error checking...
					if (swipe_name_first == card_data ||
						swipe_name_last == card_data ||
						swipe_card_number == card_data ||
						swipe_card_month == card_data ||
						swipe_card_year == card_data ) {
						alert("Error reading card. Please try again.");
						card_swipe.val("");
						return false;
					}

					// Verify with track 2...
					var swipe_card_number2 = card_data.replace(/.*\?;(\d+).*/, '$1');
					var swipe_card_month2 = card_data.replace(/.*=\d{2}(\d{2}).*/, '$1');
					var swipe_card_year2 = card_data.replace(/.*=(\d{2})\d{2}.*/, '$1');
					if (swipe_card_number != swipe_card_number2 ||
						swipe_card_month != swipe_card_month2 ||
						swipe_card_year != swipe_card_year2 ) {
						if (!confirm("Card appears damaged and cannot be verified. Please verify the data below:\n\nNumber: "+swipe_card_number+"\nExpiration (MM/YY): "+swipe_card_month+"/"+swipe_card_year)) {
							card_swipe.val("");
							return false;
						}
					}

					// Save the info and submit the form.
					name_first.val(swipe_name_first);
					name_last.val(swipe_name_last);
					card_number.val(swipe_card_number);
					card_exp_month.val(swipe_card_month);
					card_exp_year.val(swipe_card_year);
					card_swipe.val("");
					$("#p_muid_card_swiped").val("ON");
					form.submit();
				}
			});
		});
		// ]]>
	</script>
	<div id="p_muid_swipe_form">
		<div class="pf-element">
			<label><span class="pf-label">Swipe Card</span>
				<input class="pf-field ui-widget-content" type="password" id="p_muid_card_swipe" name="card_swipe" value="" /></label>
		</div>
		<div class="pf-element">
			<button class="pf-field ui-state-default ui-corner-all" type="button" onclick="
				$('#p_muid_swipe_form').slideUp('fast');
				$('#p_muid_manual_form').slideDown('fast');
				$('#p_muid_card_swiped').val('');
			">Enter Manually</button>
		</div>
	</div>
	<div id="p_muid_manual_form" style="display: none;">
		<div class="pf-element">
			<label><span class="pf-label">Cardholder First Name</span>
				<input class="pf-field ui-widget-content" type="text" id="p_muid_name_first" name="name_first" value="<?php echo $this->name_first; ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Cardholder Last Name</span>
				<input class="pf-field ui-widget-content" type="text" id="p_muid_name_last" name="name_last" value="<?php echo $this->name_last; ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Card Number</span>
				<input class="pf-field ui-widget-content" type="text" id="p_muid_card_number" name="card_number" value="<?php echo $this->card_number; ?>" /></label>
		</div>
		<div class="pf-element">
			<span class="pf-label">Expiration Date, CCV</span>
			<select class="pf-field ui-widget-content" id="p_muid_card_exp_month" name="card_exp_month">
				<?php foreach (array(
						'01' => '01 January',
						'02' => '02 February',
						'03' => '03 March',
						'04' => '04 April',
						'05' => '05 May',
						'06' => '06 June',
						'07' => '07 July',
						'08' => '08 August',
						'09' => '09 September',
						'10' => '10 October',
						'11' => '11 November',
						'12' => '12 December'
					) as $key => $value) { ?>
				<option value="<?php echo $key; ?>"<?php echo $this->card_exp_month == $key ? ' selected="selected"' : ''; ?>><?php echo $value; ?></option>
				<?php } ?>
			</select>
			<select class="pf-field ui-widget-content" id="p_muid_card_exp_year" name="card_exp_year">
				<?php for ($i = 0; $i <= 25; $i++) { ?>
				<option value="<?php echo date('y', strtotime("+$i years")); ?>"<?php echo $this->card_exp_year == date('y', strtotime("+$i years")) ? ' selected="selected"' : ''; ?>><?php echo date('y', strtotime("+$i years")); ?></option>
				<?php } ?>
			</select>
			<input class="pf-field ui-widget-content" type="password" name="cid" size="3" value="<?php echo $this->cid; ?>" />
		</div>
	</div>
	<input type="hidden" id="p_muid_card_swiped" name="card_swiped" value="" />
</form>