<?php
/**
 * Shows a dashboard tab.
 *
 * @package Components\dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$pines->com_bootstrap->load();
$max_columns = $pines->config->com_bootstrap->grid_columns;
?>
<style type="text/css" scoped="scoped">
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
</style>
<script type="text/javascript">
	pines(function(){
		<?php if ($this->editable) { ?>
		var allow_update = false;
		$("#p_muid_tab .column").sortable({
			tolerance: "pointer",
			placeholder: "ui-state-highlight placeholder",
			forcePlaceholderSize: true,
			connectWith: "#p_muid_tab .column",
			dropOnEmpty: true,
			handle: "> .widget_header",
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
				$("#p_muid_tab > .row-fluid > .column").each(function(){
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
					url: <?php echo json_encode(pines_url('com_dash', 'dashboard/tabsaveorder_json', array('id' => (string) $this->entity->guid))); ?>,
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
		<?php } ?>
		var tab = $("#p_muid_tab").on("click", ".max_widget", function(){
			// Maximize widget.
			$(this).toggleClass("icon-resize-full icon-resize-small").closest(".object").add("#p_muid_tab").toggleClass("maximized");
		}).on("click", ".min_widget", function(){
			// Minimize widget.
			$(this).toggleClass("icon-chevron-up icon-chevron-down").closest(".object").toggleClass("minimized");
		}).on("mouseleave", ".object", function(){
			// Close the edit menu.
			$(this).find(".controls").removeClass("open");
		}).on("click", ".widget_refresh", function(){
			// Refresh widget.
			reload_widget($(this).closest(".object"));
		});
		<?php if ($this->editable) { ?>
		tab.on("click", ".edit_widget_menu .widget_options", function(){
			// Edit widget options with an AJAX form.
			var widget = $(this).closest(".object");
			var options = JSON.parse(widget.children(".options").html());
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetoptions_form', array('id' => (string) $this->entity->guid))); ?>,
				type: "POST",
				dataType: "json",
				data: {"key": widget.children(".key").text()},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						alert("This widget has no options.");
						return;
					}
					pines.pause();
					if (typeof data.head !== "undefined")
						$("head").append(data.head);
					var form = $("<div title=\"Widget Options\"></div>")
					.html('<form method="post" action="">'+data.content+"</form><br />");
					form.find("form").submit(function(){
						form.dialog('option', 'buttons').Done();
						return false;
					});
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						modal: true,
						width: "auto",
						buttons: {
							"Done": function(){
								options = [];
								form.find(":input[name]").each(function(){
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
					pines.play();
				}
			});
		}).on("click", ".edit_widget_menu .widget_remove", function(){
			// Remove a widget.
			if (!confirm("Are you sure you want to remove this widget and its configuration?\nThis cannot be undone."))
				return;
			var widget = $(this).closest(".object");
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetremove_json', array('id' => (string) $this->entity->guid))); ?>,
				type: "POST",
				dataType: "json",
				data: {"key": widget.children(".key").text()},
				beforeSend: function(){
					widget.find("> .widget_header > .title").text("Deleting...");
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
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/buttons_form', array('id' => (string) $this->entity->guid))); ?>,
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
									} else if (button.hasClass("line_break")) {
										struct.push("line_break");
										return;
									}
									struct.push({
										"component": button.find(".component").text(),
										"button": button.find(".button_name").text()
									});
								});
								$.ajax({
									url: <?php echo json_encode(pines_url('com_dash', 'dashboard/buttonssave_json', array('id' => (string) $this->entity->guid))); ?>,
									type: "POST",
									dataType: "json",
									data: {"key": <?php echo json_encode($this->key); ?>, "buttons": JSON.stringify(struct), "buttons_size": form.find("[name=buttons_size]:checked").val()},
									error: function(XMLHttpRequest, textStatus){
										pines.error("An error occured while trying to save buttons:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
									},
									success: function(data){
										if (!data) {
											pines.error("Error saving buttons.");
											return;
										}
										$("#p_muid_tab").closest(".tab-pane").data("tab_loaded", false).data("trigger_link").trigger("show");
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
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetsaveoptions_json', array('id' => (string) $this->entity->guid))); ?>,
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

		$("#p_muid_add_widget").click(function(){
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
					.html('<form method="post" action="">'+data+"</form><br />");
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
									url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widgetadd_json', array('id' => (string) $this->entity->guid))); ?>,
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
										$("#p_muid_tab").closest(".tab-pane").data("tab_loaded", false).data("trigger_link").trigger("show");
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
		<?php } ?>

		var reload_widget = function(widget){
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'dashboard/widget_json', array('id' => (string) $this->entity->guid))); ?>,
				type: "POST",
				dataType: "json",
				data: {"key": widget.children(".key").text()},
				beforeSend: function(){
					widget.find("> .widget_header .title").text("Loading...").end().children(".content").html("<div class=\"p_muid_loading picon picon-32 picon-throbber\"></div>");
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to load the widget:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					widget.find("> .widget_header .title").text("Error").end().children(".content").html("An error occured while trying to load the widget:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data)
						return;
					pines.pause();
					if (typeof data.head !== "undefined")
						$("head").append(data.head);
					widget.find("> .widget_header .title").html(data.title == "" ? "Untitled Widget" : data.title).end().children(".content").html(data.content);
					pines.play();
				}
			});
		};
		// Load the widgets.
		$("#p_muid_tab .object").each(function(){
			reload_widget($(this));
		});
	});
</script>
<div id="p_muid_tab">
	<div class="buttons well <?php echo htmlspecialchars($this->tab['buttons_size']); ?>">
		<?php if ($this->editable) { ?>
		<div class="controls">
			<span class="edit_buttons w_icon icon-cog" title="Configure Buttons"></span>
		</div>
		<?php } foreach ((array) $this->tab['buttons'] as $cur_button) {
			if ($cur_button == 'separator') { ?>
		<a class="separator btn disabled"><span>&nbsp;</span></a>
			<?php } elseif ($cur_button == 'line_break') { ?>
		<a class="line_break btn disabled"><span>&nbsp;</span></a>
			<?php } else {
				$cur_def = $pines->com_dash->get_button_def($cur_button);
				// Check its conditions.
				foreach ((array) $cur_def['depends'] as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value))
						continue 2;
				} ?>
		<a class="btn" href="<?php echo htmlspecialchars($cur_def['href']); ?>" title="<?php echo htmlspecialchars($cur_def['description']); ?>">
			<span class="picon <?php echo $this->tab['buttons_size'] == 'large' ? 'picon-32' : ''; ?> <?php echo htmlspecialchars($cur_def['class']); ?>"><?php echo htmlspecialchars($cur_def['text']); ?></span>
		</a>
		<?php } } ?>
	</div>
	<div class="row-fluid">
		<?php foreach ((array) $this->tab['columns'] as $cur_c_key => $cur_column) {
			$col_style = htmlspecialchars($cur_column['size'] < 1 ? floor($max_columns * $cur_column['size']) : $cur_column['size']); ?>
		<div class="span<?php echo $col_style; ?> column">
			<div class="key" style="display: none;"><?php echo htmlspecialchars($cur_c_key); ?></div>
			<?php foreach ((array) $cur_column['widgets'] as $cur_w_key => $cur_widget) {
				// Get the widget definition.
				$cur_def = $pines->com_dash->get_widget_def($cur_widget);
				// Check its conditions.
				foreach ((array) $cur_def['widget']['depends'] as $cur_type => $cur_value) {
					if (!$pines->depend->check($cur_type, $cur_value))
						continue 2;
				} ?>
			<div class="object well clearfix">
				<div class="key" style="display: none;"><?php echo htmlspecialchars($cur_w_key); ?></div>
				<div class="options" style="display: none;"><?php echo htmlspecialchars(json_encode($cur_widget['options'])); ?></div>
				<div class="alert-info widget_header clearfix"<?php echo $this->editable ? '' : ' style="cursor: default;"'; ?>>
					<span class="title">Loading...</span>
					<div class="controls dropdown clearfix">
						<?php if ($this->editable) { ?>
						<span class="edit_widget w_icon icon-cog" title="Edit this Widget" data-toggle="dropdown"></span>
						<?php } else { ?>
						<span class="widget_refresh w_icon icon-refresh" title="Refresh this Widget"></span>
						<?php } ?>
						<span class="max_widget w_icon icon-resize-full" title="Maximize this Widget"></span>
						<span class="min_widget w_icon icon-chevron-up" title="Minimize this Widget"></span>
						<?php if ($this->editable) { ?>
						<ul class="edit_widget_menu dropdown-menu">
							<li><a href="javascript:void(0);" class="widget_options">Edit Options</a></li>
							<li><a href="javascript:void(0);" class="widget_refresh">Refresh Widget</a></li>
							<li class="divider"></li>
							<li><a href="javascript:void(0);" class="widget_remove">Remove Widget</a></li>
						</ul>
						<?php } ?>
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
	<?php if ($this->editable) { ?>
	<div class="add_widget">
		<button id="p_muid_add_widget" class="btn">Add Widgets</button>
	</div>
	<?php } ?>
</div>