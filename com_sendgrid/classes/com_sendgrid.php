<?php
/**
 * com_sendgrid's class.
 *
 * @package Components\sendgrid
 * @license none
 * @author Mohammed Ahmed <mohammedsadikahmed@gmail.com>
 * @copyright Smart Industries, LLC
 * @link http://smart108.com/
 */

defined('P_RUN') or die('Direct access prohibited');

class com_sendgrid extends component {
    
    var $sendgrid_url = 'https://api.sendgrid.com/api/mail.send.json';
    var $newsletter_base = 'https://api.sendgrid.com/api/newsletter/';
    var $username;
    var $password;
    var $ch;
    
    public function __construct() {
        global $pines;
        $this->username = $pines->config->com_sendgrid->api_user;
        $this->password = $pines->config->com_sendgrid->api_password;
        $this->ch = curl_init();
    }
    
    
    /**
     * Makes a POST call to SendGrid
     * 
     * @param string $url The url of the SendGrid endpoint
     * @param array $post_fields The parameters for the call
     * @return Object The response from SendGrid
     */
    public function postToSendGrid($url, $post_fields) {
        if (is_array($post_fields)) {
            $post_fields['api_user'] = $this->username;
            $post_fields['api_key'] = $this->password;
        } else {
            $post_fields .= '&api_user=' . $this->username . '&api_key=' . $this->password;
        }
        
        curl_setopt_array($this->ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_fields
        ));
        
        return json_decode(curl_exec($this->ch));
    }
    
    
    /**
     * Creates a Marketing Email Category
     * 
     * @param string $category The name of the category
     * @return boolean Whether the category was successfully created
     */
    public function createMarketingCategory($category) {
        $url = $this->newsletter_base.'category/create.json';
        $post_fields = array('category' => $category);
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'success') {
            return true;
        } else {
            pines_log('There was an error trying to create a marketing category. Errors: ' . json_encode($resp->errors), 'error');
            return false;
        }
        
    }
    
    
    /**
     * Adds a category to a marketing email
     * 
     * @param string $category The name of the category
     * @param string $email_name The name of the marketing email to add the category
     * @return boolean Whether the category was successfully added
     */
    public function addCategoryToMarketingEmail($category, $email_name) {
        $url = $this->newsletter_base.'category/add.json';
        $post_fields = array(
            'category' => $category,
            'name'  => $email_name,
            );
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'success') {
            return true;
        } else {
            pines_log('There was an error trying to create a marketing category. Errors: ' . json_encode($resp->errors), 'error');
            return false;
        }
    }
    
    
    /**
     * Removes a category from a marketing email. Will delete all if category isn't specified
     * 
     * @param string $email_name The name of the marketing email
     * @param string $category Optional argument is the category name to delete. If omitted, this call deletes all categories for this email
     * @return boolean
     */
    public function removeCategoryFromMarketingEmail($email_name, $category = false) {
        $url = $this->newsletter_base.'category/remove.json';
        $post_fields = array('name'  => $email_name);
        if ($category) $post_fields['category'] = $category;
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'success') {
            return true;
        } else {
            pines_log('There was an error trying to create a marketing category. Errors: ' . json_encode($resp->errors), 'error');
            return false;
        }
    }
    
    
    /**
     * 
     * @param string $category An optional argument to check if the category exists, else it will return a list of all categories
     * @return mixed Returns boolean if $category was specified, else an array of categories 
     */
    public function listAllCategories($category = false) {
        $url = $this->newsletter_base.'category/list.json';
        $post_fields = ($category) ? array('category' => $category) : array();
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'errors' || $resp->error) {
            return array();
        } else {
            return $resp;
        }
    }
    
    
    /**
     * Adds Email(s) to a Recipient List
     * 
     * @param array $emails The email(s) to add to the list. Can include other options besides name and email.
     * Structure your emails as array('email' => $email, 'name' => $name, 'other' => $other), or an array of arrays
     * @param string $list The list to add the email(s) to
     * @return boolean Whether the email(s) was successfully added to the list
     */
    public function addEmailsToList($emails, $list) {
        $url = $this->newsletter_base.'lists/email/add.json';
        $post_fields;
        
        if (is_array($emails[0])) {
            $post_fields = 'list='.$list;
            foreach ($emails as $email) {
                $post_fields .= '&data[]=' . json_encode($email);
            }
            
        } else {
            $post_fields = array('list' => $list, 'data' => json_encode($emails));
        }
        
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->error) {
            pines_log('There was an error trying to add the email(s) to the list: ' . $list . ', The error was: ' . $resp->error, 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Get list of email addresses and associated fields for a specified list
     * 
     * @param string $list The list to get the emails from
     * @return array The list of email addresses in the specified list
     */
    public function getEmailsFromList($list) {
        $url = $this->newsletter_base.'lists/email/get.json';
        $post_fields = array('list' => $list);
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to add the email(s) to the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return array();
        } else {
            return $resp;
        }
    }
    
    
    /**
     * Check if an email exists in a list
     * 
     * @param string $list The list to check if an email exists
     * @param string $email The email to check it's existence
     * @return boolean
     */
    public function doesEmailExistInList($list, $email) {
        $url = $this->newsletter_base.'lists/email/get.json';
        $post_fields = array('list' => $list, 'email' => $email);
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to add the email(s) to the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Delete an email from a list
     * 
     * @param string $list The list containing the email address to remove
     * @param string $email The email address to remove
     * @return boolean Whether the email was successfully deleted
     */
    public function deleteEmailFromList($list, $email) {
        $url = $this->newsletter_base.'lists/email/delete.json';
        $post_fields = array('list' => $list, 'email' => $email);
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to remove the errors from the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Delete emails from a list
     * 
     * @param string $list The list from which to delete the email from
     * @param array $emails An array of emails to be deleted from the list
     * @return boolean Whether the emails were successfully deleted
     */
    public function deleteEmailsFromList($list, $emails) {
        $url = $this->newsletter_base.'lists/email/delete.json';
        
        $post_fields = 'list='.$list;
        foreach ($emails as $email) {
            $post_fields .= '&email='.$email;
        }
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to remove the errors from the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Create a Recipient List
     * 
     * @param string $list The name of the new list
     * @param string $name The column name for the "email" header
     * @return boolean Whether the recipient list was created or not
     */
    public function createRecipientList($list, $name = false) {
        $url = $this->newsletter_base.'lists/add.json';
        
        $post_fields = array('list' => $list);
        if ($name) $post_fields['name'] = $name;
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to create the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Create a Recipient List with additional columns
     * 
     * @param string $list The name of the new list
     * @param array $column_name An array of additional column names
     * @param string $name The column name for the "email" header
     * @return boolean Whether the recipient list was created or not
     */
    public function createRecipientListWithAdditionalColumns($list, $column_name, $name=false) {
        $url = $this->newsletter_base.'lists/add.json';
        
        $post_fields = 'list='.$list;
        if ($name) $post_fields .= '&name='.$name;
        
        foreach ($column_name as $col) {
            $post_fields .= '&columnname[]='.$col;
        }
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to create the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Rename a Recipient List
     * 
     * @param string $list The name of the list to be renamed
     * @param string $new_name The new name of the list
     * @return boolean Whether the list was successfully renamed
     */
    public function renameRecipientList($list, $new_name) {
        $url = $this->newsletter_base.'lists/edit.json';
        
        $post_fields = array('list' => $list, 'newlist' => $new_name);
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to rename the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Get all Recipient Lists from your account
     * 
     * @return array The array of lists
     */
    public function getAllRecipientLists() {
        $url = $this->newsletter_base.'lists/get.json';
        
        $resp = $this->postToSendGrid($url, array());
        
        if ($resp->message == 'error' || $resp->errors) {
            pines_log('There were errors trying to get all the lists, The errors were: ' . json_encode($resp->errors), 'error');
            return array();
        } else {
            return $resp;
        }
    }
    
    
    /**
     * Check if the list exists
     * 
     * @param string $list
     * @return boolean Whether the list exists or not
     */
    public function doesListExist($list) {
        $url = $this->newsletter_base.'lists/get.json';
        
        $resp = $this->postToSendGrid($url, array('list' => $list));
        
        if ($resp->message == 'error' || $resp->error) {
            pines_log('There were errors trying to get all the lists, The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Remove a Receipient List from your account
     * 
     * @param string $list The list to remove. Must be an existing list
     * @return boolean Whether the list was deleted
     */
    public function deleteList($list) {
        $url = $this->newsletter_base.'lists/delete.json';
        
        $resp = $this->postToSendGrid($url, array('list' => $list));
        
        if ($resp->message == 'error' || $resp->error) {
            pines_log('There were errors trying to delete the list: ' . $list . ', The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Create a new Marketing Email
     * 
     * @param string $identity The identity that will be used for the Marketing Email being created. (Sender Address Identity)
     * @param string $name The name for this marketing email.
     * @param string $subject The subject for this marketing email
     * @param string $text The text portion of the email
     * @param string $html The html portion of the email
     * @return boolean Whether the marketing email was created successfully
     */
    public function createMarketingEmail($identity, $name, $subject, $text, $html) {
        $url = $this->newsletter_base.'add.json';
        
        $post_fields = array(
            'identity' => $identity,
            'name'  => $name,
            'subject'   => $subject,
            'text'  => $text,
            'html'  => $html
        );
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->error) {
            pines_log('There were errors trying to create the marketing email with the identity: ' . $identity. '. The error was .' . json_encode($resp->error), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Edit an existing Marketing Email
     * 
     * @param type $name The name of the marketing email being updated
     * @param type $new_name The new name of the marketing email
     * @param type $identity The new identity of the marketing email
     * @param type $subject The new subject of the marketing email
     * @param type $text The new text portion of the marketing email
     * @param type $html The new html portion of the marketing email
     * @return boolean Whether the marketing email was successfully edited
     */
    public function editMarketingEmail($name, $new_name, $identity, $subject, $text, $html) {
        $url = $this->newsletter_base.'edit.json';
        
        $post_fields = array(
            'identity' => $identity,
            'name'  => $name,
            'newname'   => $new_name,
            'subject'   => $subject,
            'text'  => $text,
            'html'  => $html
        );
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->error) {
            pines_log('There were errors trying to edit the marketing email with the identity: ' . $identity. '. The error was .' . $resp->error, 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Get the contents of a marketing email
     * 
     * @param string $name The name of the marketing email to retrieve
     * @return mixed Returns false is there was an error else the contents of the email
     */
    public function getMarketingEmail($name) {
        $url = $this->newsletter_base.'get.json';
        
        $post_fields = array('name'  => $name);
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->error) {
            pines_log('There were errors trying to get the marketing email with the name: ' . $name. '. The error was .' . $resp->error, 'error');
            return false;
        } else {
            return $resp;
        }
    }
    
    
    /**
     * Retrieve a list of all existing marketing emails
     * 
     * @return mixed False if there was an error else an array of emaisl
     */
    public function listAllMarketingEmails() {
        $url = $this->newsletter_base.'list.json';
        
        $resp = $this->postToSendGrid($url, array());
        
        if ($resp->error) {
            pines_log('There were errors trying to list the marketing emails. Error was: ' . $resp->error, 'error');
            return false;
        } else {
            return $resp;
        }
    }
    
    
    /**
     * Delete an existing marketing email
     * 
     * @param string $name The name of the marketing email to delete
     * @return boolean Whether the email was successfully deleted
     */
    public function deleteMarketingEmail($name) {
        $url = $this->newsletter_base.'delete.json';
        
        $resp = $this->postToSendGrid($url, array('name' => $name));
        
        if ($resp->error) {
            pines_log('There were errors trying to delete the marketing email: ' .$name .'. Error was: ' . $resp->error, 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Add a Recipient List to a Marketing Email
     * 
     * @param string $list The name of the list to assign to the marketing email
     * @param string $name The name of the marketing email (Note, not the identity, but the name)
     * @return boolean
     */
    public function addListToMarketingEmail($list, $name) {
        $url = $this->newsletter_base.'recipients/add.json';
        
        $resp = $this->postToSendGrid($url, array('list' => $list, 'name' => $name));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to add the list: ' . $list . ' to the marketing email: ' . $name . '. The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Gets all lists assigned to a particular marketing email
     * 
     * @param string $name The name of the marketing email for which to retrieve lists
     * @return array An array of the associated lists
     */
    public function getListsOfMarketingEmail($name) {
        $url = $this->newsletter_base.'recipients/get.json';
        
        $resp = $this->postToSendGrid($url, array('name' => $name));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to get the lists associated with the marketing email: ' . $name, 'error');
            return array();
        } else {
            return $resp;
        }
    }
    
    
    /**
     * Delete a list from the marketing email
     * 
     * @param string $name The name of the marketing email to remove the list from
     * @param string $list The name of the list to remove
     * @return boolean Whether the list was successfully deleted from the marketing email
     */
    public function deleteListFromMarketingEmail($name, $list) {
        $url = $this->newsletter_base.'recipients/delete.json';
        
        $resp = $this->postToSendGrid($url, array('name' => $name, 'list' => $list));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to delete the list: ' . $list . ' from the marketing email: ' . $name . '. The errors were ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Add a schedule delivery time for an existing Marketing Email
     * 
     * @param string $name The name of the marketing email to add the schedule to
     * @param string $at An ISO 8601 formatted Date/Time String
     * Use $datetime->format('c') to get it in the proper format
     * @param int $after Number of minutes until delivery should occur
     * 
     * If you want to send the email now, don't include $at and $after
     * @return boolean Whether the schedule was successfully set
     */
    public function addScheduleToMarketingEmail($name, $at = false, $after = false) {
        $url = $this->newsletter_base.'schedule/add.json';
        $post_fields = array('name' => $name);
        
        if ($at) {
            $post_fields['at'] = $at;
        } else if ($after) {
            $post_fields['after'] = $after;
        }
        
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying add a schedule to the marketing email: ' . $name . '. The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Get the scheduled delivery time for an existing Marketing Email
     * 
     * @param string $name The name of the marketing email
     * @return mixed
     */
    public function getScheduledTimeForMarketingEmail($name) {
        $url = $this->newsletter_base.'schedule/get.json';
        $resp = $this->postToSendGrid($url, array('name' => $name));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying get the schedule for the marketing email: ' . $name . '. The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return $resp->date;
        }
    }
    
    
    /**
     * Cancel a scheduled delivery time for a Marketing Email
     * 
     * @param string $name The name of the marketing email to remove the delivery time
     * @return boolean Whether the scheduled delivery time was deleted from the marketing email
     */
    public function deleteScheduledTimeForMarketingEmail($name) {
        $url = $this->newsletter_base.'schedule/delete.json';
        $resp = $this->postToSendGrid($url, array('name' => $name));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying get the schedule for the marketing email: ' . $name . '. The errors were: ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Create a new Sender Address
     * 
     * Emails need to be RFC 5322 Compliant
     * 
     * @param string $identity Create an address named this
     * @param string $name The name to be used for this Address
     * @param string $email The email address to be used for this Address
     * @param string $address The physical address to be used for this Address
     * @param string $city The city for this Address
     * @param string $state The state for this Address
     * @param string $zip The zip for this Address
     * @param string $country The country for this Address
     * @param string $reply_to The reply-to field (If not included, will default to email)
     * @return boolean Whether the Address was successfully created or not
     */
    public function createSenderAddress($identity, $name, $email, $address, $city, $state, $zip, $country, $reply_to = false) {
        $url = $this->newsletter_base.'identity/add.json';
        $post_fields = array(
            'identity'  => $identity,
            'name'      => $name,
            'email'     => $email,
            'address'   => $address,
            'city'      => $city,
            'state'     => $state,
            'zip'       => $zip,
            'country'   => $country
        );
        if ($reply_to) $post_fields['replyto'] = $reply_to;
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to create a new Sender Address for identity: ' . $identity . '. The errors were ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Edit an existing Address
     * 
     * @param string $identity The Address you wish to edit (Required)
     * @param string $email The email address to be used for this Address (Required)
     * @param string $new_identity The new identity to be used for this Address
     * @param string $name The new name to be used for this Address
     * @param string $address The new physical address for this Address
     * @param string $city The new city for this Address
     * @param string $state The new state for this Address
     * @param string $zip The new zip code for this Address
     * @param string $country The new country for this Address
     * @param string $reply_to The new reply to for this Address
     * @return boolean Whether the Address was successfully updated with the new information
     */
    public function editSenderAddress($identity = false, $email = false, $new_identity = false, $name = false, $address = false, $city = false, $state = false, $zip = false, $country = false, $reply_to = false) {
        if (!$identity || !$email) return false;
        
        $url = $this->newsletter_base.'identity/edit.json';
        $post_fields = array(
            'identity'  => $identity,
            'email'     => $email
        );
        if ($new_identity) $post_fields['newidentity'] = $new_identity;
        if ($name) $post_fields['name'] = $name;
        if ($address) $post_fields['address'] = $address;
        if ($city) $post_fields['city'] = $city;
        if ($state) $post_fields['state'] = $state;
        if ($zip) $post_fields['zip'] = $zip;
        if ($country) $post_fields['country'] = $country;
        if ($reply_to) $post_fields['replyto'] = $reply_to;
        $resp = $this->postToSendGrid($url, $post_fields);
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to edit the Sender Address for identity: ' . $identity . '. The errors were ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Retrieves info about a Sender Address
     * 
     * @param string $identity The identity of the Address to get back info for
     * @return mixed False if there were errors else the Address Object
     */
    public function getSenderAddressInfo($identity = false) {
        if (!$identity) return false;
        
        $url = $this->newsletter_base.'identity/get.json';
        $resp = $this->postToSendGrid($url, array('identity' => $identity));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to get the Sender Address for identity: ' . $identity . '. The errors were ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return $resp;
        }
    }
    
    
    /**
     * List all Addresses
     * 
     * @return mixed False if errors else a list of all Address Identites
     */
    public function listAllSenderAddresses() {
        $url = $this->newsletter_base.'identity/list.json';
        $resp = $this->postToSendGrid($url, array());
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to get all the Sender Addresses. The errors were ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return $resp;
        }
    }
    
    
    /**
     * Check if an Address exists for a given identity
     * 
     * @param string $identity The identity of the Address to check
     * @return boolean Whether the Address exists
     */
    public function checkIfSenderExists($identity) {
        $url = $this->newsletter_base.'identity/list.json';
        $resp = $this->postToSendGrid($url, array('identity' => $identity));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to check if the Sender Address with identity: ' . $identity . '. The errors were ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
    /**
     * Delete an Address
     * 
     * @param string $identity The identity of the Address to remove
     * @return boolean Whether the Address was successfully removed
     */
    public function deleteSenderAddress($identity) {
        $url = $this->newsletter_base.'identity/delete.json';
        $resp = $this->postToSendGrid($url, array('identity' => $identity));
        
        if ($resp->error || $resp->errors) {
            pines_log('There were errors trying to delete the Sender Address with identity: ' . $identity . '. The errors were ' . json_encode($resp->errors), 'error');
            return false;
        } else {
            return true;
        }
    }
    
    
}


?>