<?php
/**
 * com_googledrive's defaults.
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
    array(
        'name' => 'client_id',
        'cname' => 'Client ID',
        'description' => 'Your Google OAuth 2.0 Client ID',
        'value' => '',
    ),
    array(
        'name' => 'scopes_export',
        'cname' => 'Exports Scope',
        'description' => 'The scope to use for Exporting. Has to do with permissions.',
        'value' => 'https://www.googleapis.com/auth/drive',
    ),
);

?>