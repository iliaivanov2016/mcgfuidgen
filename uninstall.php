<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();
global $wpdb;
global $table_prefix;
define("MCGFUIDGEN_TABLE_NAME",$table_prefix."mcgfuidgen_data");
$wpdb->query( "DROP TABLE IF EXISTS `".MCGFUIDGEN_TABLE_NAME."`" );
?>