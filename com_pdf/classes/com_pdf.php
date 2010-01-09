<?php
/**
 * com_pdf class.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * com_pdf main class.
 *
 * Create dynamic PDFs, and even let the user format the text.
 *
 * @package Pines
 * @subpackage com_pdf
 */
class com_pdf extends component {
	/**
	 * Load the JavaScript to insert display editors into the page.
	 */
	public function load_display_editors() {
		$entity = (object) null;
		$entity->pdf_file = 'blank.pdf';
		$entity->pdf_dl_filename = 'contract-test.pdf';
		$entity->pdf_pages = 1;
		$entity->pdf_title = 'Contract';
		$entity->pdf_author = 'TECHsmart';
		$entity->pdf_creator = 'Pines';
		$entity->pdf_subject = '';
		$entity->pdf_keywords = '';

		//Todo: Test
		$entity->favfood = 'Sushi';
		$entity->displays->favfood = json_decode('[{"page":1,"left":0.393790849673,"top":0.0681003584229,"width":0.566993464052,"height":0.0227001194743,"overflow":false,"bold":true,"italic":true,"fontfamily":"Courier","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}]');
		$module = new module('com_pdf', 'editors', 'head');
		$module->entity = $entity;
	}
}

?>