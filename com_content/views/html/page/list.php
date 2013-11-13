<?php
/**
 * Lists pages and provides functions to manipulate them.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Pages';
if (isset($this->category))
	$this->title .= htmlspecialchars(" in {$this->category->name} [{$this->category->alias}]");
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = (object) json_decode($_SESSION['user']->pgrid_saved_states['com_content/page/list']);
?>
<script type="text/javascript">
	pines(function(){
		var state_xhr;
		var cur_state = <?php echo (isset($this->pgrid_state) ? json_encode($this->pgrid_state) : '{}');?>;
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_content/newpage')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: <?php echo json_encode(pines_url('com_content', 'page/edit')); ?>},
				<?php } if (gatekeeper('com_content/editpage')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: <?php echo json_encode(pines_url('com_content', 'page/edit', array('id' => '__title__'))); ?>},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				<?php if (gatekeeper('com_content/deletepage')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: <?php echo json_encode(pines_url('com_content', 'page/delete', array('id' => '__title__'))); ?>, delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'pages',
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
				state_xhr = $.post(<?php echo json_encode(pines_url('com_pgrid', 'save_state')); ?>, {view: "com_content/page/list", state: cur_state});
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
			<th>Alias</th>
			<th>Enabled</th>
			<th>Front Page</th>
			<th>Page Head</th>
			<th>Created</th>
			<th>Modified</th>
			<th>Publish Date</th>
			<th>Publish End</th>
			<th>Tags</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->pages as $page) { ?>
		<tr title="<?php echo htmlspecialchars($page->guid); ?>">
			<td><a data-entity="<?php echo htmlspecialchars($page->guid); ?>" data-entity-context="com_content_page"><?php echo htmlspecialchars($page->name); ?></a></td>
			<td><?php echo htmlspecialchars($page->alias); ?></td>
			<td><?php echo ($page->enabled ? 'Yes' : 'No'); ?></td>
			<td><?php echo (isset($page->show_front_page) ? ($page->show_front_page ? 'Yes' : 'No') : 'Use Default'); ?></td>
			<td><?php echo ($page->custom_head) ? 'Yes': ''; ?></td>
			<td><?php echo htmlspecialchars(format_date($page->p_cdate)); ?></td>
			<td><?php echo htmlspecialchars(format_date($page->p_mdate)); ?></td>
			<td><?php echo htmlspecialchars(format_date($page->publish_begin)); ?></td>
			<td><?php echo isset($page->publish_end) ? htmlspecialchars(format_date($page->publish_end)) : ''; ?></td>
			<td><?php echo htmlspecialchars(implode(', ', $page->content_tags)); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>