<?php
/**
 * Provides a form for the user to edit a raffle.
 *
 * @package Components\raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Raffle' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide raffle details in this form.';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	pines(function(){
		// Contestants
		var contestants = $("#p_muid_form [name=contestants]");
		var contestants_table = $("#p_muid_form .contestants_table");
		var contestant_dialog = $("#p_muid_form .contestant_dialog");
		var cur_contestant = null;

		<?php if (!$this->entity->complete) { ?>
		contestants_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Contestant',
					extra_class: 'picon picon-document-new',
					selection_optional: true,
					click: function(){
						cur_contestant = null;
						contestant_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Edit Contestant',
					extra_class: 'picon picon-document-edit',
					double_click: true,
					click: function(e, rows){
						cur_contestant = rows;
						contestant_dialog.find("input[name=cur_contestant_first_name]").val(pines.unsafe(rows.pgrid_get_value(1)));
						contestant_dialog.find("input[name=cur_contestant_last_name]").val(pines.unsafe(rows.pgrid_get_value(2)));
						contestant_dialog.find("input[name=cur_contestant_email]").val(pines.unsafe(rows.pgrid_get_value(3)));
						contestant_dialog.find("input[name=cur_contestant_phone]").val(pines.unsafe(rows.pgrid_get_value(4)));
						contestant_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Contestant',
					extra_class: 'picon picon-edit-delete',
					click: function(e, rows){
						rows.pgrid_delete();
						update_contestants();
					}
				}
			],
			pgrid_view_height: "200px"
		});

		// Contestant Dialog
		contestant_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function(){
					var cur_contestant_first_name = contestant_dialog.find("input[name=cur_contestant_first_name]").val();
					var cur_contestant_last_name = contestant_dialog.find("input[name=cur_contestant_last_name]").val();
					var cur_contestant_email = contestant_dialog.find("input[name=cur_contestant_email]").val();
					var cur_contestant_phone = contestant_dialog.find("input[name=cur_contestant_phone]").val();
					if (cur_contestant_first_name == "" || cur_contestant_last_name == "" || cur_contestant_email == "" || cur_contestant_phone == "") {
						alert("Please provide all information for this contestant.");
						return;
					}
					if (!cur_contestant) {
						var new_contestant = [{
							key: null,
							values: [
								pines.safe(cur_contestant_first_name),
								pines.safe(cur_contestant_last_name),
								pines.safe(cur_contestant_email),
								pines.safe(cur_contestant_phone)
							]
						}];
						contestants_table.pgrid_add(new_contestant);
					} else {
						cur_contestant.pgrid_set_value(1, pines.safe(cur_contestant_first_name));
						cur_contestant.pgrid_set_value(2, pines.safe(cur_contestant_last_name));
						cur_contestant.pgrid_set_value(3, pines.safe(cur_contestant_email));
						cur_contestant.pgrid_set_value(4, pines.safe(cur_contestant_phone));
					}
					$(this).dialog('close');
				}
			},
			close: function(){
				update_contestants();
			}
		});

		var update_contestants = function(){
			contestant_dialog.find("input[name=cur_contestant_first_name]").val("");
			contestant_dialog.find("input[name=cur_contestant_last_name]").val("");
			contestant_dialog.find("input[name=cur_contestant_email]").val("");
			contestant_dialog.find("input[name=cur_contestant_phone]").val("");
			contestants.val(JSON.stringify(contestants_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_contestants();
		<?php } else { ?>
		contestants_table.pgrid({
			pgrid_paginate: false,
			pgrid_view_height: "200px"
		});
		<?php } ?>
		$("#p_muid_form .public_contestants_table").pgrid({
			pgrid_paginate: false,
			pgrid_view_height: "200px"
		});
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_raffle', 'raffle/save')); ?>">
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
		<label><span class="pf-label">Public</span>
			<span class="pf-note">Whether contestants are able to add themselves on your website.</span>
			<input class="pf-field" type="checkbox" name="public" value="ON"<?php echo $this->entity->public ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<?php if (!$this->entity->complete) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Go Back to Form</span>
			<span class="pf-note">When a public user submits an entry, go back to the entry form.</span>
			<input class="pf-field" type="checkbox" name="back_to_form" value="ON"<?php echo $this->entity->back_to_form ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Places</span>
			<span class="pf-note">The number of winners.</span>
			<input class="pf-field" type="text" name="places" size="5" value="<?php echo htmlspecialchars($this->entity->places); ?>" /></label>
	</div>
	<?php } else { ?>
	<div class="pf-element">
		This raffle is complete, so only the name and public status can be changed.
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Contestants</h3>
	</div>
	<div class="pf-element pf-full-width">
		<table class="contestants_table">
			<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Phone</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->contestants as $cur_contestant) { ?>
				<tr>
					<td><?php echo htmlspecialchars($cur_contestant['first_name']); ?></td>
					<td><?php echo htmlspecialchars($cur_contestant['last_name']); ?></td>
					<td><?php echo htmlspecialchars($cur_contestant['email']); ?></td>
					<td><?php echo htmlspecialchars(format_phone($cur_contestant['phone'])); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
		<input type="hidden" name="contestants" />
	</div>
	<div class="contestant_dialog" title="Add an Contestant" style="display: none;">
		<div class="pf-form">
			<div class="pf-element">
				<label>
					<span class="pf-label">First Name</span>
					<input class="pf-field" type="text" name="cur_contestant_first_name" size="24" />
				</label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Last Name</span>
					<input class="pf-field" type="text" name="cur_contestant_last_name" size="24" />
				</label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Email</span>
					<input class="pf-field" type="email" name="cur_contestant_email" size="24" />
				</label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Phone</span>
					<input class="pf-field" type="tel" name="cur_contestant_phone" size="24" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" />
				</label>
			</div>
		</div>
		<br style="clear: both; height: 1px;" />
	</div>
	<div class="pf-element pf-heading">
		<h3>Public Contestants</h3>
	</div>
	<div class="pf-element pf-full-width">
		<table class="public_contestants_table">
			<thead>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Phone</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->public_contestants as $cur_contestant) { ?>
				<tr>
					<td><?php echo htmlspecialchars($cur_contestant['first_name']); ?></td>
					<td><?php echo htmlspecialchars($cur_contestant['last_name']); ?></td>
					<td><?php echo htmlspecialchars($cur_contestant['email']); ?></td>
					<td><?php echo htmlspecialchars(format_phone($cur_contestant['phone'])); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_raffle', 'raffle/list'))); ?>);" value="Cancel" />
	</div>
</form>