<?php
/**
 * Displays a product page.
 *
 * @package Components\storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = htmlspecialchars($this->entity->name);
if ($this->entity->images)
	$pines->com_popeye->load();

// Build a list of specs.
$specs = array();
$categories = (array) $this->entity->get_categories();
foreach ($categories as &$cur_category) {
	if (!isset($cur_category) || !$cur_category->enabled)
		continue;
	$specs = array_merge($specs, $cur_category->get_specs_all());
}
unset($categories, $cur_category);
$pines->com_sales->sort_specs($specs);
?>
<style type="text/css">
	#p_muid_product .ppy-placeholder {
		z-index: 100;
	}
	#p_muid_product .ppy {
		float: left;
	}
	#p_muid_product .ppy .ppy-stage {
		height: 200px;
		width: 240px;
	}
	#p_muid_product .ppy .ppy-caption {
		position: absolute;
		bottom: 0;
		left: 100%;
		width: 240px;
		margin-left: 10px;
	}
	#p_muid_product .price {
		font-size: 1.8em;
		font-weight: bold;
	}
	#p_muid_product .button_container {
		float: right;
		text-align: right;
	}
	#p_muid_product .desc {
		clear: right;
	}
</style>
<script type="text/javascript">
	pines(function(){
		$("button.add_cart", "#p_muid_product").click(function(){
			pines.com_storefront_add_to_cart(<?php echo (int) $this->entity->guid; ?>, <?php echo json_encode($this->entity->name); ?>, <?php echo (float) $this->entity->unit_price; ?>, $("#p_muid_product"));
		});
		<?php if ($this->entity->images) { ?>
		$(".ppy", "#p_muid_product").popeye();
		<?php } ?>
	});
</script>
<div id="p_muid_product">
	<?php if ($this->entity->images) { ?>
	<div class="ppy ppy1">
		<ul class="ppy-imglist">
			<?php foreach ($this->entity->images as $cur_image) {
				?><li>
					<?php if ($pines->config->com_storefront->image_thumbnails) {
						$info = pathinfo($cur_image['file']);
						?>
					<a href="<?php echo htmlspecialchars($cur_image['file']); ?>"><img src="<?php echo htmlspecialchars($info['dirname'].'/'.$info['filename'].$pines->config->com_storefront->image_thumbnails_suffix.'.'.$info['extension']); ?>" alt="" /></a>
					<?php } else { ?>
					<a href="<?php echo htmlspecialchars($cur_image['file']); ?>"><img src="<?php echo htmlspecialchars($cur_image['file']); ?>" alt="" /></a>
					<?php } ?>
					<span class="ppy-extcaption">
						<?php echo htmlspecialchars($cur_image['alt']); ?>
					</span>
				</li><?php
			} ?>
		</ul>
		<div class="ppy-outer">
			<div class="ppy-stage">
				<div class="ppy-nav">
					<div class="nav-wrap">
						<a class="ppy-prev" title="Previous image">Previous image</a>
						<a class="ppy-next" title="Next image">Next image</a>
						<a class="ppy-switch-enlarge" title="Enlarge">Enlarge</a>
						<a class="ppy-switch-compact" title="Close">Close</a>
					</div>
				</div>
			</div>
		</div>
		<div class="ppy-caption">
			<div class="ppy-counter">
				Image <strong class="ppy-current"></strong> of <strong class="ppy-total"></strong>
			</div>
			<strong><?php echo htmlspecialchars($this->entity->name); ?></strong><br />
			<span class="ppy-text"></span>
		</div>
	</div>
	<?php } ?>
	<div class="desc"><?php echo format_content($this->entity->short_description); ?></div>
	<br style="clear: right; height: 0;" />
	<div>
		<?php if (!$pines->config->com_storefront->catalog_mode) { ?>
		<div class="button_container"><button class="add_cart btn btn-large btn-primary"><i class="icon-shopping-cart icon-white"></i> Add to Cart</button></div>
		<?php } ?>
		<span class="price"><?php echo $pines->com_storefront->format_price($this->entity->unit_price); ?></span>
	</div>
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_desc" data-toggle="tab">Description</a></li>
		<?php if ($specs) { ?>
		<li><a href="#p_muid_tab_specs" data-toggle="tab">Specifications</a></li>
		<?php } ?>
	</ul>
	<div id="p_muid_tabs" class="tab-content">
		<div class="tab-pane active" id="p_muid_tab_desc">
			<?php echo format_content($this->entity->description); ?>
		</div>
		<?php if ($specs) { ?>
		<div class="tab-pane" id="p_muid_tab_specs">
			<div class="pf-form">
				<?php // TODO: Weight, additional fees.
				foreach ($specs as $key => $cur_spec) {
					if ($cur_spec['type'] == 'heading') {
						?><div class="pf-element pf-heading">
							<h3><?php echo htmlspecialchars($cur_spec['name']); ?></h3>
						</div><?php
					} else {
						switch ($cur_spec['type']) {
							case 'bool':
								$value = $this->entity->specs[$key] ? 'Yes' : 'No';
								break;
							case 'string':
							case 'float':
								$value = $this->entity->specs[$key];
								break;
						}
						?><div class="pf-element">
							<span class="pf-label"><?php echo htmlspecialchars($cur_spec['name']); ?></span>
							<div class="pf-group"><div class="pf-field"><?php echo htmlspecialchars($value); ?></div></div>
						</div><?php
					}
				}
				unset($specs);
				?>
			</div>
		</div>
		<?php } ?>
	</div>
	<br style="clear: right; height: 0;" />
</div>