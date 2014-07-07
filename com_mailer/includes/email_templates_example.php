<?php

/*
 * This is an example of an email templates file
 * 
 * This example contains a fake component called "com_app", which
 * relates to the idea of using a closed source component with the
 * email templates.
 * 
 * The idea is that the button is appended to all grids you specify.
 * Each grid uses one major entity class for it's guid/titles. This
 * represents our STARTING entity. If the emails (the functions you write
 * in cohesion with the rest of pines' mailing, using macros, mails.php etc)
 * necessitate other pieces of information (other entities) to run the email, 
 * we need to use THIS starting entity to get the others. ie. We have a loan, 
 * but need the application associated with it, or the customer, etc.
 * 
 * These rules really have to deal with answer the following:
 * 1. Where is my email function? Entity or component?
 * 2. What is the name of the function to call?
 * 3. Are there are any situations (starting entities) I cannot use this email from a certain grid
 *	ie. You cannot send specific sale emails from the customer grid
 *		because the relationship is one to many (we don't have a way to tell
 *		what specific sale you need is from a starting entity of com_customer_customer.
 * 4. What is my starting entity
 * 5. What do I do if my starting entity is X and I need X (easy)
 * 6. What do I do if my starting entity is X and I need Y, where the referenced entity relationship is $x->y
 * 6. What do I do if my starting entity is X and I need Y, where the referenced entity relationship is where $y->x
 * 7. What do I do if my starting entity is X and I need Y to get to Z?
 * 
 * 
 * Important information regarding email functions you write to fulfill these templates:
 * a. All your functions can double as your automated mail functions AND your
 *		email template mail functions IF you write them to accept a data array
 *		only (no other arguments and an empty array by default). 
 *		However, you can use the data array outside this system to give yourself
 *		any other information you'd like for your email function 
 *		(like those other arguments you wanted).
 * b. With the templates system, we create a data array, which is associative.
 *		containing things like $data['application'], which is an entity.
 *		The data array also contains the sender and log_false so that if
 *		the email form is used to send 500 emails, they don't kill the pines
 *		log. One log line will be used to indicate how many failed, skipped, or
 *		sent. Only entity names as you name them, log_false, sender, and 
 *		custom_message are provided in the data array by the template system.
 * 
 *		Because of custom_message (when a user sends an email to ONE person),
 *		make sure your email definition contain a macro custom_message. Even if 
 *		don't actually put the macro in your mail file to avoid users using them,
 *		please define the macro.
 * 
 *		The email functions should work with the data array being empty for your
 *		other use-cases. If you need entities you could check like this:
 * 
 *		if (!empty($data['customer']) {
 *			// Get the customer entity yourself.
 *			// Or in your call to this email function, send your own $data['customer']!
 *		}
 * c. All email functions should also return true or false or 'skipped'. Skipped
 *		is very important because not all users/situations in a huge batch will
 *		make sense to get each email type. ie. You select from a grid of people
 *		with sales a reminder to pay off their loan. They may not all have a 
 *		loan hooked to their sale. Some sales may have been in cash. Etc.
 *		Proper validation is key! So check for relevance and skip if it is not
 *		applicable to a certain user.
 * d. Try to do most of the unique logic in your email function. If you need
 *		a certain customer's document to email it to them  - a contract or something -
 *		try to obtain the document within the email function instead of being sad
 *		that the email template's form is limited in what data it can compile for you.
 *		With entities, you should be able to do almost everything!
 * 
 * 
 * The structure is an array of email definitions:
 *		In this example, we will sometimes relate to wanting a application entity. There is only one application per customer.
 *		You always need at least one entity - the entity on which you will call the function or get data from (to know who to send emails to etc).
 *		You may have situations where you need to get MULTIPLE entity types.
 * 
 * 'name' => The Display Name shows up in the list of emails on the form.
 * 'description' => This description will show up on the form to be informative.
 * 'function_type' => The function to call to make the email is on an entity or a component. entity|component
 * 'function_call' => The name of the function to call on the entity/component.
 * 'blocked_classes' => comma separated list of classes to block for a particular email. See 3 above for example of why.
 * 
 * Entity Rule: We want the entity we started with.
 * ie. com_app_application, we need this and we started with it, so it's easy.. we need itself.
 * 'com_entity_entitytype' => This is the rule used to determine the data needed for the email IF we came from a grid with THIS starting entity.
 *		'name' => 'entitytype' (it is the name of the entity. ie. user, application, loan, sale, product etc - In this case it's the same entity we started with.
 *		'class' => 'com_entity_entitytype' (this won't really be used here, but we need it in other situations).
 *		'use_ref' => 'self' (this means we can stop getting data, because the data we needed was entitytype... aka use itself. done.
 * 
 * Entity Rule: We need entitytype but we have com_diff_difftype. Luckily it relates to the the entitytype as a referenced entity!
 * ie. Finding the customer on an sale. $application->customer, where com_app_application is com_entity_entitytype and com_customer_customer is com_diff_difftype.
 * 'com_diff_difftype' => This rule will be used if we came from a grid with this class entity. We still need to wind up with the same data from the previous rule.
 *		'name' => 'entitytype', Notice that this is not the diff one, this is the one we NEED - but we didnt start with it. We have to GET to it.
 *		'class' => 'com_entity_entitytype', Notice this is the one we NEED.
 *		'use_ref' => 'this', This tells the system that I should use my STARTING difftype entity AS a reference to get my entitytype. It takes care of making call with entity manager.
 *		'use_entity' => 'difftype', the entity manager needed to know the variable name for the referenced entity. 
 * 
 * Entity Rule: The Starting Entity is not a ref, it HAS the entitytype on itself as the referenced entity. It's an object on our starting entity.
 * ie Perhaps you have a com_app_contract that relates to a application like this $contract->application
 * 'com_another_anothertype' => This rule will be used to get the com_entity_entitytype.
 *		'name' => 'entitytype',
 *		'class' => 'com_entity_entitytype',
 *		'variable' => 'entitytypenameonobject', this is the variable name to use to find it. The 'sale' in $contract->application example.
 * 
 * Entity Rule: The craziest rule! It uses other rules we just wrote to complete the mission! 
 *		In this situation, we need a join entity. Let's say we have a sale (com_sales_sale) and we can get the customer (com_customer_customer) to find our application (com_app_application).
 *		Once we GET the join entity (com_customer_customer), instead of rewriting more rules, we can use the one we wrote for it already (see com_diff_difftype)
 * 'com_needjoin_jointype' => This rule will only tell us how to get the join. And then tell us what rule to borrow next.
 *		'name' => 'difftype', Notice it's not entity type because we cannot go directly there, we need the join.
 *		'class' => 'com_diff_difftype', If you are bothering to use this join, it means you probably already have a rule for it above.
 *		'variable' => 'difftypenameonobject', This is similar to our previous situation because difftype is a variable on jointype. ie. $sale->customer.
 *		'get_remaining_data' => true, This just tells the system to grab the class listed here and find the rule that matches and do what it says.
 * 
 * NOTE:	If you need multiple entities within this block (relating to the sale), put the one that has get_remaining_data last.
 *			If you need multiple entities, but they relate to the customer one, then it'll fully execute the customer rule.
 * 
 * In closing,
 *	Yes, I could have made a manager to help create this file, but since the email templates are so developer driven
 *	in that they require the email functions to be written already, and a certain way to receive the data array,
 *  AND, understanding of entities and their classes is also required, it made little sense to pour some intense time and effort
 *  to create an effective manager for this. 
 * 
 *	I did not want to leave you high and dry though, thus this documentation and example file.
 * 
 *	When you are done creating such a file, put it in your closed source section and alter your com_mailer config to point to that file!
 */


return array(
	0 => array(
		'name' => 'Approval Reminder',
		'description' => 'Remind the customer of their approval or to submit their paystub.',
		'function_type' => 'entity', // component|entity
		'function_entity' => 'application', // make it com_app if a component instead. 
		'function_call' => 'email_approval',
		'blocked_classes' => array(),
		'com_app_application' => array(
			array(
				'name' => 'application',
				'class' => 'com_app_application',
				'use_ref' => 'self',
			),
		),
		'com_customer_customer' => array(
			array(
				'name' => 'application',
				'class' => 'com_app_application',
				'use_ref' => 'this',
				'use_entity' => 'customer',
			),
		),
		'com_app_contract' => array(
			array(
				'name' => 'application',
				'class' => 'com_app_application',
				'variable' => 'application',
			),
		),
		'com_loan_loan' => array(
			array(
				'name' => 'customer',
				'class' => 'com_customer_customer',
				'variable' => 'customer',
				'get_remaining_data' => true, // Uses the com_customer_customer to complete the entities needed.
			),
		),
		'com_sales_sale' => array(
			array(
				'name' => 'customer',
				'class' => 'com_customer_customer',
				'variable' => 'customer',
				'get_remaining_data' => true, // Uses the com_customer_customer to complete the entities needed.
			),
		),
	),
	1 => array(
		'name' => 'Finance Company Info',
		'description' => 'Provide the customer with their finance company contact information.',
		'function_type' => 'entity', // component|entity
		'function_entity' => 'contract', // make it com_app if a component instead. 
		'function_call' => 'email_finco_info',
		'blocked_classes' => array('com_customer_customer', 'com_app_application'),
		'com_app_contract' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'self',
			),
		),
		'com_loan_loan' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'this',
				'use_entity' => 'loan',
			),
		),
		'com_sales_sale' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'this',
				'use_entity' => 'sale',
			),
		),
	),
	2 => array(
		'name' => 'Send Contract',
		'description' => 'Provide the customer with the PDF of their contract.',
		'function_type' => 'entity', // component|entity
		'function_entity' => 'contract', // make it com_app if a component instead. 
		'function_call' => 'email_contract',
		'blocked_classes' => array('com_customer_customer', 'com_app_application'),
		'com_app_contract' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'self',
			),
		),
		'com_loan_loan' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'this',
				'use_entity' => 'loan',
			),
		),
		'com_sales_sale' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'this',
				'use_entity' => 'sale',
			),
		),
	),
	3 => array(
		'name' => 'Loan Delinquency',
		'description' => 'Inform the customer of their payment amount and their past due balance.',
		'function_type' => 'entity', // component|entity
		'function_entity' => 'contract', // make it com_app if a component instead. 
		'function_call' => 'email_loan_delinquency',
		'blocked_classes' => array('com_customer_customer', 'com_app_application'),
		'com_app_contract' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'self',
			),
		),
		'com_loan_loan' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'this',
				'use_entity' => 'loan',
			),
		),
		'com_sales_sale' => array(
			array(
				'name' => 'contract',
				'class' => 'com_app_contract',
				'use_ref' => 'this',
				'use_entity' => 'sale',
			),
		),
	),
);
?>
