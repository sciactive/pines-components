<?php
/**
 * Shows the user's dashboard.
 *
 * @package Pines
 * @subpackage com_dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Dashboard';
$pines->com_inuitcss->load();
?>
<div id="p_muid_dashboard">
	<style type="text/css" scoped="scoped">
		/* <![CDATA[ */
		#p_muid_dashboard .buttons {
			padding: .5em .25em 0;
			margin-bottom: .5em;
		}
		#p_muid_dashboard .buttons .controls {
			float: right;
			visibility: hidden;
		}
		#p_muid_dashboard .buttons:hover .controls {
			visibility: visible;
		}
		#p_muid_dashboard .buttons .controls .ui-icon {
			cursor: pointer;
		}
		#p_muid_dashboard .buttons a {
			margin: 0 .25em .5em;
		}
		#p_muid_dashboard .buttons a .ui-button-text {
			margin: 0;
			padding: .2em;
		}
		#p_muid_dashboard .buttons a .ui-button-text span {
			display: block;
			padding-top: 32px;
			min-width: 50px;
			background-repeat: no-repeat;
			background-position: top center;
		}
		#p_muid_dashboard .buttons .separator {
			display: inline-block;
			margin: 0 .25em .5;
			width: 1px;
			padding: .2em 0;
		}
		#p_muid_dashboard .buttons .separator span {
			display: block;
			padding-top: 32px;
			width: 1px;
		}
		#p_muid_dashboard .column {
			min-height: 20px;
			padding-bottom: 20px;
			/*margin-bottom: -100%;
			padding-bottom: 100%; */
		}
		#p_muid_dashboard .object, #p_muid_dashboard .placeholder {
			margin-bottom: 1em;
			padding: 0;
		}
		#p_muid_dashboard .object.maximized {
			position: absolute;
			left: 0;
			top: 0;
			width: 100% !important;
			height: 100% !important;
			z-index: 500;
		}
		#p_muid_dashboard .object.maximized .controls .move_widget {
			display: none;
		}
		#p_muid_dashboard .object.minimized {
			height: auto !important;
		}
		#p_muid_dashboard .object > .ui-widget-header {
			cursor: move;
		}
		#p_muid_dashboard .object .controls {
			float: right;
			visibility: hidden;
			position: relative;
			overflow: visible;
		}
		#p_muid_dashboard .object:hover .controls {
			visibility: visible;
		}
		#p_muid_dashboard .object .controls .edit_widget_menu {
			padding: .5em;
			white-space: nowrap;
			position: absolute;
			top: 100%;
			right: 0;
			z-index: 5000;
		}
		#p_muid_dashboard .object .controls .ui-icon {
			float: right;
			cursor: pointer;
		}
		#p_muid_dashboard .object > .content {
			padding: .5em;
		}
		#p_muid_dashboard .object.minimized > .content {
			display: none;
		}
		#p_muid_page_tabs > ul li .ui-icon {
			float: right;
			cursor: pointer;
		}
		/* ]]> */
	</style>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var tabs = $("#p_muid_page_tabs").tabs({
				<?php if (!empty($this->selected_tab)) {
					$i = 0;
					foreach(array_keys($this->entity->tabs) as $k) {
						if($k == $this->selected_tab)
							break;
						$i++;
					}
					echo "selected: $i,\n";
				} ?>
				cache: true
			}).find(".ui-tabs-nav").sortable({
				axis: "x",
				items: "> li:not(.new_tab_button)",
				update: function(){
					var struct = [];
					$("#p_muid_page_tabs > ul > li[id]").each(function(){
						struct.push($(this).attr("id"));
					});
					$.ajax({
						url: <?php echo json_encode(pines_url('com_dash', 'dashboard/savetabs_json')); ?>,
						type: "POST",
						dataType: "json",
						data: {"order": JSON.stringify(struct)},
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to save the sort order:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
						},
						success: function(data){
							if (data == "false") {
								pines.error("Sort order could not be saved.")
								return;
							}
						}
					});
				}
			}).end();
			p_muid_edit_tab = function(tab, url){
				var index = tab.prevAll().length;
				// Load the edit page, then reset the url.
				tabs.tabs("url", index, url).tabs("select", index).tabs("load", index);
			};
		});
		// ]]>
	</script>
	<div id="p_muid_page_tabs" style="clear: both;">
		<ul>
			<?php foreach ($this->entity->tabs as $cur_key => $cur_tab) { ?>
			<li id="<?php echo htmlspecialchars($cur_key); ?>">
				<a href="<?php echo htmlspecialchars(pines_url('com_dash', 'dashboard/tab', array('key' => $cur_key))); ?>"><?php echo htmlspecialchars($cur_tab['name']); ?></a>
				<span class="edit_tab ui-icon ui-icon-gear" title="Edit this Tab" onclick="p_muid_edit_tab($(this).closest('li'), <?php echo htmlspecialchars(json_encode(pines_url('com_dash', 'dashboard/edittab', array('key' => $cur_key)))); ?>); $(this).data('old_url', <?php echo htmlspecialchars(json_encode(pines_url('com_dash', 'dashboard/tab', array('key' => $cur_key)))); ?>);"></span>
			</li>
			<?php } ?>
			<li class="new_tab_button"><a href="<?php echo htmlspecialchars(pines_url('com_dash', 'dashboard/edittab')); ?>"><span class="ui-icon ui-icon-plus"></span>&nbsp;</a></li>
		</ul>
	</div>
</div>