<?php
/**
 * Provide an image preview of a PDF page.
 *
 * @package Pines
 * @subpackage com_pdf
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if (!gatekeeper())
	punt_user('You don\'t have necessary permission.', pines_url('com_pdf', 'image', $_GET, false));

$pines->page->override = true;

$file = 'media/pdf/'.clean_filename($_REQUEST['file']);
$pdfpage = intval($_REQUEST['page']) - 1;

/* Determine PDF's size */
/**
 * Require the TCPDF class.
 */
require_once('components/com_pdf/includes/tcpdf/tcpdf.php');
/**
 * Require the FPDI class.
 */
require_once('components/com_pdf/includes/fpdi/fpdi.php');

$pdf = new FPDI();
$pagecount = $pdf->setSourceFile($file);
/* End Determine PDF's size */

if ($pdfpage >= 0 && $pdfpage < $pagecount) {
	$tplidx = $pdf->importPage($pdfpage + 1);
	$pagesize = $pdf->getTemplatesize($tplidx);
	$im = new Imagick("{$file}[$pdfpage]");

	$im->setImageFormat('png');

	$output = $im->getimageblob();
	header('Content-Type: image/png');
	$pines->page->override_doc($output);
} else {
	$pines->page->override_doc('Invalid file or page.');
}

?>