<?php
/**
 * Print the repository certificate.
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

if ( !$pines->config->com_repository->public_cert )
	return;

$cert = "{$pines->config->com_repository->repository_path}private/cert.pem";
if (!file_exists($cert))
	return;
$cert = file_get_contents($cert);
if (!$cert)
	return;

//header('Content-Type: application/x-x509-server-cert'); // Doesn't display correctly.
header('Content-Type: text/plain');
header('Content-Disposition: inline');

$pines->page->override_doc($cert);

?>