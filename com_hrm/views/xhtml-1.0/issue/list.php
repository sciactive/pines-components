<?php
/**
 * Lists employee issue types and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Employee Issue Types';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var issue_dialog = $("#p_muid_issue_dialog");
		var issue_id;

		issue_dialog.find("form").submit(function(){
			issue_dialog.dialog('option', 'buttons').Save();
			return false;
		});
		issue_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 300,
			buttons: {
				'Save': function(){
					pines.post("<?php echo addslashes(pines_url('com_hrm', 'issue/save')); ?>", {
						id: issue_id,
						penalty: $("#p_muid_issue_dialog [name=penalty]").val(),
						name: $("#p_muid_issue_dialog [name=name]").val(),
						description: $("#p_muid_issue_dialog [name=description]").val()
					});
					issue_dialog.dialog("close");
				}
			}
		});

		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_hrm/listemployees')) { ?>
				{type: 'button', text: '&laquo; Employees', extra_class: 'picon picon-system-users', selection_optional: true, url: '<?php echo addslashes(pines_url('com_hrm', 'employee/list')); ?>'},
				<?php } if (gatekeeper('com_hrm/editissuetypes')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, click: function(e, rows){
					issue_id = 0;
					issue_dialog.dialog("open");
				}},
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, click: function(e, row){
					issue_id = row.attr("title");
					$("#p_muid_issue_dialog [name=name]").val(row.pgrid_get_value(1));
					$("#p_muid_issue_dialog [name=description]").val(row.pgrid_get_value(3));
					$("#p_muid_issue_dialog [name=penalty]").val(row.pgrid_get_value(2).replace('$',''));
					issue_dialog.dialog("open");
				}},
				{type: 'button', text: 'Remove', extra_class: 'picon picon-document-close', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_hrm', 'issue/delete', array('id' => '__title__'))); ?>'},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'issue types',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc'
		};
		$("#p_muid_grid").pgrid(cur_defaults);
	});

	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>Issue Name</th>
			<th>Penalty</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->types as $type) { ?>
		<tr title="<?php echo $type->guid; ?>">
			<td><?php echo htmlspecialchars($type->name); ?></td>
			<td>$<?php echo htmlspecialchars($type->penalty); ?></td>
			<td><?php echo htmlspecialchars($type->description); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div id="p_muid_issue_dialog" title="Employee Conduct Issue" style="display: none; float: left;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element">
			<label><span class="pf-label">Issue</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="name" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Description</span>
				<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="description" value="" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Penalty</span>
				<span class="pf-field">$ <input class="ui-widget-content ui-corner-all" type="text" size="3" name="penalty" /></span></label>
		</div>
	</form>
	<br />
</div>
<?php //rev #328 ; ?>