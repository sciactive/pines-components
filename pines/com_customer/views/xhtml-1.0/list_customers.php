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
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_customer/newcustomer')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_customer', 'editcustomer'); ?>'},
				<?php } if (gatekeeper('com_customer/editcustomer')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_customer', 'editcustomer', array('id' => '__title__')); ?>'},
				<?php } if ($pines->config->com_customer->resetpoints && gatekeeper('com_customer/resetpoints')) { ?>
				{type: 'button', text: 'Reset Points', extra_class: 'icon picon_16x16_actions_edit-clear', multi_select: true, url: '<?php echo pines_url('com_customer', 'resetpoints', array('id' => '__title__')); ?>', delimiter: ','},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_customer/deletecustomer')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_customer', 'deletecustomer', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
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
		$("#customer_grid").pgrid(cur_options);
	});

	// ]]>
</script>
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
	<?php foreach($this->customers as $customer) { ?>
		<tr title="<?php echo $customer->guid; ?>">
			<td><?php echo $customer->guid; ?></td>
			<td><?php echo $customer->name; ?></td>
			<td><?php echo $customer->email; ?></td>
			<td><?php echo $customer->company->name; ?></td>
			<td><?php echo pines_phone_format($customer->phone_home); ?></td>
			<td><?php echo pines_phone_format($customer->phone_work); ?></td>
			<td><?php echo pines_phone_format($customer->phone_cell); ?></td>
			<td><?php echo pines_phone_format($customer->fax); ?></td>
			<td><?php echo ($customer->login_disabled ? 'Yes' : 'No'); ?></td>
			<td><?php echo ($customer->member ? ($customer->valid_member() ? 'Yes' : 'Expired') : 'No'); ?></td>
			<td><?php echo $customer->member_exp ? date('Y-m-d', $customer->member_exp) : ''; ?></td>
			<td><?php echo $customer->points; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>