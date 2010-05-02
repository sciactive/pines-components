<?php
/**
 * Lists payment types and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Payment Types';
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newpaymenttype')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_sales', 'editpaymenttype'); ?>'},
				<?php } if (gatekeeper('com_sales/editpaymenttype')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_sales', 'editpaymenttype', array('id' => '__title__')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletepaymenttype')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_sales', 'deletepaymenttype', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'payment types',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_sales/list_payment_types", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#payment_type_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="payment_type_grid">
	<thead>
		<tr>
			<th>Name</th>
			<th>Enabled</th>
			<th>Kick Drawer</th>
			<th>Change Type</th>
			<th>Minimum</th>
			<th>Maximum</th>
			<th>Processing Type</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->payment_types as $payment_type) { ?>
		<tr title="<?php echo $payment_type->guid; ?>">
			<td><?php echo $payment_type->name; ?></td>
			<td><?php echo $payment_type->enabled ? 'Yes' : 'No'; ?></td>
			<td><?php echo $payment_type->kick_drawer ? 'Yes' : 'No'; ?></td>
			<td><?php echo $payment_type->change_type ? 'Yes' : 'No'; ?></td>
			<td><?php echo $payment_type->minimum; ?></td>
			<td><?php echo $payment_type->maximum; ?></td>
			<td><?php echo $payment_type->processing_type; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>