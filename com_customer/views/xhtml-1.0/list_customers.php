<?php
/**
 * Lists customers and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Customers';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/list_customers'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_customer/newcustomer')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon_16x16_document-new', selection_optional: true, url: '<?php echo pines_url('com_customer', 'editcustomer'); ?>'},
				<?php } if (gatekeeper('com_customer/editcustomer')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon_16x16_document-edit', double_click: true, url: '<?php echo pines_url('com_customer', 'editcustomer', array('id' => '__title__')); ?>'},
				<?php } if ($pines->config->com_customer->resetpoints && gatekeeper('com_customer/resetpoints')) { ?>
				{type: 'button', text: 'Reset Points', extra_class: 'picon picon_16x16_edit-clear', multi_select: true, url: '<?php echo pines_url('com_customer', 'resetpoints', array('id' => '__title__')); ?>', delimiter: ','},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon_16x16_mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_customer/deletecustomer')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon_16x16_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_customer', 'deletecustomer', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'picon picon_16x16_document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon_16x16_document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon_16x16_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_customer/list_customers", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var customer_grid = $("#customer_grid").pgrid(cur_options);
		customer_grid.pgrid_get_all_rows().pgrid_delete();

		var customer_search_box = $("#customer_search_box");
		var customer_search_button = $("#customer_search_button");
		customer_search_box.keydown(function(e){
			if (e.keyCode == 13)
				customer_search_button.click();
		});
		customer_search_button.click(function(search_string){
			var search_string = customer_search_box.val();
			if (search_string == "") {
				alert("Please enter a search string.");
				return;
			}
			var loader;
			$.ajax({
				url: "<?php echo pines_url("com_customer", "customersearch"); ?>",
				type: "POST",
				dataType: "json",
				data: {"q": search_string},
				beforeSend: function(){
					loader = $.pnotify({
						pnotify_title: 'Search',
						pnotify_text: 'Searching the database...',
						pnotify_notice_icon: 'picon picon_16x16_throbber',
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
					pines.error("An error occured:\n"+XMLHttpRequest.status+": "+textStatus);
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
								this.guid,
								this.name,
								this.email,
								this.company,
								this.phone_home,
								this.phone_work,
								this.phone_cell,
								this.fax,
								this.login_disabled ? "Yes" : "No",
								this.member ? (this.valid_member ? "Yes" : "Expired") : "No",
								this.member_exp,
								this.points
							]
						});
					});
					customer_grid.pgrid_add(struct);
				}
			});
		});
	});

	// ]]>
</script>
<div class="pf-form">
	<div class="pf-element">
		<label>
			<span class="pf-label">Search</span>
			<input class="pf-field ui-widget-content" type="text" id="customer_search_box" />
			<button class="pf-field ui-state-default ui-corner-all" type="button" id="customer_search_button">Search</button>
		</label>
	</div>
</div>
<table id="customer_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Email</th>
			<th>Company</th>
			<th>Home Phone</th>
			<th>Work Phone</th>
			<th>Cell Phone</th>
			<th>Fax</th>
			<th>Login Disabled</th>
			<th>Member</th>
			<th>Expiration</th>
			<th>Points</th>
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
		</tr>
	</tbody>
</table>