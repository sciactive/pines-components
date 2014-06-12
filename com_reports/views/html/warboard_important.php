<?php
/**
 * Produces the view for a warboard important block.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$cur_important = $this->important;
$employees = $cur_important->get_users();
$pines->entity_manager->sort($employees, 'job_title');
$span_num = $this->span_num;

?>
<div class="span<?php echo $span_num;?>">
	<div class="section-container important">
		<div class="alert alert-info">
			<h3 class="text-center">
				<?php echo htmlspecialchars($cur_important->name); ?>
			</h3>
		</div>
		<table class="table table-condensed">
			<tbody>
				<?php foreach ($employees as $cur_employee) { 
					if (!$cur_employee->employee || $cur_employee->terminated || (isset($cur_employee->show_warboard) && !$cur_employee->show_warboard))
								continue;
					?>
					<tr class="vcard">
						<td class="job-title"><?php echo htmlspecialchars($cur_employee->job_title); ?></td>
						<td class="emp-name"><?php echo htmlspecialchars($cur_employee->name); ?></td>
						<td class="emp-phone tel"><?php echo htmlspecialchars(format_phone($cur_employee->phone)); ?></td>
						<?php if ($pines->config->com_reports->use_extension) { ?>
						<td class="emp-extension"><?php echo !empty($cur_employee->phone_ext) ? 'ext '.htmlspecialchars($cur_employee->phone_ext) : ''; ?></td>
						<?php } if ($pines->config->com_reports->use_other_phone) { ?>
						<td class="emp-use-other-phone"><?php echo !empty($cur_employee->other_phone) ? $pines->config->com_reports->other_phone_label.' '.htmlspecialchars(format_phone($cur_employee->other_phone)) : ''; ?></td>
						<?php } ?>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>