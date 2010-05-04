<?php
/**
 * Lists employees and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employees';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/list_employees'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_sales/newemployee')) { ?>
				{type: 'button', text: 'New', extra_class: 'icon picon_16x16_actions_document-new', selection_optional: true, url: '<?php echo pines_url('com_hrm', 'editemployee'); ?>'},
				<?php } if (gatekeeper('com_sales/editemployee')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'icon picon_16x16_actions_document-open', double_click: true, url: '<?php echo pines_url('com_hrm', 'editemployee', array('id' => '__title__')); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'icon picon_16x16_actions_mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deleteemployee')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'icon picon_16x16_actions_edit-delete', confirm: true, multi_select: true, url: '<?php echo pines_url('com_hrm', 'deleteemployee', array('id' => '__title__')); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', text: 'Select All', extra_class: 'icon picon_16x16_actions_list-add', select_all: true},
				{type: 'button', text: 'Select None', extra_class: 'icon picon_16x16_actions_list-remove', select_none: true},
				{type: 'separator'},
				{type: 'button', text: 'Spreadsheet', extra_class: 'icon picon_16x16_mimetypes_x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo pines_url('system', 'csv'); ?>", {
						filename: 'employees',
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
				state_xhr = $.post("<?php echo pines_url('com_pgrid', 'save_state'); ?>", {view: "com_hrm/list_employees", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#employee_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="employee_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>User</th>
			<th>Email</th>
			<th>Job Title</th>
			<th>Country</th>
			<th>Address</th>
			<th>City</th>
			<th>State</th>
			<th>Zip</th>
			<th>Home Phone</th>
			<th>Work Phone</th>
			<th>Cell Phone</th>
			<th>Fax</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->employees as $employee) { ?>
		<tr title="<?php echo $employee->guid; ?>">
			<td><?php echo $employee->guid; ?></td>
			<td><?php echo $employee->name; ?></td>
			<td><?php echo isset($employee->user_account) ? $employee->user_account->username : ''; ?></td>
			<td><?php echo $employee->email; ?></td>
			<td><?php echo $employee->job_title; ?></td>
			<td><?php echo $employee->address_type == 'us' ? 'US' : 'Intl'; ?></td>
			<td><?php echo $employee->address_type == 'us' ? $employee->address_1.' '.$employee->address_2 :  $employee->address_international; ?></td>
			<td><?php echo $employee->city; ?></td>
			<td><?php echo $employee->state; ?></td>
			<td><?php echo $employee->zip; ?></td>
			<td><?php echo format_phone($employee->phone_home); ?></td>
			<td><?php echo format_phone($employee->phone_work); ?></td>
			<td><?php echo format_phone($employee->phone_cell); ?></td>
			<td><?php echo format_phone($employee->fax); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>