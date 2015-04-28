<?php

/**
 * WP eStore Main Admin functions
 *
 * These are the main WPESTORE Admin functions
 *
 * @package wp-eStore
 */

global $wpestore_db_version;
$wpestore_db_version = '0.3';

function wpestore_install()
{
    global $wpdb, $wpestore_db_version;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $table_name = $wpdb->prefix . 'wpestore_products';

    $sql = "CREATE TABLE " . $table_name . " (
          ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
          product_code varchar(128) NOT NULL,
          product_name text NOT NULL,
          product_description text DEFAULT NULL,
          product_summary text DEFAULT NULL,
          product_price varchar(32) DEFAULT NULL,
          product_type varchar(32) DEFAULT NULL,
          ejunkie_itemid varchar(32) DEFAULT NULL,
          ejunkie_client varchar(32) DEFAULT NULL,
          product_modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY ID (ID)
        );";

    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
    {
        dbDelta($sql);
        add_option('wpestore_db_version', $wpestore_db_version);
    }

    $installed_ver = get_option('wpestore_db_version');

    if ($installed_ver != $wpestore_db_version)
    {
        dbDelta($sql);
        update_option('wpestore_db_version', $wpestore_db_version);
    }
}
