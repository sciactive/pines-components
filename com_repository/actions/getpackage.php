<?php
/**
 * Get a package.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

$publisher = $_REQUEST['pub'];
$package = $_REQUEST['p'];
$version = $_REQUEST['v'];

if (empty($publisher) || empty($package) || empty($version))
	return;

$user = user::factory($publisher);
if (!isset($user->guid))
	return 'error_404';

$file = clean_filename("{$pines->config->com_repository->repository_path}{$user->guid}/{$package}-{$version}.slm");
if (!file_exists($file))
	return 'error_404';

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='."{$package}-{$version}.slm");

$pines->page->override_doc(file_get_contents($file));

?>