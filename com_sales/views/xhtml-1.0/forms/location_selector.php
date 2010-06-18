<?php
/**
 * Display a form to select a location.
 * 
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form {
		padding-left: 25px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	pines(function(){

		// Location Tree
		var location = $("#p_muid_form [name=location]");
		var location_saver = $("#p_muid_form [name=location_saver]");
		var location_tree = $("#p_muid_form div.location_tree");
		var block_change = function() {
			if (location_saver.val() != 'individual')
				return false;
		};
		location_tree.tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_jstree', 'groupjson'); ?>"
				}
			},
			selected : ["<?php echo ($this->location == 'all') ? $_SESSION['user']->group->guid : $this->location; ?>"],
			callback : {
				// The tree is disabled when searching all locations.
				beforechange : block_change,
				beforeclose : block_change,
				beforeopen : block_change,
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function() {
					return false;
				}
			}
		});

		$("#p_muid_form [name=all_groups]").change(function(){
			var all_groups = $(this);
			if (all_groups.is(":checked") && all_groups.val() == "individual") {
				location_tree.removeClass("ui-priority-secondary");
				location_saver.val('individual');
			} else if (all_groups.is(":checked") && all_groups.val() == "allGroups") {
				location_tree.addClass("ui-priority-secondary");
				location_saver.val('all');
			}
		}).change();
	});
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="">
	<div class="pf-element">
		<label><input class="pf-field ui-widget-content" type="radio" name="all_groups" value="allGroups" checked="checked" />All Locations</label>
		<label><input class="pf-field ui-widget-content" type="radio" name="all_groups" value="individual" <?php echo ($this->location != 'all') ? 'checked="checked"' : ''; ?>/>Single Location</label>
	</div>
	<div class="pf-element location_tree" style="padding-bottom: 5px;"></div>
	<div class="pf-element">
		<input type="hidden" name="location" value="<?php echo $this->location; ?>" />
		<input type="hidden" name="location_saver" value="<?php echo ($this->location == 'all') ? 'all' : 'individual'; ?>" />
	</div>
</form>