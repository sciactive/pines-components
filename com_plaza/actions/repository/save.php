<?php
/**
 * Print a form to add a new repository.
 *
 * @package Components\plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_plaza/editrepositories') )
	punt_user(null, pines_url('com_plaza', 'repository/new'));

$filename = basename(clean_filename($_REQUEST['filename']), '.pem');
if (empty($filename)) {
	pines_notice('Please specifiy a filename.');
	pines_redirect(pines_url('com_plaza', 'repository/new'));
	return;
}
$filename .= '.pem';
$path = "components/com_plaza/includes/cache/certs/repositories/{$filename}";
// Get certificate text.
if (!empty($_REQUEST['cert_url'])) {
	if (!($cert = file_get_contents($_REQUEST['cert_url']))) {
		pines_notice('Couldn\'t retrieve certificate. Please provide the full text instead. You can get it by going to the URL in your browser.');
		pines_redirect(pines_url('com_plaza', 'repository/new'));
		return;
	}
} else {
	$cert = $_REQUEST['cert_text'];
}

if (file_exists($path)) {
	pines_notice('Filename already exists.');
	pines_redirect(pines_url('com_plaza', 'repository/new'));
	return;
}
if (empty($cert)) {
	pines_notice('Please provide a certificate.');
	pines_redirect(pines_url('com_plaza', 'repository/new'));
	return;
}
if (!($cert_r = openssl_x509_read($cert))) {
	pines_notice('Repository certificate is malformed.');
	pines_redirect(pines_url('com_plaza', 'repository/new'));
	return;
}
$authorities = glob('components/com_plaza/includes/cache/certs/authorities/*.pem');
if (!openssl_x509_checkpurpose($cert_r, X509_PURPOSE_ANY, $authorities)) {
	pines_notice('Repository certificate is not trusted by any approved authority.');
	pines_redirect(pines_url('com_plaza', 'repository/new'));
	return;
}
if (!file_put_contents($path, $cert)) {
	pines_error('Couldn\'t save certificate file.');
	pines_redirect(pines_url('com_plaza', 'repository/new'));
	return;
}
$data = openssl_x509_parse($cert_r);
pines_notice("Added repository \"{$data['subject']['OU']}\" from \"{$data['subject']['O']}\".");

pines_redirect(pines_url('com_plaza', 'repository/list'));

?>