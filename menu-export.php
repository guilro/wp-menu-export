<?php
/**
 * Plugin Name:        Menu export
 * Plugin URI:         https://wordpress.org/plugins/menu-export/
 * Description:        Menu Export allows you to embed your menu markup on other websites.
 * Version:            1.1.0
 * Author:             Jill Royer <perso@jillro.me>
 * Author URI:         https://redado.com
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 */

defined('ABSPATH') || die('No script kiddies please!');

require 'bs3_nav_walker.php';
require 'bs4_nav_walker.php';

class WP_Menu_Export
{
    public function __construct()
    {
        add_action('init', [$this, 'main']);
        add_filter('nav_menu_link_attributes', [$this, 'ensure_absolute_urls']);
        add_action('admin_menu', [$this, 'menu']);
    }

    public function main()
    {
        if (!(isset($_REQUEST['menu_export']) && $_REQUEST['menu_export'] == 1
            && isset($_REQUEST['theme_location'])
            && has_nav_menu($_REQUEST['theme_location']))) {
            return;
        }

        $options = array();

        foreach (['theme_location', 'menu_class', 'container'] as $option) {
            if (isset($_REQUEST[$option])) {
                $options[$option] = $_REQUEST[$option];
            }
        }

        if ((isset($_REQUEST['bootstrap']) && $_REQUEST['bootstrap'] == 1)
          || (isset($_REQUEST['bootstrap3']) && $_REQUEST['bootstrap3'])) {
            $options['walker'] = new wp_menu_export_bootstrap_navwalker();
        }

        if ((isset($_REQUEST['bootstrap4']) && $_REQUEST['bootstrap4'] == 1)) {
            $options['walker'] = new WP_Bootstrap_Navwalker();
        }

        header('Access-Control-Allow-Origin: *');
        wp_nav_menu($options);
        exit;
    }

    public function menu()
    {
        add_options_page(
            'Menu Export',
            'Menu Export',
            'administrator',
            __FILE__,
            [$this, 'settings_page']
        );
    }

    public function settings_page()
    {
        $menus = get_registered_nav_menus();
?>
<div class="wrap">
    <h1>Menu Export</h1>
    Your theme menus are <code><?= implode('</code>, <code>', array_keys($menus)); ?></code>.

    You can use the following code to insert your menu on an other website :
    <pre>
    &lt;div id="menu-export"&gt;&lt;/div&gt;
    &lt;script&gt;
    (function() {
        /** SETTINGS **/
        var themeLocation = 'primary_navigation';
        var addBootstrapCSS = false;
        var bootstrapVersion = 4; // or 3
        var menu_class = 'menu';
        var container = 'div';

        var r = new XMLHttpRequest();
        r.open('GET', '<?= home_url(); ?>/?menu_export=1&theme_location='+themeLocation+
        '&menu_class='+menu_class+'&container='+container+
        (addBootstrapCSS?'&bootstrap'+bootstrapVersion+'=1':''),true);
        r.onreadystatechange=function(){if(r.readyState!=4||r.status!=200)return;
        document.getElementById('menu-export').innerHTML = r.responseText;};
        r.send();
    })();
    &lt;/script&gt;
    </pre>
</div>
<?php
    }
}

new WP_Menu_Export();
