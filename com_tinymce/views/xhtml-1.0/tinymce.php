<?php
/**
 * A view to load the TinyMCE editor.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
if (isset($pines->com_elfinder))
	$pines->com_elfinder->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_tinymce/includes/tiny_mce/jquery.tinymce.js");

	pines(function(){
		$("textarea.peditor").tinymce({
			// Location of TinyMCE script
			script_url : '<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_tinymce/includes/tiny_mce/tiny_mce.js',
			// General options
			theme : "advanced",
			plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
			// Theme options
			theme_advanced_buttons1 : "newdocument,|,undo,redo,|,styleselect,formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			theme_advanced_resize_horizontal : false,
			<?php if (isset($pines->com_elfinder)) { ?>
			// Use elFinder as the file browser.
			file_browser_callback : function(field_name, url, type, win){
				$("<div />").appendTo("body").elfinder({
					url: "<?php echo addslashes(pines_url("com_elfinder", "connector")); ?>",
					dialog: {"width": 900, "modal": true, "zIndex": 400000, "title": "Choose "+type},
					height: <?php echo (int) $pines->config->com_elfinder->default_height; ?>,
					closeOnEditorCallback: true,
					editorCallback: function(url) {
						$("input[name="+field_name+"]", win.document).val(url);
					}
				});
			},
			relative_urls: false,
			<?php } ?>
			// Template's editor CSS
			content_css : "<?php echo htmlspecialchars($pines->config->location . $pines->template->editor_css); ?>"
		});
		$("textarea.peditor-simple").tinymce({
			// Location of TinyMCE script
			script_url : '<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_tinymce/includes/tiny_mce/tiny_mce.js',
			// General options
			theme : "simple",
			// Template's editor CSS
			content_css : "<?php echo htmlspecialchars($pines->config->location . $pines->template->editor_css); ?>"
		});
	});
	// ]]>
</script>