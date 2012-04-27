<?php
/**
 * Print a complete raffle.
 *
 * @package Components\raffle
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->entity->name);
if ($this->entity->places > 1)
	$this->note = 'And the winners are...';
else
	$this->note = 'And the winner is...';
$pines->icons->load();

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

foreach ($this->entity->winners as $cur_place => $cur_winner) { ?>
<div class="hero-unit">
	<div style="height: 32px; width: 32px; float: right;" class="picon-32 <?php echo $cur_place == 1 ? 'picon-games-highscores' : 'picon-games-achievements'; ?>"></div>
	<h1>
		<span style="font-size: .9em;">
			<?php
			echo ($this->entity->places > 1 ? '<span class="badge '.($cur_place == 1 ? 'badge-success' : 'badge-info').'" style="font-size: 1em;">'.com_raffle__ordinal($cur_place).'</span>&nbsp;' : '');
			echo htmlspecialchars("{$cur_winner['first_name']} {$cur_winner['last_name']}");
			?>
		</span>
	</h1>
	<p style="margin-top: 2em;"><?php echo htmlspecialchars(format_phone($cur_winner['phone'])); ?></p>
	<p><a href="mailto:<?php echo htmlspecialchars($cur_winner['email']); ?>"><?php echo htmlspecialchars($cur_winner['email']); ?></a></p>
</div>
<?php } ?>