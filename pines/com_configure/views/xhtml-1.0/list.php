<?php
/**
 * Display a list of configurable components.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
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
		margin-bottom: .5em;
	}
	.component_list .license {
		margin-bottom: .5em;
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
		$info = $cur_component == 'system' ? $pines->info: $pines->info->$cur_component;
	?>
	<h3 class="ui-helper-clearfix">
		<a href="#">
			<span class="title"><?php echo $info->name; ?></span>
			<span class="version"><?php echo $cur_component; ?> <?php echo $info->version; ?></span>
		</a>
	</h3>
	<div class="component_entry">
		<div class="buttons">
			<?php if (in_array($cur_component, $this->config_components)) { ?>
			<input type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'edit', array('component' => urlencode($cur_component))); ?>');" value="Configure" />
			<input type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'view', array('component' => urlencode($cur_component))); ?>');" value="View Config" />
			<?php } ?>
			<?php if ($cur_component != 'system') { if (in_array($cur_component, $this->disabled_components)) { ?>
			<input type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'enable', array('component' => urlencode($cur_component))); ?>');" value="Enable" />
			<?php } else { ?>
			<input type="button" onclick="pines.get('<?php echo pines_url('com_configure', 'disable', array('component' => urlencode($cur_component))); ?>');" value="Disable" />
			<?php } } ?>
		</div>
		<div class="short_description"><?php echo $info->short_description; ?></div>
		<div class="license">License: <?php echo $info->license; ?></div>
		<div class="description"><?php echo $info->description; ?></div>
	</div>
	<?php } ?>
</div>