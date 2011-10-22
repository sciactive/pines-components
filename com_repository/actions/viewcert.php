<?php
/**
 * View the repository certificate.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_repository/gencert') )
	punt_user(null, pines_url('com_repository', 'viewcert'));

$cert = "{$pines->config->com_repository->repository_path}private/cert.pem";
if (!file_exists($cert)) {
	pines_notice('Repository certificate has not been generated yet.');
	pines_redirect(pines_url('com_repository', 'gencert'));
	return;
}
$cert = file_get_contents($cert);
if (!$cert) {
	pines_notice('Repository certificate is blank.');
	pines_redirect(pines_url('com_repository', 'gencert'));
	return;
}
if (!($cert_r = openssl_x509_read($cert))) {
	pines_notice('Repository certificate is malformed.');
	pines_redirect(pines_url('com_repository', 'gencert'));
	return;
}

$module = new module('com_repository', 'view_certificate', 'content');
$module->cert = $cert;
$module->data = openssl_x509_parse($cert_r);

?>