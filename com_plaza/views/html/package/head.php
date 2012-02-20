<?php
/**
 * Some common JavaScript functions.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.com_plaza = {
		ajax_box: null,
		ajax_show: function(){
			if (!pines.com_plaza.ajax_box)
				pines.com_plaza.ajax_box = $("<div style=\"display: none; position: absolute; top: 0; left: 0; right: 0; z-index: 2000; text-align: center;\"><img src=\"<?php echo addslashes(htmlspecialchars($pines->config->location)); ?>components/com_plaza/includes/ajax-loader.gif\" alt=\"\" /></div>").prependTo("body");
			pines.com_plaza.ajax_box.show();
		},
		ajax_hide: function(){
			pines.com_plaza.ajax_box.hide();
		},
		confirm_changes: function(message, changes, callback, cancel){
			var dialog;
			if (changes.service.length) {
				$("<div title=\"Package Change Requires a Service\"></div>").append("<p>In order to complete the requested action, the following services need to be installed.</p>")
				.append("<ul><li>"+$.map(changes.service, pines.safe).join("</li><li>")+"</li></ul></div>")
				.append("<p>Click on a service to see the available packages that provide it.</p>")
				.find("li").each(function(){
					var service = $(this);
					service.wrap($("<a href=\""+pines.safe(<?php echo json_encode(pines_url('com_plaza', 'package/repository', array('service' => '__service__'))); ?>).replace("__service__", service.text())+"\"></a>"));
				}).end()
				.dialog({
					modal: true,
					width: 500
				});
				return;
			}
			if (changes.install.length || changes.remove.length) {
				dialog = $("<div title=\"Confirm Required Package Changes\"></div>").append("<p>"+pines.safe(message.changes)+"</p>");
				if (changes.install.length)
					dialog.append("<div><h3>Install the Packages</h3><ul><li>"+$.map(changes.install, pines.safe).join("</li><li>")+"</li></ul></div>");
				if (changes.remove.length)
					dialog.append("<div><h3>Remove the Packages</h3><ul><li>"+$.map(changes.remove, pines.safe).join("</li><li>")+"</li></ul></div>");
				dialog.append("<p>Are you sure you want to make these changes?</p>");
			} else {
				dialog = $("<div title=\"Confirm Package Change\"></div>").append("<p>"+pines.safe(message.nochanges)+"</p>");
			}
			dialog.dialog({
				modal: true,
				width: 500,
				buttons: {
					"Yes, Make Changes": function(){
						dialog.dialog("close");
						if (callback)
							callback.call();
					},
					"No, Cancel Changes": function(){
						dialog.dialog("close");
						if (cancel)
							cancel.call();
					}
				}
			});
		}
	};
</script>