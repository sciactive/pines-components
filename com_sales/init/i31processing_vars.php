<?php
/**
 * Determine whether to integrate with com_customer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

/**
 * List of payment processing types.
 *
 * Payment processing types allow another component to handle the processing
 * of payments, such as credit card or gift card payments.
 *
 * To add a processing type, your code must add a new array with the
 * following values:
 *
 * - "name" - The name of your type. Ex: 'com_giftcard/giftcard'
 * - "cname" - The canonical name of your action. Ex: 'Gift Card'
 * - "description" - A description of the action. Ex: 'Deduct the payment from a gift card.'
 * - "callback" - Callback to your function. Ex: array($pines->com_giftcard, 'process_giftcard')
 *
 * The callback will be passed an array which may contain the following
 * associative entries:
 *
 * - "action" - The processing which is being requested.
 * - "name" - The name of the type being called.
 * - "payment" - The sale's payment entry. This holds information about the payment.
 * - "sale" - The sale entity.
 *
 * "action" will be one of:
 *
 * - "request" - The payment type has been selected.
 * - "approve" - The sale is being invoiced, and the payment needs to be approved.
 * - "tender" - The sale is being processed, and the payment needs to be processed.
 * - "change" - The sale requires change to be given, and this payment type has been selected to give change.
 * - "return" - The payment is being returned and the funds need to be returned.
 *
 * If "action" is "request", the callback can provide a form to collect
 * information from the user by calling $pines->page->override_doc() with
 * the HTML of the form. It is recommended to use a module to provide the
 * form's HTML. Use $module->render() to get the HTML from the module. If
 * you do not need any information from the user, simply don't do anything.
 * The form's inputs will be parsed into an array and saved as "data" in the
 * payment entry.
 *
 * If "action" is "approve", the callback needs to set the "status" entry on
 * the payment array to "approved", "declined", "info_requested", or
 * "manager_approval_needed".
 *
 * If "action" is "tender", the callback can then also set the "status" to
 * "tendered".
 *
 * If "action" is "change", the callback needs to set the "change_given"
 * variable on the sale object to true or false.
 *
 * @var array $pines->config->com_sales->processing_types
 */
$pines->config->com_sales->processing_types = array();

/**
 * List of product actions.
 *
 * Product actions are callbacks that can be called when a product is
 * received, adjusted, sold, or returned.
 *
 * To add a product action, your code must add a new array with the
 * following values:
 *
 * - "type" - An array or string of the event(s) the action should be called for. Out of "received", "adjusted", "sold", and "returned".
 * - "name" - The name of your action. Ex: 'com_gamephear/create_gamephear_account'
 * - "cname" - The canonical name of your action. Ex: 'Create GamePhear Account'
 * - "description" - A description of the action. Ex: 'Creates a GamePhear account for the customer.'
 * - "callback" - Callback to your function. Ex: array($pines->com_gamephear, 'create_account')
 *
 * The callback will be passed an array which may contain the following
 * associative entries:
 *
 * - "type" - The type of event that has occurred.
 * - "name" - The name of the action being called.
 * - "product" - The product entity.
 * - "stock_entry" - The stock entry entity.
 * - "sale" - The sale entity.
 * - "po" - The PO entity.
 * - "transfer" - The transfer entity.
 *
 * @var array $pines->config->com_sales->product_actions
 */
$pines->config->com_sales->product_actions = array();

?>