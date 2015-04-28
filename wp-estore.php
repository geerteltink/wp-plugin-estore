<?php

/*
  Plugin Name: WP eStore
  Plugin URI: http://gp.eltink.net/
  Description: Wordpress eStore plugin. Easy to use plugin for ejunkie.com.
  Version: 2011.04.18 Development
  Author: Geert Eltink
  Author URI: http://gp.eltink.net/
 */

/**
 * WP_eStore
 *
 * Main WPESTORE Plugin Class
 *
 * @package wp-eStore
 */
class WP_eStore
{
    /**
     * Start WPESTORE on plugins loaded
     */
    function WP_eStore()
    {
        add_action('plugins_loaded', array($this, 'init'), 8);
    }

    /**
     * Takes care of starting WPESTORE
     */
    function init()
    {
        // Initialize constants
        $this->init_constants();

        if (is_admin())
        {
            // Initialize admin
            //$this->admin_init();
            add_action('admin_init', array($this, 'admin_init'));
        }
        else
        {
            // Initialize frontpage
            $this->frontpage_init();
        }

        // Add post types
        add_action('init', array($this, 'init_post_types'), 8);

        $this->load();
    }

    /**
     * Initialize the basic WPEC constants
     */
    function init_constants()
    {
        // Define Plugin version
        define('WPESTORE_VERSION',      '2011.03.01');

        // Define Debug Variables for developers
        define('WPESTORE_DEBUG',        true);

        // Set the needed paths
        define('WPESTORE_FILE_PATH',    dirname(__FILE__));
        define('WPESTORE_IMAGES_PATH',  WPESTORE_FILE_PATH . '/images');

        // URLs
        define('WPESTORE_URL',          plugins_url('', __FILE__));
        define('WPESTORE_IMAGES_URL',   WPESTORE_URL . '/images');
    }

    /**
     * Initialize the heart of this plugin: custom post types.
     */
    function init_post_types()
    {
        // Register products post type
        // http://codex.wordpress.org/Function_Reference/register_post_type
        $labels = array(
            'name' => _x('Products', 'wpestore_product'),
            'singular_name' => _x('Product', 'wpestore_product'),
            'add_new' => _x('Add New', 'wpestore_product'),
            'add_new_item' => __('Add New Product', 'wpestore_product'),
            'edit_item' => __('Edit Product', 'wpestore_product'),
            'new_item' => __('New Product', 'wpestore_product'),
            'view_item' => __('View Product', 'wpestore_product'),
            'search_items' => __('Search Products', 'wpestore_product'),
            'not_found' =>  __('No products found', 'wpestore_product'),
            'not_found_in_trash' => __('No products found in Trash', 'wpestore_product'),
            'parent_item_colon' => '',
            'menu_name' => __('eStore', 'wpestore_product')
        );

        register_post_type('wpestore_product', array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'menu_icon' => WPESTORE_IMAGES_URL . '/credit_cards.png',
            'query_var' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'exclude_from_search' => false,
            'supports' => array('title','editor','thumbnail','excerpt','custom-fields'),
            //'register_meta_box_cb' => 'wpsc_meta_boxes',
            'rewrite' => array('slug' => 'estore'),
        ));

        // Remove revisions for wpestore_products
        remove_post_type_support('wpestore_product', 'revisions');

        // Add new taxonomy, NOT hierarchical (product tags)
        // http://codex.wordpress.org/Function_Reference/register_taxonomy
        $labels = array(
            'name' => _x('Product Tags', 'taxonomy general name'),
            'singular_name' => _x('Product Tag', 'taxonomy singular name'),
            'search_items' =>  __('Search Product Tags'),
            'popular_items' => __('Popular Product Tags'),
            'all_items' => __('All Products Tags'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __('Edit Tag'),
            'update_item' => __('Update Tag'),
            'add_new_item' => __('Add New Product Tag'),
            'new_item_name' => __('New Product Tag Name'),
            'menu_name' => __('Product Tags'),
        );

        register_taxonomy('wpestore_product_tag','wpestore_product',array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'tagged'),
        ));

        // Add new taxonomy, make it hierarchical (like categories)
        // http://codex.wordpress.org/Function_Reference/register_taxo__('y
        $labels = array(
            'name' => _x('Categories', 'taxonomy general name'),
            'singular_name' => _x('Product Category', 'taxonomy singular name'),
            'search_items' =>  __( 'Search Product Categories'),
            'all_items' => __('All Product Categories'),
            'parent_item' => __('Parent Product Category'),
            'parent_item_colon' => __('Parent Product Category:'),
            'edit_item' => __('Edit Product Categories'),
            'update_item' => __('Update Product Category'),
            'add_new_item' => __('Add New Product Category'),
            'new_item_name' => __('New Product Category Name'),
            'menu_name' => __('Product Categories'),
        );

        register_taxonomy('wpestore_product_category', 'wpestore_product', array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'categories'),
        ));
    }

    /**
     * Initialize admin section
     */
    function admin_init()
    {
        // Add admin menu pages
        add_action('admin_menu', array($this, 'admin_menu_pages'));

        add_filter('manage_edit-wpestore_product_columns', array($this, 'admin_product_columns'));
        add_filter('manage_edit-wpestore_product_sortable_columns', array($this, 'admin_product_columns_sortable'));
        add_action('manage_posts_custom_column', array($this, 'admin_custom_columns'), 10, 2);

        // Custom meta boxes for the product edit screen
        add_meta_box('wpestore_product_details_box', 'Product details', array($this, 'admin_product_details_box'), 'wpestore_product');
        add_meta_box('wpestore_product_ejunkie_box', 'E-junkie Data', array($this, 'admin_product_ejunkie_box'), 'wpestore_product');
        add_meta_box('wpestore_product_related_box', 'Related Products', array($this, 'admin_product_related_box'), 'wpestore_product');

        // Insert product post hook
        add_action('wp_insert_post', array($this, 'admin_insert_product'), 10, 2);

        // Assign eStore roles
        $role = get_role('administrator');
        $role->add_cap('read_wpestore_product');

        add_action('admin_print_scripts-post.php', array($this, 'admin_enqueue_script'));
    }

    /**
     * Initialize admin menu pages
     */
    function admin_menu_pages()
    {
        require_once(WPESTORE_FILE_PATH . '/admin-page-debug.php');
        // Set the base page for Products
        $products_page = 'edit.php?post_type=wpestore_product';
        add_submenu_page($products_page , __('eStore Debug', 'wpestore'), __('eStore Debug','wpestore'), 'administrator', 'wpestore-debug-page', 'wpestore_admin_page_debug');
    }

    /**
     * Initialize admin menu pages
     */
    function admin_product_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            '_wpestore_code' => 'Code',
            'title' => 'Name',
            '_wpestore_price' => 'Price',
            '_wpestore_oldprice' => 'OldPrice',
            '_wpestore_discount' => 'Discount',
            '_wpestore_testimonial' => 'Testimonial',
            '_wpestore_type' => 'Type',
            //'wpestore_tags' => 'Tags',
            'wpestore_categories' => 'Categories',
            'date' => 'Date'
        );
        return $columns;
    }

    /**
     * Register the columns as sortable
     */
    function admin_product_columns_sortable($columns) {
        $custom = array(
            // meta column id => sortby value used in query
            '_wpestore_code'    => '_wpestore_code',
            '_wpestore_price'   => '_wpestore_price',
            '_wpestore_oldprice'   => '_wpestore_oldprice',
            '_wpestore_discount'   => '_wpestore_discount',
            '_wpestore_testimonial'   => '_wpestore_testimonial',
            '_wpestore_type'    => '_wpestore_type',
        );

        return wp_parse_args($custom, $columns);
    }

    /**
     * Get data for custom columns
     */
    function admin_custom_columns($column, $post_id)
    {
        switch ($column)
        {
            case 'wpestore_tags':
                $terms = get_the_term_list($post_id, 'wpestore_product_tag' , '' , ',' , '');
                if (is_string($terms))
                {
                    echo $terms;
                }
                else
                {
                    echo 'No Tags';
                }

                break;

            case 'wpestore_categories':
                $terms = get_the_term_list($post_id, 'wpestore_product_category' , '' , ',' , '');
                if (is_string($terms))
                {
                    echo $terms;
                }
                else
                {
                    echo 'Uncategorized';
                }

                break;

            case '_wpestore_price':
                echo get_post_meta($post_id, '_wpestore_price', true);
                break;

            case '_wpestore_oldprice':
                echo get_post_meta($post_id, '_wpestore_oldprice', true);
                break;

            case '_wpestore_discount':
                echo get_post_meta($post_id, '_wpestore_discount', true);
                break;

            case '_wpestore_testimonial':
                echo get_post_meta($post_id, '_wpestore_testimonial', true);
                break;

            case '_wpestore_code':
                echo get_post_meta($post_id, '_wpestore_code', true);
                break;

            case '_wpestore_type':
                echo get_post_meta($post_id, '_wpestore_type', true);
                break;
        }
    }

    /**
     * Admin product details box
     */
    function admin_product_details_box($post, $box)
    {
        echo '<label for="_wpestore_code"><input type="text" id="_wpestore_code" name="_wpestore_code" value="' . esc_html(get_post_meta($post->ID, '_wpestore_code', true)) . '" /> Product code</label>';
        echo '<br/><label for="_wpestore_price"><input type="text" id="_wpestore_code" name="_wpestore_price" value="' . esc_html(get_post_meta($post->ID, '_wpestore_price', true)) . '" /> Product price</label>';
        echo '<br/><label for="_wpestore_oldprice"><input type="text" id="_wpestore_oldprice" name="_wpestore_oldprice" value="' . esc_html(get_post_meta($post->ID, '_wpestore_oldprice', true)) . '" /> Product old price</label>';
        echo '<br/><label for="_wpestore_discount"><input type="text" id="_wpestore_discount" name="_wpestore_discount" value="' . esc_html(get_post_meta($post->ID, '_wpestore_discount', true)) . '" /> Product discount</label>';
        echo '<br/><label for="_wpestore_type"><select name="_wpestore_type" id="_wpestore_type">';



        $type = get_post_meta($post->ID, '_wpestore_type', true);
        foreach (array('CD', 'MP3', 'PDF') as $key => $val)
        {
            //get_post_meta($post->ID, '_wpestore_type', true)
            //get_post_meta($post->ID, '_wpestore_type', true)
            $selected = ($type == $val) ? 'selected="selected"' : '';
            echo '<option value="' . $val . '" ' . $selected . '>' . $val . '</option>';
        }
        echo '</select> Product type</label>';

        echo '<br/><label for="_wpestore_length"><input type="text" id="_wpestore_length" name="_wpestore_length" value="' . esc_html(get_post_meta($post->ID, '_wpestore_length', true)) . '" /> Track / CD length</label>';
        echo '<br/><br/><label for="_wpestore_testimonial">Product testimonials<br/><textarea id="_wpestore_testimonial" wrap="hard" cols="30" rows="15" name="_wpestore_testimonial"/>' . esc_html(get_post_meta($post->ID, '_wpestore_testimonial', true)) . '</textarea></label>';

 }

    /**
     * Admin product screen eJunkie box
     */
    function admin_product_ejunkie_box($post, $box)
    {
        echo '<input type="text" id="_wpestore_ej_client" name="_wpestore_ej_client" value="' . esc_html(get_post_meta($post->ID, '_wpestore_ej_client', true)) . '" /> <label for="_wpestore_ej_client">Client ID</label>';
        echo '<br/><input type="text" id="_wpestore_ej_itemid" name="_wpestore_ej_itemid" value="' . esc_html(get_post_meta($post->ID, '_wpestore_ej_itemid', true)) . '" /> <label for="_wpestore_ej_itemid">Item ID</label>';
    }

    /**
     * Admin related products metabox
     * http://wordpress.org/extend/plugins/posts-to-posts/
     */
    function admin_product_related_box($post, $box)
    {
        $related_array = get_post_meta($post->ID, '_wpestore_related', true);
        //var_dump($related_array);

        // Display current related products
        echo '<div id="' . $box->id  . '" style="border: 1px solid #DFDFDF; border-radius: 4px; padding: 4px;"><ul class="tagchecklist">';
        if (is_array($related_array))
        {
            foreach ($related_array as $key => $val)
            {
                $related_post = get_post($val);
                echo '<li id="related-' . $related_post->ID . '"><span class="remove-related" style="border-radius: 9px; background-color: red; width: 18px; height: 18px; color: white; text-align: center; cursor: pointer;">X</span> <input type="hidden" name="_wpestore_related[]" value="' . $related_post->ID . '"/>' . esc_html(get_post_meta($related_post->ID, '_wpestore_code', true)) . ' ' . esc_html($related_post->post_title) . '</li>';
            }
        }
        echo '</ul></div><br/>';

        // Display input fiels button to add new related products
        // which autocompletes for product id, title and description
        echo '<div id="wpestore_related_products">';
        //echo '<label for="wpestore_prod_search">Add related products:</label><br/><input type="text" id="wpestore_prod_search" name="wpestore_prod_search" value="" style="width: 100%;" placeholder="Search for Product Code, Title and Description." />';
        echo '<label for="_wpestore_related">Add related products:</label><br/><input type="text" name="_wpestore_related[]" value="" style="width: 100%;" placeholder="Search for Product Code, Title and Description." />';
        echo '</div>';
    }
/*

function post_tags_meta_box($post, $box) {
    $defaults = array('taxonomy' => 'post_tag');
    if ( !isset($box['args']) || !is_array($box['args']) )
        $args = array();
    else
        $args = $box['args'];
    extract( wp_parse_args($args, $defaults), EXTR_SKIP );
    $tax_name = esc_attr($taxonomy);
    $taxonomy = get_taxonomy($taxonomy);
    $disabled = !current_user_can($taxonomy->cap->assign_terms) ? 'disabled="disabled"' : '';
?>
<div class="tagsdiv" id="<?php echo $tax_name; ?>">
    <div class="jaxtag">
    <div class="nojs-tags hide-if-js">
    <p><?php echo $taxonomy->labels->add_or_remove_items; ?></p>
    <textarea name="<?php echo "tax_input[$tax_name]"; ?>" rows="3" cols="20" class="the-tags" id="tax-input-<?php echo $tax_name; ?>" <?php echo $disabled; ?>><?php echo get_terms_to_edit( $post->ID, $tax_name ); // textarea_escaped by esc_attr() ?></textarea></div>
    <?php if ( current_user_can($taxonomy->cap->assign_terms) ) : ?>
    <div class="ajaxtag hide-if-no-js">
        <label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $box['title']; ?></label>
        <div class="taghint"><?php echo $taxonomy->labels->add_new_item; ?></div>
        <p><input type="text" id="new-tag-<?php echo $tax_name; ?>" name="newtag[<?php echo $tax_name; ?>]" class="newtag form-input-tip" size="16" autocomplete="off" value="" />
        <input type="button" class="button tagadd" value="<?php esc_attr_e('Add'); ?>" tabindex="3" /></p>
    </div>
    <p class="howto"><?php echo esc_attr( $taxonomy->labels->separate_items_with_commas ); ?></p>
    <?php endif; ?>
    </div>
    <div class="tagchecklist"></div>
</div>
<?php if ( current_user_can($taxonomy->cap->assign_terms) ) : ?>
<p class="hide-if-no-js"><a href="#titlediv" class="tagcloud-link" id="link-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->choose_from_most_used; ?></a></p>
<?php endif; ?>
<?php
}

*/


    /**
     * Insert or update post
     */
    function admin_insert_product($post_id, $post = null)
    {
        $meta_fields = array('_wpestore_code', '_wpestore_price', '_wpestore_oldprice', '_wpestore_discount', '_wpestore_testimonial', '_wpestore_type', '_wpestore_length', '_wpestore_ej_client', '_wpestore_ej_itemid', '_wpestore_related');

        if ($post->post_type == 'wpestore_product')
        {
            // Loop through the POST data
            foreach ($meta_fields as $key)
            {
                $value = @$_POST[$key];
                if (empty($value))
                {
                    delete_post_meta($post_id, $key);
                    continue;
                }

                switch ($key)
                {
                    case '_wpestore_ej_client':
                    case '_wpestore_ej_itemid':
                        // Process INT
                        update_post_meta($post_id, $key, (int) $value);
                        break;


                    case '_wpestore_length':
                        // Process FLOAT
                        update_post_meta($post_id, $key, (float) $value);
                        break;

                    case '_wpestore_related':
                        // Process arrays
                        $val_array = array();
                        foreach($value as $k => $v)
                        {
                            $v = abs(intval($v));
                            if (!in_array($v, $val_array) && $v > 0)
                            {
                                $val_array[] = $v;
                            }
                        }
                        update_post_meta($post_id, $key, $val_array);

                        break;

                    default:
                        update_post_meta($post_id, $key, trim(strip_tags($value)));
                        break;
                }
            }
        }
    }

    /**
     * Add admin javascript functions
     */
    function admin_enqueue_script()
    {
        // Add the ejunkie scripts to the footer
        //add_action('wp_footer', array($this, 'frontpage_attach_footer_script'));
        //wp_enqueue_script('', $src, $deps, $ver, $in_footer );

        wp_enqueue_script('wp-estore', WPESTORE_URL . '/wp-estore-admin.js', array('jquery'), null, true);
    }

    /**
     * Initialize frontpage section
     */
    function frontpage_init()
    {
        // Add the ejunkie scripts to the footer
        add_action('wp_footer', array($this, 'frontpage_attach_footer_script'));
    }

    /**
     * Add the ejunkie scripts to the footer
     */
    function frontpage_attach_footer_script()
    {
        $content = '
            <script type="text/javascript">
            /* <![CDATA[ */
                function EJEJC_lc(th) { return false; }

                head.js(
                    {ejunkie: "http://www.e-junkie.com/ecom/box.js"}
                );
            /* ]]> */
            </script>';
        echo $content;
    }

    /**
     * Setup the WPESTORE core
     */
    function load()
    {
        //
    }

    /**
     * WPESTORE Activation Hook
     */
    function install()
    {
        global $wp_version;

        if ((float)$wp_version < 3.0)
        {
            deactivate_plugins(plugin_basename(__FILE__)); // Deactivate ourselves
            wp_die( __('Looks like you\'re running an older version of WordPress, you need to be running at least WordPress 3.0 to use WP e-Commerce 3.8', 'wpsc'), __('WP e-Commerce 3.8 not compatible', 'wpsc'), array('back_link' => true));
            return;
        }

        define('WPESTORE_FILE_PATH', dirname( __FILE__ ));
        require_once(WPESTORE_FILE_PATH . '/wpestore-installer.php');

        $this->init_constants();
        wpestore_install();
    }
}

// Start WPEC
$wpestore = new WP_eStore();

// Activation
register_activation_hook( __FILE__, array($wpestore, 'install'));
