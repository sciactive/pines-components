<?php
/**
 * googledrive's component class
 *
 * @package Components\googledrive
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_googledrive main class.
 *
 * A JavaScript Library to handle exporting csv to Google Drive
 *
 * @package Components\googledrive
 */
class com_googledrive extends component {
    /**
     * Load the googledrive JS.
     *
     * This will place the required scripts into the document's head section.
     * @param $type A string that indicates which type of file export.
     */
    function export_to_drive($type = 'csv') {
        $module = new module('com_googledrive', 'export_csv', 'head');
        $module->render();
    }
}
?>