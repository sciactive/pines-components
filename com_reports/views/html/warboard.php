<?php
/**
 * Shows the company warboard.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$positions = $this->entity->positions;
$cols = $this->entity->columns;
$span_num = (12 / $cols); // bootstrap 12 columns..
if (isset($_REQUEST['template'])) { ?>
	<link type="text/css" rel="stylesheet" media="all" href="<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_bootstrap/includes/themes/normal/css/bootstrap.css"/>
	<link type="text/css" rel="stylesheet" media="all" href="<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_bootstrap/includes/fontawesome/css/font-awesome.css"/>
	<br/>
	<style type="text/css">
		body {
			padding: 0 10px;
		}
		h3 {
			font-size: 14px;
			line-height: 18px;
		}
		td {
			font-size: .9em;
			padding: 1px 0 !important;
			line-height: 12px !important;
		}
		.row-fluid {
			margin-left: 0 !important;
		}
	</style>
<?php } else {
	$this->title = htmlspecialchars($this->entity->company_name).' Warboard';
}
function make_location($location, $positions, $span_num) {
	$location_mod = new module('com_reports', 'warboard_location');
	$location_mod->location = $location;
	$location_mod->span_num = $span_num;
	$location_mod->positions = $positions;
	return $location_mod->render();
}

function make_important($important, $positions, $span_num) {
	$important_mod = new module('com_reports', 'warboard_important');
	$important_mod->important = $important;
	$important_mod->span_num = $span_num;
	$important_mod->positions = $positions;
	return $important_mod->render();
}

function make_headquarters($headquarters, $span_num) {
	$hq_mod = new module('com_reports', 'warboard_headquarters');
	$hq_mod->hq = $headquarters;
	$hq_mod->span_num = $span_num;
	return $hq_mod->render();
}
?>
<style type="text/css" >
	.warboard-container .text-center {
		text-align: center;
	}
	.warboard-container table {
		font-size: .9em;
		margin: 0;
	}
	.warboard-container table td {
		text-align:center;
	}
	.warboard-container .alert {
		padding: 5px;
		border-radius: 0;
		margin:0;
		border-bottom:0;
	}
	.warboard-container .alert h3 {
		margin: 0;
	}
	.warboard-container .section-container {
		border: 1px solid #eee;
	}
	.warboard-container i.block {
		font-size: 1.4em;
		display:block;
	}
	.warboard-container .location,
	.warboard-container .important {
		font-size: .8em;
	}
	.warboard-container .location .phone-numbers td {
		padding-top: 3px;
	}
	.warboard-container .location td,
	.warboard-container .important td {
		padding: 0px 5px;
	}
	.warboard-container .emp-name {
		text-align: left;
	}
	.warboard-container .emp-phone {
		font-weight:bold;
		text-align: right;
	}
	.warboard-container .title-label {
		font-weight:bold;
		text-transform: uppercase;
	}
	.warboard-container .emp-name {
		text-transform: uppercase;
	}
	.warboard-container .border-right,
	.warboard-container .hire-date {
		border-right: 1px solid #ddd;
	}
	.warboard-container .hire-date {
		text-align: left;
	}
	.warboard-container .hire-date .badge {
		float: right;
		margin-top: 1px;
	}
	.warboard-container .section-container {
		margin-bottom: 10px;
	}
	.warboard-container .important td {
		border-right: 1px solid #ddd;
	}
	.warboard-container .important .emp-phone {
		text-align:center;
	}
	@media (min-width: 768px) {
		.warboard-container .row-fluid:not(.cols2) {
			margin-left: 15px;
		}
		.warboard-container .span4 {
			margin-left: 5px;
		}
		.warboard-container .span4 .row-fluid {
			margin-left: 0;
		}
	}
	@media (min-width: 1100px) {
		.warboard-container .row-fluid:not(.cols2) {
			margin-left: 25px;
		}
	}
</style>
<div class="warboard-container">
	<?php
	// Make the warboard locations and important numbers:
	$made_hq = false;
	$num_rows = count($this->entity->rows);
	if ($num_rows > 1) {
		$c = 1;
		foreach ($this->entity->rows as $cur_row) { ?>
			<div class="row-fluid <?php echo 'cols'.$cols;?>">
				<?php foreach ($cur_row as $cur_block) { 
					if ($cur_block['type'] == 'location') {
						echo make_location($cur_block['location'], $positions, $span_num);
					} else if ($cur_block['type'] == 'stack') {
						if ($cur_block['stack_hq']) {
							echo '<div class="span'.$span_num.'">';
							echo '<div class="row-fluid">'; // start stack1
							echo make_important($cur_block['important'], $positions, 12);
							echo '</div>'; // finish stack1
							echo '<div class="row-fluid">';// start stack2
							echo make_headquarters($this->entity->hq, 12);
							$made_hq = true;
							echo '</div>'; // finish stack2
							echo '</div>';
						} else {
							echo '<div class="span'.$span_num.'">';
							echo '<div class="row-fluid">'; // start stack1
							echo make_important($cur_block['stack1'], $positions, 12);
							echo '</div>'; // finish stack1
							echo '<div class="row-fluid">';// start stack2
							echo make_important($cur_block['stack2'], $positions, 12);
							echo '</div>'; // finish stack2
							echo '</div>';
							
							if ($cur_block['make_hq'] && count($cur_row) < $cols) {
								// This means its the end of importants
								// And we ALSO checked if we had room to put the hq here
								echo make_headquarters($this->entity->hq, $span_num);
								$made_hq = true;
							}
						}
					} else {
						echo make_important($cur_block['important'], $positions, $span_num);
						if ($cur_block['make_hq'] && count($cur_row) < $cols) {
							// This means its the end of importants
							// And we ALSO checked if we had room to put the hq here
							echo make_headquarters($this->entity->hq, $span_num);
							$made_hq = true;
						}
					}
				} 
				if ($c == $num_rows && (count($cur_row) < $cols) && !$made_hq) {
					// Put the HQ here...
					echo make_headquarters($this->entity->hq, $span_num);
					$made_hq = true;
				} ?>
			</div>
	<?php	}
	} 
	if (!$made_hq) {
		echo '<div class="row-fluid">';
		echo make_headquarters($this->entity->hq, $span_num);
		echo '</div>';
	}
	?>
</div>