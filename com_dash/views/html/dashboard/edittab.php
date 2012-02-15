<?php
/**
 * Edit a tab.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Edit Tab';
$pines->com_bootstrap->load();
$max_columns = $pines->config->com_bootstrap->grid_columns;
$default_column = htmlspecialchars(floor($max_columns / 3));
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_dash', 'dashboard/tabsave')); ?>">
	<style type="text/css" scoped="scoped">
		#p_muid_form .new_column {
			border: none;
			min-height: 100px;
			cursor: pointer;
		}
	</style>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var col_input = $("#p_muid_form [name=columns]");
			var columns = $("#p_muid_cols").sortable({
				axis: "x",
				update: function(){
					update_columns();
				}
			}).delegate(".remove_column", "click", function(){
				var col = $(this).closest(".new_column");
				// Don't remove if it's the last one.
				if (col.siblings().length) {
					col.remove();
					size_columns();
					update_columns();
				}
			}).delegate(".grow_column", "click", function(){
				var max_columns = pines.com_bootstrap_get_columns();
				// Check to make sure we aren't growing too big.
				var total_cols = 0;
				columns.children().each(function(){
					// Add the column width of each column.
					var col = $(this);
					for (var i=1; i<=max_columns; i++) {
						if (col.hasClass("span"+i)) {
							total_cols += i;
							return;
						}
					}
				});
				if (total_cols >= max_columns) {
					alert("You must shrink another column before you can grow this one.");
					return;
				}
				var col = $(this).closest(".new_column"), cur_size = 1;
				// Get the column's size.
				for (var i=1; i<=max_columns; i++) {
					if (col.hasClass("span"+i)) {
						cur_size = i;
						break;
					}
				}
				if (cur_size == max_columns)
					return;
				// Grow the column.
				col.removeClass("span"+cur_size).attr("class", "span"+(cur_size+1)+" "+col.attr("class"));
				update_columns();
			}).delegate(".shrink_column", "click", function(){
				var max_columns = pines.com_bootstrap_get_columns();
				var col = $(this).closest(".new_column"), cur_size = 1;
				// Get the column's size.
				for (var i=1; i<=max_columns; i++) {
					if (col.hasClass("span"+i)) {
						cur_size = i;
						break;
					}
				}
				if (cur_size == 1)
					return;
				// Shrink the column.
				col.removeClass("span"+cur_size).attr("class", "span"+(cur_size-1)+" "+col.attr("class"));
				update_columns();
			});
			$("#p_muid_add_column").click(function(){
				// Add a new column.
				var max_columns = pines.com_bootstrap_get_columns(), all_columns = columns.children();
				if (all_columns.length >= max_columns) {
					alert("You have the maximum number of columns.");
					return;
				}
				columns.append(columns.children(":last-child").clone().removeAttr("id"));
				size_columns();
				update_columns();
			});
			var size_columns = function(){
				// Fit the columns into the width evenly.
				var max_columns = pines.com_bootstrap_get_columns(), all_columns = columns.children();
				for (var i=1; i<=max_columns; i++)
					all_columns.removeClass("span"+i);
				all_columns.attr("class", "span"+(Math.floor(max_columns/all_columns.length))+" "+all_columns.eq(0).attr("class"));
			};
			var update_columns = function(){
				var col_struct = [], max_columns = pines.com_bootstrap_get_columns();
				columns.children().each(function(){
					var cur_col_struct = {}, col = $(this);
					// Get the column's size.
					for (var i=1; i<=max_columns; i++) {
						if (col.hasClass("span"+i)) {
							cur_col_struct.size = i;
							break;
						}
					}
					// Does the column have a key?
					if (col.attr("id"))
						cur_col_struct.key = col.attr("id");
					col_struct.push(cur_col_struct);
				});
				col_input.val(JSON.stringify(col_struct));
			};
			update_columns();

			<?php if ( !empty($this->key) ) { ?>
			$("#p_muid_delete").button().click(function(){
				if (!confirm("Are you sure you want to delete this tab and all of its buttons, widgets, and configuration?\nThis cannot be undone."))
					return;
				$.ajax({
					url: <?php echo json_encode(pines_url('com_dash', 'dashboard/tabremove_json')); ?>,
					type: "POST",
					dataType: "json",
					data: {"key": <?php echo json_encode($this->key); ?>},
					beforeSend: function(){
						$(this).text("Deleting...");
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to delete the tab:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						$(this).text("Delete Tab");
						if (data == "last") {
							alert("This is the only tab, so it can't be deleted.");
							return;
						} else if (!data) {
							pines.error("The tab could not be deleted.");
							return;
						}
						var tabs = $("#p_muid_form").closest(".ui-tabs");
						var selected = tabs.tabs("option", "selected");
						tabs.tabs("select", 0).tabs("remove", selected);
					}
				});
			});
			<?php } ?>
		});

		function p_muid_cancel() {
			var tabs = $("#p_muid_form").closest(".ui-tabs");
			var selected = tabs.tabs("option", "selected");
			var url = tabs.children(".ui-tabs-nav").find(".ui-tabs-selected span").data("old_url");
			tabs.tabs("url", selected, url).tabs("load", selected);
		}
		// ]]>
	</script>
	<div class="pf-element pf-heading">
		<?php if ( !empty($this->key) ) { ?>
		<button class="ui-state-default ui-corner-all" id="p_muid_delete" type="button" style="float: right;">Delete Tab</button>
		<?php } ?>
		<h3>Editing <?php echo isset($this->tab) ? 'Tab ['.htmlspecialchars($this->tab['name']).']' : 'New Tab'; ?></h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->tab['name']); ?>" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Layout</span>
		<span class="pf-field">
			<a href="javascript:void(0);" id="p_muid_add_column">Add a Column</a>
		</span>
		<br /><br />
		<div class="row-fluid" style="margin-bottom: 1em;">
			<div class="span<?php echo htmlspecialchars($max_columns); ?> new_column ui-state-highlight" style="min-height: 40px; line-height: 40px; text-align: center;">Button area.</div>
		</div>
		<div class="row-fluid" id="p_muid_cols">
			<?php if (isset($this->tab['columns'])) { foreach ($this->tab['columns'] as $cur_key => $cur_column) {
				$col_style = htmlspecialchars($cur_column['size'] < 1 ? floor($max_columns * $cur_column['size']) : $cur_column['size']); ?>
			<div class="span<?php echo $col_style; ?> new_column ui-state-highlight" id="<?php echo htmlspecialchars($cur_key); ?>">
				<div style="padding: .4em;">
					<div style="float: right;">
						<a href="javascript:void(0);" class="remove_column">Remove</a>
					</div>
					<a href="javascript:void(0);" class="grow_column">Grow</a> <a href="javascript:void(0);" class="shrink_column">Shrink</a>
					<div style="text-align: center; margin-top: 2em;">Drag me to reorder.</div>
				</div>
			</div>
			<?php } } else { ?>
			<div class="span<?php echo $default_column; ?> new_column ui-state-highlight">
				<div style="padding: .4em;">
					<div style="float: right;">
						<a href="javascript:void(0);" class="remove_column">Remove</a>
					</div>
					<a href="javascript:void(0);" class="grow_column">Grow</a> <a href="javascript:void(0);" class="shrink_column">Shrink</a>
					<div style="text-align: center; margin-top: 2em;">Drag me to reorder.</div>
				</div>
			</div>
			<div class="span<?php echo $default_column; ?> new_column ui-state-highlight">
				<div style="padding: .4em;">
					<div style="float: right;">
						<a href="javascript:void(0);" class="remove_column">Remove</a>
					</div>
					<a href="javascript:void(0);" class="grow_column">Grow</a> <a href="javascript:void(0);" class="shrink_column">Shrink</a>
					<div style="text-align: center; margin-top: 2em;">Drag me to reorder.</div>
				</div>
			</div>
			<div class="span<?php echo $default_column; ?> new_column ui-state-highlight">
				<div style="padding: .4em;">
					<div style="float: right;">
						<a href="javascript:void(0);" class="remove_column">Remove</a>
					</div>
					<a href="javascript:void(0);" class="grow_column">Grow</a> <a href="javascript:void(0);" class="shrink_column">Shrink</a>
					<div style="text-align: center; margin-top: 2em;">Drag me to reorder.</div>
				</div>
			</div>
			<?php } ?>
		</div>
		<input type="hidden" name="columns" value="" />
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( !empty($this->key) ) { ?>
		<input type="hidden" name="key" value="<?php echo htmlspecialchars($this->key); ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<?php if ( !empty($this->key) ) { ?>
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="p_muid_cancel();" value="Cancel" />
		<?php } ?>
	</div>
	<br class="pf-clearing" />
</form>