<?php
/**
 * Provides a form for the user to edit a template.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Template' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide template details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_mailer', 'template/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			// Conditions
			var conditions = $("#p_muid_form [name=conditions]");
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
							condition_dialog.find("input[name=cur_condition_type]").val(pines.unsafe(rows.pgrid_get_value(1)));
							condition_dialog.find("input[name=cur_condition_value]").val(pines.unsafe(rows.pgrid_get_value(2)));
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
				pgrid_view_height: "300px"
			});

			// Condition Dialog
			condition_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val();
						var cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
						if (cur_condition_type == "") {
							alert("Please provide a type for this condition.");
							return;
						}
						if (cur_condition == null) {
							// Is this a duplicate type?
							var dupe = false;
							conditions_table.pgrid_get_all_rows().each(function(){
								if (dupe) return;
								if ($(this).pgrid_get_value(1) == cur_condition_type)
									dupe = true;
							});
							if (dupe) {
								pines.notice('There is already a condition of that type.');
								return;
							}
							var new_condition = [{
								key: null,
								values: [
									pines.safe(cur_condition_type),
									pines.safe(cur_condition_value)
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, pines.safe(cur_condition_type));
							cur_condition.pgrid_set_value(2, pines.safe(cur_condition_value));
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_conditions();
				}
			});

			var update_conditions = function(){
				condition_dialog.find("input[name=cur_condition_type]").val("");
				condition_dialog.find("input[name=cur_condition_value]").val("");
				conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_conditions();

			condition_dialog.find("input[name=cur_condition_type]").autocomplete({
				"source": <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>
			});
		});
	</script>
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_general" data-toggle="tab">General</a></li>
		<li><a href="#p_muid_tab_document" data-toggle="tab">Document</a></li>
		<li><a href="#p_muid_tab_conditions" data-toggle="tab">Conditions</a></li>
	</ul>
	<div id="p_muid_template_tabs" class="tab-content">
		<div class="tab-pane active" id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
				<div>Modified: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-heading">
				<h3>Content</h3>
			</div>
			<div class="pf-element pf-full-width">
				Put the text "#content#" (without quotes) where you want the content of the email to go.<br />
				<textarea rows="20" cols="35" class="peditor-email" style="width: 100%;" name="content"><?php echo htmlspecialchars($this->entity->content); ?></textarea>
			</div>
			<div class="pf-element">
				Macros let you replace a string with a value that can change. To
				use them, insert the desired macro string into the content where
				you would like the macro to appear.
			</div>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Universal Macros</span>
				<div class="pf-group">
					<div class="pf-field">
						<table class="table table-condensed table-bordered">
							<thead>
								<tr>
									<th></th>
									<th>Macro String</th>
									<th>Description</th>
									<th>Current Value</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td></td>
									<td>#subject#</td>
									<td>The email subject.</td>
									<td>N/A</td>
								</tr>
								<tr>
									<td rowspan="2">Links</td>
									<td>#site_link#</td>
									<td>The URL of the site, to be used in a link.</td>
									<td><?php echo htmlspecialchars($pines->config->full_location); ?></td>
								</tr>
								<tr>
									<td>#unsubscribe_link#</td>
									<td>The URL where the user can unsubscribe from further mailings.</td>
									<td>N/A</td>
								</tr>
								<tr>
									<td rowspan="5">Recipient</td>
									<td>#to_username#</td>
									<td>The recipient's username.</td>
									<td>N/A</td>
								</tr>
								<tr>
									<td>#to_name#</td>
									<td>The recipient's full name.</td>
									<td>N/A</td>
								</tr>
								<tr>
									<td>#to_first_name#</td>
									<td>The recipient's first name.</td>
									<td>N/A</td>
								</tr>
								<tr>
									<td>#to_last_name#</td>
									<td>The recipient's last name.</td>
									<td>N/A</td>
								</tr>
								<tr>
									<td>#to_email#</td>
									<td>The recipient's email.</td>
									<td>N/A</td>
								</tr>
								<tr>
									<td rowspan="5">Current User</td>
									<td>#username#</td>
									<td>The current user's username.</td>
									<td><?php echo htmlspecialchars($_SESSION['user']->username); ?></td>
								</tr>
								<tr>
									<td>#name#</td>
									<td>The current user's full name.</td>
									<td><?php echo htmlspecialchars($_SESSION['user']->name); ?></td>
								</tr>
								<tr>
									<td>#first_name#</td>
									<td>The current user's first name.</td>
									<td><?php echo htmlspecialchars($_SESSION['user']->name_first); ?></td>
								</tr>
								<tr>
									<td>#last_name#</td>
									<td>The current user's last name.</td>
									<td><?php echo htmlspecialchars($_SESSION['user']->name_last); ?></td>
								</tr>
								<tr>
									<td>#email#</td>
									<td>The current user's email.</td>
									<td><?php echo htmlspecialchars($_SESSION['user']->email); ?></td>
								</tr>
								<tr>
									<td rowspan="6">Date/Time</td>
									<td>#date_short#</td>
									<td>The date. (Short)</td>
									<td><?php echo htmlspecialchars(format_date(time(), 'date_short')); ?></td>
								</tr>
								<tr>
									<td>#date_med#</td>
									<td>The date. (Medium)</td>
									<td><?php echo htmlspecialchars(format_date(time(), 'date_med')); ?></td>
								</tr>
								<tr>
									<td>#date_long#</td>
									<td>The date. (Long)</td>
									<td><?php echo htmlspecialchars(format_date(time(), 'date_long')); ?></td>
								</tr>
								<tr>
									<td>#time_short#</td>
									<td>The time of day. (Short)</td>
									<td><?php echo htmlspecialchars(format_date(time(), 'time_short')); ?></td>
								</tr>
								<tr>
									<td>#time_med#</td>
									<td>The time of day. (Medium)</td>
									<td><?php echo htmlspecialchars(format_date(time(), 'time_med')); ?></td>
								</tr>
								<tr>
									<td>#time_long#</td>
									<td>The time of day. (Long)</td>
									<td><?php echo htmlspecialchars(format_date(time(), 'time_long')); ?></td>
								</tr>
								<tr>
									<td rowspan="2">System</td>
									<td>#system_name#</td>
									<td>The system name.</td>
									<td><?php echo htmlspecialchars($pines->config->system_name); ?></td>
								</tr>
								<tr>
									<td>#page_title#</td>
									<td>The page title.</td>
									<td><?php echo htmlspecialchars($pines->config->page_title); ?></td>
								</tr>
							</tbody>
						</table>
						<p>Care should be taken when using values from the current user, because they will be empty when no user is logged in.</p>
						<p>Also, the recipient info will only be available if the message is being sent to a registered user/customer.</p>
					</div>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_document">
			<div class="pf-element pf-heading">
				<h3>Email Document</h3>
				<p>The email document is the HTML portion surrounding the actual
					email. It doesn't provide any visual styling, so normally it
					doesn't need to be customized. It can't be edited with a
					WYSIWYG editor. The default document is based on the email
					boilerplate from <a href="http://www.emailology.org/#1" target="_blank">Emailology</a>.</p>
			</div>
			<div class="pf-element pf-full-width">
				Put the text "#content#" (without quotes) where you want the template content to go.<br />
				<div class="pf-group pf-full-width" style="margin-left: 0;"><textarea style="width: 100%;" rows="20" cols="35" name="document"><?php echo htmlspecialchars($this->entity->document); ?></textarea></div>
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h3>Template Conditions</h3>
				<p>This template will only be used if these conditions are met.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="conditions_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->conditions)) foreach ($this->entity->conditions as $cur_key => $cur_value) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_key); ?></td>
							<td><?php echo htmlspecialchars($cur_value); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="conditions" />
			</div>
			<div class="condition_dialog" style="display: none;" title="Add a Condition">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Detected Types</span>
						<span class="pf-note">These types were detected on this system.</span>
						<div class="pf-group">
							<div class="pf-field"><em><?php
							$checker_links = array();
							foreach (array_keys($pines->depend->checkers) as $cur_checker) {
								$checker_html = htmlspecialchars($cur_checker);
								$checker_js = htmlspecialchars(json_encode($cur_checker));
								$checker_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val($checker_js);\">$checker_html</a>";
							}
							echo implode(', ', $checker_links);
							?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<input class="pf-field" type="text" name="cur_condition_type" id="p_muid_cur_condition_type" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field" type="text" name="cur_condition_value" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_mailer', 'template/list'))); ?>);" value="Cancel" />
	</div>
</form>