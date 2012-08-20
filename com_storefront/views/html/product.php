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
//$this->note = '';
//if (!empty($this->entity->manufacturer_sku))
//	$this->note = '<strong>Model:</strong> '.format_content(htmlspecialchars($this->entity->manufacturer_sku)).' | ';
//$this->note .= '<strong>SKU:</strong> '.format_content(htmlspecialchars($this->entity->sku));

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
		height: <?php echo (int)$pines->config->com_sales->product_images_tmb_height; ?>px;
		width: <?php echo (int)$pines->config->com_sales->product_images_tmb_width; ?>px;
	}
	#p_muid_product .ppy .ppy-caption {
		position: absolute;
		bottom: 0;
		left: 100%;
		width: 260px;
		margin-left: 10px;
	}
	#p_muid_product .main_section {
		clear: right;
		margin-bottom: 0;
	}
	#p_muid_product .info_container {
		float: right;
		margin: 0 0 .5em .5em;
		padding: 1em;
		text-align: center;
	}
	#p_muid_product .price {
		font-size: 1.4em;
		font-weight: bold;
	}
	#p_muid_product .info {
		margin: .75em 0;
	}
	#p_muid_product .desc {
		font-size: 1.2em;
	}
</style>
<script type="text/javascript">
	pines(function(){
		<?php if (!$pines->config->com_storefront->catalog_mode) { ?>
		$("button.add_cart", "#p_muid_product").click(function(){
			pines.com_storefront_add_to_cart(<?php echo (int) $this->entity->guid; ?>, <?php echo json_encode($this->entity->name); ?>, <?php echo (float) $this->entity->unit_price; ?>, $("#p_muid_product"));
		});
		<?php } if ($this->entity->images) { ?>
		$(".ppy", "#p_muid_product").popeye();
		<?php } ?>
	});
</script>
<div id="p_muid_product">
	<?php if ($this->entity->images) { ?>
	<div class="ppy ppy1">
		<ul class="ppy-imglist">
			<?php $first = true; foreach ($this->entity->images as $cur_image) { ?>
			<li style="<?php if (!$first) { ?>display: none;<?php } $first = false; ?>">
				<a href="<?php echo htmlspecialchars($cur_image['file']); ?>"><img src="<?php echo htmlspecialchars($cur_image['thumbnail']); ?>" alt="" /></a>
				<span class="ppy-extcaption"><span style="white-space: pre-wrap;"><?php echo str_replace("\n", '<br />', htmlspecialchars($cur_image['alt'])); ?></span></span>
			</li>
			<?php } ?>
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
	<div class="main_section" style="<?php if ($this->entity->images) { ?>margin-left: <?php echo (int)$pines->config->com_sales->product_images_tmb_width + 40; ?>px;<?php } ?>">
		<div class="info_container alert alert-info">
			<div class="price"><?php echo $pines->com_storefront->format_price($this->entity->unit_price); ?></div>
			<div class="info">
				<?php if (!empty($this->entity->manufacturer_sku)) { ?>
				<strong>Model:</strong> <?php echo format_content(htmlspecialchars($this->entity->manufacturer_sku)); ?> | 
				<?php } ?>
				<strong>SKU:</strong> <?php echo format_content(htmlspecialchars($this->entity->sku)); ?>
			</div>
			<?php if (!$pines->config->com_storefront->catalog_mode) { ?>
			<div><button class="add_cart btn btn-large btn-primary"><i class="icon-shopping-cart icon-white"></i> Add to Cart</button></div>
			<?php } ?>
		</div>
		<div class="desc"><?php echo format_content($this->entity->short_description); ?></div>
		<div style="clear: right; height: 0; line-height: 0">&nbsp;</div>
	</div>
	<div style="clear: both;" class="clearfix description">
		<div class="page-header">
			<h3>Product Description</h3>
		</div>
		<?php echo format_content($this->entity->description); ?>
	</div>
	<?php if ($specs) { ?>
	<div style="clear: both;" class="clearfix specifications">
		<div class="page-header">
			<h3>Product Specs</h3>
		</div>
		<div class="pf-form">
			<?php // TODO: Weight, additional fees.
			foreach ($specs as $key => $cur_spec) {
				if ($cur_spec['type'] == 'heading') {
					?><div class="pf-element pf-heading">
						<h4><?php echo htmlspecialchars($cur_spec['name']); ?></h4>
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
	<br style="clear: both; height: 0;" />
</div>