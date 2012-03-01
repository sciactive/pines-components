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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css">
	.ui-selectable-helper {
		z-index: 410000;
	}
</style>
<script type="text/javascript">
	pines(function(){
		$(".puploader").each(function(){
			var pfile = $(this);
			var show_finder = function(){
				var start_path = pfile.val().replace(/[^/]+$/, "");
				var dialog = $("<div></div>").elfinder({
					url: <?php echo json_encode(pines_url('com_elfinder', 'connector', array('start_path' => '__start_path__'))); ?>.replace("__start_path__", start_path),
					height: <?php echo (int) $pines->config->com_elfinder->default_height; ?>,
					resizable : false,
					commandsOptions: {
						getfile: {
							multiple: pfile.hasClass("puploader-multiple"),
							folders: pfile.hasClass("puploader-folders")
						}
					},
					getFileCallback: function(file) {
						var title, content = "", value = "";
						console.log(file);
						dialog.dialog("destroy").remove();
						// TODO: Should the URLs be encoded?
						if ($.isArray(file)) {
							title = "Multiple Selections";
							$.each(file, function(i, file){
								content += "<h4"+(i > 0 ? " style=\"margin-top: 2em;\"" : "")+">"+(file.mime == "directory" ? "Folder" : "File")+": "+pines.safe(file.name)+"</h4>";
								if (file.tmb)
									content += "<div style=\"text-align: center; margin-bottom: 1em;\"><span class=\"thumbnail\" style=\"display: inline-block;\"><img alt=\"Thumbnail\" src=\""+pines.safe(file.tmb)+"\" /></span></div>";
								content += "<div style=\"margin-bottom: .5em;\">Type: "+pines.safe(file.mime)+"</div><div style=\"margin-bottom: .5em;\">Path: <tt>"+pines.safe(file.path)+"</tt></div><div>URL: <tt>"+pines.safe(file.url)+"</tt></div>";
								value += (value ? "//" : "")+file.url;
							});
						} else {
							title = "Selected "+(file.mime == "directory" ? "Folder" : "File")+": "+pines.safe(file.name);
							if (file.tmb)
								content = "<div style=\"text-align: center; margin-bottom: 1em;\"><span class=\"thumbnail\" style=\"display: inline-block;\"><img alt=\"Thumbnail\" src=\""+pines.safe(file.tmb)+"\" /></span></div>";
							content += "<div style=\"margin-bottom: .5em;\">Type: "+pines.safe(file.mime)+"</div><div style=\"margin-bottom: .5em;\">Path: <tt>"+pines.safe(file.path)+"</tt></div><div>URL: <tt>"+pines.safe(file.url)+"</tt></div>";
							value = file.url;
						}
						pfile.val(value).change().attr({
							"data-original-title": title,
							"data-content": content
						}).popover({placement: "bottom"});
					}
				}).css("overflow", "visible").dialog({
					width: 900,
					modal: true,
					zIndex: 400000,
					title: "Choose File"+(pfile.hasClass("puploader-multiple") ? "(s)" : "")+(pfile.hasClass("puploader-folders") ? " or Folder"+(pfile.hasClass("puploader-multiple") ? "(s)" : "") : "")
				});
				dialog.dialog("widget").css("overflow", "visible");
			};
			pfile.focus(show_finder).focus(function(){pfile.blur()});
			$("<button class=\"btn\" type=\"button\" style=\"margin-left: .5em\">Browse&hellip;</button>")
			.click(show_finder)
			.insertAfter(pfile);
		});
	});
</script>