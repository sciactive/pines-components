<?php
/**
 * Print a complete raffle.
 *
 * @package Pines
 * @subpackage com_raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->entity->name);
if ($this->entity->places > 1)
	$this->note = 'And the winners are...';
else
	$this->note = 'And the winner is...';

/**
 * Adds the English ordinal suffix to a number.
 * @param mixed $number The number.
 * @return string The number with the ordinal suffix.
 */
function com_raffle__ordinal($number) {
    if ($number % 100 > 10 && $number % 100 < 14)
        $suffix = "th";
    else {
        switch ($number % 10) {
            case 0:
                $suffix = "th";
                break;
            case 1:
                $suffix = "st";
                break;
            case 2:
                $suffix = "nd";
                break;
            case 3:
                $suffix = "rd";
                break;
            default:
                $suffix = "th";
                break;
        }
	}
    return "${number}$suffix";
}
?>
<div class="pf-form">
	<?php foreach ($this->entity->winners as $cur_place => $cur_winner) { ?>
	<fieldset class="pf-group ui-state-highlight ui-corner-all">
		<legend><?php echo ($this->entity->places > 1 ? com_raffle__ordinal($cur_place).' Place - ' : '') . '<strong>'.htmlspecialchars("{$cur_winner['first_name']} {$cur_winner['last_name']}").'</strong>'; ?></legend>
		<div class="pf-element pf-full-width" style="text-align: center;">
			<div style="float: right; text-align: left; width: 48%;">
				<?php echo format_phone($cur_winner['phone']); ?>
			</div>
			<div style="float: left; text-align: right; width: 48%;">
				<a href="mailto:<?php echo htmlspecialchars($cur_winner['email']); ?>"><?php echo htmlspecialchars($cur_winner['email']); ?></a>
			</div>
		</div>
	</fieldset>
	<?php } ?>
</div>