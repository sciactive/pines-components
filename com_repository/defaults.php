<?php
/**
 * com_repository's configuration defaults.
 *
 * @package Pines
 * @subpackage com_repository
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'repository_path',
		'cname' => 'Repository Path',
		'description' => 'The relative path of the directory containing the repository. End this path with a slash!',
		'value' => $pines->config->upload_location.'repository/',
	),
);

?>