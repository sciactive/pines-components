<?php
/**
 * A default, rather simple, dialog content of the entity.
 *
 * @package Components\entityhelper
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
if ($this->render == 'body') {
	$type = $this->entity->info('type');
	$icon = $this->entity->info('icon');
	$image = $this->entity->info('image');
	$types = $this->entity->info('types');
	$url_list = $this->entity->info('url_list');
?>
<div style="float: left;">
	<?php if ($icon) { ?>
	<i style="float: left; height: 16px; width: 16px;" class="<?php echo htmlspecialchars($icon); ?>"></i>&nbsp;
	<?php }
	echo htmlspecialchars(ucwords($type)); ?>
</div>
<?php if ($url_list) { ?>
<div style="float: right;">
	<a href="<?php echo htmlspecialchars($url_list); ?>">List <?php echo htmlspecialchars(ucwords($types)); ?></a>
</div>
<?php } ?>
<div style="clear: both; padding-top: 1em;">
	<div style="float: left;">
		Created on <?php echo format_date($this->entity->p_cdate, 'full_med'); ?>.<br />
		Last modified on <?php echo format_date($this->entity->p_mdate, 'full_med'); ?>.
	</div>
	<?php if ($this->entity->user->guid) { ?>
	<div style="float: right; clear: right;">
		Owned by <a data-entity="<?php echo htmlspecialchars($this->entity->user->guid); ?>" data-entity-context="user"><?php echo htmlspecialchars($this->entity->user->info('name')); ?></a>
	</div>
	<?php } if ($this->entity->group->guid) { ?>
	<div style="float: right; clear: right;">
		Belongs to group <a data-entity="<?php echo htmlspecialchars($this->entity->group->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($this->entity->group->info('name')); ?></a>
	</div>
	<?php } ?>
</div>
<?php if ($image) { ?>
<div style="clear: both; padding-top: 1em; text-align: center;">
	<span class="thumbnail" style="display: inline-block; max-width: 90%;">
		<img src="<?php echo htmlspecialchars($image); ?>" alt="" style="max-width: 100%;">
	</span>
</div>
<?php } } elseif ($this->render == 'footer') {
	$url_view = $this->entity->info('url_view');
	$url_edit = $this->entity->info('url_edit');
	if ($url_view) { ?>
<a href="<?php echo htmlspecialchars($url_view); ?>" class="btn">View</a>
<?php } if ($url_edit) { ?>
<a href="<?php echo htmlspecialchars($url_edit); ?>" class="btn">Edit</a>
<?php } if (!$url_view && !$url_edit) { ?>
<a href="javascript:void(0);" class="btn" data-dismiss="modal">Close</a>
<?php } } ?>