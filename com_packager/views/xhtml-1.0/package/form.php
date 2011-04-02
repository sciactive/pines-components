<?php
/**
 * Provides a form for the user to edit a package.
 *
 * @package Pines
 * @subpackage com_packager
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Package' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide package details in this form.';
$pines->editor->load();
$pines->uploader->load();
$pines->com_pgrid->load();
$pines->com_ptags->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_sortable {
		list-style-type: none;
		margin: 0;
		padding: 0;
	}
	#p_muid_sortable li {
		margin: 0.1em;
		padding: 5px;
		float: left;
		width: 120px;
		min-height: 120px;
		text-align: center;
		background-image: none;
	}
	#p_muid_sortable img {
		width: 110px;
		height: auto;
		max-height: 110px;
		vertical-align: middle;
		margin: 0;
		padding: 0;
	}
	#p_muid_sortable p, #p_muid_sortable textarea {
		text-align: left;
		margin: .4em 0 0;
		padding: 0;
	}
	#p_muid_form .ui-ptags-tag {
		float: left;
		width: 100%;
		text-align: left;
	}
	#p_muid_form .ui-ptags-tag-container {
		margin: 0;
		padding: 0;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// Conditions
		var conditions = $("#p_muid_form [name=meta_conditions]");
		var conditions_table = $("#p_muid_form .conditions_table");
		var condition_dialog = $("#p_muid_form .condition_dialog");
		var cur_condition = null;

		conditions_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Condition',
					extra_class: 'picon picon-document-new',
					selection_optional: true,
					click: function(){
						cur_condition = null;
						condition_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Edit Condition',
					extra_class: 'picon picon-document-edit',
					double_click: true,
					click: function(e, rows){
						cur_condition = rows;
						condition_dialog.find("select[name=cur_condition_class]").val(rows.pgrid_get_value(1));
						condition_dialog.find("input[name=cur_condition_type]").val(rows.pgrid_get_value(2));
						condition_dialog.find("input[name=cur_condition_value]").val(rows.pgrid_get_value(3));
						condition_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Condition',
					extra_class: 'picon picon-edit-delete',
					click: function(e, rows){
						rows.pgrid_delete();
						update_conditions();
					}
				}
			],
			pgrid_view_height: "200px"
		});

		// Condition Dialog
		condition_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function(){
					var cur_condition_class = condition_dialog.find("select[name=cur_condition_class]").val();
					var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val();
					var cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
					if (cur_condition_type == "") {
						alert("Please provide a type for this condition.");
						return;
					}
					// Is this a duplicate type?
					var dupe = false;
					conditions_table.pgrid_get_all_rows().each(function(){
						if (dupe) return;
						var check_row = $(this);
						if (check_row.pgrid_get_value(1) == cur_condition_class && check_row.pgrid_get_value(2) == cur_condition_type) {
							// If this is the current row being edited, it isn't a duplicate.
							if (!cur_condition || !cur_condition.filter(this).length)
								dupe = true;
						}
					});
					if (dupe) {
						pines.notice('There is already a condition of that type for this class.');
						return;
					}
					if (!cur_condition) {
						var new_condition = [{
							key: null,
							values: [
								cur_condition_class,
								cur_condition_type,
								cur_condition_value
							]
						}];
						conditions_table.pgrid_add(new_condition);
					} else {
						cur_condition.pgrid_set_value(1, cur_condition_class);
						cur_condition.pgrid_set_value(2, cur_condition_type);
						cur_condition.pgrid_set_value(3, cur_condition_value);
					}
					$(this).dialog('close');
				}
			},
			close: function(){
				update_conditions();
			}
		});

		var update_conditions = function(){
			condition_dialog.find("select[name=cur_condition_class]").val("depend");
			condition_dialog.find("input[name=cur_condition_type]").val("");
			condition_dialog.find("input[name=cur_condition_value]").val("");
			conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_conditions();

		$("#p_muid_package_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_packager', 'package/save')); ?>">
	<div id="p_muid_package_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_files">Files</a></li>
			<li><a href="#p_muid_tab_images">Images</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						$("input[name=type]", "#p_muid_type_radio").change(function(){
							var class_name = $("#p_muid_form input[name=type]:checked").val();
							$("#p_muid_form div.package_type").hide();
							$("#p_muid_form div.package_type."+class_name).show();
						}).change();
						$("#p_muid_type_radio").buttonset();
					});
					// ]]>
				</script>
				<span class="pf-label">Type</span>
				<div class="pf-group">
					<div id="p_muid_type_radio">
						<input class="pf-field ui-state-default" type="radio" name="type" id="p_muid_radio1" value="component"<?php echo (($this->entity->type == 'component') ? ' checked="checked"' : ''); ?> /><label for="p_muid_radio1">Component</label>
						<input class="pf-field ui-state-default" type="radio" name="type" id="p_muid_radio2" value="template"<?php echo (($this->entity->type == 'template') ? ' checked="checked"' : ''); ?> /><label for="p_muid_radio2">Template</label>
						<input class="pf-field ui-state-default" type="radio" name="type" id="p_muid_radio3" value="system"<?php echo (($this->entity->type == 'system') ? ' checked="checked"' : ''); ?> /><label for="p_muid_radio3">System</label>
						<input class="pf-field ui-state-default" type="radio" name="type" id="p_muid_radio4" value="meta"<?php echo (($this->entity->type == 'meta') ? ' checked="checked"' : ''); ?> /><label for="p_muid_radio4">Meta Package</label>
					</div>
				</div>
			</div>
			<div class="package_type component">
				<div class="pf-element pf-heading">
					<h1>Component Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Component</span>
						<span class="pf-note">Information will be gathered from the component's info file.</span>
						<select class="pf-field ui-widget-content ui-corner-all" name="pkg_component">
							<option value="null">-- Choose Component --</option>
							<?php foreach ($this->components as $cur_component) {
								if (substr($cur_component, 0, 4) != 'com_')
									continue;
								?>
							<option value="<?php echo htmlspecialchars($cur_component); ?>"<?php echo (($this->entity->component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo htmlspecialchars("{$pines->info->$cur_component->name} [{$cur_component}]"); ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
			</div>
			<div class="package_type template">
				<div class="pf-element pf-heading">
					<h1>Template Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Template</span>
						<span class="pf-note">Information will be gathered from the template's info file.</span>
						<select class="pf-field ui-widget-content ui-corner-all" name="pkg_template">
							<option value="null">-- Choose Template --</option>
							<?php foreach ($this->components as $cur_component) {
								if (substr($cur_component, 0, 4) != 'tpl_')
									continue;
								?>
							<option value="<?php echo $cur_component; ?>"<?php echo (($this->entity->component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo "{$pines->info->$cur_component->name} [{$cur_component}]"; ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
			</div>
			<div class="package_type system">
				<div class="pf-element pf-heading">
					<h1>System Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Package Name</span>
						<span class="pf-note">Information will be gathered from the system's info file.</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="system_package_name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" onkeyup="this.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '');" />
					</label>
				</div>
			</div>
			<div class="package_type meta">
				<div class="pf-element pf-heading">
					<h1>Meta Package Options</h1>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Package Name</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="meta_package_name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" onkeyup="this.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '');" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Canonical Name</span>
						<span class="pf-note">The name the user will see.</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="meta_name" size="24" value="<?php echo htmlspecialchars($this->entity->meta['name']); ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Author</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="meta_author" size="24" value="<?php echo htmlspecialchars($this->entity->meta['author']); ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Version</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="meta_version" size="24" value="<?php echo htmlspecialchars($this->entity->meta['version']); ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">License</span>
						<span class="pf-note">Provide the URL to an online version. If that's not available, provide the name of the license.</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="meta_license" size="24" value="<?php echo htmlspecialchars($this->entity->meta['license']); ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Website</span>
						<span class="pf-note">Provide the URL.</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="meta_website" size="24" value="<?php echo htmlspecialchars($this->entity->meta['website']); ?>" />
					</label>
				</div>
				<div class="pf-element">
					<label>
						<span class="pf-label">Short Description</span>
						<span class="pf-note">Please provide a simple description, sentence caps, no period. E.g. "XML parsing library"</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="meta_short_description" size="24" value="<?php echo htmlspecialchars($this->entity->meta['short_description']); ?>" />
					</label>
				</div>
				<div class="pf-element pf-full-width">
					<label>
						<span class="pf-label">Description</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="meta_description"><?php echo $this->entity->meta['description']; ?></textarea></span>
					</label>
				</div>
				<div class="pf-element pf-full-width">
					<span class="pf-label">Conditions</span>
					<span class="pf-note">There are three classes.</span>
					<span class="pf-note">Depend: package will only install if all these conditions are met.</span>
					<span class="pf-note">Recommend: package will recommend that these conditions are met.</span>
					<span class="pf-note">Conflict: package will not install if any of these conditions are met.</span>
					<div class="pf-group">
						<div class="pf-field">
							<table class="conditions_table">
								<thead>
									<tr>
										<th>Class</th>
										<th>Type</th>
										<th>Value</th>
									</tr>
								</thead>
								<tbody>
									<?php if (isset($this->entity->meta['depend'])) foreach ($this->entity->meta['depend'] as $cur_key => $cur_value) { ?>
									<tr>
										<td>depend</td>
										<td><?php echo htmlspecialchars($cur_key); ?></td>
										<td><?php echo htmlspecialchars($cur_value); ?></td>
									</tr>
									<?php } ?>
									<?php if (isset($this->entity->meta['recommend'])) foreach ($this->entity->meta['recommend'] as $cur_key => $cur_value) { ?>
									<tr>
										<td>recommend</td>
										<td><?php echo htmlspecialchars($cur_key); ?></td>
										<td><?php echo htmlspecialchars($cur_value); ?></td>
									</tr>
									<?php } ?>
									<?php if (isset($this->entity->meta['conflict'])) foreach ($this->entity->meta['conflict'] as $cur_key => $cur_value) { ?>
									<tr>
										<td>conflict</td>
										<td><?php echo htmlspecialchars($cur_key); ?></td>
										<td><?php echo htmlspecialchars($cur_value); ?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
							<input type="hidden" name="meta_conditions" />
						</div>
					</div>
				</div>
				<div class="condition_dialog" style="display: none;" title="Add a Condition">
					<div class="pf-form">
						<div class="pf-element">
							<label>
								<span class="pf-label">Class</span>
								<select class="pf-field ui-widget-content ui-corner-all" name="cur_condition_class">
									<option value="depend">Depend</option>
									<option value="recommend">Recommend</option>
									<option value="conflict">Conflict</option>
								</select>
							</label>
						</div>
						<div class="pf-element">
							<label>
								<span class="pf-label">Type</span>
								<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_type" size="24" />
							</label>
						</div>
						<div class="pf-element">
							<label>
								<span class="pf-label">Value</span>
								<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_value" size="24" />
							</label>
						</div>
					</div>
					<br style="clear: both; height: 1px;" />
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Packaging Options</h1>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Filename</span>
					<span class="pf-note">Leave this blank to use the default filename scheme, "name-version".</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="filename" size="24" value="<?php echo htmlspecialchars($this->entity->filename); ?>" />
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_files">
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					$("#p_muid_additional, #p_muid_exclude").autocomplete({
						source: function(request, response){
							var type = $("#p_muid_form input[name=type]:checked").val();
							var component = $("#p_muid_form [name=pkg_component]").val();
							var template = $("#p_muid_form [name=pkg_template]").val();
							$.ajax({
								type: "POST",
								url: "<?php echo addslashes(pines_url('com_packager', 'glob')); ?>",
								dataType: "json",
								data: {"q": request.term, "type": type, "pkg_component": component, "pkg_template": template},
								success: function(data){
									response(data);
								}
							});
						},
						minLength: 0
					});

					$("[name=additional_files], [name=exclude_files]", "#p_muid_form").ptags({ptags_input_box: false, ptags_editable: false});

					// Additional files.
					pines.com_packager_add_file = function(message){
						$("#p_muid_form [name=additional_files]").ptags_add(message);
						$("#p_muid_additional").val("");
					};

					$("#p_muid_additional").keydown(function(e){
						if (e.keyCode == '13') {
							e.preventDefault();
							pines.com_packager_add_file($(this).val());
						}
					});

					// Exclude files.
					pines.com_packager_exc_file = function(message){
						$("#p_muid_form [name=exclude_files]").ptags_add(message);
						$("#p_muid_exclude").val("");
					};

					$("#p_muid_exclude").keydown(function(e){
						if (e.keyCode == '13') {
							e.preventDefault();
							pines.com_packager_exc_file($(this).val());
						}
					});
				});
				// ]]>
			</script>
			<div class="pf-element pf-heading">
				<h1>Include Files/Folders</h1>
				<p>Component and template packages already include all files in their folder and can't include others. System packages already include default system files. Folders must end with a forward slash.</p>
			</div>
			<div class="pf-element">
				<span class="pf-label">Search: </span>
				<input class="pf-field ui-widget-content ui-corner-all" id="p_muid_additional" type="text" size="24" />
				<button class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.com_packager_add_file($('#p_muid_additional').val());">Add</button>
			</div>
			<div class="pf-element">
				<div class="pf-group">
					<div class="pf-field">
						<input type="hidden" class="pf-field ui-widget-content ui-corner-all" name="additional_files" value="<?php echo htmlspecialchars(implode(',', (array) $this->entity->additional_files)); ?>" />
					</div>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Exclude Files/Folders</h1>
				<p>System packages already exclude all components and templates, and all but the "images" and "logos" folders in "media". Folders must end with a forward slash.</p>
			</div>
			<div class="pf-element">
				<span class="pf-label">Search: </span>
				<input class="pf-field ui-widget-content ui-corner-all" id="p_muid_exclude" type="text" size="24" />
				<button class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="button" onclick="pines.com_packager_exc_file($('#p_muid_exclude').val());">Add</button>
			</div>
			<div class="pf-element">
				<div class="pf-group">
					<div class="pf-field">
						<input type="hidden" class="pf-field ui-widget-content ui-corner-all" name="exclude_files" value="<?php echo htmlspecialchars(implode(',', (array) $this->entity->exclude_files)); ?>" />
					</div>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_images">
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					var update_images = function(){
						var images = [];
						$("li", "#p_muid_sortable").each(function(){
							var cur_entry = $(this);
							images.push({
								"file": cur_entry.find("img").attr("src"),
								"alt": cur_entry.find("p").html()
							});
						});
						$("input[name=screenshots]", "#p_muid_tab_images").val(JSON.stringify(images));
					};
					update_images();

					var add_image = function(image){
						$("<li class=\"ui-state-default ui-corner-all\"><img alt=\""+image.replace(/.*\//, '')+"\" src=\""+image+"\" /><p>Click to edit description...</p></li>").appendTo($("#p_muid_sortable"));
						update_images();
					};

					$("#p_muid_screen_upload").change(function(){
						add_image($(this).val());
						$(this).val("");
					});
					$("#p_muid_sortable")
					.delegate("li p", "click", function(){
						var cur_alt = $(this);
						var desc = cur_alt.text();
						$("<textarea cols=\"4\" rows=\"3\" style=\"width: 100%\" class=\"ui-widget-content ui-corner-all\">"+desc+"</textarea>")
						.blur(function(){
							cur_alt.insertAfter(this).html($(this).remove().val());
							update_images();
						})
						.insertAfter(cur_alt)
						.focus()
						.select();
						cur_alt.detach();
					})
					.sortable({
						placeholder: 'ui-state-highlight',
						update: function(){update_images();}
					})
					.draggable();
					$("#p_muid_image_trash").droppable({
						drop: function(e, ui){
							ui.draggable.hide("explode", {}, 500, function(){
								ui.draggable.remove();
								update_images();
							});
						}
					});
					//$("#p_muid_sortable").disableSelection();

					var icon_img = $("#p_muid_icon_preview");
					icon_img.bind("load", function(){
						if (!icon_img.is(":visible"))
							return;
						// Check icon dimensions.
						if (icon_img.width() != 32 || icon_img.height() != 32) {
							alert('Please select a 32x32 icon.');
							icon_img.attr('src', '');
							$("#p_muid_icon").val('');
						}
					});

					$("#p_muid_icon").change(function(){
						$("#p_muid_icon_preview").attr("src", $(this).val());
					});
				});
				// ]]>
			</script>
			<div class="pf-element pf-heading">
				<h1>Icon</h1>
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label"><img class="pf-field" alt="Icon Preview" id="p_muid_icon_preview" src="<?php echo htmlspecialchars($this->entity->icon); ?>" /></span>
				<input class="pf-field ui-widget-content ui-corner-all puploader" id="p_muid_icon" type="text" name="icon" value="<?php echo htmlspecialchars($this->entity->icon); ?>" />
			</div>
			<div class="pf-element pf-heading">
				<h1>Screenshots</h1>
			</div>
			<div class="pf-element">
				<span class="pf-label">Add a Screenshot</span>
				<input class="pf-field ui-widget-content ui-corner-all puploader" id="p_muid_screen_upload" type="text" value="" />
			</div>
			<div class="pf-element pf-full-width">
				<div class="pf-note">
					Drag image here to remove:
					<div class="ui-widget-content ui-corner-all" id="p_muid_image_trash" style="width: 32px; height: 32px; padding: 44px;">
						<div class="picon-32 picon-user-trash" style="width: 32px; height: 32px;"></div>
					</div>
				</div>
				<div class="pf-group">
					<ul id="p_muid_sortable" class="pf-field">
						<?php if ($this->entity->screenshots) { foreach ($this->entity->screenshots as $cur_screen) { ?>
						<li class="ui-state-default ui-corner-all">
							<img alt="<?php echo htmlspecialchars(basename($cur_screen['file'])); ?>" src="<?php echo htmlspecialchars($cur_screen['file']); ?>" />
							<p><?php echo empty($cur_screen['alt']) ? 'Click to edit description...' : htmlspecialchars(basename($cur_screen['alt'])); ?></p>
						</li>
						<?php } } ?>
					</ul>
					<br class="pf-clearing" />
				</div>
			</div>
			<input type="hidden" name="screenshots" />
			<br class="pf-clearing" />
		</div>
	</div>
	<br />
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_packager', 'package/list')); ?>');" value="Cancel" />
	</div>
</form>