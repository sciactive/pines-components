<?php
/**
 * CSS for tpl_bootstrap
 *
 * @package Templates\tpl_bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */

header('Content-Type: text/css');

?>
/* Comment the style tag out, only for IDE purposes. 
<style type="text/css">*/
/* Media Queries */
@media (max-width: 767px) {
        #footer {
                margin-left: -20px !important;
                margin-right: -20px !important;
                padding-left: 20px !important;
                padding-right: 20px !important;
        }
}
/* Conditional Classes template CSS */
/* Body */
body.body-font {
        font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->body_fontface); ?>;
}
body.body-custom {
        <?php echo htmlspecialchars($pines->config->tpl_bootstrap->body_css); ?>;
}
/* Navbar Configurations */
#nav-configure.bar-colors #nav > div.navbar-inner {
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
        background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
        background-image: -moz-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>);
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>), to(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>));
        background-image: -webkit-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>);
        background-image: -o-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>);
        background-image: linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>);
        border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->border_color); ?> !important;
}
#nav-configure.bar-colors #nav ul.nav li > a:hover, #nav ul.nav > li.dropdown.open > a, #nav-configure.bar-colors #nav ul.nav > li.dropdown.active > a, #nav-configure.bar-colors #nav ul.nav > li.active > a {
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
        background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
}

#nav-configure.bar-colors #nav .dropdown-menu li > a:hover, #nav-configure.bar-colors #nav .dropdown-menu li > a:focus, #nav-configure.bar-colors #nav .dropdown-submenu:hover > a, #nav-configure.bar-colors #nav .dropdown-menu .active > a, #nav-configure.bar-colors #nav .dropdown-menu .active > a:hover {
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
        background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
        background-image: -moz-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>), to(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>)) !important;
        background-image: -webkit-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        background-image: -o-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        background-image: linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->border_color); ?> !important;
}

#nav-configure.bar-colors #nav.navbar-inverse .btn-navbar, #nav-configure.bar-colors #nav .btn-navbar  {
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
        background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
        background-image: -moz-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>), to(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>)) !important;
        background-image: -webkit-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        background-image: -o-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        background-image: linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
        border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->border_color); ?> !important;
}

#nav-configure.caret-color #nav ul.nav > li.dropdown > .dropdown-toggle .caret {
        border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_color); ?> !important;
}
#nav-configure.caret-color #nav ul.nav > li.dropdown.active > .dropdown-toggle .caret {
        border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_hover_color); ?> !important;
}
#nav-configure.caret-color #nav ul.nav > li.dropdown.open > a:hover > .caret, #nav-configure.caret-color #nav ul.nav > li.dropdown.open > a:focus > .caret, #nav-configure.caret-color #nav ul.nav > li.dropdown.open > a > .caret {
        border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_hover_color); ?> !important;
}
#nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown > .dropdown-toggle .caret {
        border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_color); ?> !important;
}

#nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown.open > .dropdown-toggle .caret, #nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown.active > .dropdown-toggle .caret, #nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown.open.active > .dropdown-toggle .caret {
        border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_hover_color); ?> !important;
}

#nav-configure.brand-color #nav a.brand {
        color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_color); ?> !important;
}
#nav-configure.brand-color #nav a.brand:hover {
        color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_hover_color); ?> !important;
}

#nav-configure.font-color #nav ul.nav > li.dropdown > a, #nav-configure.font-color #nav ul.nav > li > a {
        color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_color); ?> !important;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
#nav-configure.font-color #nav .nav-collapse.collapse .nav > li > a, #nav-configure.font-color #nav .nav-collapse.collapse .dropdown-menu a {
        color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_color); ?> !important;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
}
#nav-configure.font-color #nav .dropdown-menu li > a:hover, #nav-configure.font-color #nav .dropdown-menu li > a:focus, #nav-configure.font-color #nav .dropdown-submenu:hover > a {
        color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_hover_color); ?> !important;
}
#nav-configure.font-color #nav.navbar .btn-navbar .icon-bar {
        background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_color); ?> !important;
}
#nav-configure.menu-height #nav .navbar-inner {
        min-height: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->navbar_menu_height); ?>;
}
#nav-configure.brand-font #nav a.brand {
        font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_fontface); ?> !important;
}
#nav-configure.menu-font #nav .nav a {
        font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->menu_fontface); ?>;
}
#nav-configure.menu-custom #nav .nav a {
        <?php echo htmlspecialchars($pines->config->tpl_bootstrap->menu_css); ?>;
}
#nav-configure.brand-custom #nav a.brand {
        <?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_css); ?>;
}
#nav-configure.nav-bar-custom #nav > div.navbar-inner {
        <?php echo htmlspecialchars($pines->config->tpl_bootstrap->nav_bar_css); ?>;
}

/* Footer Configurations */
#footer.footer-fixed {
        position: fixed !important;
        bottom: 0 !important;
}
#page.footer-fixed {
        margin-bottom: 4em;
}
@media (max-width: 800px) {
        #footer.footer-fixed {
                padding: 0 20px;
                margin-left: -20px;
                position: relative !important;
        }
}
#footer.bg-color {
        background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_background); ?> !important;
        border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_border); ?> !important;
}
#footer.font-color {
        color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_font_color); ?> !important;
}
#footer.footer-custom {
        <?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_css); ?>;
}

/* Conditional Media Queries */
@media (min-width: 800px){
        #nav-configure.nav-list-custom #nav .nav {
                <?php echo htmlspecialchars($pines->config->tpl_bootstrap->nav_list_css); ?>;
        }
}
@media (max-width: 800px) {
        #nav-configure.adjusted #nav li {
                clear:both;
        }
        #nav-configure.adjusted #nav .nav-helper {
                float:right !important;
                position:relative;
                cursor: pointer;
                zoom: 1;
                z-index: 200;
        }
        #nav-configure.adjusted #nav .sub-nav-helper {
                float:right !important;
                position:relative;
                cursor: pointer;
        }
        #nav-configure.adjusted #nav ul.nav > li.dropdown > a {
                float:left !important;
                width: 60% !important;
                position:relative;
                clear:both;
        }
        #nav-configure.adjusted #nav ul.dropdown-menu {
                width: 60% !important;
        }
        #nav-configure.adjusted #nav ul.dropdown-menu a, #nav-configure.adjusted #nav ul.dropdown-menu > li.dropdown-submenu {
                clear:both !important;
        }

        #nav-configure.adjusted #nav li.dropdown-submenu > a {
                float: left !important;
                position:relative;
        }

        #nav-configure.adjusted #nav li.dropdown-submenu > a:after {
                display:none;
        }
}

<?php // Stuff that  couldn't be added by classes (like headings)
if (!empty($pines->config->tpl_bootstrap->headings_fontface)) { ?>
        h1, h2, h3, h4, h5, h6 {
                font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->headings_fontface); ?>;
        }
<?php } if (!empty($pines->config->tpl_bootstrap->headings_css)) { ?>
        h1, h2, h3, h4, h5, h6 {
                <?php echo htmlspecialchars($pines->config->tpl_bootstrap->headings_css); ?>;
        }
<?php } ?>

/*
</style>
*/
<?php exit; ?>