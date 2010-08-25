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
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/employee/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_hrm/addemployee')) { ?>
				{type: 'button', text: 'Add User(s)', extra_class: 'picon picon-list-add-user', selection_optional: true, click: function(){
					$.ajax({
						url: "<?php echo addslashes(pines_url('com_hrm', 'forms/userselect')); ?>",
						type: "POST",
						dataType: "html",
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to retreive the user select form:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (data == "")
								return;
							var form = $("<div title=\"Select User(s)\" />");
							form.html(data+"<br />");
							form.dialog({
								bgiframe: true,
								autoOpen: true,
								modal: true,
								close: function(){
									form.remove();
								},
								buttons: {
									"Make Employee": function(){
										form.dialog('close');
										var users = form.find(":input[name=users]").val();
										pines.post("<?php echo addslashes(pines_url('com_hrm', 'employee/add')); ?>", { "id": users });
									}
								}
							});
						}
					});
				}},
				<?php } if (gatekeeper('com_hrm/editemployee')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-properties', double_click: true, url: '<?php echo addslashes(pines_url('com_hrm', 'employee/edit', array('id' => '__title__'))); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_hrm/removeemployee')) { ?>
				{type: 'button', text: 'Remove User(s)', extra_class: 'picon picon-list-remove-user', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_hrm', 'employee/remove', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_hrm/employee/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Real Name</th>
			<th>Email</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->employees as $employee) { ?>
		<tr title="<?php echo $employee->guid; ?>">
			<td><?php echo $employee->guid; ?></td>
			<td><?php echo htmlspecialchars($employee->username); ?></td>
			<td><?php echo htmlspecialchars($employee->name); ?></td>
			<td><?php echo htmlspecialchars($employee->email); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>