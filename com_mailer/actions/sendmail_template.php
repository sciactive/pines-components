<?php
/**
 * The action to send the template emails.
 *
 * @package Components\mailer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');


$pines->page->override = true;

if ( !gatekeeper('com_mailer/sendtemplateemail') )
	$result = false;
if ($result !== false) { 
	// Request Fields
	$grid_class = $_REQUEST['entity_class'];
	$guids = json_decode($_REQUEST['guids']);
	$custom_message = $_REQUEST['custom_message'];
	
	// Make sure the user has permission to specify their own email prefix.
	$email_suffix = '@'.$pines->config->com_mailer->email_templates_domain;
	$email_prefix = $pines->config->com_mailer->email_templates_prefix_group ? preg_replace('#@.*$#', '', $_SESSION['user']->group->email) : (!empty($pines->config->com_mailer->email_templates_prefix_default) ? $pines->config->com_mailer->email_templates_prefix_default : preg_replace('#@.*$#', '', $_SESSION['user']->email));
	$edit_email = (gatekeeper('com_mailer/editsendtemplateemail')) ? true : false;
	$sender_default = $email_prefix.$email_suffix;
	
	$sender =  ($edit_email) ? $_REQUEST['sender'] : $sender_default;
	$template = (int) $_REQUEST['email_template'];
	
	// Get Template
	$email_templates = $pines->com_mailer->get_email_templates();
	$cur_template = $email_templates[$template];
	
	
	// Figure out function to call..
	$function_type = $cur_template['function_type'];
	$function_entity = $cur_template['function_entity'];
	$function_call = $cur_template['function_call'];
	
	// Global Variables
	$skipped = 0;
	$failed = 0;
	$sent = 0;
	$total_count = count($guids);
	$user = $_SESSION['user'];
	
	// Begin the Email Loop
	foreach ($guids as $cur_guid) {
		$grid_entity = call_user_func_array(array($grid_class, 'factory'), array((int) $cur_guid));
		if (!isset($grid_entity->guid)) {
			$skipped++;
			continue;
		}
		
		// Entity Data
		$data = $pines->com_mailer->get_email_template_data($cur_template, $grid_entity, $grid_class);
		
		// Add to data
		$data['sender'] = $sender;
		$data['log_false'] = true;
		if (!empty($custom_message))
			$data['custom_message'] = $custom_message;
		
		// The function will make sure that we have enough data...
		switch ($function_type) {
			case 'component':
				if (!method_exists($function_entity, $function_call)) {
					$failed++;
					continue;
				}
				$email_result = $pines->$function_entity->$function_call($data);
				break;
			case 'entity':
				if (!isset($data[$function_entity])) {
					$skipped++;
					continue;
				}
				if (!method_exists($data[$function_entity], $function_call)) {
					$failed++;
					continue;
				}
				$email_result = $data[$function_entity]->$function_call($data);
				break;
		}
		if ($email_result === 'skipped') {
			$skipped++;
			continue;
		} else if (!$email_result) {
			$failed++;
			continue;
		} else {
			$sent++;
			continue;
		}
	}
	
	// Determine Results
	$result = (object) array(
		'sent' => $sent,
		'skipped' => $skipped,
		'failed' => $failed,
	);
	
	// Log Results
	$info = ($sent == $total_count) ? 'All Emails Sent Successfully!' :  (($failed == $total_count) ? 'All emails failed to send!' : "Sent: $sent| Skipped: $skipped| Failed: $failed.");
	pines_log("The user {$user->name} manually sent $total_count email(s) using the {$cur_template['name']} template. $info", 'notice');
}

$pines->page->override_doc(json_encode($result));
?>