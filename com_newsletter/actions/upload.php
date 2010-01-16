<?php
/**
 * Upload an image.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/managemails') )
	$config->user_manager->punt_user("You don't have necessary permission.", pines_url('com_newsletter', 'list', null, false));

$page->override = true;

header("content-type: text/html"); // the return type must be text/html
//if file has been sent successfully:
if (isset($_FILES['image']['tmp_name'])) {
	// open the file
	$img = $_FILES['image']['tmp_name'];
	$himage = fopen ( $img, "r"); // read the temporary file into a buffer
	$image = fread ( $himage, filesize($img) );
	fclose($himage);
	//if image can't be opened, either its not a valid format or even an image:
	if ($image === FALSE) {
		$page->override_doc("{status:'Error Reading Uploaded File.'}");
		return;
	}
	// create a new random numeric name to avoid rewriting other images already on the server...
	$ran = rand ();
	$ran2 = $ran.'.';
	$path = $config->setting_upload.'images/'.$ran2.'jpg';
	// copy the image to the server, alert on fail
	$hout=fopen($path,"w");
	fwrite($hout,$image);
	fclose($hout);
	
	$path = $config->full_location . $path;
	$page->override_doc("{status:'UPLOADED', image_url:'$path'}");
} else {
	$page->override_doc("{status:'No file was submitted'}");
}

?>