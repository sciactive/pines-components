<?php
/**
 * com_elfinder's configuration defaults.
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

return array(
	array(
		'name' => 'root',
		'cname' => 'Root Path',
		'description' => 'The path of the root directory for the file manager. End this path with a slash!',
		'value' => $pines->config->upload_location,
		'peruser' => true,
	),
	array(
		'name' => 'root_url',
		'cname' => 'Root URL',
		'description' => 'The URL of the root directory for the file manager. End this path with a slash!',
		'value' => $pines->config->rela_location.$pines->config->upload_location,
		'peruser' => true,
	),
	array(
		'name' => 'full_root_url',
		'cname' => 'Full Root URL',
		'description' => 'The full URL of the root directory for the file manager. End this path with a slash!',
		'value' => $pines->config->full_location.$pines->config->upload_location,
		'peruser' => true,
	),
	array(
		'name' => 'own_root',
		'cname' => 'Own Root Path',
		'description' => 'The path of the user root directory (relative to the root path) for the file manager. End this path with a slash!',
		'value' => 'users/',
		'peruser' => true,
	),
	array(
		'name' => 'root_alias',
		'cname' => 'Root Alias',
		'description' => 'A name to call the root directory.',
		'value' => 'Media',
		'peruser' => true,
	),
	array(
		'name' => 'own_root_alias',
		'cname' => 'Own Root Alias',
		'description' => 'A name to call the user\'s root directory.',
		'value' => 'My Files',
		'peruser' => true,
	),
	array(
		'name' => 'disabled',
		'cname' => 'Disabled Commands',
		'description' => 'List of not allowed commands.',
		'value' => array(''),
		'peruser' => true,
	),
	array(
		'name' => 'dot_files',
		'cname' => 'Display Dot Files',
		'description' => 'Display hidden dot files.',
		'value' => false,
		'peruser' => true,
	),
	array(
		'name' => 'dir_size',
		'cname' => 'Directory Sizes',
		'description' => 'Count total directories sizes.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'file_mode',
		'cname' => 'New File Mode',
		'description' => 'The mode to set on new files.',
		'value' => 0644,
		'peruser' => true,
	),
	array(
		'name' => 'dir_mode',
		'cname' => 'New Directory Mode',
		'description' => 'The mode to set on new directories.',
		'value' => 0755,
		'peruser' => true,
	),
	array(
		'name' => 'mime_detect',
		'cname' => 'Mime Type Detection',
		'description' => 'The method to use to detect mime types of files.',
		'value' => 'auto',
		'options' => array(
			'auto',
			'finfo',
			'mime_content_type',
			'linux',
			'bsd',
			'internal'
		),
		'peruser' => true,
	),
	array(
		'name' => 'upload_check',
		'cname' => 'Check Upload Mime Types',
		'description' => 'Check uploaded files\' mime types.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'upload_allow',
		'cname' => 'Allowed Upload Mime Types',
		'description' => 'Mime types which are allowed to be uploaded.',
		'value' => array(
			'image/jpeg',
			'image/gif',
			'image/png',
			'image/tiff'
		),
		'peruser' => true,
	),
	array(
		'name' => 'upload_deny',
		'cname' => 'Denied Upload Mime Types',
		'description' => 'Mime types which are not allowed to be uploaded.',
		'value' => array('all'),
		'peruser' => true,
	),
	array(
		'name' => 'upload_order',
		'cname' => 'Mime Type Check Order',
		'description' => 'The order in which mime types are checked against allowed and denied settings.',
		'value' => 'deny,allow',
		'options' => array(
			'allow,deny',
			'deny,allow'
		),
		'peruser' => true,
	),
	array(
		'name' => 'img_lib',
		'cname' => 'Image Library',
		'description' => 'The image manipulation library to use.',
		'value' => 'auto',
		'options' => array(
			'auto',
			'imagick',
			'mogrify',
			'gd'
		),
		'peruser' => true,
	),
	array(
		'name' => 'tmb_dir',
		'cname' => 'Thumbnail Directory',
		'description' => 'The directory for image thumbnails. Leave blank to avoid thumbnails generation. Start with a dot (like ".tmb") to hide it.',
		'value' => '',
		'peruser' => true,
	),
	array(
		'name' => 'tmb_clean_prob',
		'cname' => 'Thumbnail Cleaning',
		'description' => 'How often to clean the thumbnail directory. (0 - never, 100 - every init request)',
		'value' => 1,
		'peruser' => true,
	),
	array(
		'name' => 'tmb_at_once',
		'cname' => 'Thumbnails Per Request',
		'description' => 'Number of thumbnails to generate per request.',
		'value' => 5,
		'peruser' => true,
	),
	array(
		'name' => 'tmb_size',
		'cname' => 'Thumbnail Size',
		'description' => 'Image thumbnails size (px).',
		'value' => 48,
		'peruser' => true,
	),
	array(
		'name' => 'file_url',
		'cname' => 'Show File URL',
		'description' => 'Display file URL in "get info".',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'date_format',
		'cname' => 'Date Format',
		'description' => 'File modification date format.',
		'value' => 'j M Y H:i',
		'peruser' => true,
	),
	array(
		'name' => 'default_height',
		'cname' => 'Default Height',
		'description' => 'The default height of the file manager interface.',
		'value' => 500,
		'peruser' => true,
	),
	array(
		'name' => 'default_read',
		'cname' => 'Default Read Permission',
		'description' => 'Allow user to read by default.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'default_write',
		'cname' => 'Default Write Permission',
		'description' => 'Allow user to write by default.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'default_rm',
		'cname' => 'Default Remove Permission',
		'description' => 'Allow user to delete by default.',
		'value' => true,
		'peruser' => true,
	),
);

?>