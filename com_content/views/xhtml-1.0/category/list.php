<?php
/**
 * Lists categories and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Page Categories';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_content/category/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_content/newcategory')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_content', 'category/edit')); ?>'},
				<?php } if (gatekeeper('com_content/editcategory')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo addslashes(pines_url('com_content', 'category/edit', array('id' => '__title__'))); ?>'},
				{type: 'button', text: 'Pages', extra_class: 'picon picon-document-multiple', url: '<?php echo addslashes(pines_url('com_content', 'page/list', array('category' => '__title__'))); ?>'},
				{type: 'separator'},
				{type: 'button', text: 'Move Up', extra_class: 'picon picon-arrow-up', url: '<?php echo addslashes(pines_url('com_content', 'category/move', array('id' => '__title__', 'dir' => 'up'))); ?>'},
				{type: 'button', text: 'Move Down', extra_class: 'picon picon-arrow-down', url: '<?php echo addslashes(pines_url('com_content', 'category/move', array('id' => '__title__', 'dir' => 'down'))); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_content/deletecategory')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_content', 'category/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Expand All', extra_class: 'picon picon-arrow-down', selection_optional: true, return_all_rows: true, click: function(e, rows){
					rows.pgrid_expand_rows();
				}},
				{type: 'button', title: 'Collapse All', extra_class: 'picon picon-arrow-right', selection_optional: true, return_all_rows: true, click: function(e, rows){
					rows.pgrid_collapse_rows();
				}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'categories',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_child_prefix: "ch_",
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_content/category/list", state: cur_state});
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
			<th>Order</th>
			<th>Name</th>
			<th>Alias</th>
			<th>Enabled</th>
			<th>Pages</th>
			<th>Show Menu</th>
			<th>Menu Position</th>
			<th>Created</th>
			<th>Modified</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->categories as $category) { ?>
		<tr title="<?php echo $category->guid; ?>" class="<?php echo $category->children ? 'parent ' : ''; ?><?php echo isset($category->parent) ? "child ch_{$category->parent->guid} " : ''; ?>">
			<td><?php echo isset($category->parent) ? $category->array_search($category->parent->children) + 1 : '0' ; ?></td>
			<td><?php echo htmlspecialchars($category->name); ?></td>
			<td><?php echo htmlspecialchars($category->alias); ?></td>
			<td><?php echo ($category->enabled ? 'Yes' : 'No'); ?></td>
			<td><?php echo count($category->pages); ?></td>
			<td><?php echo ($category->show_menu ? 'Yes' : 'No'); ?></td>
			<td><?php echo $category->show_menu ? htmlspecialchars($category->menu_position) : ''; ?></td>
			<td><?php echo format_date($category->p_cdate); ?></td>
			<td><?php echo format_date($category->p_mdate); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>