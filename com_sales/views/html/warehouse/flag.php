<?php
/**
 * Provides a form for flagging warehouse items.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Flag Warehouse Items';
$pines->com_pgrid->load();
?>
<script type="text/javascript">
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_sales/includes/farbtastic/farbtastic.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_sales/includes/farbtastic/farbtastic.js");
	pines(function(){
		var bgcolor_picker = $.farbtastic("#p_muid_bgcolor_picker", "#p_muid_bgcolor_input");
		var textcolor_picker = $.farbtastic("#p_muid_textcolor_picker", "#p_muid_textcolor_input");
		var color_dialog = $("#p_muid_color_dialog")
		.delegate(".color_preset", "click", function(){
			var preset = $(this);
			var bgcolor = preset.attr("title").split(",")[0];
			var textcolor = preset.attr("title").split(",")[1];
			bgcolor_picker.setColor(bgcolor);
			$("#p_muid_bgcolor_input").val(bgcolor);
			textcolor_picker.setColor(textcolor);
			$("#p_muid_textcolor_input").val(textcolor);
			color_dialog.dialog("option", "buttons").Done();
		})
		.dialog({
			width: 800,
			autoOpen: false,
			modal: true,
			open: function(){
				var bgcolor = $("input[name=bgcolor]", "#p_muid_form").val();
				var textcolor = $("input[name=textcolor]", "#p_muid_form").val();
				bgcolor_picker.setColor(bgcolor);
				$("#p_muid_bgcolor_input").val(bgcolor);
				textcolor_picker.setColor(textcolor);
				$("#p_muid_textcolor_input").val(textcolor);
			},
			buttons: {
				"Cancel": function(){
					color_dialog.dialog("close");
				},
				"Done": function(){
					var bgcolor = $("#p_muid_bgcolor_input").val();
					var textcolor = $("#p_muid_textcolor_input").val();
					$("#p_muid_colors").css({
						"background-color": bgcolor,
						"color": textcolor
					});
					$("input[name=bgcolor]", "#p_muid_form").val(bgcolor);
					$("input[name=textcolor]", "#p_muid_form").val(textcolor);
					color_dialog.dialog("close");
				}
			}
		});
		$("#p_muid_colors").click(function(){
			color_dialog.dialog("open");
		});
		// Colorize the presets.
		$(".color_preset", "#p_muid_color_dialog").each(function(){
			var preset = $(this);
			var color = preset.attr("title").split(",");
			preset.css({
				"background-color": color[0],
				"color": color[1]
			});
		});
	});
</script>
<form class="pf-form" id="p_muid_form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'warehouse/flagsave')); ?>">
	<div class="pf-element pf-heading">
		<p>You're about to flag the following items:</p>
	</div>
	<?php foreach ($this->items as $cur_item) { ?>
	<div class="pf-element">
		<span class="pf-label">From <a data-entity="<?php echo htmlspecialchars($cur_item['sale']->guid); ?>" data-entity-context="com_sales_sale">Sale <?php echo htmlspecialchars($cur_item['sale']->id); ?></a></span>
		<div class="pf-group">
			<?php foreach ($cur_item['products'] as $cur_product) { ?>
			<div class="pf-field">
				<strong><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars("{$cur_product['entity']->name} [{$cur_product['entity']->sku}]"); ?></a></strong>
				<em>x <?php echo (int) $cur_product['quantity']; ?></em>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Flag these Items</h3>
	</div>
	<div class="pf-element">
		<span class="pf-label">Colors</span>
		<span class="pf-field"><a href="javascript:void(0);" id="p_muid_colors" style="background-color: #ff0000; color: #000066;">Sample Text. Click me to change colors.</a></span>
		<input type="hidden" name="bgcolor" value="#ff0000" />
		<input type="hidden" name="textcolor" value="#000066" />
	</div>
	<div id="p_muid_color_dialog" title="Choose Colors" style="display: none;">
		<div class="pf-form">
			<div class="pf-element">
				<div id="p_muid_bgcolor_picker" style="float: right;"></div>
				<label><span class="pf-label">Background Color</span>
					<input class="pf-field ui-corner-all" id="p_muid_bgcolor_input" type="text" size="24" value="" /></label>
				<br />
			</div>
			<div class="pf-element">
				<div id="p_muid_textcolor_picker" style="float: right;"></div>
				<label><span class="pf-label">Text Color</span>
					<input class="pf-field ui-corner-all" id="p_muid_textcolor_input" type="text" size="24" value="" /></label>
				<br />
			</div>
			<div class="pf-element pf-heading">
				<h3>Presets</h3>
			</div>
			<div class="pf-element" style="font-size: 1.4em;">
				<a title="#000000,#ffffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#ff0000,#ffffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#00ff00,#ffffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#0000ff,#ffffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<br />
				<a title="#ffffff,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#ff0000,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#00ff00,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#0000ff,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<br />
				<a title="#333333,#ffffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#ffff00,#00ffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#00ffff,#ffffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#ff00ff,#ffffff" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<br />
				<a title="#cccccc,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#ffff00,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#00ffff,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#ff00ff,#000000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<br />
				<a title="#00ff00,#990000" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#3515b0,#ffd100" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#ffd100,#a68800" href="javascript: void(0);" class="color_preset">Sample Text</a>
				<a title="#d435cd,#6e0069" href="javascript: void(0);" class="color_preset">Sample Text</a>
			</div>
		</div>
		<br />
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Comments</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<textarea style="width: 100%;" rows="3" cols="35" name="comments"><?php echo htmlspecialchars($this->comments); ?></textarea>
				</span>
			</span></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->id); ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'warehouse/pending'))); ?>);" value="Cancel" />
	</div>
</form>