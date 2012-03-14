<?php
/**
 * Lists loans and provides functions to manipulate them.
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
$this->title = 'Loans';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_loan/list_loans']);

?>
<script type="text/javascript">
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_loan/newloan')) { ?>
//				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true},
//				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true},
//				{type: 'separator'},
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_loan', 'loan/edit')); ?>},
				<?php } if (gatekeeper('com_loan/editloan')) { ?>
				{type: 'button', text: 'Overview', extra_class: 'picon picon-graphics-viewer-document', url: <?php echo json_encode(pines_url('com_loan', 'loan/overview', array('id' => '__title__'))); ?>},
				{type: 'separator'},
				<?php } if (gatekeeper('com_loan/makepayment')) { ?>
				{type: 'button', text: 'Make Payment', extra_class: 'picon picon-wallet-open', click: function(e, row){
					loan_grid.makepayment_form($(row).attr("title"));
				}},
				<?php } if (gatekeeper('com_loan/editloan')) {
							if (gatekeeper('com_loan/editpayments')) {
							?>
							{type: 'button', text: 'Edit Payments', extra_class: 'picon picon-accessories-calculator', url: <?php echo json_encode(pines_url('com_loan', 'loan/editpayments', array('id' => '__title__'))); ?>},
							<?php
							}
				?>
				{type: 'separator'},
//				{type: 'button', text: 'Edit Terms', extra_class: 'picon picon-document-edit-verify'},
//				{type: 'button', text: 'Adjustment', extra_class: 'picon picon-edit-text-frame-update'},
//				{type: 'button', text: 'Penalties', extra_class: 'picon picon-dialog-warning'},
//				{type: 'separator'},
				<?php
					if (gatekeeper('com_loan/payoffloan')) {
				?>
				{type: 'button', text: 'Pay Off', extra_class: 'picon picon-wallet-open', click: function(e, row){
					loan_grid.payoff_form($(row).attr("title"));
				}},
				<?php }
					if (gatekeeper('com_loan/writeoffloan')) {
				?>
//				{type: 'button', text: 'Write Off', extra_class: 'picon picon-document-close', click: function(e, row){
//					loan_grid.write_off($(row).attr("title"));
//				}},
				<?php
					}
					if (gatekeeper('com_loan/cancelloan')) {
				?>
				{type: 'button', text: 'Cancel', extra_class: 'picon picon-dialog-cancel', click: function(e, row){
					loan_grid.cancel_loan($(row).attr("title"));
				}},
				<?php
					}
				} ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_loan/deleteloan')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_loan', 'loan/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'loans',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_loan/list_loans", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var loan_grid = $("#p_muid_grid").pgrid(cur_options);


//		loan_grid.write_off = function(loan_id){
//			if (!confirm("You are about to write off this loan as noncollectable and accept all unpaid amount as a non-taxable loss. \n\nAre you sure you want to do this? ")) {
//				e.preventDefault();
//				return false;
//			}
//		};

		loan_grid.cancel_loan = function(loan_id){
			if (!confirm("You are about to cancel this loan, which can't be undone. \n\nAre you sure you want to do this? ")) {
				return false;
			} else {
				pines.post(<?php echo json_encode(pines_url('com_loan', 'loan/cancelloan')); ?>, {
					"id": loan_id
				});
			}

		};


		loan_grid.makepayment_form = function(loan_id){
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
								var page = 'makepayment';
								var payment_amount = form.find(":input[name=payment_amount]").val();
								var payment_date_input = form.find(":input[name=payment_date_input]").val();
								if (payment_amount == "") {
									alert('Please specify the payment amount.');
								} else if (payment_amount < 0) {
									alert('Please specify a valid payment amount.');
								} else if (payment_date_input == "") {
									alert('Please specify a date for receiving the payment.');
								} else {
									form.dialog('close');
									// Submit the request status change.
									pines.post(<?php echo json_encode(pines_url('com_loan', 'loan/makepayment')); ?>, {
										"loan_id": loan_id,
										"page": page,
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
		};


		loan_grid.payoff_form = function(loan_id){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'forms/payoff')); ?>,
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
					var form = $("<div title=\"Pay off Loan\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: 425,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Pay Off Loan": function(){
								var page = 'makepayment';
								var payment_amount = form.find(":input[name=payment_amount]").val();
								var payment_date_input = form.find(":input[name=payment_date_input]").val();
								if (payment_amount == "") {
									alert('Please specify the payment amount.');
								} else if (payment_amount < 0) {
									alert('Please specify a valid payment amount.');
								} else if (payment_date_input == "") {
									alert('Please specify a date for receiving the payment.');
								} else {
									form.dialog('close');
									// Submit the request status change.
									pines.post(<?php echo json_encode(pines_url('com_loan', 'loan/makepayment')); ?>, {
										"loan_id": loan_id,
										"page": page,
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
		};
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Customer</th>
			<th>Employee</th>
			<th>Location</th>
			<th>Creation Date</th>
			<th>Status</th>
			<th>Principal</th>
			<th>APR</th>
			<th>Term</th>
			<th>Balance</th>
			<th>Payment</th>
			<th>Current Past Due</th>
			<th>Total Payments Made</th>
			<th>Total Principal Paid</th>
			<th>Total Interest Paid</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->loans as $loan) { ?>
		<tr title="<?php echo (int) $loan->guid ?>">
			<td><?php echo htmlspecialchars($loan->id); ?></td>
			<td><?php echo htmlspecialchars($loan->customer->name); ?></td>
			<td><?php echo htmlspecialchars($loan->user->name); ?></td>
			<td><?php echo htmlspecialchars($loan->user->group->name); ?></td>
			<td><?php echo htmlspecialchars(format_date($loan->creation_date, "date_short")); ?></td>
			<td><?php echo htmlspecialchars(ucwords($loan->status)); ?></td>
			<td style="text-align:right;"><?php echo "$".htmlspecialchars($pines->com_sales->round($loan->principal, true)); ?></td>
			<td style="text-align:right;"><?php echo htmlspecialchars($loan->apr)."%"; ?></td>
			<td><?php echo htmlspecialchars($loan->term." ".$loan->term_type); ?></td>
			<td style="text-align:right;"><?php echo !isset($loan->remaining_balance) ? "$".htmlspecialchars($pines->com_sales->round($loan->principal, true)): '$'.htmlspecialchars($pines->com_sales->round($loan->remaining_balance, true)); ?></td>
			<td style="text-align:right;"><?php echo "$".htmlspecialchars($pines->com_sales->round($loan->frequency_payment, true)); ?></td>
			<td style="text-align:right;"><?php echo ($loan->payments[0]['past_due'] < .01) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round($loan->payments[0]['past_due'], true)); ?></td>
			<td style="text-align:right;"><?php echo empty($loan->payments[0]['total_interest_paid']) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round(($loan->payments[0]['total_principal_paid'] + $loan->payments[0]['total_interest_paid']), true)); ?></td>
			<td style="text-align:right;"><?php echo empty($loan->payments[0]['total_principal_paid']) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round($loan->payments[0]['total_principal_paid'], true)); ?></td>
			<td style="text-align:right;"><?php echo empty($loan->payments[0]['total_interest_paid']) ? "$0.00" : '$'.htmlspecialchars($pines->com_sales->round($loan->payments[0]['total_interest_paid'], true)); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>