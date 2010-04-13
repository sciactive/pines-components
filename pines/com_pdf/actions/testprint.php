<?php
/**
 * Print the test PDF.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') )
	punt_user('You don\'t have necessary permission.', pines_url('com_pdf', 'testprint', $_REQUEST));

// An example display entity.
$displays = com_pdf_displays::factory();
$displays->pdf_file = 'blank.pdf';
$displays->pdf_dl_filename = 'test.pdf';
$displays->pdf_title = 'PDF Test';
$displays->pdf_author = 'Person McAuthor';
$displays->pdf_creator = 'Pines';
$displays->pdf_subject = 'A PDF generator test.';
$displays->pdf_keywords = 'test';
// This reads the display data from the script set up by load_editors().
$displays->read_request_data();

$entity = entity::factory();
$entity->name = $_REQUEST['name'];
$entity->age = $_REQUEST['age'];
$entity->phone = $_REQUEST['phone'];
$entity->favfood = $_REQUEST['favfood'];

$displays->render($entity);

?>