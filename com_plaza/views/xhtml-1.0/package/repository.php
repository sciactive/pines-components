<?php
/**
 * Lists packages from repositories.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Available Software';
if (isset($this->service))
	$this->title .= ' that Provides Service \''.htmlspecialchars($this->service).'\'';
$pines->com_pgrid->load();
pines_session();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_plaza/package/repository'];
if (isset($pines->com_fancybox))
	$pines->com_fancybox->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_info {
		padding: 1em 2em;
	}
	#p_muid_info .version {
		display: block;
		float: right;
		clear: right;
	}
	#p_muid_info .short_description {
		font-size: 1.1em;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (isset($this->service)) { ?>
				{type: 'button', text: 'All Packages', extra_class: 'picon picon-arrow-left-double', selection_optional: true, url: '<?php echo addslashes(pines_url('com_plaza', 'package/repository')); ?>'},
				<?php } ?>
				{type: 'button', text: 'Reload', extra_class: 'picon picon-view-refresh', selection_optional: true, url: '<?php echo addslashes(pines_url('com_plaza', 'reload')); ?>'},
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_plaza/package/repository", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var package_grid = $("#p_muid_grid").pgrid(cur_options);
		var buttons_new = {
			"Install": function(){
				var name = $(".package", this).text();
				pines.com_plaza.ajax_show();
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_plaza', 'package/changes')); ?>",
					type: "POST",
					dataType: "json",
					data: {"name": name, "do": "install"},
					complete: function(){
						pines.com_plaza.ajax_hide();
						info_dialog.dialog("enable");
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to calculate changes:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (!data) {
							alert("Could not determine required changes.");
							return;
						}
						if (!data.possible) {
							alert("It is not possible to install this package. Check its dependencies to see if there are any PHP extenstions you need to install to provide any required functions or classes.");
							return;
						}
						pines.com_plaza.confirm_changes({
							"changes": "The following changes are required to install the package '"+name+"'.",
							"nochanges": "Are you sure you want to install the package '"+name+"'?"
						}, data, function(){
							info_dialog.dialog("disable");
							pines.com_plaza.ajax_show();
							$.ajax({
								url: "<?php echo addslashes(pines_url('com_plaza', 'package/do')); ?>",
								type: "POST",
								dataType: "json",
								data: {"name": name, "do": "install"},
								complete: function(){
									pines.com_plaza.ajax_hide();
									info_dialog.dialog("enable");
								},
								error: function(XMLHttpRequest, textStatus){
									pines.error("An error occured while trying to perform action:\n"+XMLHttpRequest.status+": "+textStatus);
								},
								success: function(data){
									info_dialog.dialog("close");
									if (data) {
										pines.notice("Successfully installed the package '"+name+"'.");
										location.reload(true);
									} else
										pines.notice("The package '"+name+"' could not be installed.");
								}
							});
						});
					}
				});
			}
		};
		var buttons_installed = {
			"Reinstall": function(){
				var name = $(".package", this).text();
				if (!confirm("Are you sure you want to reinstall the package '"+name+"'?"))
					return;
				info_dialog.dialog("disable");
				pines.com_plaza.ajax_show();
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_plaza', 'package/do')); ?>",
					type: "POST",
					dataType: "json",
					data: {"name": name, "local": "true", "do": "reinstall"},
					complete: function(){
						pines.com_plaza.ajax_hide();
						info_dialog.dialog("enable");
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to perform action:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						info_dialog.dialog("close");
						if (data) {
							pines.notice("Successfully reinstalled the package '"+name+"'.");
							location.reload(true);
						} else
							pines.notice("The package '"+name+"' could not be reinstalled. Is the same version still in the repository?");
					}
				});
			},
			"Remove": function(){
				var name = $(".package", this).text();
				pines.com_plaza.ajax_show();
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_plaza', 'package/changes')); ?>",
					type: "POST",
					dataType: "json",
					data: {"name": name, "local": "true", "do": "remove"},
					complete: function(){
						pines.com_plaza.ajax_hide();
						info_dialog.dialog("enable");
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to calculate changes:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (!data) {
							alert("Could not determine required changes.");
							return;
						}
						if (!data.possible) {
							alert("It is not possible to remove this package.");
							return;
						}
						pines.com_plaza.confirm_changes({
							"changes": "The following changes are required to remove the package '"+name+"'.",
							"nochanges": "Are you sure you want to remove the package '"+name+"'?"
						}, data, function(){
							info_dialog.dialog("disable");
							pines.com_plaza.ajax_show();
							$.ajax({
								url: "<?php echo addslashes(pines_url('com_plaza', 'package/do')); ?>",
								type: "POST",
								dataType: "json",
								data: {"name": name, "local": "true", "do": "remove"},
								complete: function(){
									pines.com_plaza.ajax_hide();
									info_dialog.dialog("enable");
								},
								error: function(XMLHttpRequest, textStatus){
									pines.error("An error occured while trying to perform action:\n"+XMLHttpRequest.status+": "+textStatus);
								},
								success: function(data){
									info_dialog.dialog("close");
									if (data) {
										pines.notice("Successfully removed the package '"+name+"'.");
										location.reload(true);
									} else
										pines.notice("The package '"+name+"' could not be removed.");
								}
							});
						});
					}
				});
			}
		};
		var buttons_upgradable = {
			"Upgrade": function(){
				var name = $(".package", this).text();
				pines.com_plaza.ajax_show();
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_plaza', 'package/changes')); ?>",
					type: "POST",
					dataType: "json",
					data: {"name": name, "do": "upgrade"},
					complete: function(){
						pines.com_plaza.ajax_hide();
						info_dialog.dialog("enable");
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to calculate changes:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (!data) {
							alert("Could not determine required changes.");
							return;
						}
						if (!data.possible) {
							alert("It is not possible to upgrade this package. Check its dependencies to see if there are any PHP extenstions you need to install to provide any required functions or classes.");
							return;
						}
						pines.com_plaza.confirm_changes({
							"changes": "The following changes are required to upgrade the package '"+name+"'.",
							"nochanges": "Are you sure you want to upgrade the package '"+name+"'?"
						}, data, function(){
							info_dialog.dialog("disable");
							pines.com_plaza.ajax_show();
							$.ajax({
								url: "<?php echo addslashes(pines_url('com_plaza', 'package/do')); ?>",
								type: "POST",
								dataType: "json",
								data: {"name": name, "do": "upgrade"},
								complete: function(){
									pines.com_plaza.ajax_hide();
									info_dialog.dialog("enable");
								},
								error: function(XMLHttpRequest, textStatus){
									pines.error("An error occured while trying to perform action:\n"+XMLHttpRequest.status+": "+textStatus);
								},
								success: function(data){
									info_dialog.dialog("close");
									if (data) {
										pines.notice("Successfully upgraded the package '"+name+"'.");
										location.reload(true);
									} else
										pines.notice("The package '"+name+"' could not be upgraded.");
								}
							});
						});
					}
				});
			},
			"Remove": buttons_installed.Remove
		};

		var info_dialog;
		package_grid.delegate("tbody tr", "click", function(){
			var cur_row = $(this);
			var name = cur_row.pgrid_get_value(2);
			var publisher = cur_row.pgrid_get_value(3);
			var installed = (cur_row.pgrid_get_value(5) != "");
			var upgradable = (cur_row.pgrid_get_value(7) == "Yes");
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_plaza', 'package/infodialog')); ?>",
				type: "POST",
				dataType: "html",
				data: {"name": name, "local": "false", "publisher": publisher},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve info:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (!data) {
						pines.error("The server returned an unexpected value.");
						return;
					}
					info_dialog = $("<div title=\"Package Info for "+name+"\" />").appendTo("body").html(data).dialog({
						modal: true,
						width: "600px",
						buttons: (installed ? (upgradable ? buttons_upgradable : buttons_installed) : buttons_new)
					});
				}
			});
		});
	});
	// ]]>
</script>
<div>
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>Name</th>
				<th>Package</th>
				<th>Publisher</th>
				<th>Author</th>
				<th>Installed Version</th>
				<th>Latest Version</th>
				<th>Upgrade Available</th>
				<th>Type</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->index['packages'] as $key => $package) {
				if (isset($this->service) && !in_array($this->service, (array) $package['services']))
					continue;
				?>
			<tr>
				<td><?php echo htmlspecialchars($package['name']); ?></td>
				<td><?php echo htmlspecialchars($package['package']); ?></td>
				<td><?php echo htmlspecialchars($package['publisher']); ?></td>
				<td><?php echo htmlspecialchars($package['author']); ?></td>
				<td><?php echo htmlspecialchars($this->db['packages'][$key]['version']); ?></td>
				<td><?php echo htmlspecialchars($package['version']); ?></td>
				<td><?php echo isset($this->db['packages'][$key]['version']) ? (version_compare($package['version'], $this->db['packages'][$key]['version']) ? 'Yes' : 'No') : ''; ?></td>
				<td><?php switch($package['type']) {
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
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>