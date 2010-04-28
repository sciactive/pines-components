<?php
/**
 * Display a list of configurable components.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Configure Components';
?>
<style type="text/css">
	/* <![CDATA[ */
	.user_picker {
		padding: 0 2em;
	}
	.user_picker > h3 {
		margin-bottom: .5em !important;
	}
	.component_list {
		padding: 1em 2em;
	}
	.component_list > h3 {
		margin-top: .5em !important;
	}
	.component_list .title {
		font-size: 1.3em;
	}
	.component_list .version {
		float: right;
		clear: right;
		font-size: 1.1em;
	}
	.component_list .buttons {
		float: right;
		clear: right;
	}
	.component_list .short_description {
		font-size: 1.1em;
	}
	.component_list .license, .component_list .description, .component_list .service {
		margin-top: .5em;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$(".component_list").accordion({autoHeight: false})
		.find(".buttons").buttonset()
		.find("input").button();
	});

	function com_configure__go(url) {
		var peruser = <?php echo $this->per_user ? 'true' : 'false'; ?>;
		var params = {};
		if (peruser) {
			params["peruser"] = 1;
			var user = $(".user_picker select[name=user_select]").val();
			if (user == "null") {
				alert("Please pick a user first.");
				return;
			}
			params["type"] = user.replace(/\d/g, '');
			params["id"] = user.replace(/\D/g, '');
		}
		pines.get(url, params);
	}
	// ]]>
</script>
<?php if ($this->per_user) { ?>
<div class="user_picker">
	<h3>Per User/Group Configuration</h3>
	<div>
		<select class="ui-widget-content" name="user_select">
			<option value="null">-- Pick a User/Group --</option>
			<optgroup label="Groups">
				<?php foreach ($this->groups as $cur_group) { ?>
				<option value="<?php echo $cur_group->guid; ?>group"<?php echo $cur_group->is($this->user) ? ' selected="selected"' : ''; ?>><?php echo "$cur_group->name [$cur_group->groupname]"; ?></option>
				<?php } ?>
			</optgroup>
			<optgroup label="Users">
				<?php foreach ($this->users as $cur_user) { ?>
				<option value="<?php echo $cur_user->guid; ?>user"<?php echo $cur_user->is($this->user) ? ' selected="selected"' : ''; ?>><?php echo "$cur_user->name [$cur_user->username]"; ?></option>
				<?php } ?>
			</optgroup>
		</select>
		<button class="ui-state-default ui-corner-all" type="button" onclick="com_configure__go('<?php echo htmlentities(pines_url('com_configure', 'list')); ?>')">Refresh</button>
	</div>
</div>
<?php } ?>
<div class="component_list">
	<?php foreach($this->components as $cur_component) {
		if ($this->per_user && !$cur_component->is_configurable()) continue; ?>
	<h3 class="ui-helper-clearfix<?php echo $cur_component->is_disabled() ? ' ui-priority-secondary' : ''; ?>">
		<a href="#">
			<span class="title"><?php echo $cur_component->info->name; ?></span>
			<span class="status"><?php echo $cur_component->config ? ' (Modified)' : ''; ?></span>
			<span class="status"><?php echo $cur_component->is_disabled() ? ' (Disabled)' : ''; ?></span>
			<span class="version"><?php echo $cur_component->name; ?> <?php echo $cur_component->info->version; ?></span>
		</a>
	</h3>
	<div class="component_entry<?php echo $cur_component->is_disabled() ? ' ui-priority-secondary' : ''; ?>">
		<div class="buttons">
			<?php if ($cur_component->is_configurable()) { ?>
			<input type="button" onclick="com_configure__go('<?php echo htmlentities(pines_url('com_configure', 'edit', array('component' => urlencode($cur_component->name)))); ?>');" value="Configure" />
			<input type="button" onclick="com_configure__go('<?php echo htmlentities(pines_url('com_configure', 'view', array('component' => urlencode($cur_component->name)))); ?>');" value="View Config" />
			<?php } ?>
			<?php if (!$this->per_user) { ?>
				<?php if ($cur_component->name != 'system') { if ($cur_component->is_disabled()) { ?>
				<input type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_configure', 'enable', array('component' => urlencode($cur_component->name)))); ?>');" value="Enable" />
				<?php } else { ?>
				<input type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_configure', 'disable', array('component' => urlencode($cur_component->name)))); ?>');" value="Disable" />
				<?php } } ?>
			<?php } ?>
		</div>
		<div class="short_description"><?php echo $cur_component->info->short_description; ?></div>
		<?php if (is_array($cur_component->info->services)) { ?>
		<div class="service">This component provides <?php echo (count($cur_component->info->services) == 1) ? 'a service' : 'services'; ?>: <?php echo implode(', ', $cur_component->info->services); ?></div>
		<?php } ?>
		<div class="license">License: <?php echo (substr($cur_component->info->license, 0, 4) == 'http') ? '<a href="'.htmlentities($cur_component->info->license).'" onclick="window.open(this.href); return false;">'.htmlentities($cur_component->info->license).'</a>' : htmlentities($cur_component->info->license); ?></div>
		<div class="description"><?php echo $cur_component->info->description; ?></div>
	</div>
	<?php } ?>
</div>