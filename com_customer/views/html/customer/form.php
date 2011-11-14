<?php
/**
 * Provides a form for the user to edit a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Customer' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide customer profile details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
$pines->com_customer->load_company_select();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_interactions a, #p_muid_sales a {
		text-decoration: underline;
	}
	#p_muid_interaction_dialog ul {
		font-size: 0.8em;
		list-style-type: disc;
		padding-left: 10px;
	}
	#p_muid_new_interaction .combobox {
		position: relative;
	}
	#p_muid_new_interaction .combobox input {
		padding-right: 32px;
	}
	#p_muid_new_interaction .combobox a {
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
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
		var addresses = $("#p_muid_addresses");
		var addresses_table = $("#p_muid_addresses_table");
		var address_dialog = $("#p_muid_address_dialog");
		addresses_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Address',
					extra_class: 'picon picon-list-add',
					selection_optional: true,
					click: function(){
						address_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Address',
					extra_class: 'picon picon-list-remove',
					click: function(e, rows){
						rows.pgrid_delete();
						update_address();
					}
				}
			],
			pgrid_view_height: "300px"
		});

		// Address Dialog
		address_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			buttons: {
				"Done": function(){
					var cur_address_type = $("#p_muid_cur_address_type").val();
					var cur_address_addr1 = $("#p_muid_cur_address_addr1").val();
					var cur_address_addr2 = $("#p_muid_cur_address_addr2").val();
					var cur_address_city = $("#p_muid_cur_address_city").val();
					var cur_address_state = $("#p_muid_cur_address_state").val();
					var cur_address_zip = $("#p_muid_cur_address_zip").val();
					if (cur_address_type == "" || cur_address_addr1 == "") {
						alert("Please provide a name and a street address.");
						return;
					}
					var new_address = [{
						key: null,
						values: [
							pines.safe(cur_address_type),
							pines.safe(cur_address_addr1),
							pines.safe(cur_address_addr2),
							pines.safe(cur_address_city),
							pines.safe(cur_address_state),
							pines.safe(cur_address_zip)
						]
					}];
					addresses_table.pgrid_add(new_address);
					$(this).dialog('close');
				}
			},
			close: function(){
				update_addresses();
			}
		});

		var update_addresses = function(){
			$("#p_muid_cur_address_type, #p_muid_cur_address_addr1, #p_muid_cur_address_addr2, #p_muid_cur_address_city, #p_muid_cur_address_state, #p_muid_cur_address_zip").val("");
			addresses.val(JSON.stringify(addresses_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_addresses();

		<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
		// Attributes
		var attributes = $("#p_muid_tab_attributes input[name=attributes]");
		var attributes_table = $("#p_muid_tab_attributes .attributes_table");
		var attribute_dialog = $("#p_muid_tab_attributes .attribute_dialog");

		attributes_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Attribute',
					extra_class: 'picon picon-list-add',
					selection_optional: true,
					click: function(){
						attribute_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Attribute',
					extra_class: 'picon picon-list-remove',
					click: function(e, rows){
						rows.pgrid_delete();
						update_attributes();
					}
				}
			],
			pgrid_view_height: "300px"
		});

		// Attribute Dialog
		attribute_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function(){
					var cur_attribute_name = attribute_dialog.find("input[name=cur_attribute_name]").val();
					var cur_attribute_value = attribute_dialog.find("input[name=cur_attribute_value]").val();
					if (cur_attribute_name == "" || cur_attribute_value == "") {
						alert("Please provide both a name and a value for this attribute.");
						return;
					}
					var new_attribute = [{
						key: null,
						values: [
							pines.safe(cur_attribute_name),
							pines.safe(cur_attribute_value)
						]
					}];
					attributes_table.pgrid_add(new_attribute);
					$(this).dialog('close');
				}
			},
			close: function(){
				update_attributes();
			}
		});

		var update_attributes = function(){
			attribute_dialog.find("input[name=cur_attribute_name]").val("");
			attribute_dialog.find("input[name=cur_attribute_value]").val("");
			attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_attributes();
		<?php } if (isset($this->entity->guid) && gatekeeper('com_customer/viewhistory')) { ?>
		var interaction_id;
		var new_interaction = $("#p_muid_new_interaction");
		var interaction_dialog = $("#p_muid_interaction_dialog");

		$("#p_muid_interactions").pgrid({
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_customer/newinteraction')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, click: function(e, row){
					new_interaction.dialog("open");
				}},
				<?php } if (gatekeeper('com_customer/editinteraction')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, click: function(e, row){
					interaction_id = row.attr('title');
					var loader;
					$.ajax({
						url: <?php echo json_encode(pines_url('com_customer', 'interaction/info')); ?>,
						type: "POST",
						dataType: "json",
						data: {"id": interaction_id},
						beforeSend: function(){
							loader = $.pnotify({
								pnotify_title: 'Search',
								pnotify_text: 'Searching the database...',
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
								alert("No entry was found that matched the selected interaction.");
								return;
							}
							$("#p_muid_interaction_customer").empty().append(pines.safe(data.customer));
							$("#p_muid_interaction_type").empty().append(pines.safe(data.type));
							$("#p_muid_interaction_employee").empty().append(pines.safe(data.employee));
							$("#p_muid_interaction_date").empty().append(pines.safe(data.date));
							$("#p_muid_interaction_comments").empty().append(pines.safe(data.comments));
							$("#p_muid_interaction_notes").empty().append((data.review_comments.length > 0) ? "<li>"+$.map(data.review_comments, pines.safe).join("</li><li>")+"</li>" : "");
							$("#p_muid_interaction_dialog [name=status]").val(data.status);

							interaction_dialog.dialog('open');
						}
					});
				}},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'customer_interaction_<?php echo (int) $this->entity->guid; ?>',
						content: rows
					});
				}}
			],
			pgrid_footer: false,
			pgrid_view_height: 'auto',
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'desc'
		});

		$("#p_muid_sales").pgrid({
			pgrid_toolbar: false,
			pgrid_footer: false,
			pgrid_view_height: 'auto',
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'desc',
			pgrid_child_prefix: 'ch_'
		});

		$("#p_muid_acc_interaction, #p_muid_acc_sale").accordion({autoHeight: false, collapsible: true});

		new_interaction.dialog({
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
							customer: <?php echo (int) $this->entity->guid; ?>,
							employee: $("#p_muid_new_interaction [name=employee]").val(),
							date: $("#p_muid_new_interaction [name=interaction_date]").val(),
							time: $("#p_muid_new_interaction [name=interaction_time]").val(),
							type: $("#p_muid_new_interaction [name=interaction_type]").val(),
							status: $("#p_muid_new_interaction [name=interaction_status]").val(),
							comments: $("#p_muid_new_interaction [name=interaction_comments]").val()
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
							if (data == 'conflict') {
								alert("This customer already has an appointment during this timeslot.");
								return;
							} else if (!data) {
								alert("Could not log the customer interaction.");
								return;
							}
							alert("Successfully logged interaction.");
							$("#p_muid_new_interaction [name=interaction_comments]").val('');
							new_interaction.dialog("close");
						}
					});
				}
			}
		});

		// Interaction Dialog
		interaction_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 400,
			buttons: {
				"Update": function(){
					var loader;
					$.ajax({
						url: <?php echo json_encode(pines_url('com_customer', 'interaction/process')); ?>,
						type: "POST",
						dataType: "json",
						data: {
							id: interaction_id,
							status: $("#p_muid_interaction_dialog [name=status]").val(),
							review_comments: $("#p_muid_interaction_dialog [name=review_comments]").val()
						},
						beforeSend: function(){
							loader = $.pnotify({
								pnotify_title: 'Updating',
								pnotify_text: 'Processing customer interaction...',
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
								alert("Could not update the interaction. Do you have permission?");
								return;
							} else if (data == 'closed') {
								alert("This interaction is already closed.");
								return;
							} else if (data == 'comments') {
								alert("You must provide information in the comments section.");
								return;
							}
							alert("Successfully updated the interaction.");
							$("#p_muid_interaction_dialog [name=review_comments]").val('');
							interaction_dialog.dialog("close");
						}
					});
				}
			}
		});

		$("#p_muid_new_interaction [name=interaction_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		$(".combobox", "#p_muid_new_interaction").each(function(){
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
		<?php } ?>

		$("#p_muid_customer_tabs").tabs();

		$("#p_muid_company").companyselect();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/save')); ?>">
	<div id="p_muid_customer_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_account">Account</a></li>
			<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
			<li><a href="#p_muid_tab_addresses">Addresses</a></li>
			<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
			<li><a href="#p_muid_tab_attributes">Attributes</a></li>
			<?php } if (isset($this->entity->guid) && gatekeeper('com_customer/viewhistory')) { ?>
			<li><a href="#p_muid_tab_history">History</a></li>
			<?php } ?>
		</ul>
		<div id="p_muid_tab_general">
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
			<?php if (in_array('name', $pines->config->com_customer->shown_fields_customer) && (!in_array('name', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical') || !isset($this->entity->name))) { ?>
			<div class="pf-element">
				<label><span class="pf-label">First Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_first" size="24" value="<?php echo htmlspecialchars($this->entity->name_first); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Middle Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_middle" size="24" value="<?php echo htmlspecialchars($this->entity->name_middle); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Last Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_last" size="24" value="<?php echo htmlspecialchars($this->entity->name_last); ?>" /></label>
			</div>
			<?php } if (in_array('ssn', $pines->config->com_customer->shown_fields_customer) && (!in_array('ssn', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical') || !isset($this->entity->ssn))) { ?>
			<div class="pf-element">
				<label><span class="pf-label">SSN</span>
					<span class="pf-note">Without dashes.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="ssn" size="24" value="<?php echo htmlspecialchars($this->entity->ssn); ?>" /></label>
			</div>
			<?php } if (in_array('dob', $pines->config->com_customer->shown_fields_customer) && (!in_array('dob', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical') || !isset($this->entity->dob))) { ?>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						$("#p_muid_form [name=dob]").datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true,
							showOtherMonths: true,
							selectOtherMonths: true,
							yearRange: '-100:+0'
						});
					});
					// ]]>
				</script>
				<label><span class="pf-label">Date of Birth</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="dob" size="24" value="<?php echo $this->entity->dob ? htmlspecialchars(format_date($this->entity->dob, 'date_sort')) : ''; ?>" /></label>
			</div>
			<?php } if (in_array('email', $pines->config->com_customer->shown_fields_customer) && (!in_array('email', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical') || !isset($this->entity->email))) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Email</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="email" name="email" size="24" value="<?php echo htmlspecialchars($this->entity->email); ?>" /></label>
			</div>
			<?php } if (in_array('company', $pines->config->com_customer->shown_fields_customer) && (!in_array('company', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical') || !isset($this->entity->company))) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Company</span>
					<span class="pf-note">Enter part of a company name, email, or phone # to search.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_company" name="company" size="24" value="<?php echo $this->entity->company->guid ? htmlspecialchars("{$this->entity->company->guid}: \"{$this->entity->company->name}\"") : ''; ?>" />
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Job Title</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="job_title" size="24" value="<?php echo htmlspecialchars($this->entity->job_title); ?>" /></label>
			</div>
			<?php } if (in_array('phone', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Cell Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="phone_cell" size="24" value="<?php echo htmlspecialchars(format_phone($this->entity->phone_cell)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Work Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="phone_work" size="24" value="<?php echo htmlspecialchars(format_phone($this->entity->phone_work)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Home Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="phone_home" size="24" value="<?php echo htmlspecialchars(format_phone($this->entity->phone_home)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Fax</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="tel" name="fax" size="24" value="<?php echo htmlspecialchars(format_phone($this->entity->fax)); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Timezone</span>
					<span class="pf-note">This overrides the primary group's timezone.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="timezone" size="1">
						<option value="">--Inherit From Group--</option>
						<?php
						$tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) {
							?><option value="<?php echo htmlspecialchars($cur_tz); ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_tz); ?></option><?php
						} ?>
					</select>
				</label>
			</div>
			<?php } if (in_array('referrer', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Referrer</span>
					<span class="pf-note">Where did you hear about us?</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="referrer">
						<option value="">-- Please Select --</option>
						<?php foreach ($pines->config->com_customer->referrer_values as $cur_value) { ?>
						<option value="<?php echo htmlspecialchars($cur_value); ?>"<?php echo ($this->entity->referrer == $cur_value) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_value); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<?php } if (in_array('description', $pines->config->com_customer->shown_fields_customer) && (!in_array('description', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical') || !isset($this->entity->description))) { ?>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="3" cols="35" class="pf-field peditor" style="width: 100%;" name="description"><?php echo htmlspecialchars($this->entity->description); ?></textarea>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_account">
			<?php if (!in_array('account', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical') || !isset($this->entity->username)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Username</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="username" size="24" value="<?php echo htmlspecialchars($this->entity->username); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Login Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->has_tag('enabled') ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if (in_array('password', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label"><?php if (isset($this->entity->password)) echo 'Update '; ?>Password</span>
					<?php if (!isset($this->entity->password)) {
						echo ($pines->config->com_user->pw_empty ? '<span class="pf-note">May be blank.</span>' : '');
					} else {
						echo '<span class="pf-note">Leave blank, if not changing.</span>';
					} ?>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="password" size="24" /></label>
			</div>
			<?php } } if (in_array('points', $pines->config->com_customer->shown_fields_customer) && (!in_array('points', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical'))) { ?>
			<div class="pf-element pf-heading">
				<h1>Points</h1>
			</div>
			<div class="pf-element">
				<span class="pf-label">Current Points</span>
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->points); ?></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Peak Points</span>
				<span class="pf-note">The highest amount of points the customer has ever had.</span>
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->peak_points); ?></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Total Points in All Time</span>
				<span class="pf-note">The total amount of points the customer has ever had.</span>
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->total_points); ?></span>
			</div>
			<?php if ($pines->config->com_customer->adjustpoints && gatekeeper('com_customer/adjustpoints')) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Adjust Points</span>
					<span class="pf-note">Use a negative value to subtract points.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="adjust_points" size="24" value="0" /></label>
			</div>
			<?php } } if (in_array('membership', $pines->config->com_customer->shown_fields_customer) && (!in_array('membership', $pines->config->com_customer->critical_fields_customer) || gatekeeper('com_customer/editcritical'))) { ?>
			<div class="pf-element pf-heading">
				<h1>Membership</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Member</span>
					<input class="pf-field" type="checkbox" name="member" value="ON"<?php echo $this->entity->member ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if ($this->entity->member) { ?>
			<div class="pf-element">
				<span class="pf-label">Member Since</span>
				<span class="pf-field"><?php echo htmlspecialchars(format_date($this->entity->member_since, 'full_long')); ?></span>
			</div>
			<?php } ?>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						$("#p_muid_form [name=member_exp]").datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true,
							showOtherMonths: true,
							selectOtherMonths: true
						});
					});
					// ]]>
				</script>
				<label><span class="pf-label">Membership Expiration</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="member_exp" size="24" value="<?php echo $this->entity->member_exp ? htmlspecialchars(format_date($this->entity->member_exp, 'date_sort')) : ''; ?>" /></label>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
		<div id="p_muid_tab_addresses">
			<div class="pf-element pf-heading">
				<h1>Main Address</h1>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var address_us = $("#p_muid_address_us");
						var address_international = $("#p_muid_address_international");
						$("#p_muid_form [name=address_type]").change(function(){
							var address_type = $(this);
							if (address_type.is(":checked") && address_type.val() == "us") {
								address_us.show();
								address_international.hide();
							} else if (address_type.is(":checked") && address_type.val() == "international") {
								address_international.show();
								address_us.hide();
							}
						}).change();
					});
					// ]]>
				</script>
				<span class="pf-label">Address Type</span>
				<label><input class="pf-field" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="pf-field" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="p_muid_address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_1" size="24" value="<?php echo htmlspecialchars($this->entity->address_1); ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_2" size="24" value="<?php echo htmlspecialchars($this->entity->address_2); ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="city" size="15" value="<?php echo htmlspecialchars($this->entity->city); ?>" />
					<select class="pf-field ui-widget-content ui-corner-all" name="state">
						<option value="">None</option>
						<?php foreach (array(
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
								'WY' => 'Wyoming',
								'AA' => 'Armed Forces (AA)',
								'AE' => 'Armed Forces (AE)',
								'AP' => 'Armed Forces (AP)'
							) as $key => $cur_state) { ?>
						<option value="<?php echo $key; ?>"<?php echo $this->entity->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Zip</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="zip" size="24" value="<?php echo htmlspecialchars($this->entity->zip); ?>" /></label>
				</div>
			</div>
			<div id="p_muid_address_international" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo htmlspecialchars($this->entity->address_international); ?></textarea></span></label>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Additional Addresses</h1>
			</div>
			<div class="pf-element pf-full-width">
				<table id="p_muid_addresses_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Address 1</th>
							<th>Address 2</th>
							<th>City</th>
							<th>State</th>
							<th>Zip</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->addresses as $cur_address) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_address['type']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['address_1']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['address_2']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['city']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['state']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['zip']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" id="p_muid_addresses" name="addresses" />
			</div>
			<div id="p_muid_address_dialog" title="Add an Address">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="cur_address_type" id="p_muid_cur_address_type" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Address 1</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="cur_address_addr1" id="p_muid_cur_address_addr1" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Address 2</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="cur_address_addr2" id="p_muid_cur_address_addr2" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">City, State, Zip</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="8" name="cur_address_city" id="p_muid_cur_address_city" />
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="2" name="cur_address_state" id="p_muid_cur_address_state" />
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="5" name="cur_address_zip" id="p_muid_cur_address_zip" />
						</label>
					</div>
				</div>
				<br class="pf-clearing" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
		<div id="p_muid_tab_attributes">
			<div class="pf-element pf-full-width">
				<table class="attributes_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_attribute['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_attribute['value']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="attributes" />
			</div>
			<div class="attribute_dialog" style="display: none;" title="Add an Attribute">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_attribute_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_attribute_value" size="24" />
						</label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } if (isset($this->entity->guid) && gatekeeper('com_customer/viewhistory')) { ?>
		<div id="p_muid_tab_history">
			<div id="p_muid_acc_interaction">
				<h3 class="ui-helper-clearfix"><a href="#">Customer Interaction</a></h3>
				<div>
					<table id="p_muid_interactions">
						<thead>
							<tr>
								<th>ID</th>
								<th>Created</th>
								<th>Appointment</th>
								<th>Employee</th>
								<th>Interaction</th>
								<th>Status</th>
								<th>Comments</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($this->interactions as $cur_interaction) { ?>
							<tr title="<?php echo (int) $cur_interaction->guid; ?>">
								<td><?php echo (int) $cur_interaction->guid; ?></td>
								<td><?php echo htmlspecialchars(format_date($cur_interaction->p_cdate, 'full_sort')); ?></td>
								<td><?php echo htmlspecialchars(format_date($cur_interaction->action_date, 'full_sort')); ?></td>
								<td><?php echo htmlspecialchars($cur_interaction->employee->name); ?></td>
								<td><?php echo htmlspecialchars($cur_interaction->type); ?></td>
								<td><?php echo ucwords($cur_interaction->status); ?></td>
								<td><?php echo htmlspecialchars($cur_interaction->comments); ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php if ($this->com_sales) { ?>
				<?php if (!empty($this->sales) || !empty($this->returns)) {
					// Gather all the sales with returns.
					$returned_sales = array();
					foreach ((array) $this->returns as $cur_return) {
						if (isset($cur_return->sale->guid))
							$returned_sales[] = $cur_return->sale->guid;
					}
					?>
				<div id="p_muid_acc_sale">
					<h3 class="ui-helper-clearfix"><a href="#">Purchases and Returns</a></h3>
					<div>
						<table id="p_muid_sales">
							<thead>
								<tr>
									<th>ID</th>
									<th>Date</th>
									<th>Item(s)</th>
									<th>Subtotal</th>
									<th>Tax</th>
									<th>Total</th>
									<th>Status</th>
									<th>Location</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ((array) $this->sales as $cur_sale) {
								$item_count = count($cur_sale->products); ?>
								<tr title="<?php echo (int) $cur_sale->guid; ?>"<?php echo in_array($cur_sale->guid, $returned_sales) ? ' class="parent"' : ''; ?>>
									<td>Sale <?php echo htmlspecialchars($cur_sale->id); ?> (<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/receipt', array('id' => $cur_sale->guid))); ?>" onclick="window.open(this.href); return false;">Receipt</a>|<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/edit', array('id' => $cur_sale->guid))); ?>" onclick="window.open(this.href); return false;">Edit</a>)</td>
									<td><?php echo htmlspecialchars(format_date($cur_sale->p_cdate)); ?></td>
									<td><?php echo ($item_count == 1) ? htmlspecialchars($cur_sale->products[0]['entity']->name . ' x ' . $cur_sale->products[0]['quantity']) : $item_count.' products'; ?></td>
									<td>$<?php echo number_format($cur_sale->subtotal, 2); ?></td>
									<td>$<?php echo number_format($cur_sale->taxes, 2); ?></td>
									<td>$<?php echo number_format($cur_sale->total, 2); ?></td>
									<td><?php echo htmlspecialchars(ucwords($cur_sale->status)); ?></td>
									<td><?php echo htmlspecialchars($cur_sale->group->name); ?></td>
								</tr>
								<?php } foreach ((array) $this->returns as $cur_return) {
								$item_count = count($cur_return->products); ?>
								<tr title="<?php echo (int) $cur_return->guid; ?>"<?php echo (isset($cur_return->sale->guid) && $cur_return->sale->in_array((array) $this->sales)) ? ' class="child ch_'.htmlspecialchars($cur_return->sale->guid).'"' : ''; ?>>
									<td>Return <?php echo htmlspecialchars($cur_return->id); ?> (<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'return/receipt', array('id' => $cur_return->guid))); ?>" target="receipt">Receipt</a>|<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'return/edit', array('id' => $cur_return->guid))); ?>" target="receipt">Edit</a>)</td>
									<td><?php echo htmlspecialchars(format_date($cur_return->p_cdate)); ?></td>
									<td><?php echo ($item_count == 1) ? htmlspecialchars($cur_return->products[0]['entity']->name) : $item_count.' items'; ?></td>
									<td>$<?php echo number_format($cur_return->subtotal, 2); ?></td>
									<td>$<?php echo number_format($cur_return->taxes, 2); ?></td>
									<td>$<?php echo number_format($cur_return->total, 2); ?></td>
									<td><?php echo htmlspecialchars(ucwords($cur_return->status)); ?></td>
									<td><?php echo htmlspecialchars($cur_return->group->name); ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php }
			} ?>
			<br class="pf-clearing" />
			<div id="p_muid_new_interaction" title="Log Customer Interaction" style="display: none;">
				<div class="pf-form">
					<?php if (gatekeeper('com_customer/manageinteractions') && $pines->config->com_customer->com_calendar) { ?>
					<div class="pf-element">
						<label><span class="pf-label">Employee</span>
							<select class="ui-widget-content ui-corner-all" name="employee">
							<?php foreach ($pines->com_hrm->get_employees() as $cur_employee) {
								$selected = $_SESSION['user']->is($cur_employee) ? ' selected="selected"' : '';
								echo '<option value="'.((int) $cur_employee->guid).'"'.$selected.'>'.htmlspecialchars($cur_employee->name).'</option>"';
							} ?>
						</select></label>
					</div>
					<?php } else { ?>
					<div class="pf-element">
						<label><span class="pf-label">Employee</span>
							<?php echo htmlspecialchars($_SESSION['user']->name); ?></label>
					</div>
					<input type="hidden" name="employee" value="<?php echo (int) $_SESSION['user']->guid; ?>" />
					<?php } ?>
					<div class="pf-element">
						<label><span class="pf-label">Interaction Type</span>
							<select class="ui-widget-content ui-corner-all" name="interaction_type">
								<?php foreach ($pines->config->com_customer->interaction_types as $cur_type) {
									$cur_type = explode(':', $cur_type);
									echo '<option value="'.htmlspecialchars($cur_type[1]).'">'.htmlspecialchars($cur_type[1]).'</option>';
								} ?>
							</select></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Date</span>
							<input class="ui-widget-content ui-corner-all" type="text" size="22" name="interaction_date" value="<?php echo htmlspecialchars(format_date(time(), 'date_sort')); ?>" /></label>
					</div>
					<div class="pf-element pf-full-width">
						<span class="pf-label">Time</span>
						<span class="combobox">
							<input class="ui-widget-content ui-corner-all" type="text" name="interaction_time" size="18" value="<?php echo htmlspecialchars(format_date(time(), 'time_short')); ?>" />
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
							<select class="ui-widget-content ui-corner-all" name="interaction_status">
								<option value="open">Open</option>
								<option value="closed">Closed</option>
							</select>
						</label>
					</div>
					<div class="pf-element pf-full-width">
						<textarea class="ui-widget-content ui-corner-all" rows="3" cols="40" name="interaction_comments"></textarea>
					</div>
				</div>
				<br class="pf-clearing" />
			</div>
			<div id="p_muid_interaction_dialog" title="Process Customer Interaction" style="display: none;">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Customer</span>
						<span class="pf-field" id="p_muid_interaction_customer"></span>
					</div>
					<div class="pf-element">
						<span class="pf-label">Employee</span>
						<span class="pf-field" id="p_muid_interaction_employee"></span>
					</div>
					<div class="pf-element">
						<span class="pf-label">Interaction Type</span>
						<span class="pf-field" id="p_muid_interaction_type"></span>
					</div>
					<div class="pf-element">
						<span class="pf-label">Date</span>
						<span class="pf-field" id="p_muid_interaction_date"></span>
					</div>
					<div class="pf-element pf-full-width">
						<span class="pf-full-width" id="p_muid_interaction_comments"></span>
						<div class="pf-full-width">
							<ul id="p_muid_interaction_notes"></ul>
						</div>
					</div>
					<div class="pf-element pf-full-width">
						<textarea class="ui-widget-content ui-corner-all" rows="3" cols="40" name="review_comments"></textarea>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Status</span>
							<select class="ui-widget-content ui-corner-all" name="status">
								<option value="open">Open</option>
								<option value="closed">Closed</option>
								<option value="canceled">Canceled</option>
							</select>
						</label>
					</div>
				</div>
				<br class="pf-clearing" />
			</div>
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_customer', 'customer/list')); ?>');" value="Cancel" />
	</div>
</form>