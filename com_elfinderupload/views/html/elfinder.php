<?php
/**
 * A view to load the file uploader.
 *
 * @package Components\elfinderupload
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css">
.ui-selectable-helper {
	z-index: 410000;
}
</style>
<script type="text/javascript">
pines(function(){$(".puploader").each(function(){
	var pfile = $(this),
		unique_id = pfile.data("elfinder_unique_id"),
		dialog_open = false;
	if (!unique_id) {
		unique_id = Math.floor(Math.random()*1000001);
		pfile.data("elfinder_unique_id", unique_id);
	}
	var show_finder = function(){
		dialog_open = true;
		var url;
		if (pfile.hasClass("puploader-temp"))
			url = <?php echo json_encode(pines_url('com_elfinder', 'connector', array('temp' => 'true', 'request_id' => '__request_id__'))); ?>.replace("__request_id__", unique_id);
		else {
			var start_path = pfile.val().replace(/[^/]+$/, "");
			url = <?php echo json_encode(pines_url('com_elfinder', 'connector', array('start_path' => '__start_path__'))); ?>.replace("__start_path__", start_path);
		}
		pfile.elfdlg = $('<div/>').appendTo("body");
		pfile.elf = pfile.elfdlg.elfinder({
			url: url,
			height: <?php echo (int) $pines->config->com_elfinder->default_height; ?>,
			resizable : false,
			commandsOptions: {
				getfile: {
					onlyURL: false,
					multiple: pfile.hasClass("puploader-multiple"),
					folders: pfile.hasClass("puploader-folders")
				}
			},
			getFileCallback: function(file) {
				var title, content = "", value = "";
				// TODO: Should the URLs be encoded?
				if ($.isArray(file)) {
					title = "Multiple Selections";
					$.each(file, function(i, file){
						content += '<h4'+(i > 0 ? ' style="margin-top: 2em;"' : '')+'>'+(file.mime == "directory" ? "Folder" : "File")+": "+pines.safe(file.name)+'</h4>';
						if (file.tmb)
							content += '<div style="text-align: center; margin-bottom: 1em;"><span class="thumbnail" style="display: inline-block;"><img alt="Thumbnail" src="'+pines.safe(file.tmb)+'" /></span></div>';
						content += '<div style="margin-bottom: .5em;">Type: '+pines.safe(file.mime)+'</div><div style="margin-bottom: .5em;">Path: <tt>'+pines.safe(file.path)+'</tt></div><div>URL: <tt>'+pines.safe(file.url)+'</tt></div>';
						value += (value ? "//" : "")+file.url;
					});
				} else {
					title = "Selected "+(file.mime == "directory" ? "Folder" : "File")+": "+pines.safe(file.name);
					if (file.tmb && !pfile.hasClass("puploader-temp"))
						content = '<div style="text-align: center; margin-bottom: 1em;"><span class="thumbnail" style="display: inline-block;"><img alt="Thumbnail" src="'+pines.safe(file.tmb)+'" /></span></div>';
					content += '<div style="margin-bottom: .5em;">Type: '+pines.safe(file.mime)+'</div><div style="margin-bottom: .5em;">Path: <tt>'+pines.safe(file.path)+'</tt></div><div>URL: <tt>'+pines.safe(file.url)+'</tt></div>';
					value = file.url;
				}
				pfile.val(value).attr({
					"data-original-title": title,
					"data-content": content
				}).popover({placement: "bottom"});
				pfile.elfdlg.dialog("close");
				pfile.change();
			}
		}).elfinder('instance');
		pfile.elfdlg.css("overflow", "visible").dialog({
			width: 900,
			modal: true,
			autoOpen: false,
			zIndex: 400000,
			title: "Choose File"+(pfile.hasClass("puploader-multiple") ? "(s)" : "")+(pfile.hasClass("puploader-folders") ? " or Folder"+(pfile.hasClass("puploader-multiple") ? "(s)" : "") : ""),
			close: function(){
				pfile.elfdlg.unbind("dialogopen").unbind("open."+pfile.elf.namespace).unbind("select."+pfile.elf.namespace).elfinder("destroy").dialog("destroy").remove();
				delete pfile.elfdlg;
				delete pfile.elf;
				dialog_open = false;
			}
		});
		pfile.elfdlg.dialog("widget").css("overflow", "visible");
		if (pfile.hasClass("puploader-temp")) {
			pfile.elfdlg.bind("dialogopen", function(e){
				pfile.elfdlg.dialog("option", {"width": "auto", "resizable": false});
				// Only open a new dialog if one isn't there already.
				if (!pfile.elfdlg.find(".elfinder-upload-dialog-wrapper").length)
					pfile.elf.exec("upload");
				pfile.elfdlg.children(":not(.elfinder-dialog)").addClass("ui-helper-hidden-accessible").end()
				.find(".elfinder-overlay").remove().end()
				.find(".elfinder-dialog").css("position", "static")
				.find(".ui-dialog-titlebar-close").hide().end().end()
				.dialog("option", "position", "center");
			}).one("open."+pfile.elf.namespace, function(e){
				// Click the file upload.
				pfile.elfdlg.find(".elfinder-toolbar .elfinder-button input[type=file]").click();
			}).one("select."+pfile.elf.namespace, function(e){
				var i = setInterval(function(){
					if (!pfile.elf.selected().length)
						return;
					clearInterval(i);
					pfile.elf.exec("getfile").fail(function() {
						pfile.elfdlg.dialog("close");
					});
				}, 10);
			});
		}
		pfile.elfdlg.dialog("open");
	};
	pfile.focus(function(){if (!dialog_open) show_finder(); pfile.blur()}).change(function(){
		if (pfile.val() == '')
			pfile.attr({"data-original-title": 'File Upload', "data-content": 'Click to upload files.'});
	});
	$('<button class="btn" type="button" style="margin-left: .5em">Browse&hellip;</button>')
	.click(function(){pfile.focus()})
	.insertAfter(pfile);
});});
</script>