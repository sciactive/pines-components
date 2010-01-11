<?php
/**
 * Provide a test form for the display editors.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('system/all') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_pdf', 'test', null, false));
	return;
}

$module = new module('com_pdf', 'test', 'content');

$entity = com_pdf_displays::factory();
$entity->pdf_file = 'blank.pdf';
$entity->pdf_dl_filename = 'test.pdf';
$entity->pdf_title = 'PDF Test';
$entity->pdf_author = 'Person McAuthor';
$entity->pdf_creator = 'Pines';
$entity->pdf_subject = 'A PDF generator test.';
$entity->pdf_keywords = 'test';
$entity->displays['favfood'] = json_decode('[{"page":1,"left":0.393790849673,"top":0.0681003584229,"width":0.566993464052,"height":0.0227001194743,"overflow":false,"bold":true,"italic":true,"fontfamily":"Courier","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}]');
$entity->load_editors();

?>