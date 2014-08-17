<?php
/**
 * A view to load the TinyMCE editor.
 *
 * @package Components\tinymce
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
pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_tinymce/includes/tiny_mce/jquery.tinymce.js");
pines(function(){
$("textarea.peditor").tinymce({
	// Location of TinyMCE script
	script_url: '<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_tinymce/includes/tiny_mce/tiny_mce.js',
	// General options
	theme: "advanced",
        protect: [
            /<noscript>([\s\S]*?)<\/noscript>/g // Doesn't escape out of noscript tag elements
        ],
        valid_elements: "*[*]",
        extended_valid_elements: "*[*]",
	<?php switch ($pines->config->com_tinymce->skin) {
		case 'default':
		default:
			break;
		case 'cirkuit':
			?>
			skin: "cirkuit",
			<?php
			break;
		case 'o2k7-blue':
			?>
			skin: "o2k7",
			<?php
			break;
		case 'o2k7-silver':
			?>
			skin: "o2k7",
			skin_variant: "silver",
			<?php
			break;
		case 'o2k7-black':
			?>
			skin: "o2k7",
			skin_variant: "black",
			<?php
			break;
	}
	switch ($pines->config->com_tinymce->features) {
		case 'default':
		default:
			?>
			plugins : "autolink,style,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,visualchars,xhtmlxtras,advlist,youtube",
			// Theme options
			theme_advanced_buttons1: "newdocument,|,undo,redo,|,styleselect,formatselect,fontselect,fontsizeselect,|,fullscreen",
			theme_advanced_buttons2: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,outdent,indent,blockquote,|,hr,removeformat,|,youtube,charmap,media,advhr",
			theme_advanced_buttons3: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview",
			<?php
			break;
		case 'full':
			?>
			plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,youtube",
			// Theme options
			theme_advanced_buttons1: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,youtube,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage",
			<?php
			break;
		case 'minimal':
			?>
			plugins : "style,advimage,advlink,inlinepopups,insertdatetime,preview,media,contextmenu,noneditable,xhtmlxtras,advlist",
			// Theme options
			theme_advanced_buttons1: "undo,redo,|,styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor",
			theme_advanced_buttons2: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,hr,removeformat,|,bullist,numlist,|,link,unlink,anchor,image,cleanup,code",
			theme_advanced_buttons3: "",
			<?php
			break;
		case 'custom':
			?>
			plugins : <?php echo json_encode($pines->config->com_tinymce->custom_plugins); ?>,
			// Theme options
			theme_advanced_buttons1: <?php echo json_encode($pines->config->com_tinymce->custom_bar_1); ?>,
			theme_advanced_buttons2: <?php echo json_encode($pines->config->com_tinymce->custom_bar_2); ?>,
			theme_advanced_buttons3: <?php echo json_encode($pines->config->com_tinymce->custom_bar_3); ?>,
			theme_advanced_buttons4: <?php echo json_encode($pines->config->com_tinymce->custom_bar_4); ?>,
			<?php
			break;
	} ?>
	theme_advanced_toolbar_location: "top",
	theme_advanced_toolbar_align: "left",
	theme_advanced_statusbar_location: "bottom",
	theme_advanced_resizing: true,
	theme_advanced_resize_horizontal: false,
	<?php if (isset($pines->com_elfinder)) { ?>
	// Use elFinder as the file browser.
	file_browser_callback : function(field_name, url, type, win){
		elfdlg = $("<div></div>").appendTo("body").elfinder({
			url: <?php echo json_encode(pines_url("com_elfinder", "connector")); ?>,
			height: <?php echo (int) $pines->config->com_elfinder->default_height; ?>,
			resizable : false,
			getFileCallback: function(file) {
				$("input[name="+field_name+"]", win.document).val(file);
				elfdlg.dialog("close");
			}
		});
		elfdlg.css("overflow", "visible").dialog({
			width: 900,
			modal: true,
			zIndex: 400000,
			title: "Choose "+type,
			close: function(){
				elfdlg.elfinder("destroy").dialog("destroy").remove();
			}
		}).dialog("widget").css("overflow", "visible");
	},
	relative_urls: false,
	<?php } ?>
	// Template's editor CSS
	convert_urls: <?php echo ($pines->config->com_tinymce->convert_urls) ? 'true' : 'false'; ?>,
	content_css: <?php echo json_encode(implode(',', $content_css)); ?>,
	preformatted: <?php echo $pines->config->com_tinymce->preformatted ? 'true' : 'false'; ?>,
	schema: "html5",
	onchange_callback: function(i){var el=i.getElement();if(el)$(el).triggerHandler('change');},
	handle_event_callback: function(e, i){var el=i.getElement();if(el)$(el).triggerHandler(e.type);return true;}
});
$("textarea.peditor-simple").tinymce({
	script_url: '<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_tinymce/includes/tiny_mce/tiny_mce.js',
	theme: "advanced",
        valid_elements: "*[*]",
        extended_valid_elements: "*[*]",
	theme_advanced_buttons1: "newdocument,|,bold,italic,underline,strikethrough,|,undo,redo,|,bullist,numlist",
	theme_advanced_buttons2: "",
	theme_advanced_buttons3: "",
	theme_advanced_buttons4: "",
	<?php switch ($pines->config->com_tinymce->skin) {
		case 'default':
		default:
			break;
		case 'o2k7-blue':
			?>
			skin : "o2k7",
			<?php
			break;
		case 'o2k7-silver':
			?>
			skin : "o2k7",
			skin_variant : "silver",
			<?php
			break;
		case 'o2k7-black':
			?>
			skin : "o2k7",
			skin_variant : "black",
			<?php
			break;
	} ?>
	content_css: <?php echo json_encode(implode(',', $content_css)); ?>,
	preformatted: <?php echo $pines->config->com_tinymce->preformatted ? 'true' : 'false'; ?>,
	schema: "html5",
	onchange_callback: function(i){var el=i.getElement();if(el)$(el).triggerHandler('change');},
	handle_event_callback: function(e, i){var el=i.getElement();if(el)$(el).triggerHandler(e.type);return true;}
});
$("textarea.peditor-email").tinymce({
	script_url : '<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_tinymce/includes/tiny_mce/tiny_mce.js',
	theme : "advanced",
        valid_elements: "*[*]",
        extended_valid_elements: "*[*]",
	<?php switch ($pines->config->com_tinymce->skin) {
		case 'default':
		default:
			break;
		case 'cirkuit':
			?>
			skin : "cirkuit",
			<?php
			break;
		case 'o2k7-blue':
			?>
			skin : "o2k7",
			<?php
			break;
		case 'o2k7-silver':
			?>
			skin : "o2k7",
			skin_variant : "silver",
			<?php
			break;
		case 'o2k7-black':
			?>
			skin : "o2k7",
			skin_variant : "black",
			<?php
			break;
	} ?>
	plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
	theme_advanced_buttons1 : "newdocument,|,undo,redo,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect,|,fullscreen",
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,cleanup,help,code,|,forecolor,backcolor",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,advhr,|,print,|,ltr,rtl",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertdate,inserttime,preview",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	theme_advanced_resize_horizontal : true,
	<?php if (isset($pines->com_elfinder)) { ?>
	file_browser_callback : function(field_name, url, type, win){
		elfdlg = $("<div></div>").appendTo("body").elfinder({
			url: <?php echo json_encode(pines_url("com_elfinder", "connector")); ?>,
			height: <?php echo (int) $pines->config->com_elfinder->default_height; ?>,
			resizable : false,
			getFileCallback: function(file) {
				$("input[name="+field_name+"]", win.document).val(file);
				elfdlg.dialog("close");
			}
		});
		elfdlg.css("overflow", "visible").dialog({
			width: 900,
			modal: true,
			zIndex: 400000,
			title: "Choose "+type,
			close: function(){
				elfdlg.elfinder("destroy").dialog("destroy").remove();
			}
		}).dialog("widget").css("overflow", "visible");
	},
	<?php } ?>
	relative_urls: false,
	remove_script_host: false,
	document_base_url: <?php echo json_encode($pines->config->full_location); ?>,
	keep_styles: true,
	object_resizing: false,
	doctype: '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
	element_format: 'xhtml',
	force_hex_style_colors: true,
	forced_root_block: 'div',
	inline_styles: true,
	// No CSS for emails.
	content_css : [],
	preformatted : true,
	verify_html: false,
	onchange_callback: function(i){var el=i.getElement();if(el)$(el).triggerHandler('change');},
	handle_event_callback: function(e, i){var el=i.getElement();if(el)$(el).triggerHandler(e.type);return true;}
});
// tinymce.execCommand('mceToggleEditor',false,'content');
});
</script>