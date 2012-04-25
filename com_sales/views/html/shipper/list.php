<?php
/**
 * Lists shippers and provides functions to manipulate them.
 *
 * @package Components
 * @subpackage sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Shippers';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_sales/shipper/list']);
?>
<script type="text/javascript">

	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newshipper')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_sales', 'shipper/edit')); ?>},
				<?php } if (gatekeeper('com_sales/editshipper')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_sales', 'shipper/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteshipper')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_sales', 'shipper/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_sales/shipper/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});
</script>
<table id="p_muid_grid">
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
		<tr title="<?php echo (int) $shipper->guid ?>">
			<td><?php echo htmlspecialchars($shipper->name); ?></td>
			<td><?php echo htmlspecialchars($shipper->email); ?></td>
			<td><?php echo htmlspecialchars($shipper->address_1); ?></td>
			<td><?php echo htmlspecialchars($shipper->address_2); ?></td>
			<td><?php echo htmlspecialchars($shipper->city); ?></td>
			<td><?php echo htmlspecialchars($shipper->state); ?></td>
			<td><?php echo htmlspecialchars($shipper->zip); ?></td>
			<td><?php echo htmlspecialchars(format_phone($shipper->phone_work)); ?></td>
			<td><?php echo htmlspecialchars(format_phone($shipper->fax)); ?></td>
			<td><?php echo htmlspecialchars($shipper->account_number); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>