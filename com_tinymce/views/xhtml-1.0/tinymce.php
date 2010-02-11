<?php
/**
 * A view to load the TinyMCE editor.
 *
 * @package Pines
 * @subpackage com_tinymce
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

echo ("<script type=\"text/javascript\" src=\"{$pines->config->rela_location}components/com_tinymce/js/tiny_mce/jquery.tinymce.js\"></script>\n");
?>
<script type="text/javascript">
// <![CDATA[
$(function(){
	$('textarea.peditor').tinymce({
		// Location of TinyMCE script
		script_url : '<?php echo $pines->config->rela_location; ?>components/com_tinymce/js/tiny_mce/tiny_mce.js',

		// General options
		theme : "advanced",
		skin : "o2k7",
		skin_variant : "silver",
		plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		theme_advanced_resize_horizontal : false,

		// Example content CSS (should be your site CSS)
		content_css : "<?php echo $pines->config->rela_location.$pines->template->editor_css; ?>",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js"
	});

	$('textarea.peditor_simple').tinymce({
		// Location of TinyMCE script
		script_url : '<?php echo $pines->config->rela_location; ?>components/com_tinymce/js/tiny_mce/tiny_mce.js',

		// General options
		theme : "simple",

		// Example content CSS (should be your site CSS)
		content_css : "<?php echo $pines->config->rela_location.$pines->template->editor_css; ?>"
	});
});
// ]]>
</script>