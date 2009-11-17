<?php
/**
 * Lists shippers and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Shippers';
?>
<script type="text/javascript">
	// <![CDATA[

	$(document).ready(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'newshipper'); ?>'},
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_sales', 'editshipper', array('id' => '#title#')); ?>'},
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deleteshipper', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					window.open("data:text/csv;charset=utf8," + encodeURIComponent(rows));
				}}
			],
			pgrid_sort_col: 'col_1',
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_sales/list_shippers", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#shipper_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="shipper_grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Address 1</th>
			<th>Address 2</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
			<th>Corporate Phone</th>
			<th>Fax</th>
			<th>Account #</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->shippers as $shipper) { ?>
		<tr title="<?php echo $shipper->guid; ?>">
			<td><?php echo $shipper->name; ?></td>
			<td><?php echo $shipper->email; ?></td>
			<td><?php echo $shipper->address_1; ?></td>
			<td><?php echo $shipper->address_2; ?></td>
			<td><?php echo $shipper->city; ?></td>
			<td><?php echo $shipper->state; ?></td>
			<td><?php echo $shipper->zip; ?></td>
			<td><?php echo $shipper->phone_work; ?></td>
			<td><?php echo $shipper->fax; ?></td>
			<td><?php echo $shipper->account_number; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>