<?php
/**
 * Produces the view for a warboard headquarters block.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$span_num = $this->span_num;
$hq = $this->hq
?>

<div class="span<?php echo $span_num;?>">
	<div class="section-container headquarters">
		<div class="alert alert-success">
			<h3 class="text-center"><?php echo htmlspecialchars($hq->name); ?></h3>
		</div>
		<table class="table">
			<tbody>
				<tr>
					<td colspan="3" class="text-center"><i class="icon-globe block"></i> <span><?php echo htmlspecialchars($hq->address_1); ?>
						<br/>
						<?php echo htmlspecialchars($hq->city); ?>, <?php echo htmlspecialchars($hq->state); ?> <?php echo htmlspecialchars($hq->zip); ?>
						</span>
					</td>
				</tr>
				<tr class="vcard">
					<td><i class="icon-phone block"></i> <span class="tel"><?php echo htmlspecialchars(format_phone($hq->phone)); ?></span></td>
					<?php if ($pines->config->com_reports->warboard_phone2_show) { ?>
					<td><i class="icon-building block"></i> <?php echo htmlspecialchars(rtrim($pines->config->com_reports->warboard_phone2_label, ': ')); ?>: <span class="tel"><?php echo htmlspecialchars(format_phone($hq->phone2)); ?></span></td>
					<?php } ?>
					<td><i class="icon-print block"></i> <span>FAX: <span class="tel"><?php echo htmlspecialchars(format_phone($hq->fax)); ?></span></span></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

