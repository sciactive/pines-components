<?php
/**
 * Edit payments for a loan.
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
$this->title = 'Edit Loan Payments';
$this->note =  'Customer: '.htmlspecialchars($this->entity->customer->name).'<span style="float:right;"> Loan ID: '.htmlspecialchars($this->entity->id).'</span>';		

switch ($this->entity->payment_frequency) {
	case "12":
		$payment_frequency = "Monthly";
		break;
	case "1":
		$payment_frequency = "Annually";
		break;
	case "2":
		$payment_frequency = "Semi-annually";
		break;
	case "4":
		$payment_frequency = "Quarterly";
		break;
	case "6":
		$payment_frequency = "Bi-monthly";
		break;
	case "24":
		$payment_frequency = "Semi-monthly";
		break;
	case "26":
		$payment_frequency = "Bi-weekly";
		break;
	case "52":
		$payment_frequency = "Weekly";
		break;
}

?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		
		// Check all payments delete name. (That it's not "Auto-save").
		$('#delete_all_payments_name_input').change(function() {
			var rege = /^Auto-save/;
			if(rege.test($(this).val())) {
				alert('Cannot name a delete Auto-save.');
				$(this).val("");
			}
		});
		
		// Submit Delete All Payments.
		$('#p_muid_all_payment_form').submit(function(e) { 
			if ($('#delete_all_payments_name_input').val().length == 0) {
			e.preventDefault();
			alert('Please provide a name for this delete.');
			}
			if ($('#delete_all_payments_reason_input').val().length == 0) {
			e.preventDefault();
			alert('Please provide a description for this delete.');
			}
		});
		
		
		// Make an alert for restoring payments from the delete logs.
		$('#p_muid_restore_form').submit(function(e){
			// Must make a selection before restoring from the delete logs.
			if (!$('input[name="restore_point"]:checked').length) {
				alert('Please make a selection.');
				e.preventDefault();
				return false;
			}
			<?php 
				if (empty($this->entity->paid) && empty($this->entity->history->edit_payments)) {
			?>
			if (!confirm("You are about to reinstate all payments from a previous point. Are you sure you want to do this?")) {
				e.preventDefault();
				return false;
			}
			<?php } else { ?>
			if (!confirm("You are about to replace all current payments - including edit and delete history - with payments from this restore point. Doing so will create a restore point for replaced payments.\n\nAre you sure you want to do this? ")) {
				e.preventDefault();
				return false;
			}
			<?php } ?>
		});
		
		
		// Make an alert for restoring payments from the delete logs.
		$('#p_muid_deleted_all_payments_form').submit(function(e){
			// Must make a selection before restoring from the delete logs.
			if (!$('input[name="delete_restore_point"]:checked').length) {
				alert('Please make a selection.');
				e.preventDefault();
				return false;
			}
			<?php 
				if (empty($this->entity->paid) && empty($this->entity->history->edit_payments)) {
			?>
			if (!confirm("You are about to reinstate all payments from a previous point. Are you sure you want to do this?")) {
				e.preventDefault();
				return false;
			}
			<?php } else { ?>
			if (!confirm("You are about to replace all current payments - including edit and delete history - with payments from this restore point. Doing so will create a restore point for replaced payments.\n\nAre you sure you want to do this? ")) {
				e.preventDefault();
				return false;
			}
			<?php } ?>
		});
		
		// Show All Payment Reason for Deletion box if Delete Payment is checked.
		var ap_delete_check = $("#delete_all_payments");
		var ap_delete_name = $("#delete_all_payments_name");
		var ap_delete_reason = $("#delete_all_payments_reason");
		ap_delete_check.change(function(){
			if (this.checked) {
				ap_delete_name.show();
				ap_delete_reason.show();
			}
			else {
				ap_delete_name.hide();
				ap_delete_reason.hide();
			}
		}).change();
		
		
		var edit_make_payment = true;
		var loan_id = <?php echo htmlspecialchars($this->entity->guid); ?>;
		$('#p_muid_make_payment').click(function() {
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'forms/makepayment')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": loan_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the make payment form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Make a Payment\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: 425,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Make Payment": function(){
								var payment_amount = form.find(":input[name=payment_amount]").val();
								var payment_date_input = form.find(":input[name=payment_date_input]").val();
								if (payment_amount == "") {
									alert('Please specify the payment amount.');
								} else if (payment_amount < 0) {
									alert('Please specify a valid payment amount.');
								} else if (<?php echo ($this->entity->payments[0]['remaining_balance']) ? $this->entity->payments[0]['remaining_balance'] : $this->entity->principal; ?> < .01) {
									alert('Balance is paid. No more payments accepted.')
								} else if (payment_date_input == "") {
									alert('Please specify a date for receiving the payment.');
								} else {
									form.dialog('close');
									// Submit the request status change.
									pines.post(<?php echo json_encode(pines_url('com_loan', 'loan/makepayment')); ?>, {
										"loan_id": loan_id,
										"edit": edit_make_payment,
										"payment_amount": payment_amount,
										"payment_date_input": payment_date_input
									});
								}
							}
						}
					});
					pines.play();
				}
			});
		});
		
		$('#p_muid_loan_toolbar .button').button();
		$("#p_muid_editpayments_tabs").tabs();
	});
	// ]]>
</script>
<div id="p_muid_loan_toolbar" style="margin:10px 0;">
	<a class="button" href="<?php echo pines_url('com_loan', 'loan/list'); ?>">
		<span class="picon picon-go-parent-folder" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Loans</span>
	</a>
	<button id="p_muid_make_payment" class="ui-state-default ui-corner-all" type="button">
		<span class="picon picon-wallet-open" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Make Payment</span>
	</button>
	<a class="button" href="<?php echo pines_url('com_loan', 'loan/editpayments', array('id' => $this->entity->guid)); ?>">
		<span class="picon picon-view-refresh" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Refresh</span>
	</a>
</div>

<div id="p_muid_editpayments_tabs" class="ui-helper-clearfix" style="clear: both;">
	<ul>
		<li><a href="#p_muid_tab_editpayments">Edit Payments</a></li>
		<li><a href="#p_muid_tab_edit_log">Edit Log</a></li>
		<?php if (gatekeeper('com_loan/deletepayments')) { ?> 
		<li><a href="#p_muid_tab_delete_log">Delete Log</a></li>
		<li><a href="#p_muid_tab_restore_log">Restore Log</a></li>
		<?php } ?>
		<li><a href="#p_muid_tab_overview">Overview</a></li>
	</ul>
	<div id="p_muid_tab_editpayments">
		<div class="pf-form">
			<div class="pf-element pf-heading">
				<h1>Summary</h1>
			</div>
			<div class="pf-element pf-full-width">
				<div class="pf-label" style="width:280px !important;margin-right:60px;margin-left:5px;">
					<span>Principal Amount: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></span></span><br/>
					<span>Total Payments: <span style="float:right;"><?php echo ($this->entity->new_total_payment_sum) ? '$'.htmlspecialchars($pines->com_sales->round($this->entity->new_total_payment_sum)) : '$'.htmlspecialchars($pines->com_sales->round($this->entity->total_payment_sum)); ?></span></span><br/>
					<span style="font-weight:bold;">Total Payments Paid: <span style="float:right;"><?php echo empty($this->entity->payments[0]['total_interest_paid']) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round(($this->entity->payments[0]['total_principal_paid'] + $this->entity->payments[0]['total_interest_paid']), true)); ?></span></span><br/>
					<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>

					<?php if($this->entity->missed_first_payment && !(!empty($this->entity->paid))) {
						?>
						<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></span><br/>
						<span>First Payment Missed: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['first_payment_missed'], "date_short")); ?></span></span><br/>
						<span>Last Payment Made: <span style="float:right;"><?php echo '<span style="color:#b30909;">'.htmlspecialchars($this->entity->payments[0]['last_payment_made']).'</span>'; ?></span></span><br/>
						<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")); ?></span></strong></span><br/>
						<?php
					} elseif (!empty($this->entity->paid)) {
						?>
						<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></span><br/>
						<span>First Payment Made: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['first_payment_made'], "date_short")); ?></span></span><br/>
						<span>Last Payment Made: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['last_payment_made'], "date_short")); ?></span></span><br/>
						<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")); ?></span></strong></span><br/>
						<?php
					} else {
						?>
						<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></span><br/>
						<span>Last Payment Made: <span style="float:right;"><?php echo "n/a" ?></span></span><br/>
						<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></strong></span><br/>
						<?php
					}
					?>
					<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>

					<span>Remaining Payments: <span style="float:right;">
							<?php 
							echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars(round($this->entity->payments[0]['remaining_payments'], 2)) : htmlspecialchars($this->entity->number_payments);
						?>
					</span></span><br/>
					<span>Remaining Payment Due Dates: <span style="float:right;">
							<?php 
							echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars($this->entity->payments[0]['remaining_payments_due']) : htmlspecialchars($this->entity->number_payments);
						?>
					</span></span><br/>
					<span>Percentage Principal Paid: <span style="float:right;">
							<?php 
							echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['percentage_paid'], true)).'%' : '0.00%';
						?>
					</span></span>
					<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
					<?php if (gatekeeper('com_loan/makepayment')) { ?>
					<br/><br/>
					
					<?php } ?>
				</div>
				<div class="pf-group" style="width:280px !important;margin-left:5px !important;float:left;margin-right:60px !important;">
					<span>Total Initial Finance Charges: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_interest_sum_original); ?></span></span><br/>
					<span>Total Fees & Adjustments: <span style="float:right;"><?php echo (isset($this->entity->total_fees_adjustments)) ? '$'.htmlspecialchars($this->entity->total_fees_adjustments) : '$0.00'; ?></span></span><br/>
					<span>Total Finance Charges: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_interest_sum); ?></span></span><br/>
					<span>Est. Interest Savings: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->est_interest_savings, true)); ?></span></span><br/><br/>

					<div style="border:1px solid #ccc; padding:3px;"><span style="font-weight:bold;font-size:1.1em;"><?php echo $payment_frequency; ?> Payment: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->frequency_payment); ?></span></span></div><br/>
					<div style="border:1px solid #ccc; padding:3px;"><span style="font-weight:bold;font-size:1.1em;">Remaining Balance: <span style="float:right;"><?php echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? '$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['remaining_balance'], true)) : "$".htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></span></span></div><br/>

					<div>
						<span style="font-weight:bold;font-size:1.1em;">
							<?php if (isset($this->entity->payments[0]['sum_payment_short'])) { ?>
							<span style="font-size:.8em;">
								* Short Amount:
								<span style="float:right;">
									<?php 
										echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['sum_payment_short'], true)); 
									?>
								</span>
							</span>
							<?php } ?>
							<?php 
								if ($pines->com_sales->round($this->entity->payments[0]['unpaid_balance'] >= 0.01)) {
							?>
							<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
							<span style="font-size:.9em;">Unpaid Balance: </span>
							<span style="float:right;font-size:.9em;">
								<?php 
									echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['unpaid_balance'], true));  
								?>
							</span>
							<?php
								}
							?>
							<?php 
								if ($this->entity->payments[0]['unpaid_interest'] >= .01) {
							?>
							<span style="font-size:.9em;"><br/>Unpaid Interest:</span> 
							<span style="float:right;font-size:.9em;">
								<?php 
									echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['unpaid_interest'], true));
								?>
							</span>
							<?php
								}
							?>
							<?php 
								if ($this->entity->payments[0]['past_due'] >= 0.01) {
							?>
							<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
							<span style="color:#b30909;">Past Due:</span>
							<span style="float:right;color:#b30909;">
								<?php 
									echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true));
								?>
							</span>
							<?php
								}
							?>
							<div class="ui-state-highlight ui-widget-content ui-pnotify-container" style="margin-top:10px;">
								<span class="ui-helper-clearfix">Next Payment:
									<span style="float:right;">
										<?php 
											if ($this->entity->payments[0]['past_due'] >= 0.01) {
												?>
												<script type="text/javascript">
														// <![CDATA[
													pines(function(){
														// This is how to change the default settings for the entire page.
														//$.pnotify.defaults.pnotify_width = "400px";
														// If you don't want new lines ("\n") automatically converted to breaks ("<br />")
														//$.pnotify.defaults.pnotify_insert_brs = false;

														// This notice is used as a tooltip.
														p_muid_tooltip_next_payment_due = $.pnotify({
															pnotify_title: '<span style="color:#B30909">Next Payment Due: <span style="float:right;">$<?php echo (htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true)) + htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true)))?></span></span>',
															pnotify_text: '<?php echo '<span style="color:#520e0e;">Past Due: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true)).'</span><br/>'.$payment_frequency.' Payment: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true)); ?> </span><br/><span style="font-size:.8em;">Past Due Amount is due immediately.</span></span>',
															pnotify_hide: false,
															pnotify_closer: false,
															pnotify_history: false,
															pnotify_animate_speed: 100,
															pnotify_opacity: .9,
															pnotify_notice_icon: "picon picon-task-reject",
															// Setting stack to false causes Pines Notify to ignore this notice when positioning.
															pnotify_stack: false,
															pnotify_after_init: function(pnotify){
																// Remove the notice if the user mouses over it.
																pnotify.mouseout(function(){
																	pnotify.pnotify_remove();
																});
															},
															pnotify_before_open: function(pnotify){
																// This prevents the notice from displaying when it's created.
																pnotify.pnotify({
																	pnotify_before_open: null
																});
																return false;
															}
														});
													});
														// ]]>
													</script>
												<?
											}
											echo ($this->entity->payments[0]['past_due'] >= 0.01) ? '<span style="cursor:pointer;color:#b30909" onmouseout="p_muid_tooltip_next_payment_due.pnotify_remove();" onmousemove="p_muid_tooltip_next_payment_due.css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseover="p_muid_tooltip_next_payment_due.pnotify_display();">$'.(htmlspecialchars($pines->com_sales->round(($this->entity->payments[0]['next_payment_due_amount'] + $pines->com_sales->round($this->entity->payments[0]['past_due'])), true))).'</span>' : "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true));
										?>
									</span><br/>
									<span style="float:left;background:none; border:none;font-size:.8em;">Due:</span>
									<span style="float:right;background:none; border:none;font-size:.8em;"> <?php echo (isset($this->entity->payments[0]['next_payment_due'])) ? htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")) : htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span>	
									<span style="clear:both;float:left;background:none; border:none;font-size:.6em;color:#520e0e;"> <?php echo ($this->entity->payments[0]['past_due'] >= 0.01) ? "Past Due Amount is due immediately." : ""; ?></span>
								</span>
							</div>
						</span>
					</div>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Edit Payments</h1>
			</div>
			<?php 
			// If no payments have been made, this section should not be shown.
			// If a payment restore point is available, show list of dates & restore points to revert to.

		//	// Just to test real quick.
		//	$this->entity->paid = null;
		//	$this->entity->history->restore_payments = "something";

			if (empty($this->entity->paid) && gatekeeper('com_loan/deletepayments')) {
				echo (!empty($this->entity->history->all_payments)) ? "1. No Payments have been made yet.<br/><br/>" : "<center>No Payments have been made yet.<br/><br/></center>";
				if (!empty($this->entity->history->all_payments)) {
					echo "2. Payment History Restore Points are Available.<br/><br/>";
					?>
					<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
					<form class="pf-form" method="post" id="p_muid_restore_form" action="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/restorepayments')); ?>">
					<div class="pf-element">
						<span class="pf-label"  style="width:280px;">Choose a Restore Point</span>
						<span class="pf-note"  style="width:280px;">Revert Payments to previous state.</span>
						<div class="pf-group"  style="margin-left:280px;">
							<?php 
								$counter = 0;
								foreach ($this->entity->history->all_payments as $restore) {
									?>
									<label>
										<input class="pf-field" type="radio" name="restore_point" value="<?php echo $counter; ?>" />
										<input class="pf-field" type="hidden" name="restore_name[]" value="<?php echo htmlspecialchars(ucfirst($restore['all_delete']['delete_name'])); ?>" />
										<?php echo htmlspecialchars(format_date($restore['all_delete']['delete_date'], "full_short")); ?>
										<?php echo '| Name: '.htmlspecialchars(ucfirst($restore['all_delete']['delete_name'])); ?>
									</label>
									<br/>
									<?php
									$counter++;
								}
							?>
						</div>
					</div>
					<div class="pf-element" style="float:right;clear:none;">
						<?php if ( isset($this->entity->guid) ) { ?>
							<input type="hidden" name="id" value="<?php echo (int) htmlspecialchars($this->entity->guid); ?>" />
						<?php } ?>
						<input id="restore_form_submit" class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Restore" />
					</div>
					</form>
					<?php
				}
			} elseif (empty($this->entity->paid) && !gatekeeper('com_loan/deletepayments')) {
				// User does not have permission to see deletes/restores.
				echo 'No Payments have been made.';
			} else {	
				// Get the very last PAID payment array.
				foreach ($this->entity->payments as $payment) {
					if ($payment['payment_type'] == "scheduled") {
						$prev = prev($this->entity->payments);
					}

					if (!empty($prev)) {
						$payment = prev($this->entity->payments);
						break;
					}
				}	
			?>
			<div class="pf-element pf-heading">
				<p>Select Payment(s) to Edit.<br/><span style="font-size:.9em;">Preview Changes before Saving.</span></p>
			</div>
			<form class="pf-form" method="post" id="p_muid_edit_payment_form" action="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/editpayments')); ?>">		
				<div class="pf-element pf-full-width">
					<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em">
						<tbody>
						<tr class="ui-widget-header" style="text-align:center;border:0;">
							<td style="border-bottom:1px solid #b0b0b0;">Receive<br/>Date</td>
							<td style="border-bottom:1px solid #b0b0b0;">Payment<br/>Amount</td>
							<?php if (gatekeeper('com_loan/deletepayments')) {
							echo '<td style="border-bottom:1px solid #b0b0b0;">Delete</td>';
							} ?>
							<td style="border-bottom:1px solid #b0b0b0;">Error<br/>Type</td>
							<td style="border-bottom:1px solid #b0b0b0;">Edit</td>
							<td style="border-bottom:1px solid #b0b0b0;">Reset</td>
						</tr>
							<?php 
								if (!isset($this->entity->pay_by_date)) {
									?>
									<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;" colspan="6">There are currently no payments to edit.</td>	
									<?php
								} else {
									foreach ($this->entity->pay_by_date as $pbd) {
										$edit_unique_id = uniqid();
									?>
									<script type="text/javascript">
										// <![CDATA[
									pines(function(){
										// Define variables
										var edit_button = $("input[name='edit_button_<?php echo $edit_unique_id; ?>']");
										var reset_button = $("input[name='reset_button_<?php echo $edit_unique_id; ?>']");
										var receive_date = $('#receive_date_<?php echo $edit_unique_id; ?>');
										var payment_amount = $('#payment_amount_<?php echo $edit_unique_id; ?>');
										var delete_payment = $('#delete_payment_<?php echo $edit_unique_id; ?>');
										var error_type = $('#error_type_<?php echo $edit_unique_id; ?>');
										var payment_id = $('#payment_id_<?php echo $edit_unique_id; ?>');
										var blank_error = $('#blank_error_<?php echo $edit_unique_id; ?>');
										var receive_value = receive_date.val();
										var payment_value = payment_amount.val();
										var error_value = error_type.val();
										
										edit_button.click(function() {
											receive_date.removeAttr('disabled');
											receive_date.attr('class', 'ui-widget-content ui-corner-all');
											receive_date.datepicker({
												changeMonth: true,
												changeYear: true,
												yearRange: '-5:c',
												dateFormat: 'yy-mm-dd',
												maxDate: '+1'
											});
											payment_amount.removeAttr('disabled');
											payment_amount.attr('class', 'ui-widget-content ui-corner-all');
											delete_payment.removeAttr('disabled');
											delete_payment.attr('class', 'ui-widget-content ui-corner-all');
											error_type.removeAttr('disabled');
											error_type.attr('class', 'ui-widget-content ui-corner-all');
											payment_id.removeAttr('disabled');
											blank_error.attr('disabled', 'disabled');
										});
										reset_button.click(function() {
											receive_date.attr('disabled', 'disabled');
											receive_date.attr({
												'class': 'ui-state-disabled ui-widget-content ui-corner-all',
												'value': receive_value
											});
											payment_amount.attr('disabled', 'disabled');
											payment_amount.attr({
												'class': 'ui-state-disabled ui-widget-content ui-corner-all',
												'value': payment_value
											});
											delete_payment.attr('disabled', 'disabled');
											delete_payment.attr({
												'class': 'ui-state-disabled ui-widget-content ui-corner-all',
												'checked': false
											});
											error_type.attr('disabled', 'disabled');
											error_type.attr({
												'class': 'ui-state-disabled ui-widget-content ui-corner-all',
												'value': error_value
											});
											payment_id.attr('disabled', 'disabled');
											blank_error.removeAttr('disabled');
										});
										
										$('#p_muid_edit_payment_form').submit(function(e){
											if (!error_type.is('[disabled]')) {
												if (error_type.val() == "blank") {
													alert('Please provide an Error Type for all Edits.');
													e.preventDefault();
												}
											}
										});
									});
									
									
										// ]]>
									</script>
									<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
										<td style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;"><input id="<?php echo 'receive_date_'.$edit_unique_id; ?>" type="text" class="ui-state-disabled ui-widget-content ui-corner-all" value="<?php echo htmlspecialchars(format_date($pbd['date_received'], "date_sort")); ?>" style="color:inherit;padding:4px 1px;margin:0;display:inline;text-align:right" disabled="disabled" size="10" name="receive_date[]"/></td>
										<td style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;"><input id="<?php echo 'payment_amount_'.$edit_unique_id; ?>" type="text" class="ui-state-disabled ui-widget-content ui-corner-all" value="<?php echo '$'.htmlspecialchars($pines->com_sales->round($pbd['payment_amount'], true)); ?>" style="color:inherit;padding:4px 1px;margin:0;display:inline;text-align:right" disabled="disabled" size="10" name="payment_amount[]"/></td>
										
										<?php if (gatekeeper('com_loan/deletepayments')) {
											?>
											<td style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;"><input id="<?php echo 'delete_payment_'.$edit_unique_id; ?>" type="checkbox" class="ui-state-disabled ui-widget-content ui-corner-all" value="<?php echo htmlspecialchars($pbd['payment_id']); ?>" style="color:inherit;padding:4px 1px;margin:0;display:inline;text-align:right" disabled="disabled" size="10" name="delete_payment[]"/></td>
											<?php
										} ?>
										
										<td style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;">
											<select id="<?php echo 'error_type_'.$edit_unique_id; ?>"  disabled="disabled" class="ui-state-disabled ui-widget-content ui-corner-all" style="color:inherit;padding:4px 1px;margin:0;display:inline;text-align:left;" name="error_type[]">
												<option id="<?php echo 'blank_error_'.$edit_unique_id; ?>" value="blank">Select</option>
												<option value="input error">Input Error</option>
												<option value="code error">Code Error</option>
												<option value="billing error">Billing Error</option>
												<option value="other error">Other Error</option>
											</select>
										</td>
										<td style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;"><input type="button" class="pf-button ui-state-default ui-corner-all ui-button ui-widget" value="edit" style="padding:4px 1px;margin:0;display:inline;text-align:right" size="10" name="<?php echo 'edit_button_'.$edit_unique_id; ?>" id="<?php echo htmlspecialchars($pbd['payment_id']); ?>"/></td>
										<td style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;"><input type="button" class="pf-button ui-state-default ui-corner-all ui-button ui-widget" value="reset" style="padding:4px 1px;margin:0;display:inline;text-align:right" size="10" name="<?php echo 'reset_button_'.$edit_unique_id; ?>"/></td>
										<input type="hidden" id="<?php echo 'payment_id_'.$edit_unique_id; ?>" name="payment_id[]" value="<?php echo htmlspecialchars($pbd['payment_id']); ?>" disabled="disabled" />
									</tr>
									<?
									}
								}
							?>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="pf-element" style="float:right;clear:none;">
					<?php if ( isset($this->entity->guid) ) { ?>
						<input id="edit_payment_loan_id" type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
					<?php } ?>
					<span class="pf-label" style="width:230px;">Save all Edited, Enabled Payments.</span>
					<input type="hidden" name="editpayments" value="edit_payment" />
					<input id="p_muid_edit_edit_save" class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Save" />
				</div>
			</form>


			<div class="pf-element pf-heading">
				<p>Delete All Payments</p>
			</div>
			<div class="pf-element pf-full-width">
				<p>
					1. Quickly delete all payments on this loan. 
				</p>
				<p>
					2. Saves all current payment history as a restore point that can be reinstated. 
				</p>
				<?php
					if (gatekeeper('com_loan/deletepayments')) {
						?>
						<form class="pf-form" method="post" id="p_muid_all_payment_form" action="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/editpayments')); ?>">
						<div class="pf-element ui-corner-all" style="float:left;clear:none;margin-right:30px;border:1px solid #ddd; padding: 10px;">
							<span class="pf-label" style="width:230px;">Delete All Payments.
								<span class="pf-note" style="width:100%;">A record of all payments will be stored.</span>
							</span>
							<input id="delete_all_payments" class="pf-field" type="checkbox" name="delete_all_payments" value="ON" />
							<br/>
							<div id="delete_all_payments_name" class="pf-element" style="float:left;">
								<span class="pf-label" style="width:230px;margin-top:15px;">Provide Name or Description.
									<span class="pf-note">A record will be saved with this name.</span>
								</span>
								<input class="pf-field ui-widget-content ui-corner-all" type="text" name="delete_all_payments_name" id="delete_all_payments_name_input" />
							</div>
							<div id="delete_all_payments_reason" class="pf-element" style="float:left;">
								<span class="pf-label" style="width:230px;">Provide Reason for Deletion.</span>
								<input class="pf-field ui-widget-content ui-corner-all" type="text" name="delete_all_payments_reason" id="delete_all_payments_reason_input" />
							</div>
						</div>
						<div class="pf-element" style="float:right;clear:none;width:auto;margin-top:10px;">
						<?php if ( isset($this->entity->guid) ) { ?>
							<input type="hidden" name="id" value="<?php echo (int) htmlspecialchars($this->entity->guid); ?>" />
						<?php } ?>
						<span class="pf-label" style="width:230px;">Save Changes to Payments.</span>
						<input type="hidden" name="editpayments" value="all_payments" />
						<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Save" />
						</div>
						</form>
						<?php
					}
				?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php 
		// Check if any payment history type exists.
		if (!empty($this->entity->history->edit_payments)) {
			$history_exists = true;
		}
		// Use delete array to create logs for edit payments.
		// Make an array of only delete history for edit payments.
		// While we're at it, make a edit only array.
		if ($history_exists) {
			foreach ($this->entity->history->edit_payments as $edit_payment) {
				if (isset($edit_payment['edit_info'])) {
					if (!$edit_payments) {
						$edit_payments = array();
					}
					$edit_payments[] = $edit_payment;
				}
			}
			foreach ($this->entity->history->edit_payments as $delete_payment) {
				if (isset($delete_payment['delete_info'])) {
					if (!$delete_payments) {
						$delete_payments = array();
					}
					$delete_payments[] = $delete_payment;
				}
			}
		}
	
		if (gatekeeper('com_loan/deletepayments')) {
		?>
	
		<div id="p_muid_tab_delete_log">
			<div class="pf-form">
				<div class="pf-element pf-heading">
					<h1>View Payment Delete History.</h1>
				</div>
				<div>
					<?php 
					if (!empty($this->entity->history->edit_payments)) {
						// Get number of errors in the delete records 
						foreach ($this->entity->history->edit_payments as $edit_delete) {
							if (isset($edit_delete['delete_info']['delete_date_recorded'])) {
								switch ($edit_delete['delete_info']['delete_reason']) {
									case "input error":
										$input_count += 1;
										break;
									case "code error":
										$code_count += 1;
										break;
									case "billing error":
										$billing_count += 1;
										break;
									default:
										$other_count += 1;
										break;
								}
							}
						}
					}
					?>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php echo (empty($input_count)) ? 0: $input_count; ?> <span class="picon-input-keyboard" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Input Errors</span></span></div>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php echo (empty($code_count)) ? 0: $code_count; ?> <span class="picon-script-error" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Code Errors</span></span></div>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php echo (empty($billing_count)) ? 0: $billing_count; ?> <span class="picon-wallet-open" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Billing Errors</span></span></div>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php // echo (empty($other_count)) ? 0: $other_count; ?> <span class="picon-dialog-error" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Other Error</span></span></div>
					<!-- <div style="cursor:pointer;"><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:right;margin-right:10px;"><span class="picon-view-refresh" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Reset</span></span></div> -->
				</div>
				<div class="pf-element pf-heading">
					<p>Payment Deletes.</p>
				</div>
				<div class="pf-element pf-full-width">
					<?php 
				//var_dump($this->entity->pay_by_date);
				//var_dump($this->entity->paid);
				//var_dump($this->entity->payments);
				//var_dump($this->entity->history->edit_payments);
			?>
					<table style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em" cellpadding="0" cellspacing="0" border="0" width="100%">
						<tbody>
							<tr class="ui-widget-header" style="text-align:center;border:0;">
								<th style="border-bottom:1px solid #b0b0b0;">Change</th>
								<th style="border-bottom:1px solid #b0b0b0;">Error<br/>Type</th>
								<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Type</th>
								<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Date Due</th>
								<th style="border-bottom:1px solid #b0b0b0;">Receive<br/>Date</th>
								<th style="border-bottom:1px solid #b0b0b0;">Edit<br/>Date</th>
								<th style="border-bottom:1px solid #b0b0b0;">User</th>
								<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Amount</th>
								<th style="border-bottom:1px solid #b0b0b0;">Additional</th>
								<th style="border-bottom:1px solid #b0b0b0;">Interest</th>
								<th style="border-bottom:1px solid #b0b0b0;">Principal</th>
								<th style="border-bottom:1px solid #b0b0b0;">Details</th>
							</tr>
							<?php 
								// Check if deleted payment history exists yet.
								if (empty($delete_payments)) {
									?>
									<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
									<td colspan="11" style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;">
										No Payment Delete History to Display.
									</td>
									<?php		
								} else {
									// Dynamically iterate through Edit History!

									// Use a counter to get "next" values easier.
									$r = 0;
									foreach ($delete_payments as $delete_payment) {
										?>
										<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"> 
												<span class="picon-flag-red" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> -</span>
											</td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"> 
												<?php 
													switch ($delete_payment['delete_info']['delete_reason']) {
														case "input error":
															$icon_type_table = "picon-input-keyboard";
															break;
														case "code error":
															$icon_type_table = "picon-script-error";
															break;
														case "billing error":
															$icon_type_table = "picon-wallet-open";
															break;
														default:
															$icon_type_table = "picon-dialog-error";
															break;
													}
													
												?>
												<span class="<?php echo $icon_type_table; ?>" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">&nbsp;</span>
											</td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"> 
												<?php 
													switch ($delete_payment['delete_payment']['payment_type']) {
														case "past_due": 
															echo '<span style="color:#0a2aab;">Past Due</span>';
															break;
														case "none": 
															echo '<span style="color:#B30909;">Missed</span>';
															break;
														case "":
															echo '<span>Scheduled</span>';
															break;
														default:
															echo ucwords(htmlspecialchars($delete_payment['delete_payment']['payment_type']));
													}
												?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo htmlspecialchars(format_date($delete_payment['delete_info']['delete_date_expected'], "date_short")); ?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo htmlspecialchars(format_date($delete_payment['delete_info']['delete_payment_received'], "date_short")); ?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo htmlspecialchars(format_date($delete_payment['delete_info']['delete_date_recorded'], "date_short")); ?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo htmlspecialchars($delete_payment['delete_info']['delete_user_guid']." : ".$delete_payment['delete_info']['delete_user']); ?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_payment'], true)); ?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_additional'], true)); ?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_interest'], true)); ?>
											</td>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_principal'], true)); ?>
											</td>
											<?php 
												// A unique ID for each view dialog is necessary.
												$uniqueID2 = uniqid(); 
											?>
											<script type="text/javascript">
											// <![CDATA[
											pines(function(){
												$("#p_muid_button<?php echo '_'.$uniqueID2; ?>").click(function(){
													var dialog = $('#p_muid_details<?php echo '_'.$uniqueID2; ?>').dialog({
														width: 800,
														modal: true,
														open: function(){
															$(this).keypress(function(e){
																if (e.keyCode == 13)
																	dialog.dialog("option", "buttons").Done();
															});
														},
														buttons: {
															"Done": function(){
																dialog.dialog("close");
															}
														}
													});
												});
											});
											// ]]>
											</script>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<button class="pf-field ui-state-default ui-corner-all" id="p_muid_button<?php echo '_'.$uniqueID2; ?>" type="button" style="float: right;">view</button>
												<div id="p_muid_details<?php echo '_'.$uniqueID2; ?>" title="Extended Details" style="display:none;">
													<div class="pf-form">
														<div class="pf-element pf-heading">
															<p>The following <span class="ui-state-highlight" style="border:none;background:none;">record</span> was deleted: <span style="font-size:.9em;float:right;"><?php echo htmlspecialchars(format_date($delete_payment['delete_info']['delete_date_recorded'], "full_short")); ?></span></p>
														</div>
														<div class="pf-element pf-full-width">
															<table style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em" cellpadding="0" cellspacing="0" border="0" width="100%">
																<tbody>
																	<tr class="ui-widget-header" style="text-align:center;border:0;">
																		<th style="border-bottom:1px solid #b0b0b0;">Receive<br/>Date</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Date<br/>Recorded</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Amount</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Additional</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Interest</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Principal</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Remaining<br/>Balance</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Scheduled<br/>Balance</th>
																	</tr>
																	<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
																		<?php 
																		if ($delete_payment['delete_payment']['payment_id'] == $delete_payment['delete_info']['delete_payment_id']) {
																			// Top Level Payment is the one we deleted.
																			if (!$delete_payment['delete_payment']['extra_payments']) {
																				// It doesn't have any extra payments.
																				?>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars(format_date($delete_payment['delete_payment']['payment_date_received'], "full_short")).'</span>'; ?></td>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars(format_date($delete_payment['delete_info']['delete_date_recorded'], "full_short")).'</span>'; ?></td>
																				<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '<span class="ui-state-highlight" style="border:none;background:none;">$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_amount_paid'], true)).'</span>'; ?></td>
																				<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo ($edit_payment['edit_payment']['payment_additional'] >= .01) ? '<span class="ui-state-highlight" style="border:none;background:none;">$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_additional'], true)).'</span>' : ''; ?></td>
																				<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '<span class="ui-state-highlight" style="border:none;background:none;">$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_interest_paid'], true)).'</span>'; ?></td>
																				<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '<span class="ui-state-highlight" style="border:none;background:none;">$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_principal_paid'], true)).'</span>'; ?></td>
																				<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['remaining_balance'], true)); ?></td>
																				<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['scheduled_balance'], true)); ?></td>
																				<?php
																			} elseif ($delete_payment['delete_payment']['extra_payments']) {
																				// It has extra payments, but they aren't highlighted.
																				?>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																					<?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars(format_date($delete_payment['delete_payment']['payment_date_received'], "full_short")).'</span>'; ?>
																					<?php foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						echo '<br/>'.htmlspecialchars(format_date($extra_payment['payment_date_received'], "full_short")); } ?>
																				</td>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																					<?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars(format_date($delete_payment['delete_payment']['payment_date_recorded'], "full_short")).'</span>'; ?>
																					<?php foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						echo '<br/>'.htmlspecialchars(format_date($extra_payment['payment_date_recorded'], "full_short")); } ?>
																				</td>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																					<?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_payment'], true)).'</span>'; ?>
																					<?php foreach ($delete_payment['delete_paid']['extra_payments'] as $extra_payment) {
																						echo '<br/>'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'] + $extra_payment['payment_additional'], true)); } ?>
																				</td>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																					<?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_additional'], true)).'</span>'; ?>
																					<?php foreach ($delete_payment['delete_paid']['extra_payments'] as $extra_payment) {
																						echo '<br/>'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_additional'], true)); } ?>
																				</td>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																					<?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_interest'], true)).'</span>'; ?>
																					<?php foreach ($delete_payment['delete_paid']['extra_payments'] as $extra_payment) {
																						echo '<br/>'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'], true)); } ?>
																				</td>
																				<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																					<?php echo '<span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_principal'], true)).'</span>'; ?>
																					<?php foreach ($delete_payment['delete_paid']['extra_payments'] as $extra_payment) {
																						echo '<br/>'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_principal_paid'], true)); } ?>
																				</td>
																				<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['remaining_balance'], true)); ?></td>
																				<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['scheduled_balance'], true)); ?></td>
																				<?php
																			} 
																		} else {
																			if (!$delete_payment['delete_payment']['extra_payments']) {
																				echo '<td colspan="8" style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;">There was an error displaying this record.</td>';
																			}
																			?>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																				<?php 
																					// Show parent payment (no highlight). Then show extra payments. Highlight extra payment with matching ID.
																					echo htmlspecialchars(format_date($delete_payment['delete_payment']['payment_date_received'], "full_short"));
																					foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						if ($extra_payment['payment_id'] == $delete_payment['delete_info']['delete_payment_id']) {
																							echo '<br/><span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars(format_date($extra_payment['payment_date_received'], "full_short")).'</span>';
																						} else {
																							echo '<br/>'.htmlspecialchars(format_date($extra_payment['payment_date_received'], "full_short"));
																						}
																					}
																				?>
																			</td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																				<?php 
																					// Show parent payment (no highlight). Then show extra payments. Highlight extra payment with matching ID.
																					echo htmlspecialchars(format_date($delete_payment['delete_payment']['payment_date_recorded'], "full_short"));
																					foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						if ($extra_payment['payment_id'] == $delete_payment['delete_info']['delete_payment_id']) {
																							echo '<br><span class="ui-state-highlight" style="border:none;background:none;">'.htmlspecialchars(format_date($extra_payment['payment_date_recorded'], "full_short")).'</span>';
																						} else {
																							echo '<br/>'.htmlspecialchars(format_date($extra_payment['payment_date_recorded'], "full_short"));
																						}
																					}
																				?>
																			</td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																				<?php 
																					// Show parent payment (no highlight). Then show extra payments. Highlight extra payment with matching ID.
																					echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_amount_paid_orig'], true));
																					foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						if ($extra_payment['payment_id'] == $delete_payment['delete_info']['delete_payment_id']) {
																							echo '<br><span class="ui-state-highlight" style="background:none;border:none;">$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'] + $extra_payment['payment_additional_paid'], true)).'</span>';
																						} else {
																							echo '<br/>$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'] + $extra_payment['payment_additional_paid'], true));
																						}
																					}
																				?>
																			</td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																				<?php 
																					// Show parent payment (no highlight). Then show extra payments. Highlight extra payment with matching ID.
																					echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_additional_paid_orig'], true));
																					foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						if ($extra_payment['payment_id'] == $delete_payment['delete_info']['delete_payment_id']) {
																							echo '<br><span class="ui-state-highlight" style="background:none;border:none;">$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_additional_paid'], true)).'</span>';
																						} else {
																							echo '<br/>$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_additional_paid'], true));
																						}
																					}
																				?>
																			</td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																				<?php 
																					// Show parent payment (no highlight). Then show extra payments. Highlight extra payment with matching ID.
																					echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_interest_paid_orig'], true));
																					foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						if ($extra_payment['payment_id'] == $delete_payment['delete_info']['delete_payment_id']) {
																							echo '<br><span class="ui-state-highlight" style="background:none;border:none;">$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'], true)).'</span>';
																						} else {
																							echo '<br/>$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'], true));
																						}
																					}
																				?>
																			</td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
																				<?php 
																					// Show parent payment (no highlight). Then show extra payments. Highlight extra payment with matching ID.
																					echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['payment_principal_paid_orig'], true));
																					foreach ($delete_payment['delete_payment']['extra_payments'] as $extra_payment) {
																						if ($extra_payment['payment_id'] == $delete_payment['delete_info']['delete_payment_id']) {
																							echo '<br><span class="ui-state-highlight" style="background:none;border:none;">$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_principal_paid'], true)).'</span>';
																						} else {
																							echo '<br/>$'.htmlspecialchars($pines->com_sales->round($extra_payment['payment_principal_paid'], true));
																						}
																					}
																				?>
																			</td>
																			<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['remaining_balance'], true)); ?></td>
																			<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_payment']['scheduled_balance'], true)); ?></td>
																			
																			<?php
																		}
																		?>
																	</tr>
																</tbody>
															</table>
														</div>
														<div class="pf-element pf-heading">
															<h1>Interpretation</h1>
															<p>Review how this delete affected payments.</p>
														</div>
														<div class="pf-element pf-full-width">
															<div class="pf-label" style="width:325px !important;margin-left:5px;">
																<div class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;">
																	<?php
																	$affected_interest = null;
																	$affected_principal = null;
																	$affected_additional = null;
																	
																	$affected_interest = $delete_payment['delete_info']['delete_interest'];
																	$affected_principal = $delete_payment['delete_info']['delete_principal'];
																	$affected_additional = $delete_payment['delete_info']['delete_additional'];
																	
																	// A delete won't have an edited date change...
																	
																	if ($affected_interest && $affected_principal && $affected_additional) {
																		$change_affected = 'the <strong>interest</strong> value, the <strong>principal</strong> value, and the <strong>additional</strong>. <br/><br/> Changes in principal and additional values affected the remaining balance on the loan.';
																	} elseif ($affected_principal && $affected_additional) {
																		$change_affected = 'both the <strong>principal</strong> value and the <strong>additional</strong> value. <br/><br/> These changes to principal affected the remaining balance on the loan.';
																	} elseif ($affected_interest && $affected_principal) {
																		$change_affected = 'both the <strong>interest</strong> value and the <strong>principal</strong> value. <br/><br/>Changes in the principal value affected the remaining balance on the loan.';
																	} elseif ($affected_interest) {
																		$change_affected = 'only the <strong>interest</strong> value, which had <strong>no affect</strong> on remaining balance.';
																	} elseif ($affected_principal) {
																		$change_affected = 'only the <strong>principal</strong> value, as interest was paid off by a preceeding payment. <br/><br/> This change in principal affected the remaining balance on the loan.';
																	} elseif ($affected_additional) {
																		$change_affected = 'only the <strong>additional</strong> value, as no change was made to paid off interest and principal values. <br/><br/> This change in additional value modifies the principal - and therefore the remaining balance on the loan.';
																	} 
																	
																	$previous_remaining = $delete_payment['delete_payment']['remaining_balance'];
																	$modified_remaining = $delete_payment['delete_results']['remaining_balance'];
																	
																	$old_status = $delete_payment['delete_payment']['payment_status'];
																	$old_status_days_late = $delete_payment['delete_payment']['payment_days_late'];
																	
																	// Deleted new status will show what the status was at the time of deletion.
																	$new_status = $delete_payment['delete_results']['new_status'];
																	$new_status_days_late = $delete_payment['delete_results']['new_payment_days_late'];
																	?>
																	<div class="ui-state-highlight" style="padding: 0 3px;background:none;border:none;">Delete Payment: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_payment'], true)); ?></span></div>
																	<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
																	<div>
																		<span class="picon-flag-red" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> Decrease (-)</span>
																		<span style="float:right;padding-right:3px;"><?php echo '$-'.htmlspecialchars($pines->com_sales->round($delete_payment['delete_info']['delete_payment'], true)); ?></span>
																	</div><br/>
																	<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
																	<?php 
																		if ($affected_interest >= .01) { ?>
																		<div>Interest Affected: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($affected_interest, true)); ?></span></div>
																	<?php }
																		if ($affected_principal >= .01) { ?>
																		<div>Principal Affected: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($affected_principal, true)); ?></span></div>
																	<?php }
																		if ($affected_additional >= .01) { ?>
																		<div>Additional Affected: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($affected_additional, true)); ?></span></div>
																	<?php } ?>
																</div>
																<br/><br/><br/>
																<div>
																	
																	<?php 
																	switch ($delete_payment['delete_info']['delete_reason']) {
																		case "input error": 
																			$icon_type = "picon-input-keyboard";
																			break;
																		case "code error": 
																			$icon_type = "picon-script-error";
																			break;
																		case "billing error":
																			$icon_type = "picon-wallet-open";
																			break;
																		default:
																			$icon_type = "picon-dialog-error";
																			break;
																	}
																	$icon_span = '<span class="'.$icon_type.'" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">';
																	?>
																	
																	<div>Modified Date: <span style="float:right;"><?php echo htmlspecialchars(format_date($delete_payment['delete_info']['delete_date_recorded'], "full_short")); ?></span></div>
																	<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
																	<div style="margin-top:15px;"><span style="padding:5px;margin-right:20px;display:inline-block;float:left;">Delete Reason</span></div>
																	<div style="margin-top:15px;"><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;display:inline-block;float:right;"><?php echo $icon_span; echo htmlspecialchars(ucwords($delete_payment['delete_info']['delete_reason'])); ?></span></div>
																</div>
																<br/>
																<div class="ui-helper-clearfix" style="margin-top:50px;">
																	<?php
																		// Find out how many payments were deleted along with this one. They would have the same record date.
																		$delete_match = 0;
																		foreach ($delete_payments as $delete_record) {
																			if ($delete_payment['delete_info']['delete_date_recorded'] == $delete_record['delete_info']['delete_date_recorded']) {
																				// If it finds 1, that's itself. 
																				$delete_match += 1;
																			}
																		}
																		if ($delete_match > 1) {
																			?>
																			<div><span style="padding:5px;margin-right:20px;display:inline-block;float:left;font-size:.9em;">Notes: <?php echo $delete_match - 1; ?> other payment(s) were <u>deleted</u> with this one.</span></div>
																			<?php
																		}
																		// Find out how many payments were edited along with this one. They would have the same record date.
																		if ($edit_payments) {
																			$edit_match = 0;
																			foreach ($edit_payments as $edit_record) {
																				if ($delete_payment['delete_info']['delete_date_recorded'] == $edit_record['edit_info']['edit_date_recorded']) {
																					// If it finds 1, that's itself. 
																					$edit_match += 1;
																				}
																			}
																			if ($edit_match) {
																				?>
																				<div><span style="padding:5px;margin-right:20px;display:inline-block;float:left;font-size:.9em;">Notes: <?php echo $edit_match; ?> other payment(s) were <u>edited</u> with this delete.</span></div>
																				<?php
																			}
																		}
																		
																		
																		if ($edit_match && $delete_match > 1) {
																			?>
																			<div><span style="padding:5px;margin-right:20px;display:inline-block;float:left;font-size:.9em;">The modified payment status is reflective of all <?php echo $delete_match; ?> deleted payment(s) and all <?php echo $edit_match; ?> edited payment(s) that were processed at the same time.</span></div>
																			<?php
																		} elseif ($edit_match) {
																			?>
																			<div><span style="padding:5px;margin-right:20px;display:inline-block;float:left;font-size:.9em;">The modified payment status is reflective of all <?php echo $edit_match; ?> edited payment(s)that were processed at the same time.</span></div>
																			<?php
																		} elseif ($delete_match > 1) {
																			?>
																			<div><span style="padding:5px;margin-right:20px;display:inline-block;float:left;font-size:.9em;">The modified payment status is reflective of all <?php echo $delete_match; ?> deleted payment(s) that were processed at the same time.</span></div>
																			<?php
																		}
																	?>
																		
																</div>
															</div>
															<div class="pf-group" style="margin-left:0;width:420px;float:right;">
																<div>
																	<div class="ui-helper-clearfix ui-corner-all ui-widget-content" style="background:inherit;padding:10px;color:inherit;">
																		<p style="font-size:1.1em;">1. This change affected <?php echo $change_affected; ?></p>
																		<div style="float:left;">
																			<span style="font-size:1.4em"><?php echo '$'.htmlspecialchars($pines->com_sales->round($previous_remaining, true)); ?></span>
																			<br/><br/>
																			<span style="font-size:.9em;">Previous Remaining Balance</span>
																		</div>
																		<div style="float:right;">
																			<span style="font-size:1.4em"><?php echo '$'.htmlspecialchars($pines->com_sales->round($modified_remaining, true)); ?></span>
																			<br/><br/>
																			<span style="font-size:.9em;">Modified Remaining Balance</span>
																		</div>
																	</div>
																	<br/>
																	<div class="ui-helper-clearfix ui-corner-all ui-widget-content" style="background:inherit;padding:10px;color:inherit;">
																		<p style="font-size:1.1em;">2. The payment status <?php echo $status_affected; ?></p><br/>
																		<div style="float:left;">
																			<?php 
																				switch ($old_status) {
																					case "not due yet":
																						echo '<span class="picon-view-calendar-day" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Not Due Yet</span>';
																						break;
																					case "paid":
																						echo '<span class="picon-task-complete" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span>';
																						break;
																					case "paid_late":
																						echo '<span class="picon-task-accepted" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($old_status_days_late); echo ($old_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "partial_not_due":
																						echo '<span class="picon-task-recurring" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span>';
																						break;
																					case "partial":
																						echo '<span class="picon-task-attempt" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($old_status_days_late); echo ($old_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "missed":
																						echo '<span class="picon-task-reject" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Missed Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($old_status_days_late); echo ($old_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																				}
																			?>
																			<br/><br/>
																			<?php // To display properly when days late are present. 
																			if ($old_status == "paid_late" || $old_status == "missed") {
																				?>
																				<span style="font-size:.9em;margin-left:-18px;">Original Payment Status</span>
																				<?php
																			} else {
																				?>
																				<span style="font-size:.9em;"><br/>Original Payment Status</span>
																				<?php 
																			}
																			?>
																		</div>
																		<div style="float:right;">
																			<?php 
																				switch ($new_status) {
																					case "not due yet":
																						echo '<span class="picon-view-calendar-day" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Not Due Yet</span>';
																						break;
																					case "paid":
																						echo '<span class="picon-task-complete" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span>';
																						break;
																					case "paid_late":
																						echo '<span class="picon-task-accepted" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($new_status_days_late); echo ($new_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "partial_not_due":
																						echo '<span class="picon-task-recurring" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span>';
																						break;
																					case "partial":
																						echo '<span class="picon-task-attempt" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($new_status_days_late); echo ($new_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "missed":
																						echo '<span class="picon-task-reject" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Missed Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($new_status_days_late); echo ($new_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																				}
																			?>
																			<br/><br/>
																			<?php // To display properly when days late are present. 
																			if ($new_status == "paid_late" || $new_status == "missed") {
																				?>
																				<span style="font-size:.9em;">Modified Payment Status</span>
																				<?php
																			} else {
																				?>
																				<span style="font-size:.9em;"><br/>Modified Payment Status</span>
																				<?php 
																			}
																			?>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<br />
												</div>
											</td>
										</tr>
										<?php
										$r++;
									}
								}
									?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="p_muid_tab_restore_log">
			<div class="pf-form">
				<div class="pf-element pf-heading">
					<p>Restore All Payments.</p>
				</div>
				<div class="pf-element pf-full-width">
					<form class="pf-form" method="post" id="p_muid_deleted_all_payments_form" action="<?php echo htmlspecialchars(pines_url('com_loan', 'loan/restorepayments')); ?>">
						<table style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em" cellpadding="0" cellspacing="0" border="0" width="100%">
							<tbody>
								<tr class="ui-widget-header" style="text-align:center;border:0;">
									<th style="border-bottom:1px solid #b0b0b0;">Restore</th>
									<th style="border-bottom:1px solid #b0b0b0;">Delete Date</th>
									<th style="border-bottom:1px solid #b0b0b0;">Name</th>
									<th style="border-bottom:1px solid #b0b0b0;">Reason</th>
									<th style="border-bottom:1px solid #b0b0b0;">User</th>
									<th style="border-bottom:1px solid #b0b0b0;">Remaining<br/>Balance</th>
								</tr>
							<?php 
							if (empty($this->entity->history->all_payments)) {
								?>
								<tr>
									<td colspan="6">
										<p style="text-align:center;">No All-Payments Deletes Available.</p>
									</td>
								</tr>
								<?php
							} else {
								$ad = 0;
								foreach ($this->entity->history->all_payments as $all_delete) {
									?>
									<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<input class="p_muid_delete_restore_point" type="radio" name="delete_restore_point" value="<?php echo $ad; ?>"/>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars(format_date($all_delete['all_delete']['delete_date'], "full_short")); ?>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars(ucfirst($all_delete['all_delete']['delete_name'])); ?>
											<input type="hidden" name="restore_name[]" value="<?php echo htmlspecialchars(ucfirst($all_delete['all_delete']['delete_name'])); ?>" />
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars(ucfirst($all_delete['all_delete']['delete_reason'])); ?>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars($all_delete['all_delete']['delete_guid']." : ".$all_delete['all_delete']['delete_user']); ?>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo '$'.htmlspecialchars($pines->com_sales->round($all_delete['all_delete']['delete_remaining_balance'], true)); ?>
										</td>
									</tr>
									<?php
									$ad++;
								}
							}
							?>
						</tbody>
					</table>
				<div>
					<?php if ( isset($this->entity->guid) ) { ?>
						<input type="hidden" name="id" value="<?php echo (int) htmlspecialchars($this->entity->guid); ?>" />
						<input type="hidden" name="all_payments" value="delete_and_restore" />
					<?php } ?>
					<br/>
					<span class="pf-label" style="width:auto;float:left;">A record of your current payments will be saved.</span>
					<span style="float:right;"><input id="p_muid_delete_restore" class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Restore Selected" /></span>
				</div>
				</form>
				</div>
				<div class="pf-element pf-heading">
					<p>Previous Restores</p>
				</div>
				<div class="pf-element pf-full-width">
					<table style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em" cellpadding="0" cellspacing="0" border="0" width="100%">
						<tbody>
							<tr class="ui-widget-header" style="text-align:center;border:0;">
								<th style="border-bottom:1px solid #b0b0b0;">Restore Date</th>
								<th style="border-bottom:1px solid #b0b0b0;">Delete Date</th>
								<th style="border-bottom:1px solid #b0b0b0;">Name</th>
								<th style="border-bottom:1px solid #b0b0b0;">User</th>
								<th style="border-bottom:1px solid #b0b0b0;">Remaining<br/>Balance</th>
							</tr>
							<?php 
							if (empty($this->entity->history->restored)) {
								?>
								<tr>
									<td colspan="6">
										<p style="text-align:center;">No Restore Points have been reinstated..</p>
									</td>
								</tr>
								<?php
							} else {
								foreach ($this->entity->history->restored as $prev_restore) {
									?>
									<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars(format_date($prev_restore['date_restored'], "full_short")); ?>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars(format_date($prev_restore['restore_record']['all_delete']['delete_date'], "full_short")); ?>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars(ucfirst($prev_restore['restore_record']['all_delete']['delete_name'])); ?>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo htmlspecialchars($prev_restore['guid']." : ".$prev_restore['user']); ?>
										</td>
										<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
											<?php echo '$'.htmlspecialchars($pines->com_sales->round($prev_restore['restore_record']['all_delete']['delete_remaining_balance'], true)); ?>
										</td>
									</tr>
									<?php
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php
	} ?>
		<div id="p_muid_tab_edit_log">
			<div class="pf-form">
				<div class="pf-element pf-heading">
					<h1>View Payment Edit History.</h1>
				</div>
				<div>
					<?php 
					$input_count = null;
					$code_count = null;
					$billing_count = null;
					$other_count = null;
					
					if (!empty($this->entity->history->edit_payments)) {
						// Get number of errors in the delete records 
						foreach ($this->entity->history->edit_payments as $edit) {
							if (isset($edit['edit_info']['edit_date_recorded'])) {
								switch ($edit['edit_info']['edit_reason']) {
									case "input error":
										$input_count += 1;
										break;
									case "code error":
										$code_count += 1;
										break;
									case "billing error":
										$billing_count += 1;
										break;
									default:
										$other_count += 1;
										break;
								}
							}
						}
					}
					?>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php echo (empty($input_count)) ? 0: $input_count; ?> <span class="picon-input-keyboard" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Input Errors</span></span></div>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php echo (empty($code_count)) ? 0: $code_count; ?> <span class="picon-script-error" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Code Errors</span></span></div>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php echo (empty($billing_count)) ? 0: $billing_count; ?> <span class="picon-wallet-open" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Billing Errors</span></span></div>
					<div><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;float:left;margin-right:10px;"><?php echo (empty($other_count)) ? 0: $other_count; ?> <span class="picon-dialog-error" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">Other Error</span></span></div>
				</div>
				<div class="pf-element pf-heading">
					<p>Payment Edits.</p>
				</div>
				<div class="pf-element pf-full-width">
					<table style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em" cellpadding="0" cellspacing="0" border="0" width="100%">
						<tbody>
							<tr class="ui-widget-header" style="text-align:center;border:0;">
								<th style="border-bottom:1px solid #b0b0b0;">Change</th>
								<th style="border-bottom:1px solid #b0b0b0;">Error<br/>Type</th>
								<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Type</th>
								<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Date Due</th>
								<th style="border-bottom:1px solid #b0b0b0;">Receive<br/>Date</th>
								<th style="border-bottom:1px solid #b0b0b0;">Edit<br/>Date</th>
								<th style="border-bottom:1px solid #b0b0b0;">User</th>
								<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Amount</th>
								<th style="border-bottom:1px solid #b0b0b0;">Additional</th>
								<th style="border-bottom:1px solid #b0b0b0;">Interest</th>
								<th style="border-bottom:1px solid #b0b0b0;">Principal</th>
								<th style="border-bottom:1px solid #b0b0b0;">Details</th>
							</tr>
							<?php 
							
								// Check if edited payment history exists yet.
								if (empty($edit_payments)) {
									?>
									<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
									<td colspan="11" style="padding-left:7px; text-align:center;border-top:inherit;border-bottom:inherit;">
										No Payment Edit History to Display.
									</td>
									<?php		
								} else {
									// Dynamically iterate through Edit History!

									// Use a counter to get "next" values easier.
									$r = 0;
									foreach ($edit_payments as $edit_payment) {
										?>
										<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
												<?php
													// Get edit payment amount paid.
													$edit_payment_id = $edit_payment['edit_info']['edit_payment_id'];
													if ($edit_payment['edit_payment']['payment_id'] == $edit_payment_id) {
														if ($edit_payment['edit_payment']['extra_payments']) {
															$total_edit_payment = $edit_payment['edit_payment']['payment_amount_paid_orig'];
														} else {
															$total_edit_payment = $edit_payment['edit_payment']['payment_amount_paid'];
														}
													} elseif ($edit_payment['edit_payment']['extra_payments']) {
														foreach($edit_payment['edit_payment']['extra_payments'] as $extra_payment) {
															if ($extra_payment['payment_id'] == $edit_payment_id) {
																$total_edit_payment = $extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'] + $extra_payment['payment_additional'];
															}
														}
													}
													// Determine positive or negative change. (or date change)
													if ($edit_payment['edit_info']['edit_payment'] - $total_edit_payment >= .01 ) {
														echo '<span class="picon-flag-green" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> +</span>';
													} elseif ($edit_payment['edit_info']['edit_payment'] - $total_edit_payment <= -.01)
														echo '<span class="picon-flag-red" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> -</span>';
													else {
														echo '<span class="picon-view-calendar-day" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> Date</span>';
													}
												?>
											</td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"> 
												<?php 
													switch ($edit_payment['edit_info']['edit_reason']) {
														case "input error":
															$icon_type_table = "picon-input-keyboard";
															break;
														case "code error":
															$icon_type_table = "picon-script-error";
															break;
														case "billing error":
															$icon_type_table = "picon-wallet-open";
															break;
														default:
															$icon_type_table = "picon-dialog-error";
															break;
													}
													
												?>
												<span class="<?php echo $icon_type_table; ?>" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">&nbsp;</span>
											</td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
												<?php 
													if ($edit_payment['edit_info']['edit_date_received'] < $edit_payment['edit_info']['edit_date_expected']) {
														echo "Payment";
													} else {
														echo '<span style="color:#0a2aab;">Past Due</span>';
													}
												?>
											</td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($edit_payment['edit_info']['edit_date_expected'], "date_short")); ?></td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($edit_payment['edit_info']['edit_date_received'], "date_short")); ?></td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($edit_payment['edit_info']['edit_date_recorded'], "date_short")); ?></td>
											<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars($edit_payment['edit_info']['edit_user_guid'].' : '.$edit_payment['edit_info']['edit_user']); ?></td>
											<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($edit_payment['edit_info']['edit_payment'], true)); ?></td>
											<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo ($edit_payment['edit_info']['edit_additional'] >= .01) ? '$'.htmlspecialchars($pines->com_sales->round($edit_payment['edit_info']['edit_additional'], true)) : ''; ?></td>
											<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;">
												<?php
													if ($edit_payment['edit_info']['edit_interest'] >= .01) {
														echo '$'.htmlspecialchars($pines->com_sales->round($edit_payment['edit_info']['edit_interest'], true));
													} elseif ($edit_payment['edit_info']['edit_payment'] >= .01) {
														echo 'paid prior';
													} else {
														echo '-';
													}
												?>
											</td>
											<td style="padding-left:7px; text-align:right;border-top:inherit;border-bottom:inherit;"><?php echo ($edit_payment['edit_info']['edit_principal'] >= .01) ? '$'.htmlspecialchars($pines->com_sales->round($edit_payment['edit_info']['edit_principal'], true)) : '-'; ?></td>
											<?php 
												// A unique ID for each view dialog is necessary.
												$uniqueID = uniqid(); 
											?>
											<script type="text/javascript">
											// <![CDATA[
											pines(function(){
												$("#p_muid_button<?php echo '_'.$uniqueID; ?>").click(function(){
													var dialog = $('#p_muid_details<?php echo '_'.$uniqueID; ?>').dialog({
														width: 800,
														modal: true,
														open: function(){
															$(this).keypress(function(e){
																if (e.keyCode == 13)
																	dialog.dialog("option", "buttons").Done();
															});
														},
														buttons: {
															"Done": function(){
																dialog.dialog("close");
															}
														}
													});
												});
											});
											// ]]>
											</script>
											<td style="padding-right:7px; text-align:right;border-top:inherit;border-bottom:inherit; vertical-align:middle;">
												<button class="pf-field ui-state-default ui-corner-all" id="p_muid_button<?php echo '_'.$uniqueID; ?>" type="button" style="float: right;">view</button>
												<div id="p_muid_details<?php echo '_'.$uniqueID; ?>" title="Extended Details" style="display:none;">
													<div class="pf-form">
														<div class="pf-element pf-heading">
															<?php 
																$compare_it_match = 0;
																$compare_matches = array();
																foreach ($edit_payments as $compare_it) {
																	if ($edit_payment['edit_info']['edit_payment_id'] == $compare_it['edit_info']['edit_payment_id']) {
																		// Don't count itself,but we'll put it in the compare array for fun.
																		$compare_matches[] = $compare_it;
																		if ($edit_payment['edit_info']['edit_date_recorded'] != $compare_it['edit_info']['edit_date_recorded']) {
																			// found this payment edited another time in the edit array.
																			$compare_it_match += 1;
																		}
																	}
																}
															?>
															<p>This payment has been edited <?php echo $compare_it_match + 1; ?> time(s). Below is a history of the changes this payment has undergone.</p>
														</div>
														<div class="pf-element pf-full-width">
															<table style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em" cellpadding="0" cellspacing="0" border="0" width="100%">
																<tbody>
																	<tr class="ui-widget-header" style="text-align:center;border:0;">
																		<th style="border-bottom:1px solid #b0b0b0;">Entry</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Receive<br/>Date</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Date<br/>Recorded</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Payment<br/>Amount</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Additional</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Interest</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Principal</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Remaining<br/>Balance</th>
																		<th style="border-bottom:1px solid #b0b0b0;">Scheduled<br/>Balance</th>
																	</tr>
																	<?php
																	// This part reflects the original, the first payment ever created. It will be either the 
																	// edit payment OR the first match.
																	if ($edit_payment['edit_info']['edit_date_recorded'] < $compare_matches[0]['edit_info']['edit_date_recorded']) {
																		// Figures out which of these two was the first edit ever made, which will contain what it originally replaced.
																		$original_payment = $edit_payment['edit_payment'];
																	} else {
																		$original_payment = $compare_matches[0]['edit_payment'];
																	}
																	
																	if ($original_payment['payment_id'] == $edit_payment_id) {
																		if ($original_payment['extra_payments']) {
																			$original_interest = $original_payment['payment_interest_paid_orig'];
																			$original_principal = $original_payment['payment_principal_paid_orig'];
																			$original_additional = $original_payment['payment_additional_orig'];
																			$original_payment_amount = $original_payment['payment_amount_paid_orig'];
																		} else {
																			$original_interest = $original_payment['payment_interest_paid'];
																			$original_principal = $original_payment['payment_principal_paid'];
																			$original_additional = $original_payment['payment_additional'];
																			$original_payment_amount = $original_payment['payment_amount_paid'];
																		}
																		$original_received = $original_payment['payment_date_received'];
																		$original_recorded = $original_payment['payment_date_recorded'];
																	} elseif ($original_payment['extra_payments']) {
																		foreach ($original_payment['extra_payments'] as $extra_payment) {
																			if ($extra_payment['payment_id'] == $edit_payment_id) {
																				$original_interest = $extra_payment['payment_interest_paid'];
																				$original_principal = $extra_payment['payment_principal_paid'];
																				$original_additional = $extra_payment['payment_additional'];
																				$original_payment_amount = $original_interest + $original_principal + $original_additional;
																				
																			}
																			$original_received = $extra_payment['payment_date_received'];
																			$original_recorded = $extra_payment['payment_date_recorded'];
																		}
																	}
																	// This is for Original Payments:
																	?>
																	<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">Original Payment</td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($original_received, "full_short")); ?></td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($original_recorded, "full_short")); ?></td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($original_payment_amount, true)); ?></td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($original_additional, true)); ?></td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($original_interest, true)); ?></td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($original_principal, true)); ?></td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($original_payment['remaining_balance'], true)); ?></td>
																		<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($original_payment['scheduled_balance'], true)); ?></td>
																	</tr>
																	<?php
																	// For all other edits!
																	if (!empty($compare_matches)) {
																		$match_count = 1;
																		foreach ($compare_matches as $a_match) {
																			if ($edit_payment['edit_info']['edit_date_recorded'] == $a_match['edit_info']['edit_date_recorded']) {
																				// This is our edit payment!
																				$edit_payment_count = $match_count;
																				?>
																				<tr class="ui-state-highlight" style="height:40px;padding:3px;">
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo "Current Edit ".$match_count; ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($a_match['edit_info']['edit_date_received'], "full_short")); ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($a_match['edit_info']['edit_date_recorded'], "full_short")); ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_payment'], true)); ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_additional'], true)); ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_interest'], true)); ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_principal'], true)); ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_results']['new_payment']['remaining_balance'], true)); ?></td>
																					<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_results']['new_payment']['scheduled_balance'], true)); ?></td>
																				</tr>
																				<?php
																			} else {
																		?>
																		<tr style="height:40px;border-top:none;border-bottom:1px solid #eee;padding:3px;">
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo "Edit ". $match_count; ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($a_match['edit_info']['edit_date_received'], "full_short")); ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo htmlspecialchars(format_date($a_match['edit_info']['edit_date_recorded'], "full_short")); ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_payment'], true)); ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_additional'], true)); ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_interest'], true)); ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_info']['edit_principal'], true)); ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_results']['new_payment']['remaining_balance'], true)); ?></td>
																			<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($a_match['edit_results']['new_payment']['scheduled_balance'], true)); ?></td>
																		</tr>
																		<?php
																			}
																		$match_count++;
																		}
																	}
																	?>
																</tbody>
															</table>
														</div>
														<div class="pf-element pf-heading">
															<h1>Interpretation</h1>
															<p>Review how this payment was modified.</p>
														</div>
														<div class="pf-element pf-full-width">
															<div class="pf-label" style="width:325px !important;margin-left:5px;">
																<div class="ui-corner-all" style="padding:5px;background:inherit;color:inherit;">
																	<?php
																		// Clear variables
																		$date_change = null;
																		$payment_change = null;
																		$replaced = null;
																		// Check for Payment Change
																		if ($edit_payment_count - 1 == 0) {
																			$replaced = "original";
																			$replaced_amount = '$'.htmlspecialchars($pines->com_sales->round($original_payment_amount, true));
																			$replace_payment_difference = $edit_payment['edit_info']['edit_payment'] - $original_payment_amount;
																		}
																		else {
																			$replaced_amount = '$'.htmlspecialchars($pines->com_sales->round($compare_matches[$edit_payment_count-2]['edit_info']['edit_payment'], true));
																			$replace_payment_difference = $edit_payment['edit_info']['edit_payment'] - $compare_matches[$edit_payment_count-2]['edit_info']['edit_payment'];
																		}
																		if ($replace_payment_difference >= .01) {
																			$payment_change = '<span class="picon-flag-green" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> Increase (+)</span>';
																		} elseif ($replace_payment_difference <= -.01)
																			$payment_change = '<span class="picon-flag-red" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> Decrease (-)</span>';
																		
																		// Check for Date Change
																		if ($edit_payment['edit_info']['edit_date_received_orig'] < $edit_payment['edit_info']['edit_date_received'])
																			$date_change = '<span class="picon-view-calendar-day" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> A Later Date (+)</span>';
																		elseif ($edit_payment['edit_info']['edit_date_received_orig'] > $edit_payment['edit_info']['edit_date_received'])
																			$date_change = '<span class="picon-view-calendar-day" style="white-space:nowrap;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;"> An Earlier Date (-)</span>';

																		if ($payment_change) {
																			?>
																			<div class="ui-state-highlight" style="padding: 0 3px;background:none;border:none;">Replaced Payment: 
																				<span style="float:right;"><?php echo $replaced_amount; ?></span>
																			</div>
																			<div class="ui-state-highlight" style="padding: 0 3px;">New Payment: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($edit_payment['edit_info']['edit_payment'], true)); ?></span></div>
																			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
																			<div>
																				<?php
																				echo $payment_change;
																				?>
																				<span style="float:right;padding-right:3px;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($replace_payment_difference, true)); ?></span>
																			</div><br/>
																			<?php
																			if ($date_change) 
																				echo '<br/>';
																		}
																		
																		if ($date_change) {
																			?>
																			<div class="ui-state-highlight" style="padding: 0 3px;background:none;border:none;">Original Receive Date: <span style="float:right;"><?php echo htmlspecialchars(format_date($edit_payment['edit_info']['edit_date_received_orig'], "date_short")); ?></span></div>
																			<div class="ui-state-highlight" style="padding: 0 3px;">New Receive Date: <span style="float:right;"><?php echo htmlspecialchars(format_date($edit_payment['edit_info']['edit_date_received'], "date_short")); ?></span></div>
																			<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
																			<div>
																				<?php
																				echo $date_change;
																				?>
																				<span style="float:right;padding-right:3px;"><?php echo format_date_range($edit_payment['edit_info']['edit_date_received_orig'], $edit_payment['edit_info']['edit_date_received'],'#days#') ?> Days</span>
																			</div><br/>
																		<?php
																		} 
																		
																		$affected_interest = null;
																		$affected_principal = null;
																		$affected_additional = null;
																		
																		if ($replaced == "original") {
																			$old_interest = $original_interest;
																			$old_principal = $original_principal;
																			$old_additional = $original_additional;
																		} else {
																			$old_interest = $compare_matches[$edit_payment_count-2]['edit_info']['edit_interest'];
																			$old_principal = $compare_matches[$edit_payment_count-2]['edit_info']['edit_principal'];
																			$old_additional = $compare_matches[$edit_payment_count-2]['edit_info']['edit_additional'];
																		}
																		
																		$new_interest = $edit_payment['edit_info']['edit_interest'];
																		$new_principal = $edit_payment['edit_info']['edit_principal'];
																		$new_additional = $edit_payment['edit_info']['edit_additional'];
																		
																		
																		$affected_interest_value = $new_interest - $old_interest;
																		if ($pines->com_sales->round($affected_interest_value) != 0)
																			$affected_interest = true;
																		$affected_principal_value = $new_principal - $old_principal;
																		if ($pines->com_sales->round($affected_principal_value) != 0)
																			$affected_principal = true;
																		$affected_additional_value = $new_additional - $old_additional;
																		if ($pines->com_sales->round($affected_additional_value) != 0)
																			$affected_additional = true;
																		
																		
																		if ($date_change && $affected_interest && $affected_principal && $affected_additional) {
																			$change_affected = 'the <strong>interest</strong> value, the <strong>principal</strong> value, and the <strong>additional</strong>. <br/><br/> Changes in principal and additional values affected the remaining balance on the loan.<br/>Also, the date received was changed.';
																		} elseif ($affected_interest && $affected_principal && $affected_additional) {
																			$change_affected = 'the <strong>interest</strong> value, the <strong>principal</strong> value, and the <strong>additional</strong>. <br/><br/> Changes in principal and additional values affected the remaining balance on the loan.';
																		} elseif ($affected_principal && $affected_additional) {
																			$change_affected = 'both the <strong>principal</strong> value and the <strong>additional</strong> value. <br/><br/> These changes to principal affected the remaining balance on the loan.';
																		} elseif ($affected_interest && $affected_principal) {
																			$change_affected = 'both the <strong>interest</strong> value and the <strong>principal</strong> value. <br/><br/>Changes in the principal value affected the remaining balance on the loan.';
																		} elseif ($affected_interest) {
																			$change_affected = 'only the <strong>interest</strong> value, which had <strong>no affect</strong> on remaining balance.';
																		} elseif ($affected_principal) {
																			$change_affected = 'only the <strong>principal</strong> value, as interest was paid off by a preceeding payment. <br/><br/> This change in principal affected the remaining balance on the loan.';
																		} elseif ($affected_additional) {
																			$change_affected = 'only the <strong>additional</strong> value, as no change was made to interest and principal values. <br/><br/> This change in additional value modifies the principal - and therefore the remaining balance on the loan.';
																		} else {
																			$change_affected = 'only the date received.';
																		}

																		// Get old payment status:
																		$old_status = $edit_payment['edit_payment']['payment_status'];
																		$old_status_days_late = $edit_payment['edit_payment']['payment_days_late'];
																		$previous_remaining = $edit_payment['edit_payment']['remaining_balance'];

																		// Get new payment status:
																		$new_status = $edit_payment['edit_results']['new_payment']['payment_status'];
																		$new_status_days_late = $edit_payment['edit_results']['new_payment']['payment_days_late'];
																		$modified_remaining = $edit_payment['edit_results']['new_payment']['remaining_balance'];

																		// Determine if payment status was affected.
																		if ($old_status == $new_status) {
																			$status_affected = "was <strong>not affected</strong>.";
																		} else
																			$status_affected = "was also <strong>affected</strong>.";
																	?>
																	<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
																	<?php 
																		if ($affected_interest) { ?>
																		<div>Interest Affected: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($affected_interest_value, true)); ?></span></div>
																	<?php }
																		if ($affected_principal) { ?>
																		<div>Principal Affected: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($affected_principal_value, true)); ?></span></div>
																	<?php }
																		if ($affected_additional) { ?>
																		<div>Additional Affected: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($affected_additional_value, true)); ?></span></div>
																	<?php
																		}
																	?>
																</div>
																<br/><br/><br/>
																<div>
																	
																	<?php 
																	switch ($edit_payment['edit_info']['edit_reason']) {
																		case "input error": 
																			$icon_type = "picon-input-keyboard";
																			break;
																		case "code error": 
																			$icon_type = "picon-script-error";
																			break;
																		case "billing error":
																			$icon_type = "picon-wallet-open";
																			break;
																		default:
																			$icon_type = "picon-dialog-error";
																			break;
																	}
																	$icon_span = '<span class="'.$icon_type.'" style="text-align:left;display:inline-block;line-height:16px;padding-left:20px; background-repeat:no-repeat;">';
																	?>
																	<div>Modified Date: <span style="float:right;"><?php echo htmlspecialchars(format_date($edit_payment['edit_info']['edit_date_recorded'], "full_short")); ?></span></div>
																	<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
																	<div style="margin-top:15px;"><span style="padding:5px;margin-right:20px;display:inline-block;float:left;">Edit Reason</span></div>
																	<div style="margin-top:15px;"><span class="ui-corner-all ui-widget-content" style="padding:5px;background:inherit;color:inherit;display:inline-block;float:right;"><?php echo $icon_span; echo htmlspecialchars(ucwords($edit_payment['edit_info']['edit_reason'])); ?></span></div>
																</div>
																<br/><br/><br/>
																<div>
																	<?php
																	$deleted_edit = false;
																	if (!empty($delete_payments)) {
																		foreach ($delete_payments as $compare_delete_payment) {
																			if ($edit_payment_id == $compare_delete_payment['delete_info']['delete_payment_id']) {
																				$deleted_edit = true;
																			}
																		}
																	}
																	if ($deleted_edit) {
																		?>
																		<p style="font-size:.9em;">
																			<strong>* This payment was eventually deleted. No more edits can be made to it.</strong>
																		</p>
																		<?php
																	}
																	
																	$edited_other_payments = null;
																	foreach ($this->entity->history->edit_payments as $edit_other) {
																		if ($edit_other['edit_info']['edit_date_recorded'] == $edit_payment['edit_info']['edit_date_recorded'] || $edit_other['delete_info']['delete_date_recorded'] == $edit_payment['edit_info']['edit_date_recorded']) {
																			$edited_other_payments += 1;
																		}
																	}
																	
																	if ($edited_other_payments > 1) {
																		?>
																		<p style="font-size:.9em;">
																			<strong>* <?php echo $edited_other_payments - 1; ?> other payment(s) were edited/deleted along with this payment, affecting the modified remaining balance and payment status - even if this payment itself did not affect it!</strong>
																		</p>
																		<?php
																	}
																	
																	if ($edit_payment['edit_results']['new_payment']['extra_payments']) {
																		$has_extra_payments = count($edit_payment['edit_results']['new_payment']['extra_payments']);
																	}
																	
																	if ($has_extra_payments) {
																		?>
																		<p style="font-size:.9em;">
																			<strong>* This payment has <?php echo $has_extra_payments; ?> extra payment(s), which may affect interest, principal and/or additional values. </strong>
																		</p>
																		<?php
																	}
																	?>
																		
																		
																</div>
															</div>
															<div class="pf-group" style="margin-left:0;width:420px;float:right;">
																<div>
																	<div class="ui-helper-clearfix ui-corner-all ui-widget-content" style="background:inherit;padding:10px;color:inherit;">
																		<p style="font-size:1.1em;">1. This change affected <?php echo $change_affected; ?></p>
																		<div style="float:left;">
																			<span style="font-size:1.4em"><?php echo '$'.htmlspecialchars($pines->com_sales->round($previous_remaining, true)); ?></span>
																			<br/><br/>
																			<span style="font-size:.9em;">Previous Remaining Balance</span>
																		</div>
																		<div style="float:right;">
																			<span style="font-size:1.4em"><?php echo '$'.htmlspecialchars($pines->com_sales->round($modified_remaining, true)); ?></span>
																			<br/><br/>
																			<span style="font-size:.9em;">Modified Remaining Balance</span>
																		</div>
																	</div>
																	<br/>
																	<div class="ui-helper-clearfix ui-corner-all ui-widget-content" style="background:inherit;padding:10px;color:inherit;">
																		<p style="font-size:1.1em;">2. The payment status <?php echo $status_affected; ?></p><br/>
																		<div style="float:left;">
																			<?php 
																				switch ($old_status) {
																					case "not due yet":
																						echo '<span class="picon-view-calendar-day" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Not Due Yet</span>';
																						break;
																					case "paid":
																						echo '<span class="picon-task-complete" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span>';
																						break;
																					case "paid_late":
																						echo '<span class="picon-task-accepted" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($old_status_days_late); echo ($old_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "partial_not_due":
																						echo '<span class="picon-task-recurring" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span>';
																						break;
																					case "partial":
																						echo '<span class="picon-task-attempt" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($old_status_days_late); echo ($old_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "missed":
																						echo '<span class="picon-task-reject" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Missed Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($old_status_days_late); echo ($old_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																				}
																			?>
																			<br/><br/>
																			<?php // To display properly when days late are present. 
																			if ($old_status == "paid_late" || $old_status == "partial" || $old_status == "missed") {
																				?>
																				<span style="font-size:.9em;margin-left:-18px;">Original Payment Status</span>
																				<?php
																			} else {
																				?>
																				<span style="font-size:.9em;"><br/>Original Payment Status</span>
																				<?php 
																			}
																			?>
																		</div>
																		<div style="float:right;">
																			<?php 
																				switch ($new_status) {
																					case "not due yet":
																						echo '<span class="picon-view-calendar-day" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Not Due Yet</span>';
																						break;
																					case "paid":
																						echo '<span class="picon-task-complete" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span>';
																						break;
																					case "paid_late":
																						echo '<span class="picon-task-accepted" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($new_status_days_late); echo ($new_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "partial_not_due":
																						echo '<span class="picon-task-recurring" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span>';
																						break;
																					case "partial":
																						echo '<span class="picon-task-attempt" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($new_status_days_late); echo ($new_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																					case "missed":
																						echo '<span class="picon-task-reject" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Missed Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($new_status_days_late); echo ($new_status_days_late > 1) ? " days late" : " day late"."</span>";
																						break;
																				}
																			?>
																			<br/><br/>
																			<?php // To display properly when days late are present. 
																			if ($new_status == "paid_late" || $new_status == "partial" || $new_status == "missed") {
																				?>
																				<span style="font-size:.9em;">Modified Payment Status</span>
																				<?php
																			} else {
																				?>
																				<span style="font-size:.9em;"><br/>Modified Payment Status</span>
																				<?php 
																			}
																			?>
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												<br />	
												</div>
											</td>
										</tr>
										<?
										$r++;
									} 
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="p_muid_tab_overview">
			<?php
//			var_dump($this->entity->paid);
//			var_dump($this->entity->payments);
			?> 
			<div class="pf-form">
				<div class="pf-element pf-heading">
					<h1>Summary</h1>
				</div>
				<div class="pf-element pf-full-width">
					<div class="pf-label" style="width:280px !important;margin-right:60px;margin-left:5px;">
						<span style="font-weight:bold;">Principal Amount: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></span></span><br/>
						<span style="font-weight:bold;">APR: <span style="float:right;"><?php echo htmlspecialchars($this->entity->apr).'%'; ?></span></span><br/>
						<span style="font-weight:bold;">Term: <span style="float:right;"><?php echo htmlspecialchars($this->entity->term); echo " ".htmlspecialchars($this->entity->term_type); ?></span></span><br/>
						<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>

						<?php if($this->entity->missed_first_payment && !(!empty($this->entity->paid))) {
							?>
							<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></span><br/>
							<span>First Payment Missed: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['first_payment_missed'], "date_short")); ?></span></span><br/>
							<span>Last Payment Made: <span style="float:right;"><?php echo '<span style="color:#b30909;">'.htmlspecialchars($this->entity->payments[0]['last_payment_made']).'</span>'; ?></span></span><br/>
							<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")); ?></span></strong></span><br/>
							<?php
						} elseif (!empty($this->entity->paid)) {
							?>
							<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></span><br/>
							<span>First Payment Made: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['first_payment_made'], "date_short")); ?></span></span><br/>
							<span>Last Payment Made: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['last_payment_made'], "date_short")); ?></span></span><br/>
							<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")); ?></span></strong></span><br/>
							<?php
						} else {
							?>
							<span>First Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></span><br/>
							<span>Last Payment Made: <span style="float:right;"><?php echo "n/a" ?></span></span><br/>
							<span><strong>Next Payment Due: <span style="float:right;"><?php echo htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span></strong></span><br/>
							<?php
						}
						?>
						<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>

						<span>Payment Frequency: <span style="float:right;">
							<?php 
								($this->entity->payment_frequency);
								switch ($this->entity->payment_frequency) {
									case "12":
										echo "Monthly";
										$payment_frequency = "Monthly";
										break;
									case "1":
										echo "Annually";
										$payment_frequency = "Annually";
										break;
									case "2":
										echo "Semi-annually";
										$payment_frequency = "Semi-annually";
										break;
									case "4":
										echo "Quarterly";
										$payment_frequency = "Quarterly";
										break;
									case "6":
										echo "Bi-monthly";
										$payment_frequency = "Bi-monthly";
										break;
									case "24":
										echo "Semi-monthly";
										$payment_frequency = "Semi-monthly";
										break;
									case "26":
										echo "Bi-weekly";
										$payment_frequency = "Bi-weekly";
										break;
									case "52":
										echo "Weekly";
										$payment_frequency = "Weekly";
										break;
								}
							?>
						</span></span><br/>
						<span>Compound Frequency: <span style="float:right;">
								<?php 
								($this->entity->compound_frequency);
								switch ($this->entity->compound_frequency) {
									case "12":
										echo "Monthly";
										break;
									case "1":
										echo "Annually";
										break;
									case "2":
										echo "Semi-annually";
										break;
									case "4":
										echo "Quarterly";
										break;
									case "6":
										echo "Bi-monthly";
										break;
									case "24":
										echo "Semi-monthly";
										break;
									case "26":
										echo "Bi-weekly";
										break;
									case "52":
										echo "Weekly";
										break;
								}
							?>
						</span></span><br/>
						<span>Payment Type: <span style="float:right;">
								<?php 
								htmlspecialchars($this->entity->payment_type);
								switch ($this->entity->payment_type) {
									case "0":
										echo "End of Period";
										break;
									case "1":
										echo "Beginning of Period";
										break;
								}
							?>
						</span></span>

						<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
						<span>Rate (Per Period): <span style="float:right;"><?php echo htmlspecialchars(round($this->entity->rate_per_period, 3)).'%'; ?></span></span><br/>
						<span>Payments Scheduled: <span style="float:right;"><?php echo htmlspecialchars($this->entity->number_payments); ?></span></span><br/>
						<span>Total Payments: <span style="float:right;"><?php echo ($this->entity->new_total_payment_sum) ? '$'.htmlspecialchars($pines->com_sales->round($this->entity->new_total_payment_sum)) : '$'.htmlspecialchars($pines->com_sales->round($this->entity->total_payment_sum)); ?></span></span><br/>

						<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
						<span>Remaining Payments: <span style="float:right;">
								<?php 
								echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars(round($this->entity->payments[0]['remaining_payments'], 2)) : htmlspecialchars($this->entity->number_payments);
							?>
						</span></span><br/>
						<span>Remaining Payment Due Dates: <span style="float:right;">
								<?php 
								echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars($this->entity->payments[0]['remaining_payments_due']) : htmlspecialchars($this->entity->number_payments);
							?>
						</span></span><br/>
						<span>Percentage Principal Paid: <span style="float:right;">
								<?php 
								echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['percentage_paid'], true)).'%' : '0.00%';
							?>
						</span></span>
						<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
					</div>

					<div class="pf-group" style="width:280px !important;margin-left:5px !important;float:left;margin-right:60px !important;">
						<span>Total Initial Finance Charges: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_interest_sum_original); ?></span></span><br/>
						<span>Total Fees & Adjustments: <span style="float:right;"><?php echo (isset($this->entity->total_fees_adjustments)) ? '$'.htmlspecialchars($this->entity->total_fees_adjustments) : '$0.00'; ?></span></span><br/>
						<span>Total Finance Charges: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->total_interest_sum); ?></span></span><br/>
						<span>Est. Interest Savings: <span style="float:right;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->est_interest_savings, true)); ?></span></span><br/><br/>

						<div style="border:1px solid #ccc; padding:3px;"><span style="font-weight:bold;font-size:1.1em;"><?php echo $payment_frequency; ?> Payment: <span style="float:right;"><?php echo '$'.htmlspecialchars($this->entity->frequency_payment); ?></span></span></div><br/>
						<div style="border:1px solid #ccc; padding:3px;"><span style="font-weight:bold;font-size:1.1em;">Remaining Balance: <span style="float:right;"><?php echo (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) ? '$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['remaining_balance'], true)) : "$".htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></span></span></div><br/>

						<div>
							<span style="font-weight:bold;font-size:1.1em;">
								<?php if (isset($this->entity->payments[0]['sum_payment_short'])) { ?>
								<span style="font-size:.8em;">
									* Short Amount:
									<span style="float:right;">
										<?php 
											echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['sum_payment_short'], true)); 
										?>
									</span>
								</span>
								<?php } ?>
								<?php 
									if ($pines->com_sales->round($this->entity->payments[0]['unpaid_balance'] >= 0.01)) {
								?>
								<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
								<span style="font-size:.9em;">Unpaid Balance: </span>
								<span style="float:right;font-size:.9em;">
									<?php 
										echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['unpaid_balance'], true));  
									?>
								</span>
								<?php
									}
								?>
								<?php 
									if ($this->entity->payments[0]['unpaid_interest'] >= 0.01) {
								?>
								<span style="font-size:.9em;"><br/>Unpaid Interest:</span> 
								<span style="float:right;font-size:.9em;">
									<?php 
										echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['unpaid_interest'], true));
									?>
								</span>
								<?php
									}
								?>
								<?php 
									if ($this->entity->payments[0]['past_due'] >= 0.01) {
								?>
								<div style="line-height:0px;border-bottom:1px solid #ddd; margin: 5px 0;">&nbsp;</div>
								<span style="color:#b30909;">Past Due:</span>
								<span style="float:right;color:#b30909;">
									<?php 
										echo "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true));
									?>
								</span>
								<?php
									}
								?>
								<div class="ui-state-highlight ui-widget-content  ui-pnotify-container" style="margin-top:10px;">
									<span class="ui-helper-clearfix">Next Payment:
										<span style="float:right;">
											<?php 
												if ($this->entity->payments[0]['past_due'] >= 0.01) {
													?>
													<script type="text/javascript">
															// <![CDATA[
														pines(function(){
															// This is how to change the default settings for the entire page.
															//$.pnotify.defaults.pnotify_width = "400px";
															// If you don't want new lines ("\n") automatically converted to breaks ("<br />")
															//$.pnotify.defaults.pnotify_insert_brs = false;

															// This notice is used as a tooltip.
															p_muid_tooltip_next_payment_due = $.pnotify({
																pnotify_title: '<span style="color:#B30909">Next Payment Due: <span style="float:right;">$<?php echo (htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true)) + htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true)))?></span></span>',
																pnotify_text: '<?php echo '<span style="color:#520e0e;">Past Due: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['past_due'], true)).'</span><br/>'.$payment_frequency.' Payment: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true)); ?> </span><br/><span style="font-size:.8em;">Past Due Amount is due immediately.</span></span>',
																pnotify_hide: false,
																pnotify_closer: false,
																pnotify_history: false,
																pnotify_animate_speed: 100,
																pnotify_opacity: .9,
																pnotify_notice_icon: "picon picon-task-reject",
																// Setting stack to false causes Pines Notify to ignore this notice when positioning.
																pnotify_stack: false,
																pnotify_after_init: function(pnotify){
																	// Remove the notice if the user mouses over it.
																	pnotify.mouseout(function(){
																		pnotify.pnotify_remove();
																	});
																},
																pnotify_before_open: function(pnotify){
																	// This prevents the notice from displaying when it's created.
																	pnotify.pnotify({
																		pnotify_before_open: null
																	});
																	return false;
																}
															});
														});
															// ]]>
														</script>
													<?
												}
												echo ($this->entity->payments[0]['past_due'] >= 0.01) ? '<span style="cursor:pointer;color:#b30909" onmouseout="p_muid_tooltip_next_payment_due.pnotify_remove();" onmousemove="p_muid_tooltip_next_payment_due.css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseover="p_muid_tooltip_next_payment_due.pnotify_display();">$'.(htmlspecialchars($pines->com_sales->round(($this->entity->payments[0]['next_payment_due_amount'] + $pines->com_sales->round($this->entity->payments[0]['past_due'])), true))).'</span>' : "$".htmlspecialchars($pines->com_sales->round($this->entity->payments[0]['next_payment_due_amount'], true));
											?>
										</span><br/>
										<span style="float:left;background:none; border:none;font-size:.8em;">Due:</span>
										<span style="float:right;background:none; border:none;font-size:.8em;"> <?php echo (isset($this->entity->payments[0]['next_payment_due'])) ? htmlspecialchars(format_date($this->entity->payments[0]['next_payment_due'], "date_short")) : htmlspecialchars(format_date(strtotime($this->entity->first_payment_date), "date_short")); ?></span>	
										<span style="clear:both;float:left;background:none; border:none;font-size:.6em;color:#520e0e;"> <?php echo ($this->entity->payments[0]['past_due'] >= 0.01) ? "Past Due Amount is due immediately." : ""; ?></span>	
									</span>
								</div>
							</span>
						</div>
					</div>
				</div>
				<div class="pf-element pf-heading">
					<h1>Payments</h1>
				</div>
				<div class="pf-element pf-full-width">
					<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #ccc; width:100%;text-align:right; font-size:.9em">
						<tbody>
						<tr class="ui-widget-header" style="text-align:center;border:0;">
							<td style="border-bottom:1px solid #b0b0b0;">Payment<br/>Type</td>
							<td style="border-bottom:1px solid #b0b0b0;">Payment<br/>Due</td>
							<td style="border-bottom:1px solid #b0b0b0;">Payment<br/>Received</td>
							<td style="border-bottom:1px solid #b0b0b0;">Payment<br/>Status</td>
							<td style="border-bottom:1px solid #b0b0b0;">Payment</td>
							<td style="border-bottom:1px solid #b0b0b0;">Additional<br/>Payment</td>
							<td style="border-bottom:1px solid #b0b0b0;">Interest<br/>Payment</td>
							<td style="border-bottom:1px solid #b0b0b0;">Principal<br/>Payment</td>
							<td style="border-bottom:1px solid #b0b0b0;">Remaining<br/>Balance</td>
							<td style="border-bottom:1px solid #b0b0b0;">Scheduled<br/>Balance</td>


						</tr>
						<tr>
							<td style="border-bottom:1px solid #eee;padding-right:7px;" colspan="9"><span style="float:left; padding-left:7px;">Principal Balance</span><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></td>
							<td style="border-bottom:1px solid #eee;padding-right:7px;">$ <?php echo htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></td>
						</tr>
						<?php
						$first_one = false;
						foreach ($this->entity->payments as $payment) {
							if (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) {
								if ($first_one == false) {
									$first_one = true;
									continue;
								}
							}
						?>

							<?php 
							if (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) {
								if ($payment['scheduled_date_expected'] == $this->entity->payments[0]['next_payment_due']) {
									?><tr class="ui-state-highlight" style="height:40px;"><?php
								} else {
									?><tr style="height:40px;border-top:none;border-bottom:1px solid #eee;"><?php
								}
							} else {
								// No payments made or due yet, so highlighted row should be the first one.
								if ($payment['scheduled_date_expected'] == strtotime($this->entity->first_payment_date)) {
									?><tr class="ui-state-highlight" style="height:40px;"><?php
								} else {
									?><tr style="height:40px;border-top:none;border-bottom:1px solid #eee;"><?php
								}

							}

							?>
								<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
									<?php 
										switch ($payment['payment_type']) {
											case "past_due": 
												echo '<span style="color:#0a2aab;">Past Due</span>';
												break;
											case "none": 
												echo '<span style="color:#B30909;">Missed</span>';
												break;
											case "":
												echo '<span>Scheduled</span>';
												break;
											default:
												echo ucwords(htmlspecialchars($payment['payment_type']));
										}
									?>
								</td>
								<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;"><?php echo (isset($payment['payment_date_expected'])) ? htmlspecialchars(format_date($payment['payment_date_expected'], "date_short")) : htmlspecialchars(format_date($payment['scheduled_date_expected'], "date_short")); ?></td>
								<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
									<?php 
										if (isset($payment['payment_date_received'])) {
											if ($payment['extra_payments']) {
												echo htmlspecialchars(format_date($payment['payment_date_received'], "date_short"));
												foreach ($payment['extra_payments'] as $extra_payment) {
													echo "<br/>".htmlspecialchars(format_date($extra_payment['payment_date_received'], "date_short"));
												}
											} else
												echo htmlspecialchars(format_date($payment['payment_date_received'], "date_short"))."<br/>";
										} else
											echo "";
									?>
								</td>
								<td style="padding-left:7px; text-align:left;border-top:inherit;border-bottom:inherit;">
									<?php 
										switch (htmlspecialchars($payment['payment_status'])) {
											case "not due yet":
												echo '<span class="picon-view-calendar-day" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Not Due Yet</span>';
												break;
											case "paid":
												echo '<span class="picon-task-complete" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span>';
												break;
											case "paid_late":
												echo '<span class="picon-task-accepted" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Paid</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($payment['payment_days_late']); echo ($payment['payment_days_late'] > 1) ? " days late" : " day late"."</span>";
												break;
											case "partial_not_due":
												echo '<span class="picon-task-recurring" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span>';
												break;
											case "partial":
												echo '<span class="picon-task-attempt" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Partial Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($payment['payment_days_late']); echo ($payment['payment_days_late'] > 1) ? " days late" : " day late"."</span>";
												break;
											case "missed":
												echo '<span class="picon-task-reject" style="white-space:nowrap;text-align:right;display:inline-block;line-height:16px;padding-left:18px; background-repeat:no-repeat;">Missed Payment</span><br/><span style="display:inline-block;padding-left:18px;text-align:left">'.htmlspecialchars($payment['payment_days_late']); echo ($payment['payment_days_late'] > 1) ? " days late" : " day late"."</span>";
												break;
										}
									?>
								</td>
								<td style="text-align:right;border-top:inherit;border-bottom:inherit;">
									<?php 
										if (!empty($payment['extra_payments'])) {
											echo "$".htmlspecialchars($pines->com_sales->round($payment['payment_amount_paid_orig'], true));
											foreach ($payment['extra_payments'] as $extra_payment) {
												echo "<br/>$".htmlspecialchars($pines->com_sales->round(($extra_payment['payment_interest_paid'] + $extra_payment['payment_principal_paid'] + $extra_payment['payment_additional']), true));
											}
										} elseif ($payment['payment_status'] != "not due yet" && $payment['payment_short'] >= 0.01)
											echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_amount_paid'], true)).'<span style="padding-left:3px;">*</span>';
										else 
											echo "$".htmlspecialchars($pines->com_sales->round($payment['payment_amount_paid'], true));
									?>
								</td>
								<td style="padding-right:7px;border-top:inherit;border-bottom:inherit;">
									<?php 
										if (!empty($payment['extra_payments'])) {
											echo ($pines->com_sales->round($payment['payment_additional_orig']) >= 0.01) ? "$".htmlspecialchars($pines->com_sales->round($payment['payment_additional_orig'], true)) : "&nbsp;";
											foreach ($payment['extra_payments'] as $extra_payment) {
												echo ($pines->com_sales->round($extra_payment['payment_additional']) >= 0.01) ? "<br/>$".htmlspecialchars($pines->com_sales->round(($extra_payment['payment_additional']), true)) : "<br/>&nbsp;";
											}
										} elseif ($payment['payment_status'] != "not due yet" && ($payment['payment_principal_expected'] + $payment['payment_interest_expected']) < $pines->com_sales->round($payment['payment_amount_paid']))
											echo ($pines->com_sales->round($payment['payment_additional']) >= 0.01) ? '$'.htmlspecialchars($pines->com_sales->round($payment['payment_additional'], true)) : "&nbsp;";
									?>
								</td>
								<td style="padding-right:7px;border-top:inherit;border-bottom:inherit;">
									<?php 
										if (($payment['payment_interest_expected'] - $payment['payment_interest_paid']) >= 0.01 && $payment['payment_status'] != 'not due yet' && $payment['payment_status'] != 'partial_not_due') {
											// I need a tooltip to show unpaid interest and expected interest.
											$uniq2 = uniqid();
											?>
											<script type="text/javascript">
													// <![CDATA[
												pines(function(){
													// This is how to change the default settings for the entire page.
													//$.pnotify.defaults.pnotify_width = "400px";
													// If you don't want new lines ("\n") automatically converted to breaks ("<br />")
													//$.pnotify.defaults.pnotify_insert_brs = false;

													// This notice is used as a tooltip.
													p_muid_tooltip_<?php echo htmlspecialchars($uniq2); ?> = $.pnotify({
														pnotify_title: '<span style="color:#B30909">Unpaid Interest: <?php echo "$".htmlspecialchars($pines->com_sales->round($payment['payment_interest_unpaid'], true)); ?></span>',
														pnotify_text: '<?php echo '<span style="color:#520e0e;">Expected Interest: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_expected'], true)).'</span><br/>Interest Paid: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid'], true)); ?> </span><br/><span style="font-size:.8em;">Interest is calculated based on the terms of the loan at the time of payment.</span></span>',
														pnotify_hide: false,
														pnotify_closer: false,
														pnotify_history: false,
														pnotify_animate_speed: 100,
														pnotify_opacity: .9,
														pnotify_notice_icon: "picon picon-task-reject",
														// Setting stack to false causes Pines Notify to ignore this notice when positioning.
														pnotify_stack: false,
														pnotify_after_init: function(pnotify){
															// Remove the notice if the user mouses over it.
															pnotify.mouseout(function(){
																pnotify.pnotify_remove();
															});
														},
														pnotify_before_open: function(pnotify){
															// This prevents the notice from displaying when it's created.
															pnotify.pnotify({
																pnotify_before_open: null
															});
															return false;
														}
													});
												});
													// ]]>
												</script>
											<?php

											if (!empty($payment['extra_payments'])) {
												echo '<span style="cursor:pointer;color:#b30909" onmouseout="p_muid_tooltip_'.htmlspecialchars($uniq2).'.pnotify_remove();" onmousemove="p_muid_tooltip_'.htmlspecialchars($uniq2).'.css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseover="p_muid_tooltip_'.htmlspecialchars($uniq2).'.pnotify_display();">$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid_orig'], true)).'</span>';
												foreach ($payment['extra_payments'] as $extra_payment) {
													echo ($pines->com_sales->round($extra_payment['payment_interest_paid']) >= 0.01) ? "<br/>$".htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'], true)) : "<br/>-";
												}
											} else
												echo '<span style="cursor:pointer;color:#b30909" onmouseout="p_muid_tooltip_'.htmlspecialchars($uniq2).'.pnotify_remove();" onmousemove="p_muid_tooltip_'.htmlspecialchars($uniq2).'.css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseover="p_muid_tooltip_'.htmlspecialchars($uniq2).'.pnotify_display();">$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid'], true)).'</span>';
										} else {
											if (!empty($payment['extra_payments'])) {
												echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid_orig'], true)); 
												foreach ($payment['extra_payments'] as $extra_payment) {
													echo ($pines->com_sales->round($extra_payment['payment_interest_paid']) >= 0.01) ? "<br/>$".htmlspecialchars($pines->com_sales->round($extra_payment['payment_interest_paid'], true)) : "<br/>-";
												}
											} else
												echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_interest_paid'], true)); 
										}
									?>
								</td>
								<td style="padding-right:7px;border-top:inherit;border-bottom:inherit;">
									<?php 
										if (!empty($payment['extra_payments'])) {
											echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_principal_paid_orig'], true));
											foreach ($payment['extra_payments'] as $extra_payment) {
												echo "<br/>$".htmlspecialchars($pines->com_sales->round($extra_payment['payment_principal_paid'], true));
											}
										} else
											echo '$'.htmlspecialchars($pines->com_sales->round($payment['payment_principal_paid'], true));
									?>
								</td>

								<?php 
								if (!empty($this->entity->paid) || isset($this->entity->missed_first_payment)) {
								?>
									<td style="padding-right:7px;border-top:inherit;border-bottom:inherit;">
										<?php 
											if ($payment['payment_balance_unpaid'] >= 0.01 && $payment['payment_status'] != 'partial_not_due') {
												// showing tooltip to show unpaid balance specific to this payment. 
												// javascript to control tooltip:
												$uniq = uniqid();
												?>
												<script type="text/javascript">
													// <![CDATA[
												pines(function(){
													// This is how to change the default settings for the entire page.
													//$.pnotify.defaults.pnotify_width = "400px";
													// If you don't want new lines ("\n") automatically converted to breaks ("<br />")
													//$.pnotify.defaults.pnotify_insert_brs = false;

													// This notice is used as a tooltip.
													p_muid_tooltip_<?php echo htmlspecialchars($uniq); ?> = $.pnotify({
														pnotify_title: '<span style="color:#B30909">Unpaid Balance: <?php echo "$".htmlspecialchars($pines->com_sales->round($payment['payment_balance_unpaid'], true)); ?></span>',
														pnotify_text: '<?php echo '<span style="color:#520e0e;">Previous Remaining Balance: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($payment['remaining_balance'], true)).'</span><br/>Expected Balance: <span style="float:right;">$'.htmlspecialchars($pines->com_sales->round($payment['scheduled_balance'], true)); ?> </span><br/><span style="font-size:.8em;">Calculated unpaid balance based on the terms of the loan at the time of payment.</span></span>',
														pnotify_hide: false,
														pnotify_closer: false,
														pnotify_history: false,
														pnotify_animate_speed: 100,
														pnotify_opacity: .9,
														pnotify_notice_icon: "picon picon-task-reject",
														// Setting stack to false causes Pines Notify to ignore this notice when positioning.
														pnotify_stack: false,
														pnotify_after_init: function(pnotify){
															// Remove the notice if the user mouses over it.
															pnotify.mouseout(function(){
																pnotify.pnotify_remove();
															});
														},
														pnotify_before_open: function(pnotify){
															// This prevents the notice from displaying when it's created.
															pnotify.pnotify({
																pnotify_before_open: null
															});
															return false;
														}
													});
												});
													// ]]>
												</script>
												<?php
												echo '<span style="cursor:pointer;color:#b30909" onmouseout="p_muid_tooltip_'.htmlspecialchars($uniq).'.pnotify_remove();" onmousemove="p_muid_tooltip_'.htmlspecialchars($uniq).'.css({\'top\': event.clientY+12, \'left\': event.clientX+12});" onmouseover="p_muid_tooltip_'.htmlspecialchars($uniq).'.pnotify_display();">$'.htmlspecialchars($pines->com_sales->round($payment['remaining_balance'], true))."</span>"; 
											} elseif (isset($payment['remaining_balance'])) {
												echo '$'.htmlspecialchars($pines->com_sales->round($payment['remaining_balance'], true));
											} else {
												echo "";
											}
										?>
									</td>
									<td style="padding-right:7px;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($payment['scheduled_balance'], true)); ?></td>	
									<?php
								} else {
								?>
									<td style="padding-right:7px;border-top:inherit;border-bottom:inherit;"><?php echo ""; ?></td>
									<td style="padding-right:7px;border-top:inherit;border-bottom:inherit;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($payment['scheduled_balance'], true)); ?></td>
								<?php } ?>
								</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<div class="pf-element pf-heading">
					<h1>Amortization Schedule</h1>
				</div>
				<div class="pf-element pf-full-width">
					<table cellspacing="0" cellpadding="0" border="0" style="border:1px solid #ccc; width:100%;text-align:right;">
						<tbody>
						<tr class="ui-widget-header" style="text-align:center;border:0;color:#454545;">
							<td style="border-bottom:1px solid #b0b0b0;">Payment Due Date</td>
							<td style="border-bottom:1px solid #b0b0b0;">Payment</td>
							<td style="border-bottom:1px solid #b0b0b0;">Interest Payment</td>
							<td style="border-bottom:1px solid #b0b0b0;">Principal Payment</td>
							<td style="border-bottom:1px solid #b0b0b0;">Balance</td>
						</tr>
						<tr>
							<td style="border-bottom:1px solid #eee;padding-right:7px;" colspan="5"><span style="float:left; padding-left:7px;">Principal Balance:</span><?php echo '$'.htmlspecialchars($pines->com_sales->round($this->entity->principal, true)); ?></td>
						</tr>
						<?php
						foreach ($this->entity->schedule as $schedule) {
						?>
							<tr>
								<td style="border-bottom:1px solid #eee;padding-left:7px; text-align:left;"><?php echo htmlspecialchars(format_date($schedule['scheduled_date_expected'], "date_short")); ?></td>
								<td style="border-bottom:1px solid #eee;padding-right:7px;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['payment_amount_expected'], true)); ?></td>
								<td style="border-bottom:1px solid #eee;padding-right:7px;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['payment_interest_expected'], true)); ?></td>
								<td style="border-bottom:1px solid #eee;padding-right:7px;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['payment_principal_expected'], true)); ?></td>
								<td style="border-bottom:1px solid #eee;padding-right:7px;"><?php echo '$'.htmlspecialchars($pines->com_sales->round($schedule['scheduled_balance'], true)); ?></td>
							</tr>
						<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
</div>
	
	