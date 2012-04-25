<?php
/**
 * Form list of buttons.
 *
 * @package Components
 * @subpackage dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Edit Buttons';
?>
<div class="pf-form" id="p_muid_form">
	<style type="text/css" scoped="scoped">
		/* <[CDATA[ */
		#p_muid_form .buttons {
			padding: .5em .25em 0;
			margin-bottom: .5em;
			min-height: 20px;
		}
		#p_muid_form .button_well .placeholder {
			display: inline-block;
			margin-bottom: -10px;
		}
		#p_muid_form .button_well a, #p_muid_form .button_well .placeholder {
			margin: 0 .25em .5em;
			cursor: default;
		}
		#p_muid_form .button_well a span {
			display: block;
			background-repeat: no-repeat;
		}
		#p_muid_form .button_well.small a span {
			padding-left: 18px;
			background-position: center left;
		}
		#p_muid_form .button_well.large a span {
			padding-top: 32px;
			background-position: top center;
			min-width: 32px;
		}
		#p_muid_form .button_well .separator {
			width: 10px;
			overflow: hidden;
			position: relative;
		}
		#p_muid_form .button_well .separator:before {
			content: "separator";
			display: block;
			font-size: 3px;
			height: 0;
			position: absolute;
			right: 5px;
			top: 5px;
			width: 0;

			transform: rotate(90deg);
			-webkit-transform: rotate(90deg);
			-moz-transform: rotate(90deg);
			-o-transform: rotate(90deg);
			filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=1);
		}
		#p_muid_form .button_well.small .separator:before {
			content: "sep";
		}
		#p_muid_form .button_well .line_break {
			display: block;
			width: auto;
			clear: both;
			padding: 10px 0;
			height: 1px;
			line-height: 1px;
		}
		#p_muid_form .button_well .line_break:before {
			content: "line break";
			text-align: center;
		}
		#p_muid_add_buttons {
			border: none !important;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			$("#p_muid_cur_buttons").sortable({
				tolerance: "pointer",
				placeholder: "btn btn-warning placeholder",
				forcePlaceholderSize: true,
				start: function(e, ui){
					if (ui.item.hasClass("line_break"))
						ui.placeholder.css("width", "32px");
				}
			});
			$("#p_muid_add_buttons .btn").draggable({
				helper: "clone",
				connectToSortable: "#p_muid_cur_buttons"
			});
			$("#p_muid_trash").droppable({
				accept: "#p_muid_cur_buttons .btn",
				hoverClass: "alert-error",
				tolerance: 'touch',
				drop: function(e, ui){
					ui.draggable.remove();
				}
			});
			$("[name=buttons_size]", "#p_muid_button_size").change(function(){
				var box = $(this);
				if (box.is(":checked") && box.val() == "small")
					$(".button_well", "#p_muid_form").removeClass("large").addClass("small").find(".picon").removeClass("picon-32");
				else
					$(".button_well", "#p_muid_form").removeClass("small").addClass("large").find(".picon").addClass("picon-32");
			});
		});
	</script>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Button Size</span>
		<div class="pf-group" id="p_muid_button_size">
			<label><input class="pf-field" type="radio" name="buttons_size" value="small"<?php echo $this->buttons_size == 'small' ? ' checked="checked"' : ''; ?> /> Small</label>
			<label><input class="pf-field" type="radio" name="buttons_size" value="large"<?php echo $this->buttons_size == 'large' ? ' checked="checked"' : ''; ?> /> Large</label>
		</div>
	</div>
	<div class="pf-element pf-full-width">
		<div id="p_muid_cur_buttons" class="buttons button_well well <?php echo htmlspecialchars($this->buttons_size); ?>">
			<?php foreach ((array) $this->current_buttons as $cur_button) {
				if ($cur_button == 'separator') { ?>
			<a class="separator btn disabled"><span>&nbsp;</span></a>
				<?php } elseif ($cur_button == 'line_break') { ?>
			<a class="line_break btn disabled"><span>&nbsp;</span></a>
				<?php } else {
					$cur_def = $pines->com_dash->get_button_def($cur_button);
					// Check its conditions.
					foreach ((array) $cur_def['depends'] as $cur_type => $cur_value) {
						if (!$pines->depend->check($cur_type, $cur_value))
							continue 2;
					} ?>
			<a class="button btn" title="<?php echo htmlspecialchars($cur_def['description']); ?>">
				<span class="component" style="display: none;"><?php echo htmlspecialchars($cur_button['component']); ?></span>
				<span class="button_name" style="display: none;"><?php echo htmlspecialchars($cur_button['button']); ?></span>
				<span class="picon <?php echo $this->buttons_size == 'large' ? 'picon-32' : ''; ?> <?php echo htmlspecialchars($cur_def['class']); ?>"><?php echo htmlspecialchars($cur_def['text']); ?></span>
			</a>
			<?php } } ?>
		</div>
	</div>
	<div class="pf-element pf-full-width clearfix">
		<div class="ui-widget-content ui-corner-all" id="p_muid_trash" style="float: right; margin-left: .5em; width: 32px; height: 32px; padding: 10px;">
			<div class="picon-32 picon-user-trash" style="width: 32px; height: 32px;"></div>
		</div>
		Drag buttons from the following list to add to your dashboard. Drag to the trash can to remove them. You can also sort your current buttons.
	</div>
	<div id="p_muid_add_buttons" class="well" style="max-height: 430px; overflow-y: auto; clear: both;">
		<div class="pf-element pf-heading">
			<h3>Separators</h3>
		</div>
		<div class="pf-element pf-full-width">
			<div class="button_well <?php echo htmlspecialchars($this->buttons_size); ?>">
				<a class="separator btn disabled"><span>&nbsp;</span></a>
				<a class="line_break btn disabled"><span>&nbsp;</span></a>
			</div>
		</div>
		<?php foreach ((array) $this->buttons as $cur_component => $cur_button_list) { ?>
		<div class="pf-element pf-heading">
			<h3><?php echo htmlspecialchars($pines->info->$cur_component->name); ?></h3>
		</div>
		<div class="pf-element pf-full-width">
			<div class="button_well <?php echo htmlspecialchars($this->buttons_size); ?>">
				<?php foreach ($cur_button_list as $cur_name => $cur_button) { ?>
				<a class="button btn" title="<?php echo htmlspecialchars($cur_button['description']); ?>">
					<span class="component" style="display: none;"><?php echo htmlspecialchars($cur_component); ?></span>
					<span class="button_name" style="display: none;"><?php echo htmlspecialchars($cur_name); ?></span>
					<span class="picon <?php echo $this->buttons_size == 'large' ? 'picon-32' : ''; ?> <?php echo htmlspecialchars($cur_button['class']); ?>"><?php echo htmlspecialchars($cur_button['text']); ?></span>
				</a>
				<?php } ?>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
<br style="clear: both; height: 0; line-height: 0;" />