<?php /* Uninstall script for Basic Google Analytics plugin */
if (!defined('WP_UNINSTALL_PLUGIN')) { exit(); }
delete_option('mjkjr_bga_version');
delete_option('mjkjr_bga_options');
?>