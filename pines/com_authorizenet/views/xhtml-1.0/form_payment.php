<?php
/**
 * Provides a printable payment form.
 *
 * @package Pines
 * @subpackage com_authorizenet
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<form id="authorize_net_form" class="pform" method="post" action="">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			var form = $("#authorize_net_form");
			var card_swipe = form.find("input[name=card_swipe]");
			var name_first = form.find("input[name=name_first]");
			var name_last = form.find("input[name=name_last]");
			var card_number = form.find("input[name=card_number]");
			var card_exp_month = form.find("select[name=card_exp_month]");
			var card_exp_year = form.find("select[name=card_exp_year]");
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
					$("#authorize_net_form input[name=card_swiped]").val("ON");
					form.submit();
				}
			});
		});
		// ]]>
	</script>
	<div id="authorize_net_swipe">
		<div class="element">
			<label><span class="label">Swipe Card</span>
				<input class="field ui-widget-content" type="password" name="card_swipe" value="" /></label>
		</div>
		<div class="element">
			<button class="field ui-state-default ui-corner-all" type="button" onclick="
				$('#authorize_net_swipe').slideUp('fast');
				$('#authorize_net_manual').slideDown('fast');
				$('#authorize_net_form input[name=card_swiped]').val('');
			">Enter Manually</button>
		</div>
	</div>
	<div id="authorize_net_manual" style="display: none;">
		<div class="element">
			<label><span class="label">Cardholder First Name</span>
				<input class="field ui-widget-content" type="text" name="name_first" value="<?php echo $this->name_first; ?>" /></label>
		</div>
		<div class="element">
			<label><span class="label">Cardholder Last Name</span>
				<input class="field ui-widget-content" type="text" name="name_last" value="<?php echo $this->name_last; ?>" /></label>
		</div>
		<?php /* Address is unnecessary.
		<div class="element">
			<label><span class="label">Cardholder Address</span>
				<input class="field ui-widget-content" type="text" name="address" value="<?php echo $this->address; ?>" /></label>
		</div>
		<div class="element">
			<span class="label">Cardholder State, Zip</span>
			<select class="field ui-widget-content" name="state">
				<?php foreach (array(
						'' => '-- Choose State --',
						'AL' => 'Alabama',
						'AK' => 'Alaska',
						'AZ' => 'Arizona',
						'AR' => 'Arkansas',
						'CA' => 'California',
						'CO' => 'Colorado',
						'CT' => 'Connecticut',
						'DE' => 'Delaware',
						'DC' => 'DC',
						'FL' => 'Florida',
						'GA' => 'Georgia',
						'HI' => 'Hawaii',
						'ID' => 'Idaho',
						'IL' => 'Illinois',
						'IN' => 'Indiana',
						'IA' => 'Iowa',
						'KS' => 'Kansas',
						'KY' => 'Kentucky',
						'LA' => 'Louisiana',
						'ME' => 'Maine',
						'MD' => 'Maryland',
						'MA' => 'Massachusetts',
						'MI' => 'Michigan',
						'MN' => 'Minnesota',
						'MS' => 'Mississippi',
						'MO' => 'Missouri',
						'MT' => 'Montana',
						'NE' => 'Nebraska',
						'NV' => 'Nevada',
						'NH' => 'New Hampshire',
						'NJ' => 'New Jersey',
						'NM' => 'New Mexico',
						'NY' => 'New York',
						'NC' => 'North Carolina',
						'ND' => 'North Dakota',
						'OH' => 'Ohio',
						'OK' => 'Oklahoma',
						'OR' => 'Oregon',
						'PA' => 'Pennsylvania',
						'RI' => 'Rhode Island',
						'SC' => 'South Carolina',
						'SD' => 'South Dakota',
						'TN' => 'Tennessee',
						'TX' => 'Texas',
						'UT' => 'Utah',
						'VT' => 'Vermont',
						'VA' => 'Virginia',
						'WA' => 'Washington',
						'WV' => 'West Virginia',
						'WI' => 'Wisconsin',
						'WY' => 'Wyoming'
					) as $key => $cur_state) { ?>
				<option value="<?php echo $key; ?>"<?php echo $this->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
				<?php } ?>
			</select>
			<input class="field ui-widget-content" type="text" name="zip" size="5" value="<?php echo $this->zip; ?>" />
		</div>
		 */ ?>
		<div class="element">
			<label><span class="label">Card Number</span>
				<input class="field ui-widget-content" type="text" name="card_number" value="<?php echo $this->card_number; ?>" /></label>
		</div>
		<div class="element">
			<span class="label">Expiration Date, CCV</span>
			<select class="field ui-widget-content" name="card_exp_month">
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
				<option value="<?php echo $key; ?>"<? echo $this->card_exp_month == $key ? ' selected="selected"' : ''; ?>><?php echo $value; ?></option>
				<?php } ?>
			</select>
			<select class="field ui-widget-content" name="card_exp_year">
				<?php for ($i = 0; $i <= 25; $i++) { ?>
				<option value="<?php echo date('y', strtotime("+$i years")); ?>"<? echo $this->card_exp_year == date('y', strtotime("+$i years")) ? ' selected="selected"' : ''; ?>><?php echo date('y', strtotime("+$i years")); ?></option>
				<?php } ?>
			</select>
			<input class="field ui-widget-content" type="password" name="cid" size="3" value="<?php echo $this->cid; ?>" />
		</div>
	</div>
	<input type="hidden" name="card_swiped" value="" />
</form>