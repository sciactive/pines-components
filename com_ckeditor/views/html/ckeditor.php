<?php
/**
 * A view to load the CKEditor editor.
 *
 * @package Components\ckeditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
if (isset($pines->com_elfinder))
	$pines->com_elfinder->load();
$content_css = array_merge($pines->editor->get_css(), array(htmlspecialchars($pines->config->location . $pines->template->editor_css)));
?>
<script type="text/javascript">
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_ckeditor/includes/ckeditor/ckeditor.js");
	pines.load(function(){
		CKEDITOR.config.jqueryOverrideVal = true;
	});
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_ckeditor/includes/ckeditor/adapters/jquery.js");

	pines(function(){
		// Stop CKEditor from adding new lines and indents to HTML source.
		CKEDITOR.on('instanceReady', function(ev){
			var writer = ev.editor.dataProcessor.writer;
			writer.selfClosingEnd = ' />';
			writer.indentationChars = '';
			writer.lineBreakChars = '';
			<?php /* This is another, less robust, way of fixing the line break problem.
			var tags = ['p', 'ol', 'ul', 'li', 'div', 'form', 'table', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'hr', 'script', 'noscript'];
			for (var i in tags) {
				writer.setRules(tags[i], {
					indent: false,
					breakBeforeOpen: false,
					breakAfterOpen: false,
					breakBeforeClose: false,
					breakAfterClose: false
				});
			}
			*/ ?>
		});
		// Convert textareas.
		$("textarea.peditor").ckeditor(function(){}, {
			contentsCss: <?php echo json_encode($content_css); ?>,
			coreStyles_bold: {element: 'strong'},
			coreStyles_italic: {element: 'em'},
			pasteFromWordPromptCleanup: true,
			uiColor: <?php echo json_encode($pines->config->com_ckeditor->ui_color); ?>,
			startupMode: <?php echo json_encode($pines->config->com_ckeditor->default_mode); ?>,
			startupOutlineBlocks: <?php echo json_encode($pines->config->com_ckeditor->show_blocks); ?>,
			<?php if (isset($pines->com_elfinder)) { ?>
			filebrowserBrowseUrl: <?php echo json_encode(pines_url('com_elfinder', 'finder', array('ckeditor' => 'true', 'template' => 'tpl_print'))); ?>,
			<?php } if ($pines->config->com_ckeditor->auto_scayt) { ?>
			scayt_autoStartup: true,
			<?php } else { ?>
			disableNativeSpellChecker: false,
			<?php } ?>
			toolbar: <?php echo json_encode($pines->config->com_ckeditor->toolbar); ?>,
			extraPlugins: 'autogrow,stylesheetparser',
			removePlugins: 'resize',
			baseHref: pines.rela_location,
			autoGrow_minHeight: 200,
			autoGrow_maxHeight: 600,
			autoGrow_onStartup: true
		});
		$("textarea.peditor-simple").ckeditor(function(){}, {
			contentsCss: <?php echo json_encode($content_css); ?>,
			coreStyles_bold: {element: 'strong'},
			coreStyles_italic: {element: 'em'},
			pasteFromWordPromptCleanup: true,
			uiColor: <?php echo json_encode($pines->config->com_ckeditor->ui_color); ?>,
			startupMode: <?php echo json_encode($pines->config->com_ckeditor->default_mode); ?>,
			startupOutlineBlocks: <?php echo json_encode($pines->config->com_ckeditor->show_blocks); ?>,
			<?php if (isset($pines->com_elfinder)) { ?>
			filebrowserBrowseUrl: <?php echo json_encode(pines_url('com_elfinder', 'finder', array('ckeditor' => 'true', 'template' => 'tpl_print'))); ?>,
			<?php } if ($pines->config->com_ckeditor->auto_scayt) { ?>
			scayt_autoStartup: true,
			<?php } else { ?>
			disableNativeSpellChecker: false,
			<?php } ?>
			toolbar: 'Basic',
			extraPlugins: 'stylesheetparser',
			baseHref: pines.rela_location,
			toolbarCanCollapse: false
		});
	});
</script>