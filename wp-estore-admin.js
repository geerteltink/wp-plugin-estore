/**
  Plugin Name: WP eStore
  Plugin URI: http://gp.eltink.net/
  Description: Wordpress eStore plugin. Easy to use plugin for ejunkie.com.
  Author: Geert Eltink
  Author URI: http://gp.eltink.net/
 */

jQuery(document).ready(function($)
{
    $("#wpestore_product_related_box .remove-related").click(function()
    {
        $(this).parent().hide('fast').remove();
    });
});
