<?php
/**
 * Provides a file manager.
 *
 * @package Pines
 * @subpackage com_elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'File Manager';
$pines->com_elfinder->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		$(".com_elfinder_finder").elfinder({
			url: "<?php echo addslashes(pines_url('com_elfinder', 'connector')); ?>",
			docked: false,
			height: <?php echo $pines->config->com_elfinder->default_height; ?>
		});

		$(".pfile").each(function(){
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
<div class="com_elfinder_finder"></div>
<!-- File upload testing.
<br />
<div class="pf-form">
	<div class="pf-heading">
		<h1>File Uploading</h1>
	</div>
	<div class="pf-element">
		<span class="pf-label">File</span>
		<input class="pf-field ui-widget-content pfile" type="text" name="file" />
	</div>
</div>
-->