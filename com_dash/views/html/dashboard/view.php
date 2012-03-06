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
$pines->com_bootstrap->load();
?>
<div id="p_muid_dashboard">
	<style type="text/css" scoped="scoped">
		#p_muid_dashboard .buttons {
			padding: .5em .25em 0;
			margin-bottom: .5em;
			min-height: 20px;
		}
		#p_muid_dashboard .buttons .controls {
			float: right;
			visibility: hidden;
		}
		#p_muid_dashboard .buttons:hover .controls {
			visibility: visible;
		}
		#p_muid_dashboard .buttons .controls .w_icon {
			cursor: pointer;
		}
		#p_muid_dashboard .buttons a {
			margin: 0 .25em .5em;
		}
		#p_muid_dashboard .buttons a span {
			display: block;
			background-repeat: no-repeat;
		}
		#p_muid_dashboard .buttons.small a span {
			padding-left: 18px;
			background-position: center left;
		}
		#p_muid_dashboard .buttons.large a span {
			padding-top: 32px;
			background-position: top center;
			min-width: 32px;
		}
		#p_muid_dashboard .buttons .separator {
			cursor: default;
			width: 1px;
			padding-left: 0;
			padding-right: 0;
			margin-left: -.1em;
			margin-right: -.1em;
			overflow: hidden;
		}
		#p_muid_dashboard .buttons .separator span {
			width: 1px;
			min-width: inherit;
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
		#p_muid_dashboard .object > .widget_header {
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
			position: absolute;
			left: auto;
			right: 0;
		}
		#p_muid_dashboard .object .controls .w_icon {
			float: right;
			cursor: pointer;
			margin-left: .2em;
		}
		#p_muid_dashboard .object > .content {
			padding: .5em;
		}
		#p_muid_dashboard .object.minimized > .content {
			display: none;
		}
		#p_muid_page_tabs > ul li .w_icon {
			cursor: pointer;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			<?php if ($this->editable) { ?>
			$("#p_muid_page_nav").sortable({
				axis: "x",
				items: "> li:not(.new_tab_button)",
				update: function(){
					var struct = [];
					$("#p_muid_page_nav li:not(.new_tab_button) a").each(function(){
						struct.push($(this).attr("href").replace(/^#/, ""));
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
			});
			<?php } ?>
			$("#p_muid_page_nav a").on("click", ".edit_tab", function(e){
				e.preventDefault();
				e.stopPropagation();
			}).on("show", function(e){
				var tab = $($(e.target).attr("href"));
				if (tab.data("tab_loaded"))
					return;
				tab.data("tab_loaded", true).data("trigger_link", $(this)).load(tab.attr("data-url"));
			});
			var initial_link = $("#p_muid_page_nav li.active a")
			$(initial_link.attr("href")).data("tab_loaded", true).data("trigger_link", initial_link);
		});
	</script>
	<ul class="nav nav-tabs" style="clear: both;" id="p_muid_page_nav">
		<?php $first = true; foreach ($this->entity->tabs as $cur_key => $cur_tab) { ?>
		<li class="<?php echo ($cur_key === $this->selected_tab || (!isset($this->selected_tab) && $first)) ? 'active' : ''; ?>">
			<a href="#<?php echo htmlspecialchars($cur_key); ?>" data-toggle="tab">
				<?php echo htmlspecialchars($cur_tab['name']);
				if ($this->editable) { ?>
				<span class="edit_tab w_icon icon-cog" title="Edit this Tab" onclick="var link = $(this).closest('a'); $('#<?php echo htmlspecialchars($cur_key); ?>').data('tab_loaded', true).data('trigger_link', link).load(<?php echo htmlspecialchars(json_encode(pines_url('com_dash', 'dashboard/edittab', array('key' => $cur_key)))); ?>); link.tab('show');"></span>
				<?php } ?>
			</a>
		</li>
		<?php $first = false; }
		if ($this->editable) { ?>
		<li class="new_tab_button"><a href="#p_muid_edit_tab" data-toggle="tab"><span class="icon-plus"></span></a></li>
		<?php } ?>
	</ul>
	<div class="tab-content" id="p_muid_page_tabs">
		<?php $first = true; foreach ($this->entity->tabs as $cur_key => $cur_tab) { ?>
		<div class="tab-pane <?php echo ($cur_key === $this->selected_tab || (!isset($this->selected_tab) && $first)) ? 'active' : ''; ?>" id="<?php echo htmlspecialchars($cur_key); ?>" data-url="<?php echo htmlspecialchars(pines_url('com_dash', 'dashboard/tab', array('key' => $cur_key, 'editable' => ($this->editable ? 'true' : 'false')))); ?>">
			<?php if ($cur_key === $this->selected_tab || (!isset($this->selected_tab) && $first)) { ?>
			<script type="text/javascript">
				pines(function(){
					$("#<?php echo htmlspecialchars($cur_key); ?>").data("tab_loaded", true);
				});
			</script>
			<?php echo $pines->com_dash->show_dash_tab($cur_key, $this->editable);
			} else { ?>
			<div style="display: block; width: 32px; height: 32px; margin: 0 auto;" class="picon picon-32 picon-throbber"></div>
			<?php } ?>
		</div>
		<?php $first = false; }
		if ($this->editable) { ?>
		<div class="tab-pane" id="p_muid_edit_tab" data-url="<?php echo htmlspecialchars(pines_url('com_dash', 'dashboard/edittab')); ?>">
			<div style="display: block; width: 32px; height: 32px; margin: 0 auto;" class="picon picon-32 picon-throbber"></div>
		</div>
		<?php } ?>
	</div>
</div>