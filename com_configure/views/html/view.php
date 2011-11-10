<?php
/**
 * Display a list of configuration settings.
 *
 * @package Pines
 * @subpackage com_configure
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars("Viewing Configuration for {$this->entity->info->name} {$this->entity->info->version} ({$this->entity->name})");
if ($this->entity->per_user) {
	if ($this->entity->user->is_com_configure_condition)
		$this->note = htmlspecialchars("For conditional configuration {$this->entity->user->name}.");
	else
		$this->note = htmlspecialchars("For {$this->entity->type} {$this->entity->user->name} [{$this->entity->user->username}{$this->entity->user->groupname}].");
}
?>
<form class="pf-form" action="" method="post">
	<?php foreach ($this->entity->get_full_config_array() as $cur_var) { ?>
	<div class="pf-element pf-full-width">
		<span class="pf-label"><?php echo htmlspecialchars($cur_var['cname']); ?></span>
		<span class="pf-note"><?php echo str_replace("\n", '<br />', htmlspecialchars($cur_var['description'])); ?></span>
		<div class="pf-group">
			<div class="pf-field">
				<?php if (is_array($cur_var['value'])) {
					echo '<ul>';
					foreach ($cur_var['value'] as $cur_value) {
						echo '<li>'.htmlspecialchars(print_r($cur_value, true)).'</li>';
					}
					echo '</ul>';
				} else {
					if (is_bool($cur_var['value']))
						$cur_var['value'] = ($cur_var['value']) ? 'Yes' : 'No';
					echo htmlspecialchars(print_r($cur_var['value'], true));
				} ?>
			</div>
		</div>
	</div>
	<?php } ?>
</form>