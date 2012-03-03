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
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_elfinder/finder') && !gatekeeper('com_elfinder/finderself') )
	punt_user(null, pines_url('com_elfinder', 'finder'));

error_reporting(0); // Set E_ALL for debuging

/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool
 **/
function com_elfinder__access($attr, $path, $data, $volume) {
	global $pines;
	if ($pines->config->com_elfinder->dot_files) {
		return ($attr == 'read' || $attr == 'write');
	} else {
		return strpos(basename($path), '.') === 0   // if file/folder begins with '.' (dot)
			? !($attr == 'read' || $attr == 'write')  // set read+write to false, other (locked+hidden) set to true
			: ($attr == 'read' || $attr == 'write');  // else set read+write to true, locked+hidden to false
	}
}

if ($_REQUEST['temp'] == 'true') {
	$dir = $_SESSION['elfinder_request_id'][$_REQUEST['request_id']];
	if (!isset($dir)) {
		pines_session('write');
		$dir = $_SESSION['elfinder_request_id'][$_REQUEST['request_id']] = uniqid('pines_upload_');
		pines_session('close');
	}
	$tmp = sys_get_temp_dir().'/'.$dir.'/';
	$opts = array(
		'roots' => array(
			array(
				'driver' => 'LocalFileSystem',
				'path' => $tmp,
				'alias' => 'Upload Area',
				'URL' => $_REQUEST['request_id'],
				'tmbPath' => '',
				'dateFormat' => $pines->config->com_elfinder->date_format,
				'timeFormat' => $pines->config->com_elfinder->time_format,
				'defaults' => array(
					'read' => true,
					'write' => true
				),
				'disabled' => empty($pines->config->com_elfinder->disabled) ? array() : $pines->config->com_elfinder->disabled,
				'accessControl' => 'com_elfinder__access'
			)
		),
	);
	if ($pines->config->com_elfinder->upload_check) {
		$opts['roots'][0]['uploadAllow'] = $pines->config->com_elfinder->upload_allow;
		$opts['roots'][0]['uploadDeny'] = $pines->config->com_elfinder->upload_deny;
		$opts['roots'][0]['uploadOrder'] = $pines->config->com_elfinder->upload_order;
	}
	if (!file_exists($opts['roots'][0]['path']))
		mkdir($opts['roots'][0]['path'], 0700);
} else {
	$opts = array(
		'roots' => array(
			array(
				'driver' => 'LocalFileSystem',
				'path' => $pines->config->com_elfinder->root,
				'alias' => $pines->config->com_elfinder->root_alias,
				'URL' => $pines->config->com_elfinder->root_url,
				'fileMode' => $pines->config->com_elfinder->file_mode,
				'dirMode' => $pines->config->com_elfinder->dir_mode,
				'tmbPath' => $pines->config->com_elfinder->tmb_dir,
				'tmbCleanProb' => $pines->config->com_elfinder->tmb_clean_prob,
				'tmbSize' => $pines->config->com_elfinder->tmb_size,
				'tmbPathMode' => 0700,
				'dateFormat' => $pines->config->com_elfinder->date_format,
				'timeFormat' => $pines->config->com_elfinder->time_format,
				'defaults' => array(
					'read' => $pines->config->com_elfinder->default_read,
					'write' => $pines->config->com_elfinder->default_write
				),
				'disabled' => empty($pines->config->com_elfinder->disabled) ? array() : $pines->config->com_elfinder->disabled,
				'accessControl' => 'com_elfinder__access'
			)
		),
	);

	if (!empty($_REQUEST['start_path']))
		$opts['roots'][0]['startPath'] = $_REQUEST['start_path'];

	if ($pines->config->com_elfinder->upload_check) {
		$opts['roots'][0]['uploadAllow'] = $pines->config->com_elfinder->upload_allow;
		$opts['roots'][0]['uploadDeny'] = $pines->config->com_elfinder->upload_deny;
		$opts['roots'][0]['uploadOrder'] = $pines->config->com_elfinder->upload_order;
	}
	if (isset($_SESSION['user']) && file_exists($pines->config->com_elfinder->root . $pines->config->com_elfinder->own_root)) {
		if (!gatekeeper('com_elfinder/finder')) {
			$opts['roots'][0]['path'] .= $pines->config->com_elfinder->own_root . $_SESSION['user']->guid . '/';
			$opts['roots'][0]['URL'] .= $pines->config->com_elfinder->own_root . $_SESSION['user']->guid . '/';
			$opts['roots'][0]['alias'] = $pines->config->com_elfinder->own_root_alias;
			if (!file_exists($opts['roots'][0]['path']))
				mkdir($opts['roots'][0]['path']);
		} else {
			$opts['roots'][1] = $opts['roots'][0];
			$opts['roots'][1]['path'] .= $pines->config->com_elfinder->own_root . $_SESSION['user']->guid . '/';
			$opts['roots'][1]['URL'] .= $pines->config->com_elfinder->own_root . $_SESSION['user']->guid . '/';
			$opts['roots'][1]['alias'] = $pines->config->com_elfinder->own_root_alias;
			if (!file_exists($opts['roots'][1]['path']))
				mkdir($opts['roots'][1]['path']);
		}
	}
}

$connector = new elFinderConnector(new elFinder($opts));
$connector->run();

?>