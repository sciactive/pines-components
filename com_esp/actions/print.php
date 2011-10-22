<?php
/**
 * Print a copy of an ESP.
 *
 * @package Pines
 * @subpackage com_esp
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_esp/printplan') )
	punt_user(null, pines_url('com_esp', 'print'));

$entity = com_esp_plan::factory((int) $_REQUEST['id']);
if (!isset($entity->guid)) {
	pines_error('Requested ESP id is not accessible.');
	return;
}

if ($entity->status != 'approved') {
	pines_notice('Requested ESP has not been approved');
	$pines->com_esp->list_plans();
	return;
}

$pdftext->customer = $entity->customer->name;
$pdftext->date = format_date(time(), 'date_sort');
$pdftext->ssn = preg_replace('/(\d{3})(\d{2})(\d{4})/', '$1-$2-$3', $entity->customer->ssn);
$pdftext->phone_day = format_phone($entity->phone_day);
$pdftext->service_branch = $entity->service_branch;
$pdftext->service_rank = $entity->service_rank;
$pdftext->service_end_date = format_date($entity->service_end_date, 'date_sort');

$pdf = com_pdf_displays::factory();
$pdf->pdf_file = 'ESP.pdf';
$pdf->pdf_dl_filename = "Fax plan {$entity->customer->name}.pdf";
$pdf->pdf_title = "Fax plan {$entity->customer->name}";
$pdf->pdf_author = $pines->config->com_esp->esp_name;
$pdf->displays = json_decode('{"customer":[{"page":1,"left":0.267973856209,"top":0.376543209877,"width":0.607843137255,"height":0.0271604938272,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"},{"page":2,"left":0.254901960784,"top":0.773382716049,"width":0.330065359477,"height":0.0259259259259,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}],"date":[{"page":2,"left":0.699346405229,"top":0.71412345679,"width":0.173202614379,"height":0.0259259259259,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}],"ssn":[{"page":1,"left":0.179738562092,"top":0.412049379356,"width":0.700980392157,"height":0.0259259259259,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":15,"fontcolor":"black","addspacing":true,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}],"phone_day":[{"page":1,"left":0.423202614379,"top":0.455555555556,"width":0.459150326797,"height":0.0271604938272,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}],"service_branch":[{"page":2,"left":0.55,"top":0.375555555556,"width":0.330065359477,"height":0.0296296296296,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}],"service_rank":[{"page":2,"left":0.55,"top":0.403185185185,"width":0.330065359477,"height":0.0296296296296,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}],"service_end_date":[{"page":2,"left":0.55,"top":0.432345679012,"width":0.330065359477,"height":0.0296296296296,"overflow":true,"bold":false,"italic":false,"fontfamily":"Times","fontsize":12,"fontcolor":"black","addspacing":false,"border":false,"letterspacing":"normal","wordspacing":"normal","textalign":"left","textdecoration":"none","texttransform":"none","direction":"ltr"}]}');

$pdf->render($pdftext);
?>