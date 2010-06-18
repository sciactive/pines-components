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
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_customer/customer/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
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
				url: "<?php echo pines_url('com_customer', 'customer/search'); ?>",
				type: "POST",
				dataType: "json",
				data: {"q": search_string},
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
		}
		
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
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
				{type: 'button', text: 'Search', extra_class: 'picon picon-system-search', selection_optional: true, pass_csv_with_headers: true, click: submit_search},
				{type: 'separator'},
				<?php if (gatekeeper('com_customer/newcustomer')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo pines_url('com_customer', 'customer/edit'); ?>'},
				<?php } if (gatekeeper('com_customer/editcustomer')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo pines_url('com_customer', 'customer/edit', array('id' => '__title__')); ?>'},
				<?php } if ($pines->config->com_customer->resetpoints && gatekeeper('com_customer/resetpoints')) { ?>
				{type: 'button', text: 'Reset Points', extra_class: 'picon picon-edit-clear', multi_select: true, url: '<?php echo pines_url('com_customer', 'customer/resetpoints', array('id' => '__title__')); ?>', delimiter: ','},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_customer/deletecustomer')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_customer', 'customer/delete', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_customer/customer/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var customer_grid = $("#p_muid_grid").pgrid(cur_options);
		customer_grid.pgrid_get_all_rows().pgrid_delete();
	});

	// ]]>
</script>
<table id="p_muid_grid">
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