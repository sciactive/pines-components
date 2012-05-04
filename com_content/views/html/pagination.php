<?php
/**
 * Show pagination.
 *
 * @package Components\content
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->page = (int) $this->page;

if ($this->type == 'complete') { ?>
<div class="pagination">
	<ul>
		<?php if ($this->page >= 1) { if ($this->page != 0) { ?>
		<li><a href="<?php echo htmlspecialchars($this->no_page_url); ?>">&#8676;</a></li>
		<?php } ?>
		<li><a href="<?php echo htmlspecialchars($this->page == 1 ? $this->no_page_url : str_replace('__page__', $this->page, $this->page_url)); ?>">&larr;</a></li>
		<?php } if ($this->page >= 2) {
			if ($this->page > 2) { ?>
		<li class="disabled"><a href="javascript:void(0);">&hellip;</a></li>
			<?php } ?>
		<li><a href="<?php echo htmlspecialchars($this->page == 2 ? $this->no_page_url : str_replace('__page__', $this->page - 1, $this->page_url)); ?>"><?php echo $this->page - 1; ?></a></li>
		<?php } if ($this->page >= 1) { ?>
		<li><a href="<?php echo htmlspecialchars($this->page == 1 ? $this->no_page_url : str_replace('__page__', $this->page, $this->page_url)); ?>"><?php echo $this->page; ?></a></li>
		<?php } ?>
		<li class="active"><a href="javascript:void(0);"><?php echo htmlspecialchars($this->page + 1); ?></a></li>
		<?php if ($this->page <= $this->pages - 2) { ?>
		<li><a href="<?php echo htmlspecialchars(str_replace('__page__', $this->page + 2, $this->page_url)); ?>"><?php echo $this->page + 2; ?></a></li>
		<?php } ?>
		<?php if ($this->page <= $this->pages - 3) { ?>
		<li><a href="<?php echo htmlspecialchars(str_replace('__page__', $this->page + 3, $this->page_url)); ?>"><?php echo $this->page + 3; ?></a></li>
		<?php if ($this->page < $this->pages - 3) { ?>
		<li class="disabled"><a href="javascript:void(0);">&hellip;</a></li>
			<?php }
			} ?>
		<?php if ($this->page <= $this->pages - 2) { ?>
		<li><a href="<?php echo htmlspecialchars(str_replace('__page__', $this->page + 2, $this->page_url)); ?>">&rarr;</a></li>
		<?php if ($this->page != $this->pages - 1) { ?>
		<li><a href="<?php echo htmlspecialchars(str_replace('__page__', $this->pages, $this->page_url)); ?>">&#8677;</a></li>
		<?php } } ?>
	</ul>
</div>
<?php } elseif ($this->type == 'simple') { ?>
<ul class="pager">
	<?php if ($this->page > 0) { ?>
	<li class="previous">
		<a href="<?php echo htmlspecialchars($this->page == 1 ? $this->no_page_url : str_replace('__page__', $this->page, $this->page_url)); ?>">Previous</a>
	</li>
	<?php } if ($this->next_exists) { ?>
	<li class="next">
		<a href="<?php echo htmlspecialchars(str_replace('__page__', $this->page + 2, $this->page_url)); ?>">Next</a>
	</li>
	<?php } ?>
</ul>
<?php } elseif ($this->type == 'blog') { ?>
<ul class="pager">
	<?php if ($this->next_exists) { ?>
	<li class="previous">
		<a href="<?php echo htmlspecialchars(str_replace('__page__', $this->page + 2, $this->page_url)); ?>">&larr; Older</a>
	</li>
	<?php } if ($this->page > 0) { ?>
	<li class="next">
		<a href="<?php echo htmlspecialchars($this->page == 1 ? $this->no_page_url : str_replace('__page__', $this->page, $this->page_url)); ?>">Newer &rarr;</a>
	</li>
	<?php } ?>
</ul>
<?php } ?>
