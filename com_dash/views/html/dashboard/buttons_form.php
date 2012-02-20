<?php
/**
 * Form list of buttons.
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
		}
		#p_muid_add_buttons {
			border: none !important;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			$("#p_muid_separator").click(function(){
				$("#p_muid_cur_buttons").append('<a class="separator btn disabled"><span>&nbsp;</span></a>');
			});
			$("#p_muid_cur_buttons").sortable({
				//tolerance: "pointer",
				placeholder: "btn btn-warning placeholder",
				forcePlaceholderSize: true
			});
			$("#p_muid_add_buttons .button").draggable({
				helper: "clone",
				connectToSortable: "#p_muid_cur_buttons"
			});
			$("#p_muid_add_buttons").droppable({
				accept: "#p_muid_cur_buttons .button, #p_muid_cur_buttons .separator",
				hoverClass: "alert-error",
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
	<div class="pf-element pf-full-width">
		<a href="javascript:void(0);" id="p_muid_separator">Add a Separator</a><br />
		Drag buttons from the following list to add to your dashboard. Drag them back to the list to remove them. You can also reorganize your current buttons.
	</div>
	<div id="p_muid_add_buttons" style="max-height: 430px; overflow-y: auto; clear: both;">
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