<?php
/**
 * Display a form to make payments on loans.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Make Payments on Loan(s)';
$pines->icons->load();

$loan_ids = $this->loan_ids;
if (!is_array($loan_ids))
	$loan_ids = array($loan_ids); // Makes an array out of the string guid.
?>

<style type="text/css">
	#p_muid_form {
		max-height: 400px;
	}
	#p_muid_form .vertical-middle {
		vertical-align: middle;
	}
	#p_muid_form .text-right {
		text-align: right;
	}
	#p_muid_form .text-center {
		text-align: center;
	}
	#p_muid_form .entity-link a {
		text-transform: uppercase;
		font-weight:bold;
		text-decoration: underline;
		color: #379DBB;
		text-shadow: 1px 1px 0 #fff;
	}
	#p_muid_form .accurate-checkbox {
		vertical-align: top;
	}
	#p_muid_form .payment-input {
		width: 80px;
		font-weight: bold;
	}
	#p_muid_form .payment-input.text-error {
		color: #B94A48;
	}
	#p_muid_form .payment-date-label {
		font-weight:bold;
		text-transform: uppercase;
	}
	#p_muid_form .payment-date {
		width: 100px;
		position: static;
	}
	#p_muid_form .payment-status {
		background: #ddd;
		padding: 4px;
		font-size: 1.1em;
		border-radius: 4px;
	}
	#p_muid_form .payment-process {
		width: 94px;
	}
	#p_muid_form tr.payment-row, #p_muid_form tr.action-row {
		background-color: #fff;
	}
	#p_muid_form tr.action-row .add-on {
		cursor: pointer;
	}
	#p_muid_form tbody tr:nth-of-type(4n+1), #p_muid_form tbody tr:nth-of-type(4n+2)  {
		background-color:#eee;
	}
	#p_muid_form tbody tr:nth-of-type(4n+2) td {
		border-top-width: 0;
		padding-bottom: 10px;
	}
	#p_muid_form tbody tr:nth-of-type(4n+4) td {
		border-top-width: 0;
		padding-bottom: 10px;
	}
	#p_muid_form tr.action-row {
		margin-bottom: 3px;
	}
</style>
<script type="text/javascript">
	pines(function(){
		// Vars
		var form = $('#p_muid_form');
		var date_inputs = form.find('[name=date_received]');
		var date_arrows = form.find('.action-row .add-on');
		var process_buttons = form.find('.payment-process');
		var nextdue_tooltips = form.find('.nextdue-tooltip');
		var pastdue_tooltips = form.find('.pastdue-tooltip');
		var payment_inputs = form.find('[name=payment]');
		var process_all = form.find('.process-all');
		var accurate_checkbox = form.find('.accurate-checkbox');
		var process_payment = function(amount, date, button, amount_input, date_input) {
			var status = button.closest('tr').find('.payment-status');
			var id = button.attr('date-id');
			amount_input.add(date_input).attr('disabled', 'disabled');
			
			// Do stuff
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'loan/makepayment')); ?>,
				type: "GET",
				dataType: "json",
				data: {'id': id, 'payment_amount': amount, 'payment_date_input': date, 'type': 'ajax'},
				beforeSend: function(){
					button.text('...');
					status.removeClass('text-success text-error').addClass('text-info');
					status.html('<i class="icon-spin icon-spinner"></i> Saving...');
					status.show();
				},
				error: function(){
					button.text('Error').toggleClass('btn-danger btn-info disabled');
					status.removeClass('text-success text-info').addClass('text-error');
					status.html('<i class="icon-remove"></i> Connect Error');
					status.show();
					return;
				},
				success: function(data){
					if (data.failed) {
						button.text('Failed').toggleClass('btn-danger btn-info disabled');
						status.removeClass('text-success text-info').addClass('text-error');
						status.html('<i class="icon-remove"></i> Failed');
						status.show();
						return;
					}
					if (data.no_loan) {
						button.text('Error').toggleClass('btn-danger btn-info disabled');
						status.removeClass('text-success text-info').addClass('text-error');
						status.html('<i class="icon-remove"></i> No Loan Found');
						status.show();
						return;
					}
					if (data.error) {
						button.text('Error').toggleClass('btn-danger btn-info disabled');
						status.removeClass('text-success text-info').addClass('text-error');
						status.html('<i class="icon-remove"></i> Error');
						status.show();
						return;
					}
					if (data.success) {
						button.text('Processed').toggleClass('btn-info btn-success disabled');
						status.removeClass('text-error text-info').addClass('text-success');
						status.html('<i class="icon-ok"></i> Saved!');
						status.show();
						return;
					}
				}
			});
		};
		process_buttons.click(function(){
			var button = $(this);
			var action_row = button.closest('tr.action-row');
			var payment_row = action_row.prev('tr.payment-row');
			var status = action_row.find('.payment-status');
			var amount_input = payment_row.find('[name=payment]');
			var date_input = action_row.find('[name=date_received]');
			
			// If already processed, return.
			if (button.hasClass('disabled'))
				return;
			// Get Payment
			var amount = amount_input.val();
			// Get Date
			var date = date_input.val();
			
			// Missing Amount
			if (!amount.length)
				status.removeClass('text-info text-success').addClass('text-error').html('<i class="icon-remove"></i> Missing Amount');
			// Missing Date
			if (!date.length)
				status.removeClass('text-info text-success').addClass('text-error').html('<i class="icon-remove"></i> Missing Date');
			
			if (!amount.length || !date.length) {
				status.show();
				return;
			}
			// Send date:
			process_payment(amount, date, button, amount_input, date_input);
		});
		
		form.find('[tabindex=1]').select();
		// jQuery Functions
		date_inputs.datepicker({
			changeMonth: true,
			changeYear: true,
			yearRange: '-5:+5',
			dateFormat: 'yy-mm-dd',
			maxDate: '+1d'
		}).click(function(){
			$(this).datepicker('show');
		});
		payment_inputs.keyup(function(){
			this.value=this.value.replace(/^(?:.*?(\d{1,5})(\.\d{0,2})?.*|.*)/gi, '$1$2');
		});
		// Tooltip Dates
		$.timeago.settings.allowFuture = true;
		nextdue_tooltips.each(function(){
			var tooltip = $(this);
			var time_ago = $.timeago(tooltip.attr('data-due'));
			var due_date = tooltip.attr('data-date');
			var title = 'Due '+time_ago+' on '+due_date;
			tooltip.attr('title', title);
			tooltip.tooltip({
				html: true
			});
		});
		pastdue_tooltips.each(function(){
			var tooltip = $(this);
			var time_ago = $.timeago(tooltip.attr('data-due'));
			var due_date = tooltip.attr('data-date');
			var title = 'Due '+time_ago+' on '+due_date;
			tooltip.attr('title', title);
			tooltip.tooltip({
				html: true
			});
		});
		date_arrows.click(function(){
			var this_arrow = $(this);
			// Get this date input and all ones after it
			var this_action_row = this_arrow.closest('.action-row');
			var this_value = this_action_row.find('[name=date_received]').val();;
			var action_rows = this_action_row.nextAll('.action-row');
			// and copy the value from this one to those ones.
			action_rows.each(function(){
				$(this).find('[name=date_received]').val(this_value);
			});
		});
		accurate_checkbox.click(function(){
			if (!accurate_checkbox.is(':checked'))
				process_all.removeClass('btn-success').addClass('btn-info');
			else {
				process_all.addClass('btn-success').removeClass('btn-info');
				$(this).closest('p').removeClass('text-error');
			}
		});
		process_all.click(function(){
			if (process_all.hasClass('btn-success')) {
				process_buttons.click();
			} else {
				accurate_checkbox.closest('p').addClass('text-error');
			}
		});
	});
</script>
<div id="p_muid_form">
	<table class="table table-condensed" style="margin: 10px 0 0 0; font-size: .9em;">
		<thead>
			<th>ID</th>
			<th>Customer</th>
			<th>Next Due</th>
			<th>Past Due</th>
			<th>Payment</th>
		</thead>
		<tbody>
			<?php 
			$c = 1;
			foreach ($loan_ids as $cur_id) {
			$cur_loan = $pines->com_loan->get_payment_amounts($cur_id);
			$tag_status = $cur_loan->get_loan_status(true);
			$display_status = $cur_loan->get_loan_status();
			if ($cur_loan->temp_paid_off || $tag_status != 'active') { ?>
			<tr class="payment-row <?php echo ($tag_status == 'paidoff' || $cur_loan->temp_paid_off) ? 'success' : 'warning'; ?>">
				<td class="vertical-middle entity-link"><a data-entity="<?php echo htmlspecialchars($cur_loan->guid); ?>" data-entity-context="com_loan_loan"><?php echo $cur_loan->id; ?></a></td>
				<td class="vertical-middle entity-link"><a data-entity="<?php echo htmlspecialchars($cur_loan->customer->guid); ?>" data-entity-context="com_customer_customer"><?php echo htmlspecialchars($cur_loan->customer->name); ?></a></td>
				<?php if ($tag_status == 'paidoff' || $cur_loan->temp_paid_off) { ?>
				<td colspan="3" class="vertical-middle text-right text-success" style="font-weight:bold;">Paid Off!</td>
				<?php } else { ?>
				<td colspan="3" class="vertical-middle text-right text-error" style="font-weight:bold;"><?php echo $display_status; ?></td>
				<?php } ?>
			</tr>
			<tr class="action-row <?php echo ($tag_status == 'paidoff' || $cur_loan->temp_paid_off) ? 'success' : 'warning'; ?>">
				<td colspan="3" class="vertical-middle"><span class="payment-date-label">Last Payment Made</span></td>
				<td colspan="2" class="vertical-middle text-right"><?php echo (!empty($cur_loan->paid)) ? htmlspecialchars(format_date($cur_loan->payments[0]['last_payment_made'], 'date_sort')) : 'None Made';?></td>
			</tr>
			<?php } else { ?>
			<tr class="payment-row <?php echo ($cur_loan->temp_past_due > 0) ? 'text-error' : ''; ?>">
				<td class="vertical-middle entity-link"><a data-entity="<?php echo htmlspecialchars($cur_loan->guid); ?>" data-entity-context="com_loan_loan"><?php echo $cur_loan->id; ?></a></td>
				<td class="vertical-middle entity-link"><a data-entity="<?php echo htmlspecialchars($cur_loan->customer->guid); ?>" data-entity-context="com_customer_customer"><?php echo htmlspecialchars($cur_loan->customer->name); ?></a></td>
				<td class="vertical-middle"><span class="nextdue-tooltip" data-due="<?php echo (date("c",$cur_loan->temp_next_due_date)); ?>" data-date="<?php echo (format_date($cur_loan->temp_next_due_date, 'date_short')); ?>"><?php echo htmlspecialchars('$'.number_format($cur_loan->temp_next_due, 2, '.', '')); ?></span></td>
				<td class="vertical-middle"><span class="<?php echo ($cur_loan->temp_past_due > 0) ? 'pastdue-tooltip': ''; ?>" data-due="<?php echo (date("c", $cur_loan->temp_past_due_date));?>" data-date="<?php echo (format_date($cur_loan->temp_past_due_date, 'date_short')); ?>"><?php echo htmlspecialchars('$'.number_format($cur_loan->temp_past_due, 2, '.', '')); ?></span></td>
				<td class="vertical-middle text-right">
					<input class="payment-input  text-right <?php echo ($cur_loan->temp_past_due > 0) ? 'text-error' : ''; ?>" type="text" tabindex="<?php echo $c+1; ?>" name="payment" value="<?php echo htmlspecialchars('$'.number_format($cur_loan->temp_next_due + $cur_loan->temp_past_due, 2, '.', '')); ?>" placeholder="Amount"/>
				</td>
			</tr>
			<tr class="action-row">
				<td colspan="1" class="vertical-middle"></td>
				<td colspan="1" class="vertical-middle">
					<div class="control-group">
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on"><i class="icon-circle-arrow-down"></i></span>
								<input tabindex="<?php echo $c; ?>" type="text" class="payment-date" name="date_received" placeholder="Receive Date"/>
							</div>
						</div>
					</div>
					
				</td>
				<td colspan="2" class="vertical-middle text-center"><span class="payment-status text-success hide"><i class="icon-ok"></i> Saved!</span></td>
				<td colspan="1" class="text-right"><button tabindex="<?php echo $c+2; ?>" class="btn btn-info payment-process" type="text" date-id="<?php echo htmlspecialchars($cur_loan->guid); ?>">Process</button></td>
			</tr>
			<?php } $c += 3; } ?>
		</tbody>
	</table>
	<?php if ($c > 4) { ?>
	<hr/>
	<p class="text-center"><input type="checkbox" class="accurate-checkbox" name="accurate" value="on"/> <small>I will only press this button when all of these values are correct and accurate.</small></p>
	<button type="button" class="btn btn-info btn-block btn-large process-all">Process All</button><hr/>
	<?php } ?>
</div>