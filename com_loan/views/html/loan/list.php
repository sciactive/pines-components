<?php
/**
 * Lists loans and provides functions to manipulate them.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Loans';
$pines->com_pgrid->load();
$pines->com_jstree->load();
$pines->com_timeago->load();
$google_drive = false;
if (isset($pines->com_googledrive)) {
    $pines->com_googledrive->export_to_drive('csv');
    $google_drive = true;
} else {
    pines_log("Google Drive is not installed", 'notice');
}

if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_loan/loan/list']);
?>
<script type="text/javascript">
	pines(function(){
		// Time and location variables.
		var all_time = true, start_date = "", end_date = "", location = "", descendants = false;
		pines.status_tags = "<?php echo (isset($this->pgrid_state->status_tags)) ? implode(',',$this->pgrid_state->status_tags) : ''; ?>";
		var customer_search_box;
		var search_loan = <?php echo json_encode($_REQUEST['id']); ?>;
		
		var width;
		$(window).on('resize', function(){
			var thewindow = $(this);
			var window_width = thewindow.width();
			if (window_width < 500)
				width = window_width - 10;
			else
				width = 500;
			
			var leftoffset = (window_width - width) / 2;
			
			if ($('.ui-dialog:visible').length) {
				$('.ui-dialog:visible').css({
					'width': width,
					'left': leftoffset
				});
			}
		}).resize();
		
		var submit_search = function(){
			var search_string = customer_search_box.val();
			if (search_string == "") {
				alert("Please enter a search string.");
				return;
			}
			var loader;
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'loan/search')); ?>,
				type: "POST",
				dataType: "json",
				data: {"q": search_string, "status_tags" : pines.status_tags, "location": location, "descendants": descendants, "all_time": all_time, "start_date": start_date, "end_date": end_date},
				beforeSend: function(){
					loader = $.pnotify({
						title: 'Search',
						text: 'Searching the database...',
						icon: 'picon picon-throbber',
						nonblock: true,
						hide: false,
						history: false
					});
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						loan_grid.pgrid_get_all_rows().pgrid_delete();
						alert("No customers were found that matched the query.");
						return;
					}
					var struct = [];
					$.each(data, function(){
						struct.push({
							"key": this.guid,
							"values": [
								'<a data-entity="'+pines.safe(this.guid)+'" data-entity-context="com_loan_loan">'+pines.safe(this.id)+'</a>',
								'<a data-entity="'+pines.safe(this.customer_guid)+'" data-entity-context="com_customer_customer">'+pines.safe(this.customer_name)+'</a>',
								'<a data-entity="'+pines.safe(this.employee_guid)+'" data-entity-context="com_hrm_employee">'+pines.safe(this.employee)+'</a>',
								pines.safe(this.location),
								pines.safe(this.creation_date),
								pines.safe(this.collection_code),
								pines.safe(this.status),
								pines.safe(this.principal),
								pines.safe(this.apr),
								pines.safe(this.term),
								pines.safe(this.balance),
								pines.safe(this.payment),
								pines.safe(this.current_past_due),
								pines.safe(this.total_payments_made),
								pines.safe(this.total_principal_paid),
								pines.safe(this.total_interest_paid)
							]
						});
					});
					loan_grid.pgrid_get_all_rows().pgrid_delete();
					loan_grid.pgrid_add(struct);
				}
			});
		};
		
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'text', load: function(textbox){
					// Display the current customer being searched for.
					textbox.keydown(function(e){
						if (e.keyCode == 13)
							submit_search();
					});
					customer_search_box = textbox;
					<?php if (!empty($_REQUEST['id'])) { ?>
					customer_search_box.val('loan:'+<?php echo json_encode($_REQUEST['id']); ?>);
					submit_search();
					<?php } ?>
					<?php if (!empty($this->show)) { ?>
					customer_search_box.val(<?php echo json_encode((string) $this->show); ?>);
					submit_search();
					<?php } ?>
				}},
				{type: 'button', extra_class: 'picon picon-system-search', selection_optional: true, pass_csv_with_headers: true, click: submit_search},
				{type: 'separator'},
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){loan_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){loan_grid.date_form();}},
				<?php if (gatekeeper('com_loan/viewarchived')) { ?>
				{type: 'button', title: 'Loan Status', extra_class: 'picon picon-view-filter', selection_optional: true, click: function(){loan_grid.search_status_form();}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_loan/newloan')) { ?>
//				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true},
//				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true},
//				{type: 'separator'},
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_loan', 'loan/edit')); ?>},
				<?php } if (gatekeeper('com_loan/editloan')) { ?>
				{type: 'button', text: 'Overview', extra_class: 'picon picon-graphics-viewer-document', url: <?php echo json_encode(pines_url('com_loan', 'loan/overview', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_loan/changecollectioncode')) { ?>
				{type: 'button', title: 'Collection Code', text: 'Code', extra_class: 'picon picon-dialog-information', click: function(e, row){
					loan_grid.collection_code_form($(row).attr("title"));
				}},
				<?php } if (gatekeeper('com_loan/changestatus')) { ?>
				{type: 'button', text: 'Status', title: 'Change Status of Loan', extra_class: 'picon picon-flag-green', multi_select: true, click: function(e, rows){
					loan_grid.loan_status_form(rows);
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_loan/makepayment')) { ?>
				{type: 'button', text: 'Make Payments', Title: 'Make Payments on Loans', extra_class: 'picon picon-wallet-open', multi_select: true, click: function(e, rows){
					loan_grid.makepayments_form(rows);
				}},
				<?php } if (gatekeeper('com_customer/viewhistory')) { ?>
				{type: 'button', text: 'Customer History', title: 'Customer History', extra_class: 'picon picon-view-history', multi_select: true, click: function(e, rows){
						loan_grid.custhistory_form(rows);
				}},
				<?php } if (gatekeeper('com_customer/newinteraction')) { ?>
				{type: 'button', text: 'Add Interaction', title: 'Add Interaction', extra_class: 'picon picon-user-group-new', multi_select: true, click: function(e, rows){
						loan_grid.interaction_form(rows);
				}},
				<?php } if (gatekeeper('com_customer/newinteraction') || gatekeeper('com_customer/viewhistory')) { ?>
				{type: 'separator'},
				<?php } if (gatekeeper('com_loan/editloan')) {
					if (gatekeeper('com_loan/editpayments')) { ?>
				{type: 'button', text: 'Edit Payments', extra_class: 'picon picon-accessories-calculator', url: <?php echo json_encode(pines_url('com_loan', 'loan/editpayments', array('id' => '__title__'))); ?>},
				<?php } ?>
				{type: 'separator'},
//				{type: 'button', text: 'Edit Terms', extra_class: 'picon picon-document-edit-verify'},
//				{type: 'button', text: 'Adjustment', extra_class: 'picon picon-edit-text-frame-update'},
//				{type: 'button', text: 'Penalties', extra_class: 'picon picon-dialog-warning'},
//				{type: 'separator'},
				<?php if (gatekeeper('com_loan/payoffloan')) { ?>
				{type: 'button', text: 'Pay Off', extra_class: 'picon picon-wallet-open', click: function(e, row){
					loan_grid.payoff_form($(row).attr("title"));
				}},
				<?php } } ?>
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
						filename: 'applications',
						content: rows
					});
				}},
				<?php if ($google_drive && !empty($pines->config->com_googledrive->client_id)) { 
						// Need to check if Google Drive is installed ?>
				{type: 'button', title: 'Export to Google Drive', extra_class: 'picon drive-icon', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					// First need to set the rows to which we want to export
					setRows(rows);
					// Then we have to check if we have permission to post to user's google drive
					checkAuth();
				}},
				<?php } elseif ($google_drive && empty($pines->config->com_googledrive->client_id)) { ?>
				{type: 'button', title: 'Export to Google Drive', extra_class: 'picon drive-icon', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					// They have com_googledrive installed but didn't set the client id, so alert them on click
					alert('You need to set the CLIENT ID before you can export to Google Drive');
				}},
				<?php } ?>
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				var tags = pines.status_tags.split(',');
				state.status_tags = tags;
				cur_state = JSON.stringify(state);
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_loan/loan/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var loan_grid = $("#p_loan_grid").pgrid(cur_options);
		loan_grid.pgrid_get_all_rows().pgrid_delete();
		cur_options.pgrid_state_change(loan_grid.pgrid_export_state());


		loan_grid.date_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'forms/dateselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the date form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Date Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						width: width,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Update": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
								submit_search();
							}
						}
					});
					pines.play();
				}
			});
		};
		loan_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'forms/locationselect')); ?>,
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendants": descendants},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the location form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Location Selector\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						width: width,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Update": function(){
								location = form.find(":input[name=location]").val();
								descendants = !!form.find(":input[name=descendants]").attr('checked');
								form.dialog('close');
								submit_search();
							}
						}
					});
					pines.play();
				}
			});
		};
		
		loan_grid.search_status_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'forms/searchstatus')); ?>,
				type: "POST",
				dataType: "html",
				data: {'cur_state': cur_state},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the loan status form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Search Loan Status\"></div>").html(data+"<br />").dialog({
						bgiframe: true,
						autoOpen: true,
						width: width,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Update": function(){
								pines.status_tags = form.find(":input[name=status_tags]").val(); // is a string separated by commas
								if (pines.status_tags != '')
									var tags = pines.status_tags.split(','); // make it into an array
								else
									var tags = ''; // make it an empty string
								cur_state = JSON.parse(cur_state); // read json as object
								cur_state.status_tags = tags; // add status tags
								cur_state = JSON.stringify(cur_state); // back to json string
								$.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_loan/loan/list", state: cur_state})
								form.dialog('close');
								submit_search();
							}
						}
					});
					pines.play();
				}
			});
		};

		loan_grid.makepayments_form = function(rows){
			if (rows.length > 1) {
				var row_ids = new Array();
				rows.each(function(){
					row_ids.push($(this).attr("title"));
				});
			} else {
				var row_ids = rows.attr("title");
			}
			var payments_limit = <?php echo $pines->config->com_loan->payments_limit; ?>;
			if (rows.length > payments_limit) {
				alert('You do not have permission to make more than '+pines.safe(payments_limit)+' different customer payments, therefore you cannot make payments for the selected '+rows.length+' customers.');
			} else {
				$.ajax({
					url: <?php echo json_encode(pines_url('com_loan', 'forms/makepayments')); ?>,
					type: "POST",
					dataType: "html",
					data: {"ids": row_ids},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retrieve the make payments form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (data == "") {
							return;
						}
						pines.pause();
						var form = $("<div title=\"Make Payment(s)\"></div>").html(data+"<br />");
						form.dialog({
							bgiframe: true,
							autoOpen: true,
							width: width,
							modal: true,
							close: function(){
								form.remove();
							},
							buttons: {
								"Done": function() {
									$(this).dialog("close");
								}
							}
						});
						pines.play();
					}
				});
			}
		};
		
		loan_grid.collection_code_form = function(loan_id){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_loan', 'forms/collection_code')); ?>,
				type: "POST",
				dataType: "html",
				data: {"id": loan_id},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the collection code form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "")
						return;
					pines.pause();
					var form = $("<div title=\"Change Collection Code\"></div>").html(data+"<br />");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						width: width,
						modal: true,
						close: function(){
							form.remove();
						},
						buttons: {
							"Close": function(){
								form.dialog('close');
							}
						}
					});
					pines.play();
				}
			});
		};
		
		loan_grid.loan_status_form = function(rows){
			if (rows.length > 1) {
				var row_ids = new Array();
				rows.each(function(){
					row_ids.push($(this).attr("title"));
				});
			} else {
				var row_ids = rows.attr("title");
			}
			var status_limit = <?php echo $pines->config->com_loan->status_limit; ?>;
			if (rows.length > status_limit) {
				alert('You do not have permission to change loan status on more than '+pines.safe(status_limit)+' different customers, therefore you cannot change the status for the selected '+rows.length+' customers.');
			} else {
				$.ajax({
					url: <?php echo json_encode(pines_url('com_loan', 'forms/loan_status')); ?>,
					type: "POST",
					dataType: "html",
					data: {"ids": row_ids},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retrieve the loan status form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (data == "") {
							return;
						}
						pines.pause();
						var form = $("<div title=\"Change status of Loan(s)\"></div>").html(data+"<br />");
						form.dialog({
							bgiframe: true,
							autoOpen: true,
							width: width,
							modal: true,
							close: function(){
								form.remove();
							},
							buttons: {
								"Done": function() {
									$(this).dialog("close");
								}
							}
						});
						pines.play();
					}
				});
			}
		};
		
		loan_grid.custhistory_form = function(rows){
			if (rows.length > 1) {
				var row_ids = new Array();
				rows.each(function(){
					row_ids.push($(this).attr("title"));
				});
			} else {
				var row_ids = rows.attr("title");
			}
			var hist_limit = <?php echo $pines->config->com_loan->cust_hist_limit; ?>;
			if (rows.length > hist_limit) {
				alert('You do not have permission to view more than '+pines.safe(hist_limit)+' different customer histories, therefore you cannot view the history for the selected '+rows.length+' customers.');
			} else {
				$.ajax({
					url: <?php echo json_encode(pines_url('com_loan', 'forms/cust_history')); ?>,
					type: "POST",
					dataType: "html",
					data: {"ids": row_ids},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retrieve the customer history dialog:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (data == "") {
							return;
						}
						pines.pause();
						var form = $("<div title=\"View Customer History\"></div>").html(data+"<br />");
						form.dialog({
							bgiframe: true,
							autoOpen: true,
							width: width,
							modal: true,
							close: function(){
								form.remove();
							},
							buttons: {
								"Done": function() {
									$(this).dialog("close");
								}
							}
						});
						if (rows.length > 1)
							form.find(".history_status").html('<div class="alert alert-info" style="margin-bottom: 8px;"><i class="icon-info-sign"></i> You are viewing '+rows.length+' customers.</div>');
						
						pines.play();
					}
				});
			}
		};

		loan_grid.interaction_form = function(rows){
			if (rows.length > 1) {
				var row_ids = new Array();
				rows.each(function(){
					row_ids.push($(this).attr("title"));
				});
			} else {
				var row_ids = rows.attr("title");
			}
			
			var limit = <?php echo $pines->config->com_loan->add_interaction_limit; ?>;
			if (rows.length > limit) {
				alert('You do not have permission to add more than '+pines.safe(limit)+' interactions, therefore you cannot add interactions to the selected '+rows.length+' customers.');
			} else {
				$.ajax({
					url: <?php echo json_encode(pines_url('com_loan', 'forms/add_interaction')); ?>,
					type: "POST",
					dataType: "html",
					data: {"id": row_ids},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to retrieve the interaction form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (data == "") {
							return;
						}
						pines.pause();
						var form = $("<div title=\"Add Customer Interaction\"></div>").html(data+"<br />");
						if (rows.length > 1)
							form.find(".interaction_status").html('<div class="alert alert-info" style="padding-bottom: 10px;"><i class="icon-info-sign"></i> You are adding interactions to '+rows.length+' customers.</div>');
						form.dialog({
							bgiframe: true,
							autoOpen: true,
							width: width,
							modal: true,
							close: function(){
								form.remove();
							},
							buttons: {
								"Add Interaction": function(){
									var status_bar = form.find(".interaction_status_bar"),
										interaction_type = form.find(".interaction_type"),
										status = form.find(".interaction_status"),
										comments = form.find(".interaction_comments"),
										success = form.find(":input[name=interaction_success]");
									if (comments.val() == '') {
										alert('Please provide a description of the interaction.');
										status_bar.html('<div class="alert alert-error" style="padding-bottom: 10px;"><i class="icon-remove-sign"></i> Provide Interaction Description!</div>');
										return;
									}
									if (status.val() == '') {
										alert('Please specify whether the interaction is open or closed.');
										status_bar.html('<div class="alert alert-error" style="padding-bottom: 10px;"><i class="icon-remove-sign"></i> Provide Interaction Status (Open / Closed)</div>');
										return;
									}
									$.ajax({
										url: <?php echo json_encode(pines_url('com_loan', 'saveinteraction')); ?>,
										type: "POST",
										dataType: "json",
										data: {
											loan_ids: row_ids,
											employee: <?php echo json_encode("{$_SESSION['user']->guid}"); ?>,
											type: interaction_type.val(),
											status: status.val(),
											comments: comments.val()
										},
										beforeSend: function(){
											status_bar.html('<div class="alert alert-success" style="padding-bottom: 10px;"><i class="picon picon-throbber"></i> Processing...</div>');
										},
										error: function(XMLHttpRequest, textStatus){
											pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
											success.val('false');
										},
										success: function(data){
											if (data) {
												alert('The interaction successfully saved.');
												form.dialog('close');
											} else {
												alert('Interaction was not saved.');
												return;
											}
										}
									});
								},
								"Cancel": function() {
									$(this).dialog("close");
								}
							}
						});
						pines.play();
					}
				});
			}
			
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
						width: width,
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
<table id="p_loan_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Customer</th>
			<th>Employee</th>
			<th>Location</th>
			<th>Creation Date</th>
			<th>Code</th>
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
		<tr title="<?php echo htmlspecialchars($loan->guid); ?>">
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
			<td>-</td>
		</tr>
	</tbody>
</table>