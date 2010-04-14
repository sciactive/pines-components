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
	$(function(){
		$(".component_list").accordion({autoHeight: false})
		.find(".buttons").buttonset()
		.find("input").button();
	});
	// ]]>
</script>
<div class="component_list">
	<?php foreach($this->components as $cur_component) {
		if ($this->peruser && !$cur_component->is_configurable()) continue; ?>
	<h3 class="ui-helper-clearfix<?php echo $cur_component->is_disabled() ? ' ui-priority-secondary' : ''; ?>">
		<a href="#">
			<span class="title"><?php echo $cur_component->info->name; ?><?php echo $cur_component->is_disabled() ? ' (Disabled)' : ''; ?></span>
			<span class="version"><?php echo $cur_component->name; ?> <?php echo $cur_component->info->version; ?></span>
		</a>
	</h3>
	<div class="component_entry<?php echo $cur_component->is_disabled() ? ' ui-priority-secondary' : ''; ?>">
		<div class="buttons">
			<?php if ($cur_component->is_configurable()) { ?>
			<input type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_configure', 'edit', array('component' => urlencode($cur_component->name), 'peruser' => urlencode($this->peruser)))); ?>');" value="Configure" />
			<input type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_configure', 'view', array('component' => urlencode($cur_component->name), 'peruser' => urlencode($this->peruser)))); ?>');" value="View Config" />
			<?php } ?>
			<?php if (!$this->peruser) { ?>
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