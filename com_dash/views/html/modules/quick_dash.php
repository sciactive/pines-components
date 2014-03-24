<?php
/**
 * Quick dash button.
 *
 * @package Components\dash
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$pines->icons->load();
?>
<script type="text/javascript">
	pines(function(){
		$(".quick-dash button").one("click", function(){
			console.log('here');
			$.ajax({
				url: <?php echo json_encode(pines_url('com_dash', 'quick_dash')); ?>,
				type: "POST",
				dataType: "html",
				data: {},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retrieve the quick dash:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					var jwin = $(window);
					$("#p_muid_quick_dash").html(data).dialog("option", {
						height: jwin.height() * .9,
						width: jwin.width() * .9
					}).dialog("option", {
						position: "center"
					});
				}
			});
		}).click(function(){
			var jwin = $(window);
			$("#p_muid_quick_dash").dialog().dialog("option", {
				height: jwin.height() * .9,
				width: jwin.width() * .9
			}).dialog("option", {
				position: "center"
			});
		});
	});
</script>
<style type="text/css" scoped="scoped">
	#p_muid_button button span {
		display: block;
		padding-top: 32px;
		min-width: 50px;
		background-repeat: no-repeat;
		background-position: top center;
	}
</style>
<div id="p_muid_button" class="quick-dash" style="text-align: center;">
	<button class="btn" title="See your dashboard in a popup dialog.">
		<span class="picon picon-32 picon-dashboard-show">Quick Dash</span>
	</button>
</div>
<div id="p_muid_quick_dash" style="display: none;" title="Dashboard">
	<div class="picon picon-32 picon-throbber" style="height: 32px; background-repeat: no-repeat; background-position: center; text-align:center;"></div>
</div>