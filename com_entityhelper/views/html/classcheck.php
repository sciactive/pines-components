<?php
/**
 * Show the results of the class check.
 *
 * @package Components\entityhelper
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Class Check';
?>
<style type="text/css">
	#p_muid_classes label, #p_muid_classes input {
		display: inline;
	}
	#p_muid_classes table thead th {
		cursor: pointer;
	}
	#p_muid_classes .sort-icon {
		margin-left: 1em;
	}
</style>
<script type="text/javascript">
	pines(function(){
		$("#p_muid_classes").on("change keyup", "input[name=filter]", function(){
			var filter = $(this).val(), rows = $("table tbody tr", "#p_muid_classes");
			if (filter == "")
				rows.show();
			else
				rows.each(function(){
					var row = $(this);
					if (row.children(":first-child").is(":contains("+filter+")"))
						row.show();
					else
						row.hide();
				});
		}).on("click", "table thead th", function(){
			var column = $(this),
				desc = !!column.find(".icon-chevron-up").length,
				table = column.closest("table"),
				col_num = column.prevAll().length,
				tbody = table.children("tbody"),
				rows = tbody.children("tr");
			tbody.prepend(rows.get().sort(function(a, b){
				return (desc ? -1 : 1) * $("td:eq("+col_num+")", a).text().localeCompare($("td:eq("+col_num+")", b).text());
			}));
			table.find("thead tr .sort-icon").remove();
			column.append('<i class="sort-icon icon-chevron-'+(desc ? "down" : "up")+'"></i>');
		}).find("table thead th:first-child").click();
	});
</script>
<div id="p_muid_classes">
	<p>
		The following table shows whether a class has a custom helper. Classes
		without one will use the default helper.
	</p>
	<p>
		<label>Filter: <input type="text" name="filter" value="" size="24" /></label>
	</p>
	<table class="table table-condensed table-bordered">
		<thead>
			<tr>
				<th>Class</th>
				<th>Custom Helper</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->entity_classes as $cur_class) { ?>
			<tr>
				<td style="font-family: monospace;"><?php echo htmlspecialchars($cur_class); ?></td>
				<td><?php echo in_array($cur_class, $this->no_helper) ? 'No' : 'Yes'; ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</div>