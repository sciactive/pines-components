<?php
/**
 * Display a list of configuration settings.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Viewing Configuration for {$this->req_component}";
?>
<form class="pform" action="" method="post">
	<?php foreach ($this->config as $cur_var) { ?>
	<div class="element" style="width: 95%">
		<span class="label">
			<?php echo $cur_var['cname']; ?>
		</span>
		<span class="note">
			<?php echo $cur_var['description']; ?>
		</span>
		<div class="group">
			<?php if (is_array($cur_var['value'])) {
				echo '<div class="field"><ul>';
				foreach ($cur_var['value'] as $cur_value) {
					echo '<li>'.htmlentities(print_r($cur_value, true)).'</li>';
				}
				echo '</ul></div>';
			} else {
				echo '<span class="field">';
				if (is_bool($cur_var['value']))
					$cur_var['value'] = ($cur_var['value']) ? 'Yes' : 'No';
				echo htmlentities(print_r($cur_var['value'], true));
				echo '</span>';
			} ?>
		</div>
	</div>
	<?php } ?>
</form>