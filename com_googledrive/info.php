<?php
/**
 * com_googledrive's information.
 *
 * @package Components\googledrive
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

return array(
	'name' => 'Google Drive',
	'author' => 'Mohammed Ahmed',
	'version' => '1.0.0',
	'license' => 'none',
	'website' => 'http://smart108.com',
	'short_description' => 'Users can export csv to Google Drive',
	'description' => 'Exports rows in a grid to Google Drive as a spreadsheet',
	'depend' => array(
		'pines' => '<3',
                'component' => 'com_pnotify'
	),
	
);

?>