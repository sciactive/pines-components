<?php
/**
 * Provide a trusted function for getting repositories.
 *
 * @package Components\plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Get trusted repositories.
 *
 * @return array An array of trusted repositories.
 */
function com_plaza__get_repositories() {
	static $repos = array();
	static $perm_warning_shown = false;
	if (!empty($repos))
		return $repos;
	$authorities = glob('components/com_plaza/includes/cache/certs/authorities/*.pem');
	$certs = glob('components/com_plaza/includes/cache/certs/repositories/*.pem');
	foreach ($certs as $cur_cert) {
		// Open the cert.
		$cert_r = openssl_x509_read(file_get_contents($cur_cert));
		if (!$cert_r)
			continue;
		// Check that it is from a trusted authority.
		if (!openssl_x509_checkpurpose($cert_r, X509_PURPOSE_ANY, $authorities))
			continue;
		// Extract the URL and add it to the list.
		$data = openssl_x509_parse($cert_r);
		$repos[] = array(
			'url' => $data['subject']['CN'],
			'cert' => $cur_cert,
			'data' => $data
		);
		openssl_x509_free($cert_r);
	}
	if (!$perm_warning_shown && is_writable('components/com_plaza/includes/cache/certs/authorities/')) {
		$module = new module('com_plaza', 'bad_permissions', 'content');
		$perm_warning_shown = true;
	}
	return $repos;
}

?>