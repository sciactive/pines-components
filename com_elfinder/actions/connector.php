<?php
/**
 * Connector for the elFinder file manager.
 *
 * @package Pines
 * @subpackage com_elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_elfinder/finder') )
	punt_user('You don\'t have necessary permission.', pines_url('com_elfinder', 'finder'));

$opts = array(
	'root' => $pines->config->com_elfinder->root,
	'URL' => $pines->config->com_elfinder->root_url,
	'rootAlias' => $pines->config->com_elfinder->root_alias,
	'disabled' => empty($pines->config->com_elfinder->disabled) ? array() : $pines->config->com_elfinder->disabled,
	'dotFiles' => $pines->config->com_elfinder->dot_files,
	'dirSize' => $pines->config->com_elfinder->dir_size,
	'fileMode' => $pines->config->com_elfinder->file_mode,
	'dirMode' => $pines->config->com_elfinder->dir_mode,
	'mimeDetect' => $pines->config->com_elfinder->mime_detect,
	'imgLib' => $pines->config->com_elfinder->img_lib,
	'tmbDir' => $pines->config->com_elfinder->tmb_dir,
	'tmbCleanProb' => $pines->config->com_elfinder->tmb_clean_prob,
	'tmbAtOnce' => $pines->config->com_elfinder->tmb_at_once,
	'tmbSize' => $pines->config->com_elfinder->tmb_size,
	'fileURL' => $pines->config->com_elfinder->file_url,
	'dateFormat' => $pines->config->com_elfinder->date_format,
	'defaults' => array(
		'read'   => $pines->config->com_elfinder->default_read,
		'write'  => $pines->config->com_elfinder->default_write,
		'rm'     => $pines->config->com_elfinder->default_rm
	),
);

if ($pines->config->com_elfinder->upload_check) {
	$opts['uploadAllow'] = $pines->config->com_elfinder->upload_allow;
	$opts['uploadDeny'] = $pines->config->com_elfinder->upload_deny;
	$opts['uploadOrder'] = $pines->config->com_elfinder->upload_order;
}

$fm = new elFinder($opts);
$fm->run();

?>