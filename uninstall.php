<?php
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();

delete_option('rs_seller');
delete_option('rs_template');
delete_option('rs_ebaycampaignid');
delete_option('rs_defaultkeywords');
delete_option('rs_revenuesharing');
?>