<?php
/**
 * Lists customers and provides functions to manipulate them.
 *
 * @package Components
 * @subpackage customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Companies';
$this->note = 'Begin by searching for a company.';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_customer/company/list']);
?>
<script type="text/javascript">

	pines(function(){
		// Company search function for the pgrid toolbar.
		var company_search_box;
		var submit_search = function(){
			var search_string = company_search_box.val();
			if (search_string == "") {
				alert("Please enter a search string.");
				return;
			}
			var loader;
			$.ajax({
				url: <?php echo json_encode(pines_url('com_customer', 'company/search')); ?>,
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
					company_grid.pgrid_get_all_rows().pgrid_delete();
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						alert("No companies were found that matched the query.");
						return;
					}
					var struct = [];
					$.each(data, function(){
						struct.push({
							"key": this.guid,
							"values": [
								pines.safe(this.guid),
								pines.safe(this.name),
								this.address_type == 'us' ? 'US' : 'Intl',
								pines.safe(this.address),
								pines.safe(this.city),
								pines.safe(this.state),
								pines.safe(this.zip),
								pines.safe(this.email),
								pines.safe(this.phone),
								pines.safe(this.fax),
								pines.safe(this.website)
							]
						});
					});
					company_grid.pgrid_add(struct);
				}
			});
		}
		
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
					company_search_box = textbox;
				}},
				{type: 'button', extra_class: 'picon picon-system-search', selection_optional: true, pass_csv_with_headers: true, click: submit_search},
				{type: 'separator'},
				<?php if (gatekeeper('com_customer/newcompany')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_customer', 'company/edit')); ?>},
				<?php } if (gatekeeper('com_customer/editcompany')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_customer', 'company/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_customer/deletecompany')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_customer', 'company/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'companies',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_customer/company/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var company_grid = $("#p_muid_grid").pgrid(cur_options);
		company_grid.pgrid_get_all_rows().pgrid_delete();
	});
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Address Type</th>
			<th>Address</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Fax</th>
			<th>Website</th>
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
		</tr>
	</tbody>
</table>