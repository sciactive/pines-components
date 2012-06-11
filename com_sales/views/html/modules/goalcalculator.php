<?php
/**
 * Calculates monthly sales goal.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Goal Calculator';
$pines->icons->load();

$daysthismonth = date("t");
$dayspassed = date("j");
$percentmonth = (int) $dayspassed / (int) $daysthismonth;

if (!$this->yellow_multiplier)
	$yellow_multiplier = .8;
else
	$yellow_multiplier = round($this->yellow_multiplier) / 100;

if (!$this->goals) {
	$goals = "20,30,60,80";
} else {
	$this->goals = str_replace(" ", "", $this->goals);
	$goals = $this->goals;
}
$goals = explode(",", $goals);

// Verify Goals
$c = 0;
foreach ($goals as &$goal) {
	$goal = round($goal);
	if ($goal < 1) {
		unset($goals[$c]);
	} elseif ($goal > 1000) {
		unset($goals[$c]);
	}
	$c++;
}
unset($goal);
sort($goals);

$trends = array();
foreach ($goals as $goal) {
	$new_trend = array();
	$new_trend['goal'] = $goal;
	$new_trend['green'] = $goal*$percentmonth;
	$new_trend['yellow'] = $new_trend['green'] * $yellow_multiplier;
	$trends[] = $new_trend;
}
?>
<div id="p_muid_form">
	<style type="text/css" scoped="scoped">
		.table th, .table td {
			padding:1%;
			width: 10%;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			$("[name=days_passed]").keypress(function(e){
				if (e.keyCode == 13) {
					var days_this_month = $("[name=days_this_month]").val();
					var yellow_multiplier = $("[name=yellow_multiplier]").val();
					var days_passed = $("[name=days_passed]").val();
					days_passed = Math.round(days_passed);
					$("[name=days_passed]").val(days_passed);
					var percent_passed = days_passed/days_this_month;
					if (parseFloat(percent_passed) > 1 || days_passed < 1) {
						$("#p_muid_days_passed").addClass("control-group error");
						$("#p_muid_status").show();
					} else {
						$("#p_muid_days_passed").removeClass("control-group error");
						$("#p_muid_status").hide();
						$('td.goal_green').each(function() {
							var goal = $(this).siblings(".goal").text();
							var green = parseFloat(goal*percent_passed);
							var green = green.toFixed(2);
							$(this).html(green);
						});	
						$('td.goal_yellow').each(function() {
							var goal = $(this).siblings(".goal").text();
							var green = goal*percent_passed;
							var yellow = parseFloat(green * yellow_multiplier);
							var yellow = yellow.toFixed(2);
							$(this).html(yellow);
						});	
						percent_passed = parseFloat(percent_passed);
						percent_passed = percent_passed.toFixed(2);
						$('#p_muid_percent_passed').text(percent_passed);
					}
				}
			});
		});
	</script>
<!--	<div style="padding:5px; margin-bottom:10px;" class="alert-info clearfix"><label style="margin-bottom:0;"><span style="float:left;">Days this Month</span></label><span style="float:right;"><?php //echo htmlspecialchars($daysthismonth); ?></span></div>-->
	<div><input name="days_this_month" type="hidden" size="2" value="<?php echo htmlspecialchars($daysthismonth); ?>" disabled="true" /></div>
	<div><input name="yellow_multiplier" type="hidden" size="3" value="<?php echo htmlspecialchars($yellow_multiplier); ?>" disabled="true" /></div>
	<div id="p_muid_status" style="font-size:.7em; display:none;">Impossible # of Days Entered</div>
	<div id="p_muid_days_passed" style="padding:5px; margin-bottom:2px;" class="alert-info clearfix"><label style="margin-bottom:0;"><span style="float:left;">Days Passed</span></label><span style="float:right;"><input name="days_passed" style="text-align:right;" type="text" size="2" value="<?php echo htmlspecialchars($dayspassed); ?>"/></span></div>
	<div style="padding:5px; margin-bottom:2px;" class="alert-success clearfix"><label style="margin-bottom:0;"><span style="float:left;">% of Month Passed</span></label><span id="p_muid_percent_passed" style="float:right;"><?php echo htmlspecialchars($pines->com_sales->round($percentmonth, true)); ?></span></div>
	<div style="padding:5px; margin-bottom:2px;" class="clearfix">
		<table class="table" style="margin-bottom:0;">
			<thead>
				<th>Goal</th>
				<th>Green</th>
				<th>Yellow</th>
			</thead>
			<tbody>
				<?php foreach ($trends as $trend) { ?>
				<tr>
					<td class="goal"><span><?php echo htmlspecialchars($pines->com_sales->round($trend['goal'],true)); ?></span></td>
					<td class="alert-success goal_green"><?php echo htmlspecialchars($pines->com_sales->round($trend['green'],true)); ?></td>
					<td class="alert goal_yellow"><?php echo htmlspecialchars($pines->com_sales->round($trend['yellow'],true)); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>