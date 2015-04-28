<?php

/**
 * WP eStore debug page
 *
 * Displays the debug admin page
 *
 * @package wp-eStore
 * @since 2011.03.01
 */

// wpestore_categories_page() displays the page content for tags
function wpestore_admin_page_debug() {
    echo "<h2>" . __('eStore Debug', 'wpestore') . "</h2>";
    echo '<ul>';
    echo '<li>WPESTORE_VERSION: ' . WPESTORE_VERSION . '</li>';
    echo '<li>DATABASE_VERSION: ' . get_option('wpestore_db_version') . '</li>';
    echo '<li>WPESTORE_DEBUG: ' . WPESTORE_DEBUG . '</li>';
    echo '<li>WPESTORE_FILE_PATH: ' . WPESTORE_FILE_PATH . '</li>';
    echo '<li>WPESTORE_IMAGES_PATH: ' . WPESTORE_IMAGES_PATH . '</li>';
    echo '<li>WPESTORE_URL: ' . WPESTORE_URL . '</li>';
    echo '<li>WPESTORE_IMAGES_URL: ' . WPESTORE_IMAGES_URL . '</li>';
    echo '</ul>';
}
