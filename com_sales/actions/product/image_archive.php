<?php
/**
 * Download all product images.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_sales/listproducts') )
	punt_user(null, pines_url('com_sales', 'product/list'));

// First check that we can make a tar.gz archive.
$result = shell_exec('tar --version');
if (!isset($result)) {
	pines_error('Tar utility is not installed or not available.');
	throw new HttpServerException(null, 501);
}

// Make a temporary directory.
$tmp = sys_get_temp_dir();
$dir = uniqid('com_sales_');
$tmp_dir = "$tmp/$dir";
if (
		!mkdir($tmp_dir, 0700) ||
		!mkdir("$tmp_dir/images", 0700) ||
		!mkdir("$tmp_dir/enabled", 0700) ||
		!mkdir("$tmp_dir/enabled/by-guid", 0700) ||
		!mkdir("$tmp_dir/enabled/by-sku", 0700) ||
		!mkdir("$tmp_dir/enabled/by-name", 0700) ||
		!mkdir("$tmp_dir/disabled", 0700) ||
		!mkdir("$tmp_dir/disabled/by-guid", 0700) ||
		!mkdir("$tmp_dir/disabled/by-sku", 0700) ||
		!mkdir("$tmp_dir/disabled/by-name", 0700)
	) {
	pines_error('Can\'t make temporary directories.');
	throw new HttpServerException(null, 500);
}

// Get all products with images.
$products = $pines->entity_manager->get_entities(
		array('class' => com_sales_product, 'skip_ac' => true),
		array('&',
			'tag' => array('com_sales', 'product')
		),
		array('|',
			'data' => array(
				array('thumbnail', true), // Non-empty strings == true.
				array('images', true) // Full arrays == true.
			)
		)
	);
if (!$products) {
	pines_notice('There are no products with images.');
	return;
}

// Go through all products and copy their images.
$found_images = false;
foreach ($products as $cur_product) {
	$image_dir = "$tmp_dir/images/{$cur_product->guid}/";
	$link_dir = "$tmp_dir/".($cur_product->enabled ? 'enabled/' : 'disabled/');
	$make_links = false;
	// Copy the product thumbnail.
	if ($cur_product->thumbnail && file_exists($cur_product->thumbnail)) {
		if (!file_exists($image_dir) && !mkdir($image_dir, 0700)) {
			pines_error("Can't make product directory for {$cur_product->name}.");
			continue;
		}
		$extension = pathinfo($cur_product->thumbnail, PATHINFO_EXTENSION);
		if (!copy($cur_product->thumbnail, "$image_dir/thumb.$extension"))
			pines_error("Can't copy thumbnail for {$cur_product->name}.");
		$make_links = true;
	}
	// Copy the product images and their thumbnails.
	$i = 1;
	foreach ($cur_product->images as $cur_image) {
		$increment = false;
		if ($cur_image['file'] && file_exists($cur_image['file'])) {
			if (!file_exists($image_dir) && !mkdir($image_dir, 0700)) {
				pines_error("Can't make product directory for {$cur_product->name}.");
				continue;
			}
			$extension = pathinfo($cur_image['file'], PATHINFO_EXTENSION);
			if (!copy($cur_image['file'], "$image_dir/image-$i.$extension"))
				pines_error("Can't copy image $i for {$cur_product->name}.");
			$make_links = $increment = true;
		}
		if ($cur_image['thumbnail'] && file_exists($cur_image['thumbnail'])) {
			if (!file_exists($image_dir) && !mkdir($image_dir, 0700)) {
				pines_error("Can't make product directory for {$cur_product->name}.");
				continue;
			}
			$extension = pathinfo($cur_image['thumbnail'], PATHINFO_EXTENSION);
			if (!copy($cur_image['thumbnail'], "$image_dir/image-thumb-$i.$extension"))
				pines_error("Can't copy image thumb $i for {$cur_product->name}.");
			$make_links = $increment = true;
		}
		if ($increment)
			$i++;
	}
	// Make links to the image folder.
	if ($make_links) {
		$cwd = getcwd();
		chdir("$link_dir/by-guid/");
		symlink("../../images/{$cur_product->guid}/", "$link_dir/by-guid/{$cur_product->guid}");
		if (!empty($cur_product->sku)) {
			chdir("$link_dir/by-sku/");
			symlink("../../images/{$cur_product->guid}/", "$link_dir/by-sku/".substr(str_replace('..', '__', $cur_product->sku), 0, 255));
		}
		if (!empty($cur_product->name)) {
			chdir("$link_dir/by-name/");
			symlink("../../images/{$cur_product->guid}/", "$link_dir/by-name/".substr(str_replace('..', '__', $cur_product->name), 0, 255));
		}
		chdir($cwd);
		$found_images = true;
	}
}

if (!$found_images) {
	pines_notice('No product image files were found.');
	return;
}

// Tar the whole directory.
$result = shell_exec('tar -C '.escapeshellarg($tmp_dir).' -czf '.escapeshellarg($tmp_dir).'/archive.tar.gz images enabled disabled');
if (!file_exists("$tmp_dir/archive.tar.gz")) {
	pines_error('Couldn\'t create tar archive.');
	return;
}
header('Content-Type: application/x-gzip');
header('Content-Disposition: attachment; filename=image-archive.tar.gz');
$pines->page->override = true;
$pines->page->override_doc(file_get_contents("$tmp_dir/archive.tar.gz"));

?>