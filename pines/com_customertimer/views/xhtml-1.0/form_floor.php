<?php
/**
 * Provides a form for the user to edit a floor.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Floor' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide floor details in this form.';
?>
<style type="text/css">
	/* <![CDATA[ */
	.station_layout {
		position: relative;
		border: 1px solid;
	}
	.station_layout .station_layout_bg {
		float: left;
		width: 98%;
		height: auto;
	}
	.station_layout .station {
		position: absolute;
		background-image: none;
	}
	.station_layout .station .station_id {
		display: block;
		margin: 5px;
		font: bold large sans-serif;
	}
	/* ]]> */
</style>
<form enctype="multipart/form-data" class="pform" method="post" id="floor_details" action="<?php echo htmlentities(pines_url('com_customertimer', 'savefloor')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			var station_floor = $("#floor_tabs .station_layout .station_floor");
			var station_input = $("#floor_tabs input[name=stations]");

			var stations = JSON.parse("<?php echo addslashes(json_encode($this->entity->stations)); ?>");

			// Remove all the DOM elements.
			function remove_station_elements() {
				station_floor.find("div.station").remove();
				$.each(stations, function(station_id, station){
					if (station.element)
						delete station.element;
				});
			}

			// Make buttons for each station.
			function update_layout() {
				remove_station_elements();
				$.each(stations, function(station_id, station){
					station.element = $("<div />", {
						"class": "station",
						"css": {
							"left": (station.left*100)+"%",
							"top": (station.top*100)+"%",
							"width": (station.width*100)+"%",
							"height": (station.height*100)+"%"
						},
						"html": $("<span />", {
							"class": "station_id",
							"html": station_id
						}),
						"dblclick": function(){
							var name;
							do {
								name = prompt("Change Station's Name:", station_id);
							} while (name == "");
							if (name == null || name == "")
								return;
							stations[name] = station;
							delete stations[station_id];
							update_layout();
						}
					})
					.button()
					.appendTo(station_floor)
					.draggable({
						"stop": function(){
							station.left = $(this).position().left/$(this).parent().parent().width();
							station.top = $(this).position().top/$(this).parent().parent().height();
							station.width = $(this).width()/$(this).parent().parent().width();
							station.height = $(this).height()/$(this).parent().parent().height();
						}
					})
					.resizable({
						"stop": function(){
							station.left = $(this).position().left/$(this).parent().parent().width();
							station.top = $(this).position().top/$(this).parent().parent().height();
							station.width = $(this).width()/$(this).parent().parent().width();
							station.height = $(this).height()/$(this).parent().parent().height();
						}
					});
				});
			}

			$("#floor_tabs").tabs();
			$("#floor_tabs .station_layout_buttonset").buttonset();
			// Save the station object to the hidden input.
			$("#floor_tabs .station_layout_save").button().click(function(){
				var stationobject = $.extend({}, stations);
				$.each(stationobject, function(){
					if (this.element)
						delete this.element;
				});
				station_input.val(JSON.stringify(stationobject));
				alert("Saved layout.");
			});
			// Revert the changes to the station object since the last save.
			$("#floor_tabs .station_layout_revert").button().click(function(){
				stations = JSON.parse(station_input.val());
				alert("Reverted layout.");
				update_layout();
			});
			// Import a station object.
			$("#floor_tabs .station_layout_import").button().click(function(){
				$("<div />", {
					"title": "Station Layout Import",
					"html": $("<textarea />", {
						"css": {
							"width": "100%",
							"height": "440px"
						}
					})
				}).dialog({
					"width": "640px",
					"modal": true,
					"buttons": {
						"Import": function(){
							var dialog = $(this);
							try {
								var newdata = JSON.parse(dialog.find("textarea").val());
							} catch(err) {
								alert("Could not interpret data!");
								return;
							}
							if (typeof newdata == "object") {
								stations = newdata;
								alert("Import complete.");
								dialog.dialog("close").remove();
								update_layout();
							} else {
								alert("Data is not formatted correctly!");
							}
						}
					}
				});
			});
			// Export the station object.
			$("#floor_tabs .station_layout_export").button().click(function(){
				var stationobject = $.extend({}, stations);
				$.each(stationobject, function(){
					if (this.element)
						delete this.element;
				});
				$("<div />", {
					"title": "Station Layout Export",
					"html": $("<textarea />", {
						"css": {
							"width": "100%",
							"height": "440px"
						},
						"val": JSON.stringify(stationobject),
						"click": function(){
							if (this.select)
								this.select();
						}
					})
				}).dialog({
					"width": "640px",
					"modal": true
				});
			});
			// Add a new station to the layout.
			$("#floor_tabs .station_layout_add").button().click(function(){
				var name;
				do {
					name = prompt("New Station's Name:");
				} while (name == "");
				if (name == null || name == "")
					return;
				stations[name] = {"left": .45, "top": .45, "width": .1, "height": .1};
				update_layout();
			});
			// Delete all the stations in the layout.
			$("#floor_tabs .station_layout_clear").button().click(function(){
				remove_station_elements();
				stations = {};
			});

			update_layout();
		});
		// ]]>
	</script>
	<div id="floor_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_layout">Station Layout</a></li>
		</ul>
		<div id="tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
					<?php if (isset($this->entity->uid)) { ?>
				<span>Created By: <span class="date"><?php echo $pines->user_manager->get_username($this->entity->uid); ?></span></span>
				<br />
					<?php } ?>
				<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
				<br />
				<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Name</span>
					<input class="field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Enabled</span>
					<input class="field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element">
				<span class="label">Description</span>
				<textarea class="field ui-widget-content" rows="3" cols="35" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<?php if (isset($this->entity->background)) { ?>
			<div class="element full_width">
				<span class="label">Current Background</span>
				<div class="group">
					<span class="field"><img src="<?php echo $pines->config->rela_location.$this->entity->get_background(); ?>" alt="Floor Background" style="max-width: 100%; width: auto; height: auto;" /></span>
					<br />
					<label><span class="field"><input class="field ui-widget-content" type="checkbox" name="remove_background" value="ON" />Remove this background.</span></label>
				</div>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Change Background</span>
					<input class="field ui-widget-content" type="file" name="background" /></label>
			</div>
			<br class="clearing" />
		</div>
		<div id="tab_layout">
			<div class="ui-widget-header ui-corner-all" style="padding: 10px;">
				<span class="station_layout_buttonset">
					<button type="button" class="station_layout_save"><span style="display: block; width: 32px; height: 32px;" class="picon_32x32_actions_document-save"></span> Save</button>
					<button type="button" class="station_layout_revert"><span style="display: block; width: 32px; height: 32px;" class="picon_32x32_actions_document-revert"></span> Revert</button>
				</span>
				<span class="station_layout_buttonset">
					<button type="button" class="station_layout_import"><span style="display: block; width: 32px; height: 32px;" class="picon_32x32_actions_go-down"></span> Import</button>
					<button type="button" class="station_layout_export"><span style="display: block; width: 32px; height: 32px;" class="picon_32x32_actions_go-up"></span> Export</button>
				</span>
				<span class="station_layout_buttonset">
					<button type="button" class="station_layout_add"><span style="display: block; width: 32px; height: 32px;" class="picon_32x32_actions_list-add"></span> Add</button>
					<button type="button" class="station_layout_clear"><span style="display: block; width: 32px; height: 32px;" class="picon_32x32_actions_edit-clear"></span> Clear</button>
				</span>
			</div>
			<br class="clearing" />
			<div class="station_layout">
				<img src="<?php echo $pines->config->rela_location.$this->entity->get_background(); ?>" class="station_layout_bg" alt="Station Layout" />
				<div class="station_floor"></div>
				<br class="clearing" />
			</div>
			<input type="hidden" name="stations" value="<?php echo htmlentities(json_encode($this->entity->stations)); ?>" />
			<br class="clearing" />
		</div>
	</div>
	<br />
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_customertimer', 'listfloors')); ?>');" value="Cancel" />
	</div>
</form>