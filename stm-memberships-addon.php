<?php
/*
Plugin Name: STM-MEMBERSHIPS addon for membership plans
Plugin URI: http://masterstudy.stylemixthemes.com/lms-plugin/
Description: Create brilliant lessons with videos, graphs, images, slides and any other attachments thanks to flexible and user-friendly lesson management tool powered by WYSIWYG editor.
As the ultimate LMS WordPress Plugin, MasterStudy makes it simple and hassle-free to build, customize and manage your Online Education WordPress website.
Author: Saodat
Author URI: https://stylemixthemes.com/
Text Domain: masterstudy-lms-learning-management-system-pro
Version: 2.0.4
*/

if ( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly

define('STM_MEMBERSHIPS_FILE', __FILE__);
define('STM_MEMBERSHIPS_PATH', dirname(STM_MEMBERSHIPS_FILE));
define('STM_MEMBERSHIPS_URL', plugin_dir_url(STM_MEMBERSHIPS_FILE));

if (!is_textdomain_loaded('masterstudy-lms-learning-management-system-pro')) {
    load_plugin_textdomain(
        'masterstudy-lms-learning-management-system-pro',
        false,
        'masterstudy-lms-learning-management-system-pro/languages'
    );
}

add_action('plugins_loaded', 'stm_memberships_init');

function stm_memberships_init()
{
    $lms_installed = defined('STM_LMS_PATH');
    if(!$lms_installed) {
//        function stm_memberships_admin_notice__success() {
//            require_once STM_MEMBERSHIPS_PATH . '/wizard/templates/notice.php';
//        }
//        add_action( 'admin_notices', 'stm_memberships_admin_notice__success' );
//        require_once STM_MEMBERSHIPS_PATH . '/wizard/wizard.php';
    } else {
        require_once(STM_MEMBERSHIPS_PATH . '/lms/class-stm-membership-plans.php');
    }
}

function my_pmpro_pages_custom_template_path( $default_templates, $page_name, $type, $where, $ext ) {
    $default_templates = array(
        STM_MEMBERSHIPS_PATH . '/pages/' . $page_name . '.' . $ext, // default plugin path
        get_stylesheet_directory() . "/paid-memberships-pro/{$type}/{$page_name}.{$ext}", // child / active theme
    );
    return $default_templates;
}
add_filter( 'pmpro_pages_custom_template_path', 'my_pmpro_pages_custom_template_path', 10, 5 );