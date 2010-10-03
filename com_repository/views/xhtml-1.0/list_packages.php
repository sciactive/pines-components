<?php
/**
 * Lists packages and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Packages';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_repository/list_packages'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_repository/makeallindices')) { ?>
				{type: 'button', text: 'Refresh Repository Index', extra_class: 'picon picon-view-refresh', selection_optional: true, url: '<?php echo addslashes(pines_url('com_repository', 'uploadpackage', array('all' => 'true'))); ?>'},
				<?php } if (gatekeeper('com_repository/makeindices')) { ?>
				{type: 'button', text: 'Refresh My Index', extra_class: 'picon picon-view-refresh', selection_optional: true, url: '<?php echo addslashes(pines_url('com_repository', 'uploadpackage')); ?>'},
				<?php } if (gatekeeper('com_repository/newpackage')) { ?>
				{type: 'button', text: 'Upload New Package', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_repository', 'uploadpackage')); ?>'},
				<?php } if (gatekeeper('com_repository/deletepackage')) { ?>
				{type: 'separator'},
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_repository', 'deletepackage', array('id' => '__title__'))); ?>', delimiter: ','},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'packages',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_repository/list_packages", state: cur_state});
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
			<th>Package</th>
			<th>Type</th>
			<th>Component</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->packages as $package) { ?>
		<tr title="<?php echo $package->guid; ?>">
			<td><?php echo htmlspecialchars($package->name); ?></td>
			<td><?php switch($package->type) {
				case 'component':
					echo 'Component Package';
					break;
				case 'template':
					echo 'Template Package';
					break;
				case 'system':
					echo 'System Package';
					break;
				case 'meta':
					echo 'Meta Package';
					break;
			} ?></td>
			<td><?php if (!in_array($package->type, array('system', 'meta'))) {
				$name = $package->component;
				echo htmlspecialchars("{$pines->info->$name->name} [{$name} {$pines->info->$name->version}]");
			} else {
				if ($package->type == 'system') {
					echo htmlspecialchars("{$pines->info->name} [system {$pines->info->version}]");
				} else {
					echo 'N/A';
				}
			} ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>