<?php
/**
 * Produces the view for a warboard location block.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$cur_location = $this->location;
$employees = $cur_location->get_users();
$pines->entity_manager->sort($employees, 'hire_date');
$span_num = $this->span_num;
?>

<div class="span<?php echo $span_num;?>">
	<div class="section-container location">
		<div class="alert">
			<h3 class="text-center">
				<?php echo htmlspecialchars($cur_location->name); 
				echo ($pines->config->com_reports->warboard_states) ? ', ' . htmlspecialchars($cur_location->state) : ''; ?>
			</h3>
		</div>
		<table class="table table-condensed">
			<tbody>
				<?php if (!empty($cur_location->phone) || ($pines->config->com_reports->warboard_phone2_show && !empty($cur_location->phone2))) { 
					$phone_span = (!empty($cur_location->phone) && ($pines->config->com_reports->warboard_phone2_show && !empty($cur_location->phone2))) ? 'colspan="2"' : 'colspan="4"';
				?>
				<tr class="phone-numbers vcard">
					<?php if (!empty($cur_location->phone)) { ?>
					<td <?php echo $phone_span; ?>><i class="icon-phone block"></i> <span class="tel"><?php echo htmlspecialchars(format_phone($cur_location->phone)); ?></span></td>
					<?php } ?>
					<?php if ($pines->config->com_reports->warboard_phone2_show && !empty($cur_location->phone2)) { ?>
					<td <?php echo $phone_span; ?>><i class="icon-building block"></i> <?php echo htmlspecialchars(rtrim($pines->config->com_reports->warboard_phone2_label, ': ')); ?>: <span class="tel"><?php echo htmlspecialchars(format_phone($cur_location->phone2)); ?></span></td>
					<?php } ?>
				</tr>
				<tr>
					<td colspan="2">District</td>
					<td colspan="2"><?php echo isset($cur_location->parent->name) ? htmlspecialchars($cur_location->parent->name) : '-'; ?></td>
				</tr>
				<?php }  
					foreach ($this->positions as $cur_title) { ?>
						<tr>
							<td colspan="1" class="title-label alert border-right">Hire Date</td>
							<td colspan="4" class="title-label alert"><?php echo htmlspecialchars($cur_title);?></td>
						</tr>
						<?php 
						foreach ($employees as $cur_employee) {
							if (!$cur_employee->employee || $cur_employee->terminated || $cur_employee->job_title != $cur_title || (isset($cur_employee->show_warboard) && !$cur_employee->show_warboard))
								continue;
							?>
							<tr class="vcard <?php echo ($cur_employee->new_hire) ? 'success': ''; ?>">
								<td class="hire-date"><?php echo htmlspecialchars(format_date($cur_employee->hire_date, 'custom', 'n/j/y')); ?> <?php echo ($cur_employee->new_hire) ? ' <span class="badge badge-success">New!</span>': ''; ?></td>
								<td class="emp-name" colspan="2"><?php echo htmlspecialchars($cur_employee->name); ?></td>
								<td class="emp-phone tel"><?php echo htmlspecialchars(format_phone($cur_employee->phone)); ?></td>
							</tr>
							<?php
						}
					}?>
			</tbody>
		</table>
	</div>
</div>
