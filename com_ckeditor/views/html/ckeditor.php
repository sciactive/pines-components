<?php
/**
 * A view to load the CKEditor editor.
 *
 * @package Pines
 * @subpackage com_ckeditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
if (isset($pines->com_elfinder))
	$pines->com_elfinder->load();
$content_css = array_merge(array(htmlspecialchars($pines->config->location . $pines->template->editor_css)), $pines->editor->get_css());
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_ckeditor/includes/ckeditor/ckeditor.js");
	pines.load(function(){
		CKEDITOR.config.jqueryOverrideVal = true;
	});
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_ckeditor/includes/ckeditor/adapters/jquery.js");

	pines(function(){
		$("textarea.peditor").ckeditor(function(){}, {
			toolbar : '<?php echo htmlspecialchars($pines->config->com_ckeditor->toolbar); ?>',
			contentsCss : <?php echo json_encode($content_css); ?>,
			coreStyles_bold	: { element : 'strong' },
			coreStyles_italic : { element : 'em' },
			<?php if (isset($pines->com_elfinder)) { ?>
			filebrowserBrowseUrl : <?php echo json_encode(pines_url('com_elfinder', 'finder', array('ckeditor' => 'true', 'template' => 'tpl_print'))); ?>,
			<?php } ?>
			extraPlugins : 'autogrow,stylesheetparser',
			removePlugins : 'resize',
			<?php if ($pines->config->com_ckeditor->auto_scayt) { ?>
			scayt_autoStartup : true,
			<?php } ?>
			pasteFromWordPromptCleanup : true
		});
		$("textarea.peditor-simple").ckeditor(function(){}, {
			toolbar : 'Basic',
			contentsCss : <?php echo json_encode($content_css); ?>,
			coreStyles_bold	: { element : 'strong' },
			coreStyles_italic : { element : 'em' },
			<?php if (isset($pines->com_elfinder)) { ?>
			filebrowserBrowseUrl : <?php echo json_encode(pines_url('com_elfinder', 'finder', array('ckeditor' => 'true', 'template' => 'tpl_print'))); ?>,
			<?php } ?>
			extraPlugins : 'stylesheetparser',
			<?php if ($pines->config->com_ckeditor->auto_scayt) { ?>
			scayt_autoStartup : true,
			<?php } ?>
			pasteFromWordPromptCleanup : true,
			//resize_dir : 'vertical',
			toolbarCanCollapse : false
		});
	});
	// ]]>
</script>