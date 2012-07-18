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
	<?php } if ($pines->config->com_ckeditor->toolbar == 'Full') { ?>
	toolbar: [
		{name: 'document', items: ['Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates']},
		{name: 'clipboard', items: ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
		{name: 'editing', items: ['Find','Replace','-','SelectAll','-','SpellChecker','Scayt']},
		{name: 'forms', items: ['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField']},
		'/',
		{name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat']},
		{name: 'paragraph', items: ['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl']},
		{name: 'insert', items: ['Image','MediaEmbed','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe']},
		'/',
		{name: 'styles', items: ['Styles','Format','Font','FontSize']},
		{name: 'colors', items: ['TextColor','BGColor']},
		{name: 'links', items: ['Link','Unlink','Anchor']},
		{name: 'tools', items: ['Maximize','ShowBlocks','-','About']}
	],
	<?php } elseif ($pines->config->com_ckeditor->toolbar == 'Basic') { ?>
	toolbar: 'Basic',
	<?php } ?>
	extraPlugins: 'autogrow,stylesheetparser,MediaEmbed',
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
$("textarea.peditor-email").ckeditor(function(){}, {
	pasteFromWordPromptCleanup: true,
	uiColor: <?php echo json_encode($pines->config->com_ckeditor->ui_color); ?>,
	startupMode: <?php echo json_encode($pines->config->com_ckeditor->default_mode); ?>,
	startupOutlineBlocks: <?php echo json_encode($pines->config->com_ckeditor->show_blocks); ?>,
	<?php if (isset($pines->com_elfinder)) { ?>
	filebrowserBrowseUrl: <?php echo json_encode(pines_url('com_elfinder', 'finder', array('ckeditor' => 'true', 'template' => 'tpl_print', 'absolute' => 'true'))); ?>,
	<?php } if ($pines->config->com_ckeditor->auto_scayt) { ?>
	scayt_autoStartup: true,
	<?php } else { ?>
	disableNativeSpellChecker: false,
	<?php } ?>
	extraPlugins: 'autogrow',
	removePlugins: 'resize',
	autoGrow_minHeight: 200,
	autoGrow_maxHeight: 600,
	autoGrow_onStartup: true,
	autoParagraph: false,
	baseHref: <?php echo json_encode($pines->config->full_location); ?>,
	docType: '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
	enterMode: CKEDITOR.ENTER_BR,
	fillEmptyBlocks: false,
	pasteFromWordRemoveFontStyles: false,
	pasteFromWordRemoveStyles: false,
	shiftEnterMode: CKEDITOR.ENTER_P,
	toolbar: [
		{name: 'document', items: ['Source','-','NewPage','DocProps','Preview','Print','-','Templates']},
		{name: 'clipboard', items: ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
		{name: 'editing', items: ['Find','Replace','-','SelectAll','-','SpellChecker','Scayt']},
		'/',
		{name: 'basicstyles', items: ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat']},
		{name: 'paragraph', items: ['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl']},
		{name: 'insert', items: ['Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak']},
		'/',
		{name: 'styles', items: ['Styles','Format','Font','FontSize']},
		{name: 'colors', items: ['TextColor','BGColor']},
		{name: 'links', items: ['Link','Unlink','Anchor']},
		{name: 'tools', items: ['Maximize','ShowBlocks','-','About']}
	]
});
});
</script>