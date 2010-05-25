<?php
/**
 * A view to load the file uploader.
 *
 * @package Pines
 * @subpackage com_elfinderupload
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
		$(".puploader").each(function(){
			var pfile = $(this);
			var show_finder = function(){
				$("<div />").appendTo("body").elfinder({
					url: "<?php echo addslashes(pines_url('com_elfinder', 'connector')); ?>",
					dialog: {"width": 900, "modal": true, "zIndex": 400000, "title": "Choose File"},
					height: <?php echo $pines->config->com_elfinder->default_height; ?>,
					closeOnEditorCallback: true,
					editorCallback: function(url) {
						pfile.val(url);
					}
				});
			};
			pfile.click(show_finder).focus(function(){pfile.blur()});
			$("<button />", {
				"class": "ui-state-default ui-corner-all",
				"css": {"margin-left": ".5em"},
				"html": "Browse"
			})
			.button()
			.click(show_finder)
			.insertAfter(pfile);
		});
	});
	// ]]>
</script>