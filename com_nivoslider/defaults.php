<?php
/**
 * com_nivoslider's configuration defaults.
 *
 * @package Components\nivoslider
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	array(
		'name' => 'check_files',
		'cname' => 'Check Files',
		'description' => 'Check that the file path given is safe according to the uploader.',
		'value' => true,
		'peruser' => true,
	),
	array(
		'name' => 'allow_html_captions',
		'cname' => 'Allow HTML in Captions',
		'description' => 'Allow HTML code to be placed in image captions.',
		'value' => true,
		'peruser' => true,
	),
);

?>