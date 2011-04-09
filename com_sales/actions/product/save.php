<?php
/**
 * Save changes to a product.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( isset($_REQUEST['id']) ) {
	if ( !gatekeeper('com_sales/editproduct') )
		punt_user(null, pines_url('com_sales', 'product/list'));
	$product = com_sales_product::factory((int) $_REQUEST['id']);
	if (!isset($product->guid)) {
		pines_error('Requested product id is not accessible.');
		return;
	}
} else {
	if ( !gatekeeper('com_sales/newproduct') )
		punt_user(null, pines_url('com_sales', 'product/list'));
	$product = com_sales_product::factory();
}

// General
$product->name = $_REQUEST['name'];
$product->enabled = ($_REQUEST['enabled'] == 'ON');
$product->autocomplete_hide = ($_REQUEST['autocomplete_hide'] == 'ON');
$product->sku = $_REQUEST['sku'];
$product->receipt_description = $_REQUEST['receipt_description'];
$product->short_description = $_REQUEST['short_description'];
$product->description = $_REQUEST['description'];
$product->manufacturer = ($_REQUEST['manufacturer'] == 'null' ? null : com_sales_manufacturer::factory((int) $_REQUEST['manufacturer']));
if (!isset($product->manufacturer->guid))
	$product->manufacturer = null;
$product->manufacturer_sku = $_REQUEST['manufacturer_sku'];

// Images
$originals = array();
$old_images = $product->images;
$product->images = (array) json_decode($_REQUEST['images'], true);
foreach ($product->images as $key => &$cur_image) {
	if ($cur_image['alt'] == 'Click to edit description...')
		$cur_image['alt'] = '';
	if ($pines->uploader->check($cur_image['file'])) {
		$filename = $pines->uploader->real($cur_image['file']);
		$working_dir = dirname($filename);
		$existing_md5 = $working_dir.'/.md5_'.basename($filename);
		if (!file_exists($existing_md5) || md5($filename) != file_get_contents($existing_md5)) {
			$originals[] = $filename;
			$image = new Imagick($filename);
			// Adjust this for blur amount.
			$image->blurImage(8, 3);
			// Adjust this for crop sensitivity.
			$image->trimImage(30000);
			// Now that Imagick cropped a blurred copy, we can find where it should be cropped.
			$page = $image->getImagePage();
			$width = $image->getImageWidth();
			$height = $image->getImageHeight();
			$x = $page['x'];
			$y = $page['y'];
			// Add a little padding to grab any edges we may have cut off.
			$pad_x = ceil($width * $pad);
			$pad_y = ceil($height * $pad);
			$width += ($pad_x * 2);
			$height += ($pad_y * 2);
			$x -= $pad_x;
			if ($x < 0) {
				$width += $x;
				$x = 0;
			}
			$y -= $pad_y;
			if ($y < 0) {
				$height += $y;
				$y = 0;
			}
			// Now do the actual trimming, sizing, and rotating.
			$image = new Imagick($filename);
			$image->cropImage($width, $height, $x, $y);
			$thumbnail = $image->clone();
			// Create the thumbnail similtaneously.
			$thumbnail->setBackgroundColor($pines->config->com_sales->image_padding_color);
			$thumbnail->thumbnailImage($pines->config->com_sales->thumbnail_size['width'], $pines->config->com_sales->thumbnail_size['height'], true, true);
			if ($image->getImageWidth() > $pines->config->com_sales->image_max_width)
				$image->resizeImage($pines->config->com_sales->image_max_width, $image->getImageWidth());
			// Rename the new images with the sequential numbers.
			$thumb_dir = $working_dir.'/'.$pines->config->com_sales->thumbnail_folder;
			if (!is_dir($thumb_dir))
				mkdir($thumb_dir);
			// Rename the images.
			$basename = basename($filename);
			$basename = substr($basename, 0, strrpos($basename, '.'));
			$out = $working_dir.'/'.$basename;
			$thumb_out = $thumb_dir.'/'.$basename;
			// Check to see if we need to reformat the images.
			$image_format = $image->getImageFormat();
			if (!in_array($image_format, $pines->config->com_sales->image_formats)) {
				$image->setImageFormat($pines->config->com_sales->image_format);
				$thumbnail->setImageFormat($pines->config->com_sales->image_format);
				$out .= '.'.$pines->config->com_sales->image_format;
				$thumb_out .= '.'.$pines->config->com_sales->image_format;
			} else {
				// Add the appropriate file extensions.
				$out .= '.'.$image_format;
				$thumb_out .= '.'.$image_format;
			}
			// Write out the new files.
			$image->writeImage($out);
			file_put_contents($working_dir.'/.md5_'.basename($out), md5($out));
			$thumbnail->writeImage($thumb_out);
			file_put_contents($thumb_dir.'/.md5_thumb_'.basename($thumb_out), md5($thumb_out));
			$cur_image['file'] = $pines->uploader->url($out);
			$original_key = array_search($out, $originals);
			if ($original_key !== false)
				unset($originals[$original_key]);
		}
	} else {
		unset($product->images[$key]);
	}
}
unset($cur_image);
$product->images = array_values($product->images);

// Main thumbnail
$product->thumbnail = $_REQUEST['thumbnail'];
if (!$pines->uploader->check($product->thumbnail))
	$product->thumbnail = null;
if (isset($product->thumbnail)) {
	$filename = $pines->uploader->real($product->thumbnail);
	$working_dir = dirname($filename);
	$existing_md5 = $working_dir.'/.md5_thumb_'.basename($filename);
	if (!file_exists($existing_md5) || md5($filename) != file_get_contents($existing_md5)) {
		$image = new Imagick($filename);
		// Adjust this for blur amount.
		$image->blurImage(8, 3);
		// Adjust this for crop sensitivity.
		$image->trimImage(30000);
		// Now that Imagick cropped a blurred copy, we can find where it should be cropped.
		$page = $image->getImagePage();
		$width = $image->getImageWidth();
		$height = $image->getImageHeight();
		$x = $page['x'];
		$y = $page['y'];
		// Add a little padding to grab any edges we may have cut off.
		$pad_x = ceil($width * $pad);
		$pad_y = ceil($height * $pad);
		$width += ($pad_x * 2);
		$height += ($pad_y * 2);
		$x -= $pad_x;
		if ($x < 0) {
			$width += $x;
			$x = 0;
		}
		$y -= $pad_y;
		if ($y < 0) {
			$height += $y;
			$y = 0;
		}
		// Now do the actual trimming, sizing, and rotating.
		$image = new Imagick($filename);
		$image->cropImage($width, $height, $x, $y);
		// Create the thumbnail similtaneously.
		$image->setBackgroundColor($pines->config->com_sales->image_padding_color);
		$image->thumbnailImage($pines->config->com_sales->thumbnail_size['width'], $pines->config->com_sales->thumbnail_size['height'], true, true);

		// Rename the new thumbnail image.
		$thumb_dir = $working_dir.'/'.$pines->config->com_sales->thumbnail_folder;
		if (!is_dir($thumb_dir))
			mkdir($thumb_dir);
		$basename = basename($filename);
		$basename = substr($basename, 0, strrpos($basename, '.'));
		$new_thumb = $thumb_dir.'/thumb_'.$basename;
		// Check to see if we need to reformat the images.
		$image_format = $image->getImageFormat();
		if (!in_array($image_format, $pines->config->com_sales->image_formats)) {
			$image->setImageFormat($pines->config->com_sales->image_format);
			$new_thumb .= '.'.$pines->config->com_sales->image_format;
		} else {
			$new_thumb .= '.'.$image_format;
		}
		// Write out the new files.
		$image->writeImage($new_thumb);
		file_put_contents($thumb_dir.'/.md5_thumb_'.basename($new_thumb), md5($new_thumb));
		$product->thumbnail = $pines->uploader->url($new_thumb);
	}
}
foreach ($originals as $cur_original)
	unlink($cur_original);

// Purchasing
$product->stock_type = $_REQUEST['stock_type'];
$product->vendors = (array) json_decode($_REQUEST['vendors']);
foreach ($product->vendors as &$cur_vendor) {
	$cur_vendor = array(
		'entity' => com_sales_vendor::factory((int) $cur_vendor->key),
		'sku' => $cur_vendor->values[1],
		'cost' => $cur_vendor->values[2],
		'link' => $cur_vendor->values[3]
	);
	if (!isset($cur_vendor['entity']->guid))
		$cur_vendor['entity'] = null;
}
unset($cur_vendor);

// Pricing
$product->pricing_method = $_REQUEST['pricing_method'];
$product->unit_price = (float) $_REQUEST['unit_price'];
$product->margin = (float) $_REQUEST['margin'];
$product->floor = (float) $_REQUEST['floor'];
$product->ceiling = (float) $_REQUEST['ceiling'];
// TODO: Tax exempt by location.
$product->tax_exempt = ($_REQUEST['tax_exempt'] == 'ON');
$product->additional_tax_fees = array();
if (is_array($_REQUEST['additional_tax_fees'])) {
	foreach ($_REQUEST['additional_tax_fees'] as $cur_tax_fee_guid) {
		$cur_tax_fee = com_sales_tax_fee::factory((int) $cur_tax_fee_guid);
		if (isset($cur_tax_fee->guid))
			$product->additional_tax_fees[] = $cur_tax_fee;
	}
}
$product->return_checklists = array();
if (is_array($_REQUEST['return_checklists'])) {
	foreach ($_REQUEST['return_checklists'] as $cur_return_checklist_guid) {
		$cur_return_checklist = com_sales_return_checklist::factory((int) $cur_return_checklist_guid);
		if (isset($cur_return_checklist->guid))
			$product->return_checklists[] = $cur_return_checklist;
	}
}

// Attributes
$product->weight = (float) $_REQUEST['weight'];
$product->rma_after = (float) $_REQUEST['rma_after'];
$product->serialized = ($_REQUEST['serialized'] == 'ON');
$product->discountable = ($_REQUEST['discountable'] == 'ON');
$product->require_customer = ($_REQUEST['require_customer'] == 'ON');
$product->one_per_ticket = ($_REQUEST['one_per_ticket'] == 'ON');
$product->hide_on_invoice = ($_REQUEST['hide_on_invoice'] == 'ON');
$product->non_refundable = ($_REQUEST['non_refundable'] == 'ON');
$product->additional_barcodes = explode(',', $_REQUEST['additional_barcodes']);
$product->actions = (array) $_REQUEST['actions'];

// Commission
if ($pines->config->com_sales->com_hrm) {
	$product->commissions = (array) json_decode($_REQUEST['commissions']);
	foreach ($product->commissions as $key => &$cur_commission) {
		$cur_commission = array(
			'group' => group::factory((int) $cur_commission->values[0]),
			'type' => $cur_commission->values[1],
			'amount' => (float) $cur_commission->values[2]
		);
		if (!isset($cur_commission['group']->guid) || !in_array($cur_commission['type'], array('spiff', 'percent_price')))
			unset($product->commissions[$key]);
	}
	unset($cur_commission);
}

// Storefront
if ($pines->config->com_sales->com_storefront) {
	$product->show_in_storefront = ($_REQUEST['show_in_storefront'] == 'ON');
	$product->featured = ($_REQUEST['featured'] == 'ON');
	$product->featured_image = $_REQUEST['featured_image'];
	if (!$pines->uploader->check($product->featured_image))
		$product->featured_image = null;
	// Build a list of categories.
	$categories = array();
	if (is_array($_REQUEST['categories']))
		$categories = array_map('intval', $_REQUEST['categories']);
	$categories = (array) $pines->entity_manager->get_entities(
			array('class' => com_sales_category),
			array('&',
				'tag' => array('com_sales', 'category'),
				'data' => array('enabled', true)
			),
			array('|',
				'guid' => $categories
			)
		);
	// Build a list of specs.
	$specs = array();
	foreach ($categories as &$cur_category) {
		$specs = array_merge($specs, $cur_category->get_specs_all());
	}
	unset($categories, $cur_category);
	// Save specs.
	$product->specs = array();
	foreach ($specs as $key => $cur_spec) {
		switch ($cur_spec['type']) {
			case 'bool':
				$product->specs[$key] = ($_REQUEST[$key] == 'ON');
				break;
			case 'string':
				$product->specs[$key] = (string) $_REQUEST[$key];
				if ($cur_spec['restricted'] && !in_array($product->specs[$key], $cur_spec['options']))
					unset($product->specs[$key]);
				break;
			case 'float':
				$product->specs[$key] = (float) $_REQUEST[$key];
				if ($cur_spec['restricted'] && !in_array($product->specs[$key], $cur_spec['options']))
					unset($product->specs[$key]);
				break;
			default:
				break;
		}
	}
	unset($specs);
}

if (empty($product->name)) {
	$product->print_form();
	pines_notice('Please specify a name.');
	return;
}
if ($product->stock_type == 'non_stocked' && $product->pricing_method == 'margin') {
	$product->print_form();
	pines_notice('Margin pricing is not available for non stocked items.');
	return;
}
$test = $pines->entity_manager->get_entity(array('class' => com_sales_product, 'skip_ac' => true), array('&', 'tag' => array('com_sales', 'product'), 'data' => array('name', $product->name)));
if (isset($test) && $test->guid != $_REQUEST['id']) {
	$product->print_form();
	pines_notice('There is already a product with that name. Please choose a different name.');
	return;
}

if ($pines->config->com_sales->global_products)
	$product->ac->other = 1;

if ($product->save()) {
	pines_notice('Saved product ['.$product->name.']');
	// Assign the product to the selected categories.
	// We have to do this here, because new products won't have a GUID until now.
	$categories = array();
	if (is_array($_REQUEST['categories']))
		$categories = array_map('intval', $_REQUEST['categories']);
	$all_categories = $pines->entity_manager->get_entities(array('class' => com_sales_category), array('&', 'tag' => array('com_sales', 'category'), 'data' => array('enabled', true)));
	foreach($all_categories as &$cur_cat) {
		if (in_array($cur_cat->guid, $categories) && !$product->in_array($cur_cat->products)) {
			$cur_cat->products[] = $product;
			if (!$cur_cat->save())
				pines_error("Couldn't add product to category {$cur_cat->name}. Do you have permission?");
		} elseif (!in_array($cur_cat->guid, $categories) && $product->in_array($cur_cat->products)) {
			$key = $product->array_search($cur_cat->products);
			unset($cur_cat->products[$key]);
			if (!$cur_cat->save())
				pines_error("Couldn't remove product from category {$cur_cat->name}. Do you have permission?");
		}
	}
	unset($cur_cat);
} else {
	pines_error('Error saving product. Do you have permission?');
}

redirect(pines_url('com_sales', 'product/list'));

?>