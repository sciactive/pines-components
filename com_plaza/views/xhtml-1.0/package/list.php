<?php
/**
 * Lists packages.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Installed Software';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_plaza/package/list'];
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
				{type: 'button', text: 'Get Software', extra_class: 'picon picon-view-refresh', selection_optional: true, url: '<?php echo addslashes(pines_url('com_plaza', 'package/repository')); ?>'},
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_plaza/package/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var package_grid = $("#p_muid_grid").pgrid(cur_options);
		var info_dialog = $("#p_muid_info").dialog({
			modal: true,
			autoOpen: false,
			width: "600px",
			buttons: {
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
							if (data)
								pines.notice("Successfully reinstalled the package '"+name+"'.");
							else
								pines.notice("The package '"+name+"' could not be reinstalled. Is the same version still in the repository?");
						}
					});
				},
				"Remove": function(){}
			}
		});

		package_grid.delegate("tbody tr", "click", function(){
			var cur_row = $(this);
			var name = cur_row.pgrid_get_value(2);
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_plaza', 'package/infojson')); ?>",
				type: "GET",
				dataType: "json",
				data: {"name": name, "local": "true"},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve info:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (typeof data != "object") {
						pines.error("The server returned an unexpected value.");
						return;
					}
					load_info(data, name);
				}
			});
		});

		var load_info = function(data, name) {
			info_dialog.find(".package").text(name);
			info_dialog.find(".name").text(data.name);
			info_dialog.find(".author").text(data.author);
			info_dialog.find(".version .text").text(data.version);
			if (data.license != null && data.license.indexOf("http://") == 0)
				info_dialog.find(".license .pf-field").html("<a href=\""+data.license+"\" onclick=\"window.open(this.href); return false;\">"+data.license+"</a>");
			else
				info_dialog.find(".license .pf-field").text(data.license);
			if (data.website != null && data.website.indexOf("http://") == 0)
				info_dialog.find(".website .pf-field").html("<a href=\""+data.website+"\" onclick=\"window.open(this.href); return false;\">"+data.website+"</a>");
			else
				info_dialog.find(".website .pf-field").text(data.website);
			if (data.services && data.services.length) {
				info_dialog.find(".services").show();
				info_dialog.find(".services .pf-field").text(data.services.join(", "));
			} else {
				info_dialog.find(".services").hide();
			}
			info_dialog.find(".short_description").text(data.short_description);
			info_dialog.find(".description").text(data.description);
			info_dialog.dialog("option", "title", "Package Info for "+name).dialog("open");
		}
	});
	// ]]>
</script>
<div>
	<table id="p_muid_grid">
		<thead>
			<tr>
				<th>Name</th>
				<th>Package</th>
				<th>Author</th>
				<th>Version</th>
				<th>Type</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->db['packages'] as $key => $package) { ?>
			<tr>
				<td><?php echo htmlspecialchars($package['name']); ?></td>
				<td><?php echo htmlspecialchars($key); ?></td>
				<td><?php echo htmlspecialchars($package['author']); ?></td>
				<td><?php echo htmlspecialchars($package['version']); ?></td>
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
	<div id="p_muid_info" style="display: none;">
		<div class="pf-form">
			<div class="pf-element pf-heading">
				<h1><span class="name"></span><span class="package" style="float: right;"></span></h1>
				<p>
					<span>By <span class="author"></span></span>
					<span class="version">Version <span class="text"></span></span>
				</p>
			</div>
			<div class="pf-element pf-full-width short_description"></div>
			<div class="pf-element services">
				<span class="pf-label">Provides Services</span>
				<span class="pf-field"></span>
			</div>
			<div class="pf-element license">
				<span class="pf-label">License</span>
				<span class="pf-field"></span>
			</div>
			<div class="pf-element website">
				<span class="pf-label">Website</span>
				<span class="pf-field"></span>
			</div>
			<div class="pf-element description"></div>
		</div>
		<br />
	</div>
</div>