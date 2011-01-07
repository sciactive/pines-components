<?php
/**
 * Provides a homepage for the customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customer History ['.$this->entity->name.']';
$pines->com_pgrid->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_interactions a, #p_muid_sales a, #p_muid_returns a {
		text-decoration: underline;
	}
	#p_muid_interaction_dialog ul {
		font-size: 0.8em;
		list-style-type: disc;
		margin: 0;
		padding: 0;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
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
						url: "<?php echo addslashes(pines_url('com_customer', 'interaction/info')); ?>",
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
							pines.error("An error occured:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("No entry was found that matched the selected interaction.");
								return;
							}
							$("#p_muid_interaction_customer").empty().append(data.customer);
							$("#p_muid_interaction_type").empty().append(data.type);
							$("#p_muid_interaction_employee").empty().append(data.employee);
							$("#p_muid_interaction_date").empty().append(data.date);
							$("#p_muid_interaction_comments").empty().append(data.comments);
							$("#p_muid_interaction_notes").empty().append(data.review_comments ? "<li>"+data.review_comments.join("</li><li>")+"</li>" : "");

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
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'customer_interaction_<?php echo $this->entity->guid; ?>',
						content: rows
					});
				}}
			],
			pgrid_footer: false,
			pgrid_view_height: 'auto',
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'desc'
		});

		$("#p_muid_sales, #p_muid_returns").pgrid({
			pgrid_toolbar: false,
			pgrid_footer: false,
			pgrid_view_height: 'auto',
			pgrid_sort_col: 2,
			pgrid_sort_ord: 'desc'
		});

		$("#p_muid_acc_interaction, #p_muid_acc_sale, #p_muid_acc_return").accordion({autoHeight: false, collapsible: true});

		new_interaction.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 402,
			buttons: {
				"Log Interaction": function(){
					var loader;
					$.ajax({
						url: "<?php echo addslashes(pines_url('com_customer', 'interaction/add')); ?>",
						type: "POST",
						dataType: "json",
						data: {
							customer: <?php echo $this->entity->guid; ?>,
							employee: <?php echo $_SESSION['user']->guid; ?>,
							date: $("#p_muid_new_interaction [name=interaction_date]").val(),
							time_ampm: $("#p_muid_new_interaction [name=interaction_ampm]").val(),
							time_hour: $("#p_muid_new_interaction [name=interaction_hour]").val(),
							time_minute: $("#p_muid_new_interaction [name=interaction_minute]").val(),
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
							pines.error("An error occured:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
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
						url: "<?php echo addslashes(pines_url('com_customer', 'interaction/process')); ?>",
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
							pines.error("An error occured:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("Could not update the customer interaction.");
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
	});
	// ]]>
</script>
<div class="pf-form">
	<div id="p_muid_acc_interaction">
		<h3 class="ui-helper-clearfix"><a href="#">Customer Interaction</a></h3>
		<div>
			<table id="p_muid_interactions">
				<thead>
					<tr>
						<th>ID</th>
						<th>Date</th>
						<th>Employee</th>
						<th>Interaction</th>
						<th>Status</th>
						<th>Comments</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($this->interactions as $cur_interaction) { ?>
					<tr title="<?php echo $cur_interaction->guid; ?>">
						<td><?php echo $cur_interaction->guid; ?></td>
						<td><?php echo format_date($cur_interaction->action_date, 'full_sort'); ?></td>
						<td><?php echo htmlspecialchars($cur_interaction->user->name); ?></td>
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
		<?php if (!empty($this->sales)) { ?>
		<div id="p_muid_acc_sale">
			<h3 class="ui-helper-clearfix"><a href="#">Purchases</a></h3>
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
						<?php foreach ($this->sales as $cur_sale) {
						$item_count = count($cur_sale->products); ?>
						<tr title="<?php echo $cur_sale->guid; ?>">
							<td><a href="<?php echo pines_url('com_sales', 'sale/receipt', array('id' => $cur_sale->guid)); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_sale->id); ?></a></td>
							<td><?php echo format_date($cur_sale->p_cdate); ?></td>
							<td><a href="<?php echo pines_url('com_sales', 'sale/receipt', array('id' => $cur_sale->guid)); ?>" onclick="window.open(this.href); return false;"><?php echo ($item_count == 1) ? htmlspecialchars($cur_sale->products[0]['entity']->name . ' x ' . $cur_sale->products[0]['quantity']) : $item_count.' products'; ?></a></td>
							<td>$<?php echo number_format($cur_sale->subtotal, 2); ?></td>
							<td>$<?php echo number_format($cur_sale->taxes, 2); ?></td>
							<td>$<?php echo number_format($cur_sale->total, 2); ?></td>
							<td><?php switch ($cur_sale->status) {
								case 'invoiced':
									echo 'Invoiced';
									break;
								case 'paid':
									echo 'Paid';
									break;
								default:
									echo 'Unrecognized';
									break;
							} ?></td>
							<td><?php echo htmlspecialchars($cur_sale->group->name); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php } if (!empty($this->returns)) { ?>
		<div id="p_muid_acc_return">
			<h3 class="ui-helper-clearfix"><a href="#">Returns</a></h3>
			<div>
				<table id="p_muid_returns">
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
						<?php foreach ($this->returns as $cur_return) {
						$item_count = count($cur_return->products); ?>
						<tr title="<?php echo $cur_return->guid; ?>">
							<td><a href="<?php echo pines_url('com_sales', 'return/receipt', array('id' => $cur_return->guid)); ?>" target="receipt"><?php echo htmlspecialchars($cur_return->id); ?></a></td>
							<td><?php echo format_date($cur_return->p_cdate); ?></td>
							<td><a href="<?php echo pines_url('com_sales', 'return/receipt', array('id' => $cur_return->guid)); ?>" target="receipt"><?php echo ($item_count == 1) ? htmlspecialchars($cur_return->products[0]['entity']->name) : $item_count.' items'; ?></a></td>
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
		<form class="pf-form" method="post" action="">
			<div class="pf-element">
				<label><span class="pf-label">Interaction Type</span>
					<select class="ui-widget-content ui-corner-all" name="interaction_type">
						<?php foreach ($pines->config->com_customer->interaction_types as $cur_type) {
							echo '<option value="'.htmlspecialchars($cur_type).'">'.htmlspecialchars($cur_type).'</option>';
						} ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Date</span>
					<input class="ui-widget-content ui-corner-all" type="text" size="22" name="interaction_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
			</div>
			<div class="pf-element pf-full-width">
				<?php
				$time_hour = format_date(time(), 'custom', 'H');
				$time_minute = format_date(time(), 'custom', 'i');
				?>
				<span class="pf-label">Time</span>
				<select class="ui-widget-content ui-corner-all" name="interaction_hour">
					<option value="1" <?php echo ($time_hour == '1' || $time_hour == '13') ? 'selected="selected"' : ''; ?>>1</option>
					<option value="2" <?php echo ($time_hour == '2' || $time_hour == '14') ? 'selected="selected"' : ''; ?>>2</option>
					<option value="3" <?php echo ($time_hour == '3' || $time_hour == '15') ? 'selected="selected"' : ''; ?>>3</option>
					<option value="4" <?php echo ($time_hour == '4' || $time_hour == '16') ? 'selected="selected"' : ''; ?>>4</option>
					<option value="5" <?php echo ($time_hour == '5' || $time_hour == '17') ? 'selected="selected"' : ''; ?>>5</option>
					<option value="6" <?php echo ($time_hour == '6' || $time_hour == '18') ? 'selected="selected"' : ''; ?>>6</option>
					<option value="7" <?php echo ($time_hour == '7' || $time_hour == '19') ? 'selected="selected"' : ''; ?>>7</option>
					<option value="8" <?php echo ($time_hour == '8' || $time_hour == '20') ? 'selected="selected"' : ''; ?>>8</option>
					<option value="9" <?php echo ($time_hour == '9' || $time_hour == '21') ? 'selected="selected"' : ''; ?>>9</option>
					<option value="10" <?php echo ($time_hour == '10' || $time_hour == '22') ? 'selected="selected"' : ''; ?>>10</option>
					<option value="11" <?php echo ($time_hour == '11' || $time_hour == '23') ? 'selected="selected"' : ''; ?>>11</option>
					<option value="0" <?php echo ($time_hour == '0' || $time_hour == '12') ? 'selected="selected"' : ''; ?>>12</option>
				</select> :
				<select class="ui-widget-content ui-corner-all" name="interaction_minute">
					<option value="0" <?php echo ($time_minute >= '0' && $time_minute < '15') ? 'selected="selected"' : ''; ?>>00</option>
					<option value="15" <?php echo ($time_minute >= '15' && $time_minute < '30') ? 'selected="selected"' : ''; ?>>15</option>
					<option value="30" <?php echo ($time_minute >= '30' && $time_minute < '45') ? 'selected="selected"' : ''; ?>>30</option>
					<option value="45" <?php echo ($time_minute >= '45' && $time_minute < '60') ? 'selected="selected"' : ''; ?>>45</option>
				</select>
				<select class="ui-widget-content ui-corner-all" name="interaction_ampm">
					<option value="am" selected="selected">AM</option>
					<option value="pm" <?php echo ($time_hour >= 12) ? 'selected="selected"' : ''; ?>>PM</option>
				</select>
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
		</form>
		<br />
	</div>
	<div id="p_muid_interaction_dialog" title="Process Customer Interaction" style="display: none;">
		<form class="pf-form" method="post" action="">
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
				<span class="pf-label">Comments</span>
				<span class="pf-field pf-full-width" id="p_muid_interaction_comments"></span>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Review Comments</span>
				<span class="pf-field pf-full-width">
					<ul id="p_muid_interaction_notes"></ul>
				</span>
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
		</form>
		<br />
	</div>
</div>
<button class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_customer', 'customer/list')); ?>');">Close</button>