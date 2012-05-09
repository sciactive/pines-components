<?php
/**
 * Display a list of configurable components.
 *
 * @package Components\configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Configure Components';
?>
<style type="text/css">
	#p_muid_form {
		padding: 1em 2em;
	}
	#p_muid_form .user_picker {
		padding: 0 0 1em;
	}
	#p_muid_form .user_picker > h3 {
		margin-bottom: .5em !important;
	}
	#p_muid_components .accordion-group {
		margin-top: .5em !important;
	}
	#p_muid_components .title {
		font-size: 1.3em;
	}
	#p_muid_components .label {
		position: relative;
		top: -0.3em;
	}
	#p_muid_components .version {
		float: right;
		font-size: 1.3em;
	}
	#p_muid_components .version-text {
		font-size: .8em;
	}
	#p_muid_components .buttons {
		float: right;
	}
	#p_muid_components .short_description {
		font-size: 1.1em;
	}
	#p_muid_components .license, #p_muid_components .description, #p_muid_components .service {
		margin-top: .5em;
	}
</style>
<script type="text/javascript">
	pines(function(){
		pines.com_configure_go = function(url){
			var peruser = <?php echo $this->per_user || $this->per_condition ? 'true' : 'false'; ?>;
			if (peruser) {
				var params = {};
				params["<?php echo $this->per_user ? 'peruser' : 'percondition'; ?>"] = 1;
				var user = $("#p_muid_user_select").val();
				if (user == "null") {
					alert("Please pick a <?php echo $this->per_user ? 'user' : 'condition'; ?> first.");
					return;
				}
				params["type"] = user.replace(/\d/g, '');
				params["id"] = user.replace(/\D/g, '');
				pines.get(url, params);
			} else
				pines.get(url);
		};
	});
</script>
<div id="p_muid_form">
	<?php if ($this->per_user) { ?>
	<div class="user_picker">
		<h3>Per User/Group Configuration</h3>
		<div>
			<select id="p_muid_user_select" name="user_select">
				<option value="null">-- Pick a User/Group --</option>
				<optgroup label="Groups">
					<?php foreach ($this->groups as $cur_group) { ?>
					<option value="<?php echo (int) $cur_group->guid ?>group"<?php echo $cur_group->is($this->user) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("$cur_group->name [$cur_group->groupname]"); ?></option>
					<?php } ?>
				</optgroup>
				<optgroup label="Users">
					<?php foreach ($this->users as $cur_user) { ?>
					<option value="<?php echo (int) $cur_user->guid ?>user"<?php echo $cur_user->is($this->user) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars("$cur_user->name [$cur_user->username]"); ?></option>
					<?php } ?>
				</optgroup>
			</select>
			<button class="btn" type="button" onclick="pines.com_configure_go(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'list'))); ?>)">Refresh</button>
		</div>
		<?php if (!$pines->config->com_configure->peruser) { ?>
		<p>
			Per user/group configuration is not enabled, so these settings will have
			no effect. You can enable per user/group configuration
			<a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_configure'))); ?>">here</a>.
		</p>
		<?php } ?>
	</div>
	<?php } elseif ($this->per_condition) { ?>
	<div class="user_picker">
		<h3>Per Condition Configuration</h3>
		<div class="btn-toolbar">
			<div class="btn-group">
				<select id="p_muid_user_select" name="user_select" style="float: left;">
					<option value="null">-- Pick a Condition --</option>
					<?php foreach ($this->conditions as $cur_condition) { ?>
					<option value="<?php echo (int) $cur_condition->guid ?>"<?php echo $cur_condition->is($this->user) ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_condition->name); ?></option>
					<?php } ?>
				</select>
			</div>
			<div class="btn-group">
				<button class="btn" type="button" onclick="pines.com_configure_go(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'list'))); ?>)">Refresh</button>
			</div>
			&nbsp;&nbsp;&nbsp;
			<div class="btn-group">
				<button class="btn btn-success" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'condition/edit'))); ?>)">New</button>
				<button class="btn" type="button" onclick="pines.com_configure_go(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'condition/edit'))); ?>)">Edit</button>
				<button class="btn btn-danger" type="button" onclick="pines.com_configure_go(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'condition/delete'))); ?>)">Delete</button>
			</div>
		</div>
		<?php if (!$pines->config->com_configure->percondition) { ?>
		<p>
			Per condition configuration is not enabled, so these settings will have
			no effect. You can enable per condition configuration
			<a href="<?php echo htmlspecialchars(pines_url('com_configure', 'edit', array('component' => 'com_configure'))); ?>">here</a>.
		</p>
		<?php } ?>
	</div>
	<?php } ?>
	<div id="p_muid_components" class="accordion">
		<?php foreach($this->components as $cur_component) {
			if (($this->per_user || $this->per_condition) && !$cur_component->is_configurable()) continue; ?>
		<div class="accordion-group">
			<a class="accordion-heading ui-helper-clearfix" href="javascript:void(0);" data-parent="#p_muid_components" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
				<big class="accordion-toggle">
					<span class="title"><?php echo htmlspecialchars($cur_component->info->name); ?></span>
					<?php echo $cur_component->config ? ' <span class="label label-info">Modified</span>' : ''; echo $cur_component->is_disabled() ? ' <span class="label label-important">Disabled</span>' : ''; ?>
					<span class="version">
						<span class="version-text"><?php echo htmlspecialchars($cur_component->name); ?> <?php echo htmlspecialchars($cur_component->info->version); ?></span>
					</span>
				</big>
			</a>
			<div class="accordion-body collapse">
				<div class="accordion-inner clearfix">
					<div class="buttons btn-group">
						<?php if ($cur_component->is_configurable()) { ?>
						<input class="btn" type="button" onclick="pines.com_configure_go(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'edit', array('component' => $cur_component->name)))); ?>);" value="Configure" />
						<input class="btn" type="button" onclick="pines.com_configure_go(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'view', array('component' => $cur_component->name)))); ?>);" value="View Config" />
						<?php } ?>
						<?php if (!$this->per_user && !$this->per_condition) { ?>
							<?php if ($cur_component->name != 'system') { if ($cur_component->is_disabled()) { ?>
							<input class="btn btn-success" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'enable', array('component' => $cur_component->name)))); ?>);" value="Enable" />
							<?php } else { ?>
							<input class="btn btn-danger" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_configure', 'disable', array('component' => $cur_component->name)))); ?>);" value="Disable" />
							<?php } } ?>
						<?php } ?>
					</div>
					<div class="short_description"><?php echo htmlspecialchars($cur_component->info->short_description); ?></div>
					<?php if (is_array($cur_component->info->services)) { ?>
					<div class="service">This component provides <?php echo (count($cur_component->info->services) == 1) ? 'a service' : 'services'; ?>: <?php echo htmlspecialchars(implode(', ', $cur_component->info->services)); ?></div>
					<?php } ?>
					<div class="license">License: <?php echo (substr($cur_component->info->license, 0, 4) == 'http') ? '<a href="'.htmlspecialchars($cur_component->info->license).'" target="_blank">'.htmlspecialchars($cur_component->info->license).'</a>' : htmlspecialchars($cur_component->info->license); ?></div>
					<div class="license">Website: <?php echo (substr($cur_component->info->website, 0, 4) == 'http') ? '<a href="'.htmlspecialchars($cur_component->info->website).'" target="_blank">'.htmlspecialchars($cur_component->info->website).'</a>' : htmlspecialchars($cur_component->info->website); ?></div>
					<div class="description"><?php echo str_replace("\n", '<br />', htmlspecialchars($cur_component->info->description)); ?></div>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</div>