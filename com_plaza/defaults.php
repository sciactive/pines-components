<?php
/**
 * com_plaza's configuration defaults.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'fetch_method',
		'cname' => 'URL Fetching Method',
		'description' => 'The method Pines Plaza will use to fetch files from the internet.',
		'value' => 'auto',
		'options' => array(
			'Detect Automatically' => 'auto',
			'PECL HTTP Library' => 'pecl',
			'cURL Library' => 'curl',
			'Build in PHP Functions (fopen)' => 'fopen',
		),
	),
);

?>