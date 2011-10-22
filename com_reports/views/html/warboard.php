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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->entity->company_name).' Warboard';
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_warboard_table {
		background-color: white;
		color: black;
		font-size: 8pt;
		text-align: center;
		white-space: nowrap;
		width: 100%;
	}
	#p_muid_warboard_table td {
		vertical-align: text-top;
		width: <?php echo 100 / $this->entity->columns; ?>%;
	}
	#p_muid_warboard_table .location, .important, .hq {
		border-collapse: collapse;
		border-spacing: 0;
		width: 100%;
	}
	#p_muid_warboard_table .location td, .important td, .hq td {
		border: solid 1px;
	}
	#p_muid_warboard_table .location .label {
		background-color: beige;
	}
	#p_muid_warboard_table .location .heading {
		background-color: gainsboro;
		color: #2B2B2B;
	}
	#p_muid_warboard_table .important .label {
		background-color: lightsteelblue;
	}
	#p_muid_warboard_table .hq .label {
		background-color: palegreen;
	}
	#p_muid_warboard_table .empty {
		background-color: #F1F1F1;
	}
	#p_muid_warboard_table .newhire {
		background-color: #CCFFCC;
	}
	/* ]]> */
</style>
<table id="p_muid_warboard_table">
	<tr>
	<?php
	$count = 0;
	$location_rows = $important_rows = 0;
	foreach ($this->entity->locations as $cur_location) {
		$location_count[$cur_location->guid] = 0;
		$employees = $cur_location->get_users();
		$pines->entity_manager->sort($employees, 'hire_date');
		if ($count > 0 && ($count / $this->entity->columns) == floor($count / $this->entity->columns)) { ?>
		</tr>
		<tr>
		<?php } ?>
			<td>
				<table class="location" id="location_<?php echo $cur_location->guid; ?>">
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
						<td colspan="3"><?php echo isset($cur_location->parent->name) ? htmlspecialchars($cur_location->parent->name) : '-'; ?></td>
					</tr>
					<?php
					foreach ($this->entity->positions as $cur_title) {
						$location_count[$cur_location->guid]++;
						$empty = true;
					?>
					<tr class="heading <?php echo strtolower(str_replace(' ', '_', $cur_location->guid.$cur_title)); ?>">
						<td colspan="3"><?php echo htmlspecialchars($cur_title).$plural; ?></td>
					</tr>
					<?php
					foreach ($employees as $cur_employee) {
						if (!$cur_employee->employee || $cur_employee->terminated || $cur_employee->job_title != $cur_title)
							continue;
						$location_count[$cur_location->guid]++;
						$empty = false;
						?>
					<tr <?php echo ($cur_employee->new_hire) ? 'class="newhire"' : ''; ?>>
						<td style="width: 25%;"><?php echo format_date($cur_employee->hire_date, 'custom', 'n/j/y'); ?></td>
						<td style="width: 50%;"><?php echo htmlspecialchars($cur_employee->name); ?></td>
						<td style="width: 25%;"><?php echo format_phone($cur_employee->phone); ?></td>
					</tr>
						<?php
					}
					if ($empty) {
						$location_count[$cur_location->guid]--; ?>
					<script type="text/javascript">
						// <![CDATA[
						pines(function(){
							$(".<?php echo strtolower(str_replace(' ', '_', $cur_location->guid.$cur_title)); ?>").hide();
						});
						// ]]>
					</script>
					<?php }
					}
					if ($location_count[$cur_location->guid] > $location_rows) $location_rows = $location_count[$cur_location->guid];
					?>
				</table>
			</td>
		<?php $count++; } ?>
	</tr>
	<tr>
		<td colspan="<?php echo $this->entity->columns; ?>"><strong>Important Numbers</strong></td>
	</tr>
	<tr>
		<td>
			<table class="hq">
				<tr>
					<td class="label"><strong><?php echo htmlspecialchars($this->entity->hq->name); ?></strong></td>
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
			$imortant_count[$cur_important->guid] = 0;
			$employees = $cur_important->get_users();
			$pines->entity_manager->sort($employees, 'job_title');
		?>
		<td colspan="<?php echo floor(($this->entity->columns - 1)/count($this->entity->important)); ?>">
			<table class="important" id="important_<?php echo $cur_important->guid; ?>">
				<tr>
					<td colspan="4" class="label"><strong><?php echo htmlspecialchars($cur_important->name); ?></strong></td>
				</tr>
				<?php
				foreach ($employees as $cur_employee) {
					if (!$cur_employee->employee || $cur_employee->terminated)
						continue;
					$important_count[$cur_important->guid]++;
				?>
				<tr>
					<td><?php echo htmlspecialchars($cur_employee->name); ?></td>
					<td><?php echo htmlspecialchars($cur_employee->job_title); ?></td>
					<td><?php echo format_phone($cur_employee->phone); ?></td>
					<td><?php echo !empty($cur_employee->phone_ext) ? 'ext '.$cur_employee->phone_ext : ''; ?></td>
				</tr>
				<?php } ?>
			</table>
		</td>
		<?php if ($important_count[$cur_important->guid] > $important_rows) $important_rows = $important_count[$cur_important->guid]; } ?>
	</tr>
</table>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
<?php
foreach ($this->entity->locations as $cur_location) {
	$add_rows = $location_rows - $location_count[$cur_location->guid];
	for ($i=0; $i < $add_rows; $i++) { ?>
		$("#location_<?php echo $cur_location->guid; ?>").append('<tr class="empty"><td style="width: 25%;">&nbsp;</td><td style="width: 50%;">&nbsp;</td><td style="width: 25%;">&nbsp;</td></tr>');
<?php
	}
}
foreach ($this->entity->important as $cur_important) {
	$add_rows = $important_rows - $important_count[$cur_important->guid];
	for ($i=0; $i < $add_rows; $i++) { ?>
		$("#important_<?php echo $cur_important->guid; ?>").append('<tr class="empty"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>');
<?php } } ?>
		return;
	});
	// ]]>
</script>