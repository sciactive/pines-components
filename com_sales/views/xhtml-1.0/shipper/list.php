<?php
/**
 * Lists shippers and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Shippers';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/shipper/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newshipper')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'shipper/edit'); ?>'},
				<?php } if (gatekeeper('com_sales/editshipper')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo pines_url('com_sales', 'shipper/edit', array('id' => '__title__')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteshipper')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'shipper/delete', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'shippers',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/shipper/list", state: cur_state});
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
			<td><?php echo format_phone($shipper->phone_work); ?></td>
			<td><?php echo format_phone($shipper->fax); ?></td>
			<td><?php echo $shipper->account_number; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>