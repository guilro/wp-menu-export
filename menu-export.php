<?php
/*
Plugin Name:        Menu export
Description:        Menu export
Version:            1.0.0
Author:             Guillaume Royer
Author URI:         perso@guilro.com

License:            GPL
*/

defined('ABSPATH') || die('No script kiddies please!');

class WP_Menu_Export
{
    public function __construct()
    {
        add_action('init', [$this, 'main']);
        add_action('admin_menu', [$this, 'menu']);
    }

    public function main()
    {
        if (!(isset($_REQUEST['menu_export']) && $_REQUEST['menu_export'] == 1
            && isset($_REQUEST['theme_location'])
            && has_nav_menu($_REQUEST['theme_location']))) {
            return;
        }

        $options = array(
            'theme_location' => $_GET['theme_location'],
        );

        if (isset($_REQUEST['bootstrap']) && $_REQUEST['bootstrap'] == 1) {
            $options['walker'] = new wp_bootstrap_navwalker();
            $options['menu_class'] = 'nav navbar-nav navbar-right';
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

        var r = new XMLHttpRequest();
        r.open('GET', '<?= home_url(); ?>/?menu_export=1&theme_location=' + themeLocation + (addBootstrapCSS ? '&bootstrap=1' : ''), true);
        r.onreadystatechange = function () {
          if (r.readyState != 4 || r.status != 200) return;
          document.getElementById('menu-export').innerHTML = r.responseText;
        };
        r.send();
    })();
    &lt;/script&gt;
    </pre>
</div>
<?php
    }
}

new WP_Menu_Export();
