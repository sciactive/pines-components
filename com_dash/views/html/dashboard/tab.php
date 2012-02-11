<?php
/**
 * Shows a dashboard tab.
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
$pines->com_inuitcss->load();
$max_columns = $pines->config->com_inuitcss->grid_columns;
?>
<style type="text/css" scoped="scoped">
	/* <![CDATA[ */
	#p_muid_tab {
		position: relative;
	}
	#p_muid_tab.maximized {
		min-height: 500px;
	}
	#p_muid_tab .p_muid_loading {
		height: 32px;
		width: 32px;
		margin: 0 auto;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var allow_update = false;
		$("#p_muid_tab .column").sortable({
			tolerance: "pointer",
			placeholder: "ui-state-highlight placeholder",
			forcePlaceholderSize: true,
			helper: "clone",
			connectWith: "#p_muid_tab .column",
			dropOnEmpty: true,
			handle: "> .ui-widget-header",
			beforeStop: function(){
				// This ensures that if 2 columns are updated during sort, only
				// 1 will fire the update.
				allow_update = true;
			},
			stop: function(){
				// If update doesn't actually fire, reset allow_update.
				allow_update = false;
			},
			update: function(){
				if (!allow_update)
					return;
				allow_update = false;
				var struct = {};
				$("#p_muid_tab > .grids > .column").each(function(){
					var column = $(this);
					var col_key = column.children(".key").text();
					struct[col_key] = [];
					column.children(".object").each(function(){
						var widget = $(this);
						var wid_key = widget.children(".key").text();
						struct[col_key].push(wid_key);
					});
				});
				$.ajax({
					url: <?php echo json_encode(pines_url('com_dash', 'dashboard/tabsaveorder_json')); ?>,
					type: "POST",
					dataType: "json",
					data: {"key": <?php echo json_encode($this->key); ?>, "order": JSON.stringify(struct)},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to save the sort order:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (!data) {
							pines.error("Sort order could not be saved.");
							return;
						}
					}
				});
			}
		});
		$("#p_muid_tab").on("click", ".max_widget", function(){
			// Maximize widget.
			$(this).closest(".object").add("#p_muid_tab").toggleClass("maximized");
		}).on("click", ".min_widget", function(){
			// Minimize widget.
			$(this).toggleClass("ui-icon-triangle-1-n ui-icon-triangle-1-s").closest(".object").toggleClass("minimized");
		}).on("click", ".edit_widget", function(){
			// Bring up the edit menu.
			var menu = $(this).closest(".controls").children(".edit_widget_menu");
			menu.show().focus();
		}).on("mouseleave", ".controls", function(){
			// Close the edit menu.
			$(this).children(".edit_widget_menu").hide();
		}).on("click", ".edit_widget_menu a", function(){
			// Close the edit menu.
			$(this).parent().hide();
		}).on("click", ".edit_widget_menu .widget_refresh", function(){
			// Refresh widget.
			reload_widget($(this).closest(".object"));
		}).on("click", ".edit_widget_menu .widget_options", function(){
			// Edit widget options with an AJAX form.
			var widget = $(this).closest(".object");
			var options = JSON.parse(widget.children(".options").html());
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetoptions_form')); ?>,
				type: "POST",
				dataType: "html",
				data: {"key": widget.children(".key").text()},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "false") {
						alert("This widget has no options.");
						return;
					}
					pines.pause();
					var form = $("<div title=\"Widget Options\"></div>")
					.html('<form method="post" action="">'+data+"</form><br />");
					form.find("form").submit(function(){
						form.dialog('option', 'buttons').Done();
						return false;
					});
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						width: "auto",
						open: function(){
							if (options.length) {
								$.each(options, function(i, cur_option){
									var name = cur_option.name;
									var value = cur_option.value;
									form.find(":input:not(:radio, :checkbox)[name="+name+"]").val(value).change();
									form.find(":input:radio[name="+name+"][value="+value+"]").attr("checked", "checked").change();
									if (value == "")
										form.find(":input:checkbox[name="+name+"]").removeAttr("checked").change();
									else
										form.find(":input:checkbox[name="+name+"][value="+value+"]").attr("checked", "checked").change();
								});
							}
						},
						buttons: {
							"Done": function(){
								options = [];
								form.find(":input").each(function(){
									var cur_input = $(this);
									if (cur_input.is(":radio:not(:checked)"))
										return;
									var cur_value = cur_input.val();
									if (cur_input.is(":checkbox:not(:checked)"))
										cur_value = "";
									options.push({
										name: cur_input.attr("name"),
										value: cur_value
									});
								});
								widget.children(".options").html(pines.safe(JSON.stringify(options)));
								save_options(widget);
								form.dialog('close');
							}
						}
					});
					if (form.find("textarea.peditor, textarea.peditor_simple").length)
						$("head").append(<?php echo json_encode($editor_html); ?>);
					pines.play();
				}
			});
		}).on("click", ".edit_widget_menu .widget_remove", function(){
			// Remove a widget.
			if (!confirm("Are you sure you want to remove this widget and its configuration?\nThis cannot be undone."))
				return;
			var widget = $(this).closest(".object");
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetremove_json')); ?>,
				type: "POST",
				dataType: "json",
				data: {"key": widget.children(".key").text()},
				beforeSend: function(){
					widget.find("> .ui-widget-header > .title").text("Deleting...");
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to remove the widget:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						pines.error("The widget could not be removed.");
						return;
					}
					widget.remove();
				}
			});
		}).on("click", ".edit_buttons", function(){
			// Edit the button bar.
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/buttons_form')); ?>,
				type: "POST",
				dataType: "html",
				data: {"key": <?php echo json_encode($this->key); ?>},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "false") {
						alert("The list of buttons could not be retrieved.");
						return;
					}
					pines.pause();
					var form = $("<div title=\"Edit Buttons\"></div>")
					.html('<form method="post" action="">'+data+"</form>");
					form.find("form").submit(function(){
						form.dialog('option', 'buttons').Done();
						return false;
					});
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						width: 800,
						close: function(){
							form.remove();
						},
						buttons: {
							"Cancel": function(){
								form.dialog('close');
							},
							"Done": function(){
								var struct = [];
								form.find(".pf-form .buttons").children().each(function(){
									var button = $(this);
									if (button.hasClass("separator")) {
										struct.push("separator");
										return;
									}
									struct.push({
										"component": button.find(".component").text(),
										"button": button.find(".button_name").text()
									});
								});
								$.ajax({
									url: <?php echo json_encode(pines_url('com_dash', 'dashboard/buttonssave_json')); ?>,
									type: "POST",
									dataType: "json",
									data: {"key": <?php echo json_encode($this->key); ?>, "buttons": JSON.stringify(struct)},
									error: function(XMLHttpRequest, textStatus){
										pines.error("An error occured while trying to save buttons:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
									},
									success: function(data){
										if (!data) {
											pines.error("Error saving buttons.");
											return;
										}
										var tabs = $("#p_muid_tab").closest(".ui-tabs");
										var selected = tabs.tabs("option", "selected");
										tabs.tabs("load", selected);
									}
								});
								form.dialog('close');
							}
						}
					});
					pines.play();
				}
			});
		});
		var save_options = function(widget){
			// Save a widget's options.
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetsaveoptions_json')); ?>,
				type: "POST",
				dataType: "json",
				data: {"key": widget.children(".key").text(), "options": widget.children(".options").text()},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to save options:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						pines.error("Error saving options.");
						return;
					}
					reload_widget(widget);
				}
			});
		};

		$("#p_muid_tab > .buttons a").button();
		$("#p_muid_add_widget").button().click(function(){
			// Add new widget(s).
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetadd_form')); ?>,
				type: "POST",
				dataType: "html",
				data: {},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (data == "false") {
						alert("The list of widgets could not be retrieved.");
						return;
					}
					pines.pause();
					var form = $("<div title=\"Add Widgets\"></div>")
					.html('<form method="post" action="">'+data+"</form>");
					form.find("form").submit(function(){
						form.dialog('option', 'buttons').Done();
						return false;
					});
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						width: 800,
						close: function(){
							form.remove();
						},
						buttons: {
							"Done": function(){
								var struct = [];
								form.find(".pf-form .widget_type.ui-selected").each(function(){
									var widget = $(this);
									struct.push({
										"component": widget.children(".component").text(),
										"widget": widget.children(".widget_name").text()
									});
								});
								$.ajax({
									url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetadd_json')); ?>,
									type: "POST",
									dataType: "json",
									data: {"key": <?php echo json_encode($this->key); ?>, "widgets": JSON.stringify(struct)},
									error: function(XMLHttpRequest, textStatus){
										pines.error("An error occured while trying to add widgets:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
									},
									success: function(data){
										if (!data) {
											pines.error("Error adding widgets.");
											return;
										}
										var tabs = $("#p_muid_tab").closest(".ui-tabs");
										var selected = tabs.tabs("option", "selected");
										tabs.tabs("load", selected);
									}
								});
								form.dialog('close');
							}
						}
					});
					pines.play();
				}
			});
		});

		var reload_widget = function(widget){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widget_json')); ?>,
				type: "POST",
				dataType: "json",
				data: {"key": widget.children(".key").text()},
				beforeSend: function(){
					widget.find("> .ui-widget-header .title").text("Loading...").end().children(".content").html("<div class=\"p_muid_loading picon picon-32 picon-throbber\"></div>");
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to load the widget:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					widget.find("> .ui-widget-header .title").text("Error").end().children(".content").html("An error occured while trying to load the widget:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data)
						return;
					pines.pause();
					widget.find("> .ui-widget-header .title").html(data.title == "" ? "Untitled Widget" : data.title).end().children(".content").html(data.content);
					pines.play();
				}
			});
		};
		// Load the widgets.
		$("#p_muid_tab .object").each(function(){
			reload_widget($(this));
		});
	});
	// ]]>
</script>
<div id="p_muid_tab">
	<div class="buttons ui-widget-content ui-corner-all">
		<div class="controls">
			<span class="edit_buttons ui-icon ui-icon-gear" title="Configure Buttons"></span>
		</div>
		<?php foreach ($this->tab['buttons'] as $cur_button) {
			if ($cur_button == 'separator') { ?>
		<div class="separator ui-widget-content"><span>&nbsp;</span></div>
			<?php } else {
				$cur_def = $pines->com_dash->get_button_def($cur_button);
				// Check its conditions.
				foreach ((array) $cur_def['depends'] as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value))
						continue 2;
				} ?>
		<a class="ui-state-default ui-corner-all" href="<?php echo htmlspecialchars($cur_def['href']); ?>" title="<?php echo htmlspecialchars($cur_def['description']); ?>">
			<span class="picon picon-32 <?php echo htmlspecialchars($cur_def['class']); ?>"><?php echo htmlspecialchars($cur_def['text']); ?></span>
		</a>
		<?php } } ?>
	</div>
	<div class="grids">
		<?php foreach ($this->tab['columns'] as $cur_c_key => $cur_column) {
			$col_style = htmlspecialchars($cur_column['size'] < 1 ? floor($max_columns * $cur_column['size']) : $cur_column['size']); ?>
		<div class="grid-<?php echo $col_style; ?> column">
			<div class="key" style="display: none;"><?php echo htmlspecialchars($cur_c_key); ?></div>
			<?php foreach ($cur_column['widgets'] as $cur_w_key => $cur_widget) {
				// Get the widget definition.
				$cur_def = $pines->com_dash->get_widget_def($cur_widget);
				// Check its conditions.
				foreach ((array) $cur_def['widget']['depends'] as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value))
						continue 2;
				} ?>
			<div class="object ui-widget-content">
				<div class="key" style="display: none;"><?php echo htmlspecialchars($cur_w_key); ?></div>
				<div class="options" style="display: none;"><?php echo htmlspecialchars(json_encode($cur_widget['options'])); ?></div>
				<div class="ui-widget-header ui-helper-clearfix">
					<span class="title">Loading...</span>
					<div class="controls ui-helper-clearfix">
						<span class="edit_widget ui-icon ui-icon-gear" title="Edit this Widget"></span>
						<span class="max_widget ui-icon ui-icon-arrow-4-diag" title="Maximize this Widget"></span>
						<span class="min_widget ui-icon ui-icon-triangle-1-n" title="Minimize this Widget"></span>
						<div class="edit_widget_menu ui-widget-content" style="display: none;">
							<a href="javascript:void(0);" class="widget_options">Edit Options</a>
							<br />
							<a href="javascript:void(0);" class="widget_refresh">Refresh Widget</a>
							<br /><br />
							<a href="javascript:void(0);" class="widget_remove">Remove Widget</a>
						</div>
					</div>
				</div>
				<div class="content">
					<div class="p_muid_loading picon picon-32 picon-throbber"></div>
				</div>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<div class="add_widget">
		<button id="p_muid_add_widget" class="ui-state-default ui-corner-all">Add Widgets</button>
	</div>
</div>