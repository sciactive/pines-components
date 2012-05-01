<?php
/**
 * Lists customers and provides functions to manipulate them.
 *
 * @package Components\customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customers';
$this->note = 'Begin by searching for a customer.';
$pines->com_pgrid->load();
$pines->com_jstree->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_customer/customer/list']);
?>
<style type="text/css">
	#p_muid_interaction_dialog .combobox {
		position: relative;
	}
	#p_muid_interaction_dialog .combobox input {
		padding-right: 32px;
	}
	#p_muid_interaction_dialog .combobox a {
		display: block;
		position: absolute;
		right: 8px;
		top: 50%;
		margin-top: -8px;
	}
	.ui-autocomplete {
		max-height: 200px;
		overflow-y: auto;
		/* prevent horizontal scrollbar */
		overflow-x: hidden;
		/* add padding to account for vertical scrollbar */
		padding-right: 20px;
	}
	/* IE 6 doesn't support max-height
	 * we use height instead, but this forces the menu to always be this tall
	 */
	* html .ui-autocomplete {
		height: 200px;
	}
</style>
<script type="text/javascript">
	pines(function(){
		// Time and location variables.
		var all_time = true, start_date = "", end_date = "", location = "", descendants = false;
		// Customer search function for the pgrid toolbar.
		var customer_search_box;
		var submit_search = function(){
			var search_string = customer_search_box.val();
			if (search_string == "") {
				alert("Please enter a search string.");
				return;
			}
			var loader;
			$.ajax({
				url: <?php echo json_encode(pines_url('com_customer', 'customer/search')); ?>,
				type: "POST",
				dataType: "json",
				data: {"q": search_string, "location": location, "descendants": descendants, "all_time": all_time, "start_date": start_date, "end_date": end_date},
				beforeSend: function(){
					loader = $.pnotify({
						pnotify_title: 'Search',
						pnotify_text: 'Searching the database...',
						pnotify_notice_icon: 'picon picon-throbber',
						pnotify_nonblock: true,
						pnotify_hide: false,
						pnotify_history: false
					});
					customer_grid.pgrid_get_all_rows().pgrid_delete();
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						alert("No customers were found that matched the query.");
						return;
					}
					var struct = [];
					$.each(data, function(){
						struct.push({
							"key": this.guid,
							"values": [
								pines.safe(this.guid),
								'<a data-entity="'+pines.safe(this.guid)+'" data-entity-context="com_customer_customer">'+pines.safe(this.username)+'</a>',
								pines.safe(this.name),
								pines.safe(this.location),
								'<a href="mailto:'+pines.safe(this.email)+'">'+pines.safe(this.email)+'</a>',
								pines.safe(this.company),
								pines.safe(this.phone_home),
								pines.safe(this.phone_work),
								pines.safe(this.phone_cell),
								pines.safe(this.fax),
								pines.safe(this.cdate),
								this.enabled ? "Yes" : "No",
								this.member ? (this.valid_member ? "Yes" : "Expired") : "No",
								pines.safe(this.member_exp),
								pines.safe(this.points),
								pines.safe(this.referral)
							]
						});
					});
					customer_grid.pgrid_add(struct);
				}
			});
		}
		var customer_id;
		var interaction_dialog = $("#p_muid_interaction_dialog");

		interaction_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 402,
			buttons: {
				"Log Interaction": function(){
					var loader;
					$.ajax({
						url: <?php echo json_encode(pines_url('com_customer', 'interaction/add')); ?>,
						type: "POST",
						dataType: "json",
						data: {
							customer: customer_id,
							employee: $("#p_muid_interaction_dialog [name=employee]").val(),
							date: $("#p_muid_interaction_dialog [name=interaction_date]").val(),
							time: $("#p_muid_interaction_dialog [name=interaction_time]").val(),
							type: $("#p_muid_interaction_dialog [name=interaction_type]").val(),
							status: $("#p_muid_interaction_dialog [name=interaction_status]").val(),
							comments: $("#p_muid_interaction_dialog [name=interaction_comments]").val()
						},
						beforeSend: function(){
							loader = $.pnotify({
								pnotify_title: 'Logging',
								pnotify_text: 'Documenting customer interaction...',
								pnotify_notice_icon: 'picon picon-throbber',
								pnotify_nonblock: true,
								pnotify_hide: false,
								pnotify_history: false
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
								alert("Could not log the customer interaction.");
								return;
							}
							alert("Successfully logged interaction.");
							$("#p_muid_interaction_dialog [name=interaction_comments]").val('');
							interaction_dialog.dialog("close");
						}
					});
				}
			}
		});

		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'text', load: function(textbox){
					// Display the current sku being searched.
					textbox.keydown(function(e){
						if (e.keyCode == 13)
							submit_search();
					});
					customer_search_box = textbox;
				}},
				{type: 'button', extra_class: 'picon picon-system-search', selection_optional: true, pass_csv_with_headers: true, click: submit_search},
				{type: 'separator'},
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){customer_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){customer_grid.date_form();}},
				{type: 'separator'},
				<?php if (gatekeeper('com_customer/newcustomer')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_customer', 'customer/edit')); ?>},
				<?php } if (gatekeeper('com_customer/editcustomer')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_customer', 'customer/edit', array('id' => '__title__'))); ?>},
				{type: 'button', text: 'Edit as User', extra_class: 'picon picon-user-properties', url: <?php echo json_encode(pines_url('com_user', 'edituser', array('id' => '__title__'))); ?>},
				<?php } if (gatekeeper('com_customer/newinteraction')) { ?>
				{type: 'button', title: 'Add New Interaction', extra_class: 'picon picon-meeting-participant-optional', click: function(e, row){
					customer_id = row.attr('title');
					interaction_dialog.dialog("open");
				}},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if ($pines->config->com_customer->resetpoints && gatekeeper('com_customer/resetpoints')) { ?>
				{type: 'button', title: 'Reset the Customer\'s Points', extra_class: 'picon picon-edit-clear', multi_select: true, url: <?php echo json_encode(pines_url('com_customer', 'customer/resetpoints', array('id' => '__title__'))); ?>, delimiter: ','},
				<?php } if (gatekeeper('com_customer/deletecustomer')) { ?>
				{type: 'button', title: 'Remove as Customer', extra_class: 'picon picon-list-remove', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_customer', 'customer/remove', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'button', title: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_customer', 'customer/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'customers',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_customer/customer/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var customer_grid = $("#p_muid_grid").pgrid(cur_options);
		customer_grid.pgrid_get_all_rows().pgrid_delete();
	
		$("#p_muid_interaction_dialog [name=interaction_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		$(".combobox", "#p_muid_interaction_dialog").each(function(){
			var box = $(this);
			var autobox = box.children("input").autocomplete({
				minLength: 0,
				source: $.map(box.children("select").children(), function(elem){
					return $(elem).attr("value");
				})
			});
			box.children("a").hover(function(){
				$(this).addClass("ui-icon-circle-triangle-s").removeClass("ui-icon-triangle-1-s");
			}, function(){
				$(this).addClass("ui-icon-triangle-1-s").removeClass("ui-icon-circle-triangle-s");
			}).click(function(){
				autobox.focus().autocomplete("search", "");
			});
		});

		customer_grid.date_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/dateselect')); ?>,
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
		customer_grid.location_form = function(){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_sales', 'forms/locationselect')); ?>,
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
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Name</th>
			<th>Location</th>
			<th>Email</th>
			<th>Company</th>
			<th>Home Phone</th>
			<th>Work Phone</th>
			<th>Cell Phone</th>
			<th>Fax</th>
			<th>Created</th>
			<th>Login Enabled</th>
			<th>Member</th>
			<th>Expiration</th>
			<th>Points</th>
			<th>Referral Code</th>
		</tr>
	</thead>
	<tbody>
		<tr>
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
<div id="p_muid_interaction_dialog" title="Log Customer Interaction" style="display: none;">
	<form class="pf-form" method="post" action="">
		<?php if (gatekeeper('com_customer/manageinteractions') && $pines->config->com_customer->com_calendar) { ?>
		<div class="pf-element pf-full-width">
			<label><span class="pf-label">Employee</span>
				<select name="employee">
				<?php foreach ($pines->com_hrm->get_employees() as $cur_employee) {
					$selected = $_SESSION['user']->is($cur_employee) ? ' selected="selected"' : '';
					echo '<option value="'.$cur_employee->guid.'"'.$selected.'>'.htmlspecialchars($cur_employee->name).'</option>"';
				} ?>
			</select></label>
		</div>
		<?php } else { ?>
		<div class="pf-element pf-full-width">
			<label><span class="pf-label">Employee</span>
				<?php echo htmlspecialchars($_SESSION['user']->name); ?></label>
		</div>
		<input type="hidden" name="employee" value="<?php echo (int) $_SESSION['user']->guid ?>" />
		<?php } ?>
		<div class="pf-element">
			<label><span class="pf-label">Interaction Type</span>
				<select name="interaction_type">
					<?php foreach ($pines->config->com_customer->interaction_types as $cur_type) {
						$cur_type = explode(':', $cur_type);
						echo '<option value="'.htmlspecialchars($cur_type[1]).'">'.htmlspecialchars($cur_type[1]).'</option>';
					} ?>
				</select></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Date</span>
				<input type="text" size="22" name="interaction_date" value="<?php echo htmlspecialchars(format_date(time(), 'date_sort')); ?>" /></label>
		</div>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Time</span>
			<span class="combobox">
				<input type="text" name="interaction_time" size="18" value="<?php echo htmlspecialchars(format_date(time(), 'time_short')); ?>" />
				<a href="javascript:void(0);" class="ui-icon ui-icon-triangle-1-s"></a>
				<select style="display: none;">
					<option value="12:00 AM">12:00 AM</option>
					<option value="1:00 AM">1:00 AM</option>
					<option value="2:00 AM">2:00 AM</option>
					<option value="3:00 AM">3:00 AM</option>
					<option value="4:00 AM">4:00 AM</option>
					<option value="5:00 AM">5:00 AM</option>
					<option value="6:00 AM">6:00 AM</option>
					<option value="7:00 AM">7:00 AM</option>
					<option value="8:00 AM">8:00 AM</option>
					<option value="9:00 AM">9:00 AM</option>
					<option value="10:00 AM">10:00 AM</option>
					<option value="11:00 AM">11:00 AM</option>
					<option value="12:00 PM">12:00 PM</option>
					<option value="1:00 PM">1:00 PM</option>
					<option value="2:00 PM">2:00 PM</option>
					<option value="3:00 PM">3:00 PM</option>
					<option value="4:00 PM">4:00 PM</option>
					<option value="5:00 PM">5:00 PM</option>
					<option value="6:00 PM">6:00 PM</option>
					<option value="7:00 PM">7:00 PM</option>
					<option value="8:00 PM">8:00 PM</option>
					<option value="9:00 PM">9:00 PM</option>
					<option value="10:00 PM">10:00 PM</option>
					<option value="11:00 PM">11:00 PM</option>
				</select>
			</span>
		</div>
		<div class="pf-element">
			<label>
				<span class="pf-label">Status</span>
				<select name="interaction_status">
					<option value="open">Open</option>
					<option value="closed">Closed</option>
				</select>
			</label>
		</div>
		<div class="pf-element pf-full-width">
			<textarea rows="3" cols="40" name="interaction_comments"></textarea>
		</div>
	</form>
	<br />
</div>