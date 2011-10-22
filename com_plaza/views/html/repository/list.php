<?php
/**
 * Lists repositories and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Software Sources';
$this->note = 'These repositories are where Pines looks for new and updated software.';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_plaza/repository/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_plaza', 'repository/new')); ?>'},
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_plaza', 'repository/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'repositories',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_plaza/repository/list", state: cur_state});
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
			<th>Organization</th>
			<th>URL</th>
			<th>Validity</th>
			<th>Location</th>
			<th>Contact Email</th>
			<th>File</th>
			<th>Full Detail</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->repositories as $key => $repository) { ?>
		<tr title="<?php echo htmlspecialchars(basename($repository['cert'])); ?>">
			<td><?php echo htmlspecialchars($key + 1); ?></td>
			<td><?php echo htmlspecialchars($repository['data']['subject']['OU']); ?></td>
			<td><?php echo htmlspecialchars($repository['data']['subject']['O']); ?></td>
			<td><a href="<?php echo htmlspecialchars($repository['url']); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($repository['url']); ?></a></td>
			<td><?php echo format_date(intval($repository['data']['validFrom_time_t']), 'date_med').' to '.format_date(intval($repository['data']['validTo_time_t']), 'date_med'); ?></td>
			<td><?php echo htmlspecialchars("{$repository['data']['subject']['L']}, {$repository['data']['subject']['ST']}, {$repository['data']['subject']['C']}"); ?></td>
			<td><a href="mailto:<?php echo htmlspecialchars($repository['data']['subject']['emailAddress']); ?>"><?php echo htmlspecialchars($repository['data']['subject']['emailAddress']); ?></a></td>
			<td><?php echo htmlspecialchars(basename($repository['cert'])); ?></td>
			<td>
				<a href="javascript:void(0);" onclick="$(this).next().dialog({'width': 800});">Open Detail</a>
				<div title="Repository Certificate Detail" style="display: none;">
					<div style="padding: 0 1em 1em 0;">
						<textarea class="ui-widget-content ui-corner-all" readonly="readonly" cols="24" rows="5" style="width: 100%; height: 500px;"><?php print_r($repository['data']); ?></textarea>
					</div>
				</div>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>