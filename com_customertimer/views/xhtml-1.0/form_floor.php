<?php
/**
 * Provides a form for the user to edit a floor.
 *
 * @package Pines
 * @subpackage com_customertimer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Floor' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide floor details in this form.';
$pines->uploader->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_form .station_layout {
		position: relative;
		border: 1px solid;
	}
	#p_muid_form .station_layout .station_layout_bg {
		float: left;
		width: 98%;
		height: auto;
	}
	#p_muid_form .station_layout .station {
		position: absolute;
		background-image: none;
	}
	#p_muid_form .station_layout .station .station_id {
		display: block;
		margin: 5px;
		font: bold large sans-serif;
	}
	#p_muid_tab_layout button .picon {
		display: block;
		min-width: 32px;
		height: 32px;
		background-repeat: no-repeat;
		background-position: top center;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var station_floor = $("#p_muid_floor_tabs .station_layout .station_floor");
		var station_input = $("#p_muid_floor_tabs input[name=stations]");

		var stations = JSON.parse("<?php echo addslashes(json_encode($this->entity->stations, JSON_FORCE_OBJECT)); ?>");

		// Remove all the DOM elements.
		var remove_station_elements = function(){
			station_floor.find("div.station").remove();
			$.each(stations, function(station_id, station){
				if (station.element)
					delete station.element;
			});
		};

		// Make buttons for each station.
		var update_layout = function(){
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
		};

		$("#p_muid_floor_tabs").tabs();
		$("#p_muid_floor_tabs .station_layout_buttonset").buttonset();
		// Save the station object to the hidden input.
		$("#p_muid_floor_tabs .station_layout_save").button().click(function(){
			var stationobject = $.extend({}, stations);
			$.each(stationobject, function(){
				if (this.element)
					delete this.element;
			});
			station_input.val(JSON.stringify(stationobject));
			alert("Saved layout.");
		});
		// Revert the changes to the station object since the last save.
		$("#p_muid_floor_tabs .station_layout_revert").button().click(function(){
			stations = JSON.parse(station_input.val());
			alert("Reverted layout.");
			update_layout();
		});
		// Import a station object.
		$("#p_muid_floor_tabs .station_layout_import").button().click(function(){
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
		$("#p_muid_floor_tabs .station_layout_export").button().click(function(){
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
		$("#p_muid_floor_tabs .station_layout_add").button().click(function(){
			var name;
			do {
				name = prompt("New Station's Name:");
			} while (name == "");
			if (name == null || name == "")
				return;
			stations[name] = {"left": .45, "top": .45, "width": .1, "height": .1};
			update_layout();
		});
		// Remove a station from the layout.
		$("#p_muid_floor_tabs .station_layout_remove").button().click(function(){
			var name;
			do {
				name = prompt("Station's Name:");
			} while (name == "");
			if (name == null || name == "")
				return;
			delete stations[name];
			update_layout();
		});
		// Delete all the stations in the layout.
		$("#p_muid_floor_tabs .station_layout_clear").button().click(function(){
			remove_station_elements();
			stations = {};
		});

		update_layout();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_customertimer', 'savefloor')); ?>">
	<div id="p_muid_floor_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_layout">Station Layout</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo "{$this->entity->user->name} [{$this->entity->user->username}]"; ?></span></div>
				<div>Group: <span class="date"><?php echo "{$this->entity->group->name} [{$this->entity->group->groupname}]"; ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<span class="pf-label">Description</span>
				<textarea class="pf-field ui-widget-content" rows="3" cols="35" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						$("#p_muid_form input[name=background]").change(function(){
							$("#p_muid_form img.station_layout_bg").attr("src", $(this).val());
						});
					});
					// ]]>
				</script>
				<label><span class="pf-label">Background</span>
					<span class="pf-note">See the Station Layout tab to preview the background.</span>
					<input class="pf-field ui-widget-content puploader" type="text" name="background" value="<?php echo $this->entity->background; ?>" /></label>
			</div>
			<?php /* <div class="pf-element pf-full-width">
				<span class="pf-label">Background Preview</span>
				<div class="pf-group">
					<span class="pf-field"><img class="station_layout_bg" src="<?php echo $this->entity->background; ?>" alt="Floor Background" style="max-width: 100%; width: auto; height: auto; border: 1px dashed black;" /></span>
				</div>
			</div> */ ?>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_layout">
			<div class="ui-widget-header ui-corner-all" style="padding: 10px;">
				<span class="station_layout_buttonset">
					<button type="button" class="station_layout_save"><span class="picon picon-32 picon-document-save"></span> Save</button>
					<button type="button" class="station_layout_revert"><span class="picon picon-32 picon-document-revert"></span> Revert</button>
				</span>
				<span class="station_layout_buttonset">
					<button type="button" class="station_layout_import"><span class="picon picon-32 picon-document-import"></span> Import</button>
					<button type="button" class="station_layout_export"><span class="picon picon-32 picon-document-export"></span> Export</button>
				</span>
				<span class="station_layout_buttonset">
					<button type="button" class="station_layout_add"><span class="picon picon-32 picon-list-add"></span> Add</button>
					<button type="button" class="station_layout_remove"><span class="picon picon-32 picon-list-remove"></span> Remove</button>
					<button type="button" class="station_layout_clear"><span class="picon picon-32 picon-edit-clear"></span> Clear</button>
				</span>
			</div>
			<br class="pf-clearing" />
			<div class="station_layout">
				<img class="station_layout_bg" src="<?php echo $this->entity->background; ?>" alt="Floor Background" />
				<div class="station_floor"></div>
				<br class="pf-clearing" />
			</div>
			<input type="hidden" name="stations" value="<?php echo htmlentities(json_encode($this->entity->stations, JSON_FORCE_OBJECT)); ?>" />
			<br class="pf-clearing" />
		</div>
	</div>
	<br />
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_customertimer', 'listfloors')); ?>');" value="Cancel" />
	</div>
</form>