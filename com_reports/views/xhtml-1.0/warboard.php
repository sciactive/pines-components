<?php
/**
 * Shows the company warboard.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->entity->company_name).' Warboard';
$this->note = format_date(time(), 'date_short');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_warboard_table {
		width: 100%;
		text-align: center;
		font-size: 8pt;
	}
	#p_muid_warboard_table td {
		width: 33%;
		vertical-align: text-top;
	}
	#p_muid_warboard_table .location, .important, .hq {
		width: 100%;
		border-spacing: 0;
		border-collapse: collapse;
	}
	#p_muid_warboard_table .location td, .important td, .hq td {
		border: solid 1px;
	}
	#p_muid_warboard_table .location .label {
		background-color: beige;
		color: black;
	}
	#p_muid_warboard_table .location .heading {
		background-color: gainsboro;
		color: #2B2B2B;
	}
	#p_muid_warboard_table .important .label {
		background-color: lightsteelblue;
		color: black;
	}
	#p_muid_warboard_table .hq .label {
		background-color: palegreen;
		color: black;
	}
	/* ]]> */
</style>
<table id="p_muid_warboard_table">
	<tr>
	<?php
	$count = 1;
	foreach ($this->entity->locations as $cur_location) {
		$employees = $cur_location->get_users();
		if ($count/4 == floor($count/4)) { ?>
		</tr>
		<tr>
		<?php } ?>
		<td>
		<table class="location">
			<tr class="label">
				<td colspan="2">
					<strong><?php
					echo htmlspecialchars($cur_location->name);
					if ($pines->config->com_reports->warboard_states)
						echo ', ' . htmlspecialchars($cur_location->state);
					?></strong>
				</td>
				<td><?php echo format_phone($cur_location->phone); ?></td>
			</tr>
			<tr class="heading">
				<td colspan="3">District</td>
			</tr>
			<tr>
				<td colspan="3"><?php echo isset($cur_location->parent->groupname) ? htmlspecialchars($cur_location->parent->groupname) : '-'; ?></td>
			</tr>
			<?php foreach ($this->entity->positions as $cur_title) { $empty = true; ?>
			<tr class="heading <?php echo strtolower(preg_replace('/ /', '_', $cur_location->guid.$cur_title)); ?>">
				<td colspan="3"><?php echo htmlspecialchars($cur_title).$plural; ?></td>
			</tr>
			<?php
			foreach ($employees as $key => &$cur_employee) {
				if (!$cur_employee->employee) {
					unset($cur_employee);
				} elseif ($cur_employee->job_title == $cur_title) {
					$empty = false;
			?>
			<tr>
				<td><?php echo format_date($cur_employee->p_cdate, 'date_short'); ?></td>
				<td><?php echo htmlspecialchars($cur_employee->name); ?></td>
				<td><?php echo format_phone($cur_employee->phone); ?></td>
			</tr>
			<?php } } if ($empty) { ?>
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					$(".<?php echo strtolower(preg_replace('/ /', '_', $cur_location->guid.$cur_title)); ?>").hide();
				});
				// ]]>
			</script>
			<?php } } ?>
		</table>
		</td>
		<?php
		$count++;
	}
	?>
	</tr>
	<tr>
		<td colspan="3"><strong>Important Numbers</strong></td>
	</tr>
	<tr>
		<td>
			<table class="hq">
				<tr>
					<td class="label"><strong><?php echo htmlspecialchars($this->entity->company_name); ?></strong></td>
				</tr>
				<tr>
					<td><?php echo format_phone($this->entity->hq->phone); ?></td>
				</tr>
				<tr>
					<td>FAX: <?php echo format_phone($this->entity->hq->fax); ?></td>
				</tr>
				<tr>
					<td><?php echo htmlspecialchars($this->entity->hq->address_1); ?></td>
				</tr>
				<tr>
					<td><?php echo htmlspecialchars($this->entity->hq->city); ?>, <?php echo htmlspecialchars($this->entity->hq->state); ?> <?php echo htmlspecialchars($this->entity->hq->zip); ?></td>
				</tr>
			</table>
		</td>
		<?php
		foreach ($this->entity->important as $cur_important) {
			$employees = $cur_important->get_users();
		?>
		<td>
		<table class="important">
			<tr>
				<td colspan="3" class="label"><strong><?php echo htmlspecialchars($cur_important->name); ?></strong></td>
			</tr>
			<?php
			foreach ($employees as $key => &$cur_employee) {
				if (!$cur_employee->employee) {
					unset($cur_employee);
				} else {
			?>
			<tr>
				<td><?php echo htmlspecialchars($cur_employee->name); ?></td>
				<td><?php echo htmlspecialchars($cur_employee->job_title); ?></td>
				<td><?php echo format_phone($cur_employee->phone); ?></td>
			</tr>
			<?php } } ?>
		</table>
		</td>
		<?php } ?>
	</tr>
</table>