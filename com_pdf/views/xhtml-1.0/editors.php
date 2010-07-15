<?php
/**
 * Provide display editors for the fields of a Pines Form.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var display_editor_window;
		var current_holder;
		var pdf_file = "<?php echo addslashes($this->entity->pdf_file); ?>";
		var pages = <?php echo $this->entity->pdf_pages; ?>;
		var display_jsons = JSON.parse("<?php echo addslashes(json_encode($this->entity->displays)); ?>");

		var open_display_editor = function(holder) {
			current_holder = holder;
			if (display_editor_window) display_editor_window.close();
			display_editor_window = window.open("<?php echo addslashes(pines_url('com_pdf', 'editor')); ?>", "display_editor", "directories=no,location=no,menubar=no,scrollbars=yes,status=yes,toolbar=no");
			display_editor_window.onload = function(){
				display_editor_window.current_json = holder.val();
				display_editor_window.pdf_file = pdf_file;
				display_editor_window.pages = pages;
				display_editor_window.update_json = function(new_json){
					update_json(new_json);
				};
				display_editor_window.load_editor();
			};
		};

		var update_json = function(new_json) {
			current_holder.val(new_json);
		};

		$(".pf-form div.pf-element.display_edit").each(function(){
			$(this).append($("<div />")
				.addClass("displays_div pf-group")
				.append($("<input />")
					.attr({type: "hidden", value: (undefined !== display_jsons[$(this).attr("id")] ? JSON.stringify(display_jsons[$(this).attr("id")]) : "[]"), name: $(this).attr("id")+"_displays_json"})
					.addClass("displays_holder")
				).append($("<input />")
					.attr({type: "button", value: "Edit Displays"})
					.addClass("displays_button field ui-state-default ui-corner-all")
					.click(function(){
						open_display_editor($(this).prev("input"));
					})
				)
			);
		});
	});
	// ]]>
</script>