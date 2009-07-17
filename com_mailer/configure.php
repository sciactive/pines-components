<?php
/**
 * com_mailer's configuration.
 *
 * @package XROOM
 * @subpackage com_mailer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */

/**
 * Creates and sends emails.
 *
 * This class supports attachments and custom headers.
 *
 * Credit for this class goes to Alejandro Gervasio
 * http://www.devshed.com/cp/bio/Alejandro-Gervasio/
 *
 * @package XROOM
 * @subpackage com_mailer
 */
class com_mailer extends component {
	var $sender;
	var $recipient;
	var $subject;
	var $headers=array();
	var $mimeTypes=array();
	var $attachments=array();
	
    function __construct($sender, $recipient, $subject, $message) {
		// validate incoming parameters
		if (!preg_match("/^.+@.+$/",$sender)) {
			display_error('Invalid value for email sender.');
		}
		if (!preg_match("/^.+@.+$/",$recipient)) {
			display_error('Invalid value for email recipient.');
		}
		if (!$subject||strlen($subject)>255) {
			display_error('Invalid length for email subject.');
		}
		if (!$message) {
			display_error('Invalid value for email message.');
		}
		$this->sender=$sender;
		$this->recipient=$recipient;
		$this->subject=$subject;
		$this->message=$message;
		// define some default MIME headers
		$this->headers['MIME-Version']='1.0';
		$this->headers['Content-Type']='multipart/mixed;boundary="MIME_BOUNDRY"';
		$this->headers['From']='<'.$this->sender.'>';
		$this->headers['Return-Path']='<'.$this->sender.'>';
		$this->headers['Reply-To']=$this->sender;
		$this->headers['X-Mailer']='PHP5';
		$this->headers['X-Sender']=$this->sender;
		$this->headers['X-Priority']='3';
		// define some default MIME types
		$this->mimeTypes['image/jpeg']='jpg';
		$this->mimeTypes['image/jpg']='jpg';
		$this->mimeTypes['image/gif']='gif';
		$this->mimeTypes['text/plain']='txt';
		$this->mimeTypes['text/html']='htm';
		$this->mimeTypes['text/xml']='xml';
		$this->mimeTypes['application/pdf']='pdf';
	}

	// create text part of the message
	function buildTextPart() {
		return "--MIME_BOUNDRY\nContent-Type: text/html; charset=iso-8859-1\nContent-Transfer-Encoding: 7bit\n\n\n".$this->message."\n\n";
	}

	// create attachments part of the message
	function buildAttachmentPart() {
		if (count($this->attachments) > 0) {
			$attachmentPart='';
			foreach($this->attachments as $attachment) {
				$fileStr=file_get_contents($attachment);
				$fileStr=chunk_split(base64_encode($fileStr));
				$attachmentPart.="--MIME_BOUNDRY\nContent-Type: ".$this->getMimeType($attachment)."; name=".basename($attachment)."\nContent-disposition: attachment\nContent-Transfer-Encoding: base64\n\n".$fileStr."\n\n";
			}
			return $attachmentPart;
		}
	}

	// create message MIME headers
	function buildHeaders() {
		foreach($this->headers as $name=>$value) {
			$headers[]=$name.': '.$value;
		}
		return implode("\n",$headers)."\nThis is a multi-part message in MIME format.\n";
	}

	// add new MIME header
	function addHeader($name,$value) {
		$this->headers[$name]=$value;
	}

	// add new attachment
	function addAttachment($attachment) {
		if (!file_exists($attachment)) {
			display_error('Invalid attachment.');
		}
		$this->attachments[]=$attachment;
	}

	// get MIME Type of attachment
	function getMimeType($attachment) {
		$attachment=explode('.',basename($attachment));
		if (!$mimeType=array_search(strtolower($attachment[count($attachment)-1]),$this->mimeTypes)) {
			display_error('MIME Type not found.');
		}
		return $mimeType;
	}

	// send email
	function send() {
		$to=$this->recipient;
		$subject=$this->subject;
		$headers=$this->buildHeaders();
		$message=$this->buildTextPart().$this->buildAttachmentPart()."--MIME_BOUNDRY--\n";
		if (!mail($to,$subject,$message,$headers)) {
			display_error('Error sendind email.');
			return false;
		} else {
			return true;
		}
	}
}
?>
