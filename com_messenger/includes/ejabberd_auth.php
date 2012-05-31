#!/usr/bin/php
<?php
/*
 * I tinkered with this file and made it much more gooder by giving it that same
 * Pines power I give all my code. (Plus some Mrs. Dash.)
 *   -Hunter
 * 
 * - Instructions -
 * To use this script for ejabberd authentication, thus giving Pines users
 * access to the service, edit the ejabberd.cfg file.
 * In the authentication section, set the method to external authentication
 * and the program to this script, like so:
 *  {auth_method, external}.
 *  {extauth_program, "/path/to/pines/components/com_messenger/includes/ejabberd_auth.php"}.
 *  {extauth_cache, false}.
 * 
 * Note that the 3rd line turns caching off. This is necessary if you use the
 * web messaging clients in Pines.
 * 
 * - Permissions - (Read if you have errors.)
 * I'm assuming this file is owned by your web server user (probably www-data).
 * It needs to be executable by ejabberd. Setting the executable flag is usually
 * sufficient. If you experience errors, you can try using the setuid bit:
 * 1. Make this file executable by its user only, and have setuid bit.
 *     $ chmod 4744 ejabberd_auth.php
 * 2. If you choose, you can set this file's group to ejabberd, which is safest.
 *     $ chgrp ejabberd ejabberd_auth.php
 * 3. If this file's group is ejabberd, set it executable by group, else you can
 *    set it executable by others, but that's less safe.
 *     $ chmod g+x ejabberd_auth.php
 * 
 * - Testing -
 * Does user "admin" exist:
 *  $ php -r "echo pack('n', strlen(\$_SERVER['argv'][1])).\$_SERVER['argv'][1];" "isuser:admin" | ./ejabberd_auth.php | od -c
 * Does user "admin" have the password "password":
 *  $ php -r "echo pack('n', strlen(\$_SERVER['argv'][1])).\$_SERVER['argv'][1];" "auth:admin:server:password" | ./ejabberd_auth.php | od -c
 */


/*
Copyright (c) <2005> LISSY Alexandre, "lissyx" <alexandrelissy@free.fr>

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software andassociated documentation files (the "Software"), to deal in the
Software without restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
Software, and to permit persons to whom the Software is furnished to do so,
subject to thefollowing conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

error_reporting(0);

// First of all, check that we're running in CLI.
if (isset($_SERVER['REQUEST_METHOD']))
	die('You can\'t request this file.');

// Before we get into Jabber commands, we need to load up Pines.
// First switch to the Pines dir.
if (preg_match('/components\/com_messenger\/includes\/ejabberd_auth\.php$/', $_SERVER['PHP_SELF']))
	chdir(preg_replace('/components\/com_messenger\/includes\/ejabberd_auth\.php$/', '', $_SERVER['PHP_SELF']));
else
	chdir('../../../');
// Check that we're in the Pines dir...
if (!file_exists('system/classes/pines.php'))
	die('I have no idea where the Pines dir is, so I\'m cowardly refusing to proceed.');
// Set up constants...
define('P_EXEC_TIME', microtime(true));
define('P_RUN', true);
define('P_BASE_PATH', getcwd().'/');
define('P_INDEX', 'index.php');
define('P_SCRIPT_TIMING', false);
// Run the system init scripts.
foreach (glob('system/init/i*.php') as $_p_cur_sysinit) {
	// But stop once the system is all ready.
	if ($_p_cur_sysinit == 'system/init/i60action.php')
		break;
	require($_p_cur_sysinit);
}
// Now Pines is ready to go, so we can authenticate.
$auth = new JabberAuth();
$auth->play(); // We simply start process !

class JabberAuth {
	var $debug = false;		   /* Debug mode */
	var $debugfile = "/var/log/pipe-debug.log";  /* Debug output */
	var $logging = false;		   /* Do we log requests ? */
	var $logfile = "/var/log/pipe-log.log";   /* Log file ... */
	/*
	 * For both debug and logging, ejabberd have to be able to write.
	 */
	var $jabber_user;   /* This is the jabber user passed to the script. filled by $this->command() */
	var $jabber_pass;   /* This is the jabber user password passed to the script. filled by $this->command() */
	var $jabber_server; /* This is the jabber server passed to the script. filled by $this->command(). Useful for VirtualHosts */
	var $jid;		   /* Simply the JID, if you need it, you have to fill. */
	var $data;		  /* This is what SM component send to us. */
	var $dateformat = "M d H:i:s"; /* Check date() for string format. */
	var $command; /* This is the command sent ... */
	var $stdin;   /* stdin file pointer */
	var $stdout;  /* stdout file pointer */

	function JabberAuth() {
		@define_syslog_variables();
		@openlog("pipe-auth", LOG_NDELAY, LOG_SYSLOG);

		if ($this->debug) {
			@error_reporting(E_ALL);
			@ini_set("log_errors", "1");
			@ini_set("error_log", $this->debugfile);
		}
		$this->logg("Starting pipe-auth ..."); // We notice that it's starting ...
		$this->openstd();
	}

	function stop() {
		$this->logg("Shutting down ..."); // Sorry, have to go ...
		closelog();
		$this->closestd(); // Simply close files
		exit(0); // and exit cleanly
	}

	function openstd() {
		$this->stdout = @fopen("php://stdout", "w"); // We open STDOUT so we can read
		$this->stdin = @fopen("php://stdin", "r"); // and STDIN so we can talk !
	}

	function readstdin() {
		$l = @fgets($this->stdin, 3); // We take the length of string
		$length = @unpack("n", $l); // ejabberd give us something to play with ...
		$len = $length["1"]; // and we now know how long to read.
		if ($len > 0) { // if not, we'll fill logfile ... and disk full is just funny once
			$this->logg("Reading $len bytes ... "); // We notice ...
			$data = @fgets($this->stdin, $len + 1);
			// $data = iconv("UTF-8", "ISO-8859-15", $data); // To be tested, not sure if still needed.
			$this->data = $data; // We set what we got.
			$this->logg("IN: " . $data);
		}
	}

	function closestd() {
		@fclose($this->stdin); // We close everything ...
		@fclose($this->stdout);
	}

	function out($message) {
		@fwrite($this->stdout, $message); // We reply ...
		$dump = @unpack("nn", $message);
		$dump = $dump["n"];
		$this->logg("OUT: " . $dump);
	}

	function play() {
		do {
			$this->readstdin(); // get data
			$length = strlen($this->data); // compute data length
			if ($length > 0) { // for debug mainly ...
				$this->logg("GO: " . $this->data);
				$this->logg("data length is : " . $length);
			}
			$ret = $this->command(); // play with data !
			$this->logg("RE: " . $ret); // this is what WE send.
			$this->out($ret); // send what we reply.
			$this->data = NULL; // more clean. ...
		} while (true);
	}

	function command() {
		$data = $this->splitcomm(); // This is an array, where each node is part of what SM sent to us :
		// 0 => the command,
		// and the others are arguments .. e.g. : user, server, password ...

		if (strlen($data[0]) > 0) {
			$this->logg("Command was : " . $data[0]);
		}
		switch ($data[0]) {
			case "isuser": // this is the "isuser" command, used to check for user existance
				$this->jabber_user = $data[1];
				$parms = $data[1];  // only for logging purpose
				$return = $this->checkuser();
				break;
			case "auth": // check login, password
				$this->jabber_user = $data[1];
				$this->jabber_pass = $data[3];
				$parms = $data[1] . ":" . $data[2] . ":" . md5($data[3]); // only for logging purpose
				$return = $this->checkpass();
				break;
			case "setpass":
				$return = false; // We do not want jabber to be able to change password
				break;
			default:
				$this->stop(); // if it's not something known, we have to leave.
				// never had a problem with this using ejabberd, but might lead to problem ?
				break;
		}

		$return = ($return) ? 1 : 0;

		if (strlen($data[0]) > 0 && strlen($parms) > 0) {
			$this->logg("Command : " . $data[0] . ":" . $parms . " ==> " . $return . " ");
		}
		return @pack("nn", 2, $return);
	}

	function checkpass() {
		/*
		 * Put here your code to check password
		 * $this->jabber_user
		 * $this->jabber_pass
		 * $this->jabber_server
		 */

		// Use this simple script to run a listening notifier and display it
		// when EjabberD authenticates using this script.
		// -- BEGIN SCRIPT --
		//	#! /bin/bash
		//	while true; do
		//		data="$(nc -l 1337)"
		//		subject="$(echo $data | sed s/\|.*//)"
		//		body="$(echo $data | sed s/.*\|//)"
		//		notify-send -u critical -i dialog-information "$subject" "$body"
		//	done
		// -- END SCRIPT --

		// Uncomment the following line to start using the notifier above.
		//`echo "EjabberD Auth|User: {$this->jabber_user} --- Passord: {$this->jabber_pass}" | nc localhost 1337`;

		global $pines;
		if ($pines->config->com_messenger->guest_access && strpos($this->jabber_user, 'guest_') === 0)
			return (md5($this->jabber_user.format_date(time(), 'date_short', '', 'UTC').$pines->config->com_messenger->guest_key) === $this->jabber_pass);

		$user = user::factory($this->jabber_user);
		if (!isset($user->guid) || !$user->has_tag('enabled'))
			return false;
		if (strpos($this->jabber_pass, 'xmpp_secret_') === 0) {
			$result = ($user->xmpp_secret === $this->jabber_pass && time() < $user->xmpp_secret_expire);
			unset($user->xmpp_secret);
			unset($user->xmpp_secret_expire);
			$user->save();
			return $result;
		} else
			return $user->check_password($this->jabber_pass);
	}

	function checkuser() {
		/*
		 * Put here your code to check user
		 * $this->jabber_user
		 * $this->jabber_pass
		 * $this->jabber_server
		 */
		global $pines;
		if ($pines->config->com_messenger->guest_access && strpos($this->jabber_user, 'guest_') === 0)
			return true;
		$user = user::factory($this->jabber_user);
		return (isset($user->guid) && $user->has_tag('enabled'));
	}

	function splitcomm() { // simply split command and arugments into an array.
		return explode(":", $this->data);
	}

	function logg($message) { // pretty simple, using syslog.
	// some says it doesn't work ? perhaps, but AFAIR, it was working.
		if ($this->logging) {
			@syslog(LOG_INFO, $message);
		}
	}

}
?>