<?php
/**
 * com_mailer_mail class.
 *
 * @package Pines
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
 * @package Pines
 * @subpackage com_mailer
 */
class com_mailer_mail extends p_base {
	/**
	 * The sender's email address.
	 *
	 * @var string
	 */
	var $sender;
	/**
	 * The recipient's email address.
	 *
	 * @var string
	 */
	var $recipient;
	/**
	 * The message subject.
	 *
	 * @var string
	 */
	var $subject;
	/**
	 * The message text's MIME type.
	 *
	 * @var string
	 */
	var $text_mime_type = 'text/html';
	/**
	 * An array of headers to include in the message.
	 *
	 * @var array
	 */
	var $headers = array();
	/**
	 * An array of known MIME types.
	 *
	 * The values are the mime types, and the keys are the file extensions.
	 *
	 * @var array
	 */
	var $mimeTypes = array();
	/**
	 * An array of attachments.
	 *
	 * @var array
	 */
	var $attachments = array();

	/**
	 * @param string $sender The sender's email address.
	 * @param string $recipient The recipient's email address.
	 * @param string $subject The message subject.
	 * @param string $message The message text.
	 */
	function __construct($sender, $recipient, $subject, $message) {
		global $pines;
		// validate incoming parameters
		if (!preg_match('/^.+@.+$/',$sender))
			display_error('Invalid value for email sender.');
		if (!preg_match('/^.+@.+$/',$recipient))
			display_error('Invalid value for email recipient.');
		if (!$subject||strlen($subject)>255)
			display_error('Invalid length for email subject.');
		if (!$message)
			display_error('Invalid value for email message.');
		$this->sender = $sender;
		$this->recipient = $recipient;
		$this->subject = $subject;
		$this->message = $message;
		// Define some default MIME headers
		$this->headers['MIME-Version'] = '1.0';
		$this->headers['Content-Type'] = 'multipart/mixed;boundary="MIME_BOUNDRY"';
		$this->headers['From'] = $this->sender;
		$this->headers['Return-Path'] = $this->sender;
		$this->headers['Reply-To'] = $this->sender;
		//$this->headers['X-Mailer'] = 'PHP5';
		$this->headers['X-Sender'] = $this->sender;
		$this->headers['X-Priority'] = '3';
		$this->headers['User-Agent'] = "{$pines->program_title} {$pines->program_version}";
		// Define some default MIME types
		$this->mimeTypes['doc'] = 'application/msword';
		$this->mimeTypes['pdf'] = 'application/pdf';
		$this->mimeTypes['gz'] = 'application/x-gzip';
		$this->mimeTypes['exe'] = 'application/x-msdos-program';
		$this->mimeTypes['rar'] = 'application/x-rar-compressed';
		$this->mimeTypes['swf'] = 'application/x-shockwave-flash';
		$this->mimeTypes['tgz'] = 'application/x-tar-gz';
		$this->mimeTypes['tar'] = 'application/x-tar';
		$this->mimeTypes['zip'] = 'application/zip';
		$this->mimeTypes['mid'] = 'audio/midi';
		$this->mimeTypes['mp3'] = 'audio/mpeg';
		$this->mimeTypes['au'] = 'audio/ulaw';
		$this->mimeTypes['aif'] = 'audio/x-aiff';
		$this->mimeTypes['aiff'] = 'audio/x-aiff';
		$this->mimeTypes['wma'] = 'audio/x-ms-wma';
		$this->mimeTypes['wav'] = 'audio/x-wav';
		$this->mimeTypes['gif'] = 'image/gif';
		$this->mimeTypes['jpg'] = 'image/jpeg';
		$this->mimeTypes['jpeg'] = 'image/jpeg';
		$this->mimeTypes['jpe'] = 'image/jpeg';
		$this->mimeTypes['png'] = 'image/png';
		$this->mimeTypes['tif'] = 'image/tiff';
		$this->mimeTypes['tiff'] = 'image/tiff';
		$this->mimeTypes['css'] = 'text/css';
		$this->mimeTypes['htm'] = 'text/html';
		$this->mimeTypes['html'] = 'text/html';
		$this->mimeTypes['txt'] = 'text/plain';
		$this->mimeTypes['rtf'] = 'text/rtf';
		$this->mimeTypes['xml'] = 'text/xml';
		$this->mimeTypes['flv'] = 'video/flv';
		$this->mimeTypes['mpe'] = 'video/mpeg';
		$this->mimeTypes['mpeg'] = 'video/mpeg';
		$this->mimeTypes['mpg'] = 'video/mpeg';
		$this->mimeTypes['mov'] = 'video/quicktime';
		$this->mimeTypes['asf'] = 'video/x-ms-asf';
		$this->mimeTypes['wmv'] = 'video/x-ms-wmv';
		$this->mimeTypes['avi'] = 'video/x-msvideo';
	}

	/**
	 * Create a new instance.
	 * 
	 * @param string $sender The sender's email address.
	 * @param string $recipient The recipient's email address.
	 * @param string $subject The message subject.
	 * @param string $message The message text.
	 */
	public static function factory($sender, $recipient, $subject, $message) {
		global $pines;
		$class = get_class();
		$instance = new $class($sender, $recipient, $subject, $message);
		$pines->hook->hook_object($instance, $class.'->', false);
		return $instance;
	}

	/**
	 * Create text part of the message.
	 *
	 * @return string The text.
	 */
	function buildTextPart() {
		return "--MIME_BOUNDRY\nContent-Type: {$this->text_mime_type}; charset=iso-8859-1\nContent-Transfer-Encoding: 7bit\n\n\n{$this->message}\n\n";
	}

	/**
	 * Create attachments part of the message.
	 *
	 * @return string The attachment section.
	 */
	function buildAttachmentPart() {
		if (count($this->attachments) > 0) {
			$attachmentPart = '';
			foreach($this->attachments as $attachment) {
				$fileStr = file_get_contents($attachment);
				$fileStr = chunk_split(base64_encode($fileStr));
				$attachmentPart .= "--MIME_BOUNDRY\nContent-Type: ".$this->getMimeType($attachment)."; name=".basename($attachment)."\nContent-disposition: attachment\nContent-Transfer-Encoding: base64\n\n{$fileStr}\n\n";
			}
			return $attachmentPart;
		}
	}

	/**
	 * Create message MIME headers.
	 *
	 * @return string The MIME headers.
	 */
	function buildHeaders() {
		foreach($this->headers as $name=>$value) {
			$headers[] = "{$name}: {$value}";
		}
		return implode("\n",$headers)."\nThis is a multi-part message in MIME format.\n";
	}

	/**
	 * Add new MIME header.
	 *
	 * @param string $name The header's name.
	 * @param string $value The header's value.
	 */
	function addHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	/**
	 * Add new attachment.
	 *
	 * @param string $attachment The attachment filename.
	 * @return bool True on success, false on failure.
	 */
	function addAttachment($attachment) {
		if (!file_exists($attachment)) {
			display_error('Invalid attachment.');
			return false;
		}
		$this->attachments[] = $attachment;
		return true;
	}

	/**
	 * Get MIME Type of attachment.
	 *
	 * @param string $attachment The attachment filename.
	 * @return mixed MIME type on success, null on failure.
	 */
	function getMimeType($attachment) {
		$attachment = explode('.',basename($attachment));
		if (!isset($this->mimeTypes[strtolower($attachment[count($attachment)-1])])) {
			display_error('MIME Type not found.');
			return null;
		}
		return $this->mimeTypes[strtolower($attachment[count($attachment)-1])];
	}

	/**
	 * Send email.
	 *
	 * @return bool True on success, false on failure.
	 */
	function send() {
		$to = $this->recipient;
		$subject = $this->subject;
		$headers = $this->buildHeaders();
		$message = $this->buildTextPart().$this->buildAttachmentPart()."--MIME_BOUNDRY--\n";
		if (!mail($to, $subject, $message, $headers, '-femail@example.com')) {
			display_error('Error sending email.');
			return false;
		}
		return true;
	}
}
?>