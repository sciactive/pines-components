<?php
/**
 * Lists customers and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Zak Huber <zakhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Companies';
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newcompany')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_customer', 'editcompany'); ?>'},
				<?php } if (gatekeeper('com_sales/editcompany')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_customer', 'editcompany', array('id' => '#title#')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:#col_2#', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletecompany')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_customer', 'deletecompany', array('id' => '#title#')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
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
				state_xhr = $.post("<?php echo pines_url('system', 'pgrid_save_state'); ?>", {view: "com_customer/list_companies", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#company_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="company_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Company Name</th>
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
	<?php foreach($this->companies as $company) { ?>
		<tr title="<?php echo $company->guid; ?>">
			<td><?php echo $company->guid; ?></td>
			<td><?php echo $company->name; ?></td>
			<td><?php echo $company->address_type == 'us' ? 'US' : 'Intl'; ?></td>
			<td><?php echo $company->address_type == 'us' ? $company->address_1 .' '. $company->address_2 : $company->address_international; ?></td>
			<td><?php echo $company->city; ?></td>
			<td><?php echo $company->state; ?></td>
			<td><?php echo $company->zip; ?></td>
			<td><?php echo $company->email; ?></td>
			<td><?php echo pines_phone_format($company->phone); ?></td>
			<td><?php echo pines_phone_format($company->fax); ?></td>
			<td><?php echo $company->website; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>