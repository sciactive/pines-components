<?php
/**
 * Get a package.
 *
 * @package Components
 * @subpackage repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

$pines->page->override = true;

$publisher = $_REQUEST['pub'];
$package = $_REQUEST['p'];
$version = $_REQUEST['v'];

if (empty($publisher) || empty($package) || empty($version))
	return;

$user = user::factory($publisher);
if (!isset($user->guid))
	throw new HttpClientException(null, 404);

$file = clean_filename("{$pines->config->com_repository->repository_path}{$user->guid}/{$package}/{$version}/{$package}-{$version}.slm");
$sigfile = clean_filename("{$pines->config->com_repository->repository_path}{$user->guid}/{$package}/{$version}/{$package}-{$version}.sig");
if (!file_exists($file) || !file_exists($sigfile))
	throw new HttpClientException(null, 404);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='."{$package}-{$version}.slm");
// Provide the signature, so authenticity can be verified.
header('X-Pines-Slim-Signature: '.base64_encode(file_get_contents($sigfile)));

$pines->page->override_doc(file_get_contents($file));

?>