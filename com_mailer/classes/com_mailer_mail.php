<?php
/**
 * com_mailer_mail class.
 *
 * @package Components
 * @subpackage mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');

/**
 * Creates and sends emails.
 *
 * This class supports attachments and custom headers.
 *
 * Credit for this class goes to Alejandro Gervasio
 * http://www.devshed.com/cp/bio/Alejandro-Gervasio/
 *
 * @package Components
 * @subpackage mailer
 */
class com_mailer_mail {
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
	public function __construct($sender, $recipient, $subject, $message) {
		global $pines;
		// Validate incoming parameters.
		if (!preg_match('/^.+@.+$/',$sender))
			pines_error('Invalid value for email sender.');
		if (!preg_match('/^.+@.+$/',$recipient))
			pines_error('Invalid value for email recipient.');
		if (!$subject||strlen($subject)>255)
			pines_error('Invalid length for email subject.');
		if (!$message)
			pines_error('Invalid value for email message.');
		$this->sender = $sender;
		$this->recipient = $recipient;
		$this->subject = $subject;
		$this->message = $message;
		// Define some default MIME headers
		$this->headers['MIME-Version'] = '1.0';
		$this->headers['Content-Type'] = 'multipart/mixed;boundary="MIME_BOUNDRY"';
		//$this->headers['X-Mailer'] = 'PHP5';
		$this->headers['X-Priority'] = '3';
		$this->headers['User-Agent'] = "{$pines->info->name} {$pines->info->version}";
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
	 * @return com_mailer_mail The new instance.
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
	 * @access private
	 * @return string The text.
	 */
	private function buildTextPart() {
		return "--MIME_BOUNDRY\nContent-Type: {$this->text_mime_type}; charset=utf-8\nContent-Transfer-Encoding: 7bit\n\n\n{$this->message}\n\n";
	}

	/**
	 * Create attachments part of the message.
	 *
	 * @access private
	 * @return string The attachment section.
	 */
	private function buildAttachmentPart() {
		if (count($this->attachments) > 0) {
			$attachment_part = '';
			foreach ($this->attachments as $attachment) {
				$file_str = chunk_split(base64_encode(file_get_contents($attachment)));
				$attachment_part .= "--MIME_BOUNDRY\nContent-Type: ".$this->getMimeType($attachment)."; name=".basename($attachment)."\nContent-disposition: attachment\nContent-Transfer-Encoding: base64\n\n{$file_str}\n\n";
			}
			return $attachment_part;
		}
	}

	/**
	 * Create message headers.
	 *
	 * @access private
	 * @param array $required_headers Any headers that should append/replace the defined headers.
	 * @return string The headers.
	 */
	private function buildHeaders($required_headers = array()) {
		$build_headers = array_merge($this->headers, $required_headers);
		$headers = array();
		foreach ($build_headers as $name => $value) {
			$headers[] = "{$name}: {$value}";
		}
		return implode("\n", $headers)."\nThis is a multi-part message in MIME format.\n";
	}

	/**
	 * Add new header.
	 *
	 * @param string $name The header's name.
	 * @param string $value The header's value.
	 */
	public function addHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	/**
	 * Add new attachment.
	 *
	 * @param string $attachment The attachment filename.
	 * @return bool True on success, false on failure.
	 */
	public function addAttachment($attachment) {
		if (!file_exists($attachment)) {
			pines_error('Invalid attachment.');
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
	public function getMimeType($attachment) {
		$attachment = explode('.', basename($attachment));
		if (!isset($this->mimeTypes[strtolower($attachment[count($attachment) - 1])])) {
			pines_error('MIME Type not found.');
			return null;
		}
		return $this->mimeTypes[strtolower($attachment[count($attachment) - 1])];
	}

	/**
	 * Send email.
	 *
	 * @return bool True on success, false on failure.
	 */
	public function send() {
		global $pines;
		// First verify values.
		if (!preg_match('/^.+@.+$/', $this->sender))
			return false;
		if (!preg_match('/^.+@.+$/', $this->recipient))
			return false;
		if (!$this->subject || strlen($this->subject) > 255)
			return false;

		// Headers that must be in the sent message.
		$required_headers = array();

		// Are we in testing mode?
		if ($pines->config->com_mailer->testing_mode) {
			// If the testing email is empty, just return true.
			if (empty($pines->config->com_mailer->testing_email))
				return true;
			// The testing email isn't empty, so replace stuff now.
			// Save the original to, cc, and bcc in additional headers.
			$required_headers['X-Testing-Original-To'] = $this->recipient;
			foreach ($this->headers as $name => $value) {
				switch (strtolower($name)) {
					case 'cc':
						$this->headers['X-Testing-Original-Cc'] = $value;
						$required_headers[$name] = '';
						break;
					case 'bcc':
						$this->headers['X-Testing-Original-Bcc'] = $value;
						$required_headers[$name] = '';
						break;
				}
			}
			$to = $pines->config->com_mailer->testing_email;
			$subject = '*Test* '.$this->subject;
		} else {
			$to = $this->recipient;
			$subject = $this->subject;
		}
		// Add from headers.
		$required_headers['From'] = $this->sender;
		$required_headers['Return-Path'] = $this->sender;
		$required_headers['Reply-To'] = $this->sender;
		$required_headers['X-Sender'] = $this->sender;
		$headers = $this->buildHeaders($required_headers);
		$message = $this->buildTextPart().$this->buildAttachmentPart()."--MIME_BOUNDRY--\n";

		// Now send the mail.
		if (!mail($to, $subject, $message, $headers, $pines->config->com_mailer->additional_parameters)) {
			pines_error('Error sending email.');
			return false;
		}
		return true;
	}
}

?>