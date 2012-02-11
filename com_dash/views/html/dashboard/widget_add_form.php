<?php
/**
 * Form list of widgets.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Add Widget(s)';
$grid_thirds = floor($pines->config->com_inuitcss->grid_columns / 3);
?>
<div class="pf-form" id="p_muid_form">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#p_muid_form").selectable({
				filter: ".widget_type",
				selected: function(e, ui){
					$(ui.selected).addClass("ui-state-active");
				},
				unselected: function(e, ui){
					$(ui.unselected).removeClass("ui-state-active");
				},
				stop: function(){
					update_count();
				}
			});
			var update_count = function(){
				$("#p_muid_count").text($(".ui-selected", "#p_muid_form").length);
			};
		});
		// ]]>
	</script>
	<div class="pf-element pf-full-width">
		Pick widgets from the following list to add to your dashboard. You can pick multiple by holding CTRL while selecting.
	</div>
	<div class="ui-helper-clearfix" style="max-height: 600px; overflow-y: auto; clear: both;">
		<?php foreach ($this->widgets as $cur_component => $cur_widget_list) { ?>
		<div class="pf-element pf-heading">
			<h1><?php echo htmlspecialchars($pines->info->$cur_component->name); ?></h1>
		</div>
		<div class="pf-element pf-full-width">
			<div style="padding-right: .5em;">
				<div class="grids">
				<?php
				$i = 1;
				foreach ($cur_widget_list as $cur_name => $cur_widget) {
					if ($i % 3 == 1) { ?>
				</div>
				<div class="grids">
					<?php } ?>
					<div class="grid-<?php echo $grid_thirds; ?>" style="margin-bottom: .5em;">
						<div class="ui-widget-content ui-corner-all widget_type" style="padding: .5em; cursor: default;">
							<div class="component" style="display: none;"><?php echo htmlspecialchars($cur_component); ?></div>
							<div class="widget_name" style="display: none;"><?php echo htmlspecialchars($cur_name); ?></div>
							<h3 style="margin-top: 0;"><?php echo htmlspecialchars($cur_widget['cname']); ?></h3>
							<p style="margin-bottom: 0;"><?php echo htmlspecialchars($cur_widget['description']); ?></p>
						</div>
					</div>
					<?php
					$i++;
				} ?>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-full-width" style="text-align: right; padding-bottom: 0;">
		Adding <span id="p_muid_count">0</span> widget(s).
	</div>
</div>
<br style="clear: both; height: 0; line-height: 0;" />