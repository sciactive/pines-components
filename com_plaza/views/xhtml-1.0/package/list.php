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
		var package_grid = $("#p_muid_grid").pgrid();
		var info_dialog = $("#p_muid_info").dialog({
			modal: true,
			autoOpen: false,
			width: "600px"
		});

		package_grid.delegate("tbody tr", "click", function(){
			var cur_row = $(this);
			var name = cur_row.pgrid_get_value(2);
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_plaza', 'package/infojson')); ?>",
				type: "GET",
				dataType: "json",
				data: {"name": name},
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
			info_dialog.find(".name").text(data.name);
			info_dialog.find(".author").text(data.author);
			info_dialog.find(".version .text").text(data.version);
			if (data.license.indexOf("http://") == 0)
				info_dialog.find(".license .pf-field").html("<a href=\""+data.license+"\" onclick=\"window.open(this.href); return false;\">"+data.license+"</a>");
			else
				info_dialog.find(".license .pf-field").text(data.license);
			if (data.website.indexOf("http://") == 0)
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
				<td><?php echo htmlentities($package['name']); ?></td>
				<td><?php echo htmlentities($key); ?></td>
				<td><?php echo htmlentities($package['author']); ?></td>
				<td><?php echo htmlentities($package['version']); ?></td>
				<td><?php echo htmlentities($package['type']); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<div id="p_muid_info" style="display: none;">
		<div class="pf-form">
			<div class="pf-element pf-heading">
				<h1><span class="name"></span></h1>
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