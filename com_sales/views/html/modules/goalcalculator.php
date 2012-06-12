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

$daysthismonth = (int) date('t');
$dayspassed = (int) date('j');
$percentmonth = $dayspassed / $daysthismonth;

if (!$this->yellow_multiplier)
	$yellow_multiplier = .8;
else
	$yellow_multiplier = round($this->yellow_multiplier) / 100;

if (!$this->goals)
	$goals = '20,30,60,80';
else
	$goals = str_replace(' ', '', $this->goals);
$goals = explode(',', $goals);

// Verify Goals
foreach ($goals as $key => &$goal) {
	if ($goal < 1)
		unset($goals[$key]);
}
unset($goal);
sort($goals);

$trends = array();
foreach ($goals as $goal) {
	$trends[] = array(
		'goal' => $goal,
		'green' => $goal * $percentmonth,
		'yellow' => $goal * $percentmonth * $yellow_multiplier
	);
}

?>
<div id="p_muid_form">
	<style type="text/css" scoped="scoped">
		#p_muid_form .table td {
			text-align: right;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			var calc = function(){
				var days_this_month = <?php echo json_encode($daysthismonth); ?>,
					yellow_multiplier = <?php echo json_encode($yellow_multiplier); ?>,
					days_passed_box = $("[name=days_passed]", "#p_muid_form"),
					days_passed = parseInt(days_passed_box.val()),
					percent_passed = days_passed / days_this_month;
				days_passed_box.val(days_passed);
				if (parseFloat(percent_passed) > 1 || days_passed < 1) {
					$("#p_muid_days_passed").addClass("control-group error");
					$("#p_muid_err").show();
					return;
				}
				$("#p_muid_days_passed").removeClass("control-group error");
				$("#p_muid_err").hide();
				$('#p_muid_percent_passed').text(parseInt(percent_passed * 100)+"%");
				$("#p_muid_form").find('td.goal_green').each(function() {
					var goal = $(this).siblings(".goal").text(),
						green = parseFloat(goal * percent_passed).toFixed(2);
					$(this).text(green);
				}).end().find('td.goal_yellow').each(function() {
					var goal = $(this).siblings(".goal").text(),
						yellow = parseFloat(goal * percent_passed * yellow_multiplier).toFixed(2);
					$(this).text(yellow);
				});	
			};
			$("[name=days_passed]", "#p_muid_form").keypress(function(e){
				if (e.keyCode != 13)
					return;
				calc();
			}).change(calc);
		});
	</script>
	<div id="p_muid_err" style="font-size:.7em; display:none;">Impossible # of Days Entered</div>
	<div id="p_muid_days_passed" style="padding:5px; margin-bottom:2px;" class="alert-info clearfix">
		<label style="margin-bottom:0;"><span style="float:left;">Days Passed</span></label>
		<span style="float:right;"><input name="days_passed" style="text-align:right;" type="text" size="2" value="<?php echo htmlspecialchars($dayspassed); ?>" /></span>
	</div>
	<div style="padding:5px; margin-bottom:2px;" class="alert-success clearfix">
		<label style="margin-bottom:0;"><span style="float:left;">% of Month Passed</span></label>
		<span id="p_muid_percent_passed" style="float:right;"><?php echo htmlspecialchars((int) ($percentmonth * 100)); ?>%</span>
	</div>
	<div style="padding:5px; margin-bottom:2px;" class="clearfix">
		<table class="table table-condensed" style="margin-bottom:0;">
			<caption>Min Sales Needed for Goal</caption>
			<thead>
				<tr>
					<th>Goal</th>
					<th>Green</th>
					<th>Yellow</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($trends as $trend) { ?>
				<tr>
					<td class="goal"><span><?php echo htmlspecialchars($trend['goal']); ?></span></td>
					<td class="alert-success goal_green"><?php echo htmlspecialchars($pines->com_sales->round($trend['green'], true)); ?></td>
					<td class="alert goal_yellow"><?php echo htmlspecialchars($pines->com_sales->round($trend['yellow'], true)); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>