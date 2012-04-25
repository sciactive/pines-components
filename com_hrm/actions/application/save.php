<?php
/**
 * Save changes to an employement application.
 *
 * @package Components
 * @subpackage hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_hrm/editapplication') )
	punt_user(null, pines_url('com_hrm', 'application/list'));

$application = com_hrm_application::factory((int) $_REQUEST['id']);

// General Applicant Information
$application->name_first = $_REQUEST['name_first'];
$application->name_middle = $_REQUEST['name_middle'];
$application->name_last = $_REQUEST['name_last'];
$application->name = $application->name_first.(!empty($application->name_middle) ? ' '.$application->name_middle : '').(!empty($application->name_last) ? ' '.$application->name_last : '');
$application->phone = $_REQUEST['phone'];
$application->email = $_REQUEST['email'];
if ($pines->config->com_hrm->ssn_field)
	$application->ssn = preg_replace('/\D/', '', $_REQUEST['ssn']);
// Location
$application->address_type = $_REQUEST['address_type'];
$application->address_1 = $_REQUEST['address_1'];
$application->address_2 = $_REQUEST['address_2'];
$application->city = $_REQUEST['city'];
$application->state = $_REQUEST['state'];
$application->zip = $_REQUEST['zip'];
$application->address_international = $_REQUEST['address_international'];
// Job Specific Information
$application->position = $_REQUEST['position'];
$application->work_authorized = ($_REQUEST['work_authorized'] == 'ON');
// Education
$application->education = array();

for ($i = 0; $i < count($_REQUEST['school_type']); $i++) {
	$application->education[$i] = array(
		'type' => $_REQUEST['school_type'][$i],
		'name' => $_REQUEST['school_name'][$i],
		'major' => $_REQUEST['school_major'][$i]
	);
}
// Employment History
$application->employment = array();
for ($i = 0; $i < count($_REQUEST['employer_start']); $i++) {
	$application->employment[$i] = array(
		'start' => strtotime($_REQUEST['employer_start'][$i]),
		'end' => strtotime($_REQUEST['employer_end'][$i]),
		'position' => $_REQUEST['employer_position'][$i],
		'company' => $_REQUEST['employer_company'][$i],
		'phone' => preg_replace('/\D/', '', $_REQUEST['employer_phone'][$i]),
		'reason' => $_REQUEST['employer_reason'][$i]
	);
}
// References
$application->references = array(
	array(
		'name' => $_REQUEST['reference_name'][0],
		'phone' => preg_replace('/\D/', '', $_REQUEST['reference_phone'][0]),
		'occupation' => $_REQUEST['reference_occupation'][0]
	),
	array(
		'name' => $_REQUEST['reference_name'][1],
		'phone' => preg_replace('/\D/', '', $_REQUEST['reference_phone'][1]),
		'occupation' => $_REQUEST['reference_occupation'][1]
	),
	array(
		'name' => $_REQUEST['reference_name'][2],
		'phone' => preg_replace('/\D/', '', $_REQUEST['reference_phone'][2]),
		'occupation' => $_REQUEST['reference_occupation'][2]
	)
);
// Resume
if (!isset($application->resume) || $_REQUEST['update_resume'] == 'ON') {
	if ($_FILES['uploadedfile']['error'] == 0) {
		$filename = $pines->config->com_hrm->application_dir.'/'.strtolower($application->name_last).'_'.strtolower($application->name_first).'/';
		if (!is_dir($filename))
			mkdir($filename, 0700, true);
		$filename .= $_FILES['uploadedfile']['name'];
		move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $filename);
		$application->resume = array(
			'path' => $filename,
			'type' => $_FILES['uploadedfile']['type']
		);
	}

	if ($_FILES['uploadedfile']['error'] != 0) {
		$application->print_form();
		pines_notice('Please upload a Resume.');
		return;
	}
	/*
	if ($application->resume['type'] != 'application/msword') {
		$application->send_documents();
		pines_notice('This file does not appear to be a Word Document. Please follow the instructions and try again.');
		return;
	}
	*/
}

if ($pines->config->com_hrm->ssn_field_require && empty($application->ssn)) {
	$application->print_form();
	pines_notice('Please provide an SSN.');
	return;
}

if ($application->save()) {
	pines_notice('Saved employement application ['.$application->name.']');
} else {
	pines_error('Error saving employement application. Do you have permission?');
}

pines_redirect(pines_url('com_hrm', 'application/list'));

?>