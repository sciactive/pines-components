<?php
defined('P_RUN') or die('Direct access prohibited');
return array (
  'packages' => 
  array (
    'com_about' => 
    array (
      'name' => 'About',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Configurable about dialog',
      'description' => 'Displays configurable information about Pines and your installation.',
      'depend' => 
      array (
        'pines' => '<2',
      ),
      'recommend' => 
      array (
        'component' => 'com_jquery',
      ),
      'type' => 'component',
    ),
    'com_authorizenet' => 
    array (
      'name' => 'Authorize.Net Interface',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Payment gateway interface',
      'description' => 'Processes credit transactions through the Authorize.Net payment gateway.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_sales&com_jquery',
      ),
      'type' => 'component',
    ),
    'com_barcode' => 
    array (
      'name' => 'Barcode Creator',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Creates various types of barcodes.',
      'description' => 'Creates and displays barcode images using a variety of formats.',
      'depend' => 
      array (
        'pines' => '<2',
        'function' => 'ImageCreate',
      ),
      'type' => 'component',
    ),
    'com_configure' => 
    array (
      'name' => 'System Configurator',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'configurator',
      ),
      'short_description' => 'Manages system configuration',
      'description' => 'Allows you to edit your system\'s configuration and the configuration of any installed components.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery&com_ptags',
      ),
      'type' => 'component',
    ),
    'com_content' => 
    array (
      'name' => 'CMS',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Content Management System',
      'description' => 'Manage content articles.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'user_manager&entity_manager&editor',
        'component' => 'com_jquery&com_pgrid&com_ptags',
      ),
      'type' => 'component',
    ),
    'com_customer' => 
    array (
      'name' => 'CRM',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Customer relationship manager',
      'description' => 'Manage your customers using accounts. Features include membership and point tracking.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'entity_manager&editor',
        'component' => 'com_jquery&com_pgrid&com_pnotify',
      ),
      'recommend' => 
      array (
        'component' => 'com_sales',
      ),
      'type' => 'component',
    ),
    'com_customertimer' => 
    array (
      'name' => 'Customer Timer',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Customer account timer',
      'description' => 'Allows the use of com_customer\'s membership and point tracking feature to run a service that requires customers to buy time, such as an internet cafe.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'entity_manager&uploader',
        'component' => 'com_customer&com_jquery&com_pgrid&com_pnotify',
      ),
      'type' => 'component',
    ),
    'com_elfinder' => 
    array (
      'name' => 'elFinder File Manager',
      'author' => 'SciActive (Component), Studio 42 Ltd. (JavaScript)',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'elFinder file manager and widget',
      'description' => 'A file manager using the elFinder jQuery plugin. See the readme in the includes folder for elFinder\'s license information.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery',
      ),
      'type' => 'component',
    ),
    'com_elfinderupload' => 
    array (
      'name' => 'elFinder Upload Widget',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'uploader',
      ),
      'short_description' => 'elFinder file upload widget',
      'description' => 'A standard file upload widget using the elFinder jQuery plugin.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_elfinder&com_jquery',
      ),
      'type' => 'component',
    ),
    'com_entitytools' => 
    array (
      'name' => 'Entity Manager Tools',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Tools for testing and maintaining your entity manager',
      'description' => 'Includes the following tools: test, benchmark, export, import.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'entity_manager',
      ),
      'type' => 'component',
    ),
    'com_example' => 
    array (
      'name' => 'Example Component',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'An example component design',
      'description' => 'This component functions as an example of how to use various features of the Pines framework.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'entity_manager&editor',
        'component' => 'com_jquery&com_pgrid',
      ),
      'type' => 'component',
    ),
    'com_financial' => 
    array (
      'name' => 'Financial Functions',
      'author' => 'SciActive (Component), Enrique Garcia (Library)',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/gpl.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Financial function library',
      'description' => 'A library of financial functions with identical names and arguments as those used in Microsoft Excel. Entirely based on work by Enrique Garcia.',
      'depend' => 
      array (
        'pines' => '<2',
      ),
      'type' => 'component',
    ),
    'com_fortune' => 
    array (
      'name' => 'Fortune',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Daily fortune',
      'description' => 'Reminiscent of the "fortune" program in Unix, this prints a daily adage from a database of fortunes, quotes, riddles, etc.',
      'depend' => 
      array (
        'pines' => '<2',
      ),
      'type' => 'component',
    ),
    'com_hrm' => 
    array (
      'name' => 'HRM',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Human resource manager',
      'description' => 'Manage your employees. You can allow your HR manager to securely create employees with restricted priveleges. Includes a timeclock to track your employees\' working hours.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'user_manager&entity_manager&editor',
        'component' => 'com_jquery&com_pgrid&com_pnotify&com_jstree',
      ),
      'type' => 'component',
    ),
    'com_jquery' => 
    array (
      'name' => 'jQuery',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'jQuery JavaScript library',
      'description' => 'Provides the jQuery JavaScript library and the jQuery UI JavaScript and CSS framework.',
      'depend' => 
      array (
        'pines' => '<2',
      ),
      'type' => 'component',
    ),
    'com_jstree' => 
    array (
      'name' => 'jsTree',
      'author' => 'SciActive (Component), Ivan Bozhanov (JavaScript)',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'jsTree jQuery plugin',
      'description' => 'A JavaScript tree jQuery component. Includes the context menu plugin.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery',
      ),
      'type' => 'component',
    ),
    'com_logger' => 
    array (
      'name' => 'Logger',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'log_manager',
      ),
      'short_description' => 'System log manager',
      'description' => 'Provides a method for components to log their activity.',
      'depend' => 
      array (
        'pines' => '<2',
      ),
      'type' => 'component',
    ),
    'com_mailer' => 
    array (
      'name' => 'Mailer',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Email interface',
      'description' => 'Provides a more object oriented interface for creating emails in Pines. Supports attachments.',
      'depend' => 
      array (
        'pines' => '<2',
      ),
      'type' => 'component',
    ),
    'com_myentity' => 
    array (
      'name' => 'Entity Manager (MySQL)',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'entity_manager',
      ),
      'short_description' => 'MySQL based entity manager',
      'description' => 'Provides an object relational mapper, which conforms to the Pines entity manager service standard and uses MySQL as its backend.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_mysql',
      ),
      'type' => 'component',
    ),
    'com_mysql' => 
    array (
      'name' => 'MySQL Link Manager',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'MySQL link manager',
      'description' => 'Provides an easy way to manage links to one or more databases, and the ability to keep more than one data set in those databases.',
      'depend' => 
      array (
        'pines' => '<2',
        'function' => 'mysql_connect',
      ),
      'type' => 'component',
    ),
    'com_newsletter' => 
    array (
      'name' => 'Newsletter Manager',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Manage newsletters to your users',
      'description' => 'Create and send newsletters to your users.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'entity_manager&editor&uploader',
        'component' => 'com_mailer&com_jquery&com_pgrid&com_jstree',
      ),
      'type' => 'component',
    ),
    'com_oxygenicons' => 
    array (
      'name' => 'Oxygen Icon Theme',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'icons',
      ),
      'short_description' => 'Pines Icon theme using Oxygen icons',
      'description' => 'A Pines Icon theme using the Oxygen icon library.',
      'depend' => 
      array (
        'pines' => '<2',
      ),
      'type' => 'component',
    ),
    'com_package' => 
    array (
      'name' => 'Package Management Libraries',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Pines package libraries',
      'description' => 'Package management functions. This component is meant to be used by other components.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_slim',
      ),
      'type' => 'component',
    ),
    'com_packager' => 
    array (
      'name' => 'Package Creator',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Pines package creator',
      'description' => 'Package your components and templates into a Pines repository ready Slim archive. You can use these packages to distribute your component to other Pines users.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'entity_manager',
        'component' => 'com_slim&com_jquery&com_pgrid',
      ),
      'type' => 'component',
    ),
    'com_pdf' => 
    array (
      'name' => 'PDF Generator',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Generate PDFs from templates',
      'description' => 'Easily insert information into a PDF template. Also allows users to format their own PDFs.',
      'depend' => 
      array (
        'pines' => '<2',
        'class' => 'Imagick',
        'component' => 'com_jquery',
      ),
      'type' => 'component',
    ),
    'com_pgrid' => 
    array (
      'name' => 'Pines Grid',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Pines Grid jQuery plugin',
      'description' => 'A JavaScript data grid jQuery component. Supports many features, and fully themeable using jQuery UI.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'icons',
        'component' => 'com_jquery',
      ),
      'type' => 'component',
    ),
    'com_pinlock' => 
    array (
      'name' => 'PIN Locker',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'PIN based security',
      'description' => 'Provides a PIN based security measure to both prevent unauthorized use of accounts and securely allow users to switch accounts quickly.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'user_manager',
      ),
      'type' => 'component',
    ),
    'com_plaza' => 
    array (
      'name' => 'Plaza Package Manager',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Plaza package manager',
      'description' => 'Find, install, and manage packages.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery&com_package',
      ),
      'type' => 'component',
    ),
    'com_pnotify' => 
    array (
      'name' => 'Pines Notify',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Pines Notify jQuery plugin',
      'description' => 'A JavaScript notification jQuery component. Supports many features, and fully themeable using jQuery UI.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'icons',
        'component' => 'com_jquery',
      ),
      'type' => 'component',
    ),
    'com_ptags' => 
    array (
      'name' => 'Pines Tags',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Pines Tags jQuery plugin',
      'description' => 'A JavaScript tag editor jQuery component. Supports many features, and fully themeable using jQuery UI.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery',
      ),
      'type' => 'component',
    ),
    'com_reports' => 
    array (
      'name' => 'Company Reports',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Production and workflow reports',
      'description' => 'Reports for sales totals, inventory and employee reports.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery&com_pgrid&com_jstree&(com_hrm|com_sales)',
      ),
      'type' => 'component',
    ),
    'com_sales' => 
    array (
      'name' => 'POS',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Point of Sales system',
      'description' => 'Manage products, inventory, sales, shipments, etc. Sell merchandise. Integrates with a cash drawer.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'user_manager&entity_manager&editor',
        'component' => 'com_jquery&com_pgrid&com_pnotify&com_ptags&com_jstree',
      ),
      'recommend' => 
      array (
        'component' => 'com_customer',
      ),
      'type' => 'component',
    ),
    'com_slim' => 
    array (
      'name' => 'Slim Archive',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Slim archiver and extracter',
      'description' => 'A library for archiving files to and extracting files from the Slim archive format. Slim archives are designed to easily work with PHP programs.',
      'depend' => 
      array (
        'pines' => '<2',
        'function' => 'gzdeflate&gzinflate&stream_filter_append',
      ),
      'type' => 'component',
    ),
    'com_su' => 
    array (
      'name' => 'Switch User',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Switch to a different user',
      'description' => 'Allow users to login as a different user quickly, without having to logout first.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'user_manager',
        'component' => 'com_jquery&com_pnotify',
      ),
      'type' => 'component',
    ),
    'com_tinymce' => 
    array (
      'name' => 'TinyMCE',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'editor',
      ),
      'short_description' => 'TinyMCE editor widget',
      'description' => 'TinyMCE based editor widget.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery',
      ),
      'recommend' => 
      array (
        'component' => 'com_elfinder',
      ),
      'type' => 'component',
    ),
    'com_user' => 
    array (
      'name' => 'User Manager',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'user_manager',
      ),
      'short_description' => 'Entity based user manager',
      'description' => 'Manages system users, groups, and abilities. Uses an entity manager as a storage backend.',
      'depend' => 
      array (
        'pines' => '<2',
        'service' => 'entity_manager&uploader',
        'component' => 'com_jquery&com_pgrid&com_pnotify',
      ),
      'type' => 'component',
    ),
    'tpl_pines' => 
    array (
      'name' => 'Pines Template',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'template',
      ),
      'short_description' => 'jQuery UI styled template',
      'description' => 'A well integrated template, completely styled with jQuery UI.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery',
      ),
      'recommend' => 
      array (
        'component' => 'com_pnotify',
      ),
      'type' => 'template',
    ),
    'tpl_print' => 
    array (
      'name' => 'Print Template',
      'author' => 'SciActive',
      'version' => '1.0.0',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'services' => 
      array (
        0 => 'template',
      ),
      'short_description' => 'Simple template suitable for printing',
      'description' => 'This template only shows the content modules. It\'s suitable for letting the user print the page without any excess information.',
      'depend' => 
      array (
        'pines' => '<2',
        'component' => 'com_jquery',
      ),
      'type' => 'template',
    ),
    'pines' => 
    array (
      'name' => 'Pines',
      'author' => 'SciActive',
      'version' => '0.70.0alpha',
      'license' => 'http://www.gnu.org/licenses/agpl-3.0.html',
      'website' => 'http://www.sciactive.com',
      'short_description' => 'Pines PHP framework core system',
      'description' => 'The core system of the Pines PHP application framework.',
      'depend' => 
      array (
        'php' => '>=5.2.10',
      ),
      'type' => 'system',
    ),
  ),
  'services' => 
  array (
    'configurator' => 
    array (
      0 => 'com_configure',
    ),
    'uploader' => 
    array (
      0 => 'com_elfinderupload',
    ),
    'log_manager' => 
    array (
      0 => 'com_logger',
    ),
    'entity_manager' => 
    array (
      0 => 'com_myentity',
    ),
    'icons' => 
    array (
      0 => 'com_oxygenicons',
    ),
    'editor' => 
    array (
      0 => 'com_tinymce',
    ),
    'user_manager' => 
    array (
      0 => 'com_user',
    ),
    'template' => 
    array (
      0 => 'tpl_pines',
      1 => 'tpl_print',
    ),
  ),
);
?>