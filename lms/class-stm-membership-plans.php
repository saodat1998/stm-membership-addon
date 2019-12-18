<?php

class STM_Membership_Plans extends STM_LMS_Subscriptions
{
    protected $levels = array(array());
    protected $ent_levels = array(array());

    function __construct()
    {
        add_action('init', array($this, 'pmpro_levels_arrayf'), 1000);
        add_filter('stm_lms_fields', array($this, 'stm_lms_fields_alt'), 15, 1);

        remove_action('pmpro_membership_level_after_other_settings', 'STM_LMS_Subscriptions::stm_lms_pmpro_settings');
        remove_action('pmpro_save_membership_level', 'STM_LMS_Subscriptions::stm_lms_pmpro_save_settings');

        add_action('pmpro_membership_level_after_other_settings', 'STM_Membership_Plans::stm_lms_pmpro_settings');
        add_action('pmpro_save_membership_level', 'STM_Membership_Plans::stm_lms_pmpro_save_settings');

        add_filter('stm_lms_template_file', function($path, $filename){
            $path_child = STM_MEMBERSHIPS_PATH;
            $file_exists = file_exists($path_child.$filename);

            if($file_exists) { return $path_child; }

            return $path;

        },100,2);



        remove_action('stm_lms_template_file', 'stm_lms_template_file_pro');
        add_action('stm_lms_template_file', array($this, 'stm_lms_template_file_pro'), 100, 2);


    }

    public function stm_lms_template_file_pro($path, $template) {
        $path_child = STM_MEMBERSHIPS_PATH;
        $file_exists = file_exists($path_child.$template);
        if($file_exists) { return $path_child; }

        return file_exists(STM_LMS_PRO_PATH . $template) ? STM_LMS_PRO_PATH : $path;
    }


    public static function user_subscriptions($all = false, $user_id = '')
    {

        if (!STM_LMS_Subscriptions::subscription_enabled()) return false;

        $subs = object;

        if (is_user_logged_in() && function_exists('pmpro_hasMembershipLevel') && pmpro_hasMembershipLevel()) {
            if(empty($user_id)) {
                $user = STM_LMS_User::get_current_user();
                if (empty($user['id'])) return $subs;
                $user_id = $user['id'];
            }
            $subs = pmpro_getMembershipLevelForUser($user_id);

            $subscription_id = ($all) ? '*' : $subs->subscription_id;
            $subscriptions = (!empty($subs->ID)) ? count(stm_lms_get_user_courses_by_subscription($user_id, '*', array('user_course_id'), 0)) : 0;

            $subs->course_number = (!empty($subs->ID)) ? STM_LMS_Subscriptions::get_course_number($subs->ID) : 0;
            $subs->used_quotas = $subscriptions;
            $subs->quotas_left = $subs->course_number - $subs->used_quotas;
            $subs->enable_ent_plan = (!empty($subs->ID)) ? STM_Membership_Plans::get_enable_ent_plan($subs->ID) : 0;
        }

        return $subs;
    }

    public static function stm_lms_pmpro_settings()
    {
        $level_id = (!empty($_GET['edit'])) ? intval($_GET['edit']) : 0;
        $course_number = STM_LMS_Subscriptions::get_course_number($level_id);
        $course_featured = STM_LMS_Subscriptions::get_featured_courses_number($level_id);
        $plan_group = STM_LMS_Subscriptions::get_plan_group($level_id);
        $enabled = STM_Membership_Plans::get_enable_ent_plan($level_id);
        ?>
        <h3 class="topborder"><?php esc_html_e('STM LMS Settings', 'masterstudy-lms-learning-management-system'); ?></h3>
        <table class="form-table">
            <tbody>
            <tr class="membership_categories">
                <th scope="row" valign="top">
                    <label>
                        <?php esc_html_e('Enable subscription for Enterprise groups', 'masterstudy-lms-learning-management-system'); ?>
                        :
                    </label>
                </th>
                <td>
                    <input name="stm_lms_enable_ent_plan" type="checkbox" size="10"
                           id="stm_lms_enable_ent_plan_<?php echo $level_id ?>"
                        <?php if ($enabled) {
                            echo "checked='checked'";
                        } ?>
                    />
                    <label for="stm_lms_enable_ent_plan_<?php echo $level_id ?>">
                        <?php esc_html_e('Enterprise groups can buy courses after subscription', 'masterstudy-lms-learning-management-system'); ?>
                    </label>
                </td>
            </tr>

            <tr class="membership_categories">
                <th scope="row" valign="top">
                    <label>
                        <?php esc_html_e('Number of available courses in subscription', 'masterstudy-lms-learning-management-system'); ?>
                        :
                    </label>
                </th>
                <td>
                    <input name="stm_lms_course_number" type="text" size="10"
                           value="<?php echo esc_attr($course_number); ?>"/>
                    <small><?php esc_html_e('User can enroll several courses after subscription', 'masterstudy-lms-learning-management-system'); ?></small>
                </td>
            </tr>

            <tr class="membership_categories">
                <th scope="row" valign="top">
                    <label>
                        <?php esc_html_e('Number of featured courses quote in subscription', 'masterstudy-lms-learning-management-system'); ?>
                        :
                    </label>
                </th>
                <td>
                    <input name="stm_lms_featured_courses_number" type="text" size="10"
                           value="<?php echo esc_attr($course_featured); ?>"/>
                    <small><?php esc_html_e('Instructors can mark their courses as featured', 'masterstudy-lms-learning-management-system'); ?></small>
                </td>
            </tr>

            <tr class="membership_categories">
                <th scope="row" valign="top">
                    <label><?php esc_html_e('Group Plan', 'masterstudy-lms-learning-management-system'); ?>:</label>
                </th>
                <td>
                    <input name="stm_lms_plan_group" type="text" size="10"
                           value="<?php echo esc_attr($plan_group); ?>"/>
                    <small><?php esc_html_e('Show plan group in separate tab', 'masterstudy-lms-learning-management-system'); ?></small>
                </td>
            </tr>
            </tbody>
        </table>
    <?php }

    public static function get_enable_ent_plan($level_id)
    {
        return get_option('stm_lms_enable_ent_plan_' . $level_id, 0);
    }

    public static function stm_lms_pmpro_save_settings($level_id)
    {
        STM_Membership_Plans::save_course_number($level_id);
        return $level_id;
    }

    public static function save_course_number($level_id)
    {

        if (isset($_REQUEST['stm_lms_enable_ent_plan'])) {
            update_option('stm_lms_enable_ent_plan_' . $level_id, true);
        } else {
            update_option('stm_lms_enable_ent_plan_' . $level_id, false);
        }
        if (isset($_REQUEST['stm_lms_course_number'])) {
            update_option('stm_lms_course_number_' . $level_id, intval($_REQUEST['stm_lms_course_number']));
        }
        if (isset($_REQUEST['stm_lms_featured_courses_number'])) {
            update_option('stm_lms_featured_courses_number_' . $level_id, intval($_REQUEST['stm_lms_featured_courses_number']));
        }
        if (isset($_REQUEST['stm_lms_plan_group'])) {
            update_option('stm_lms_plan_group_' . $level_id, sanitize_text_field($_REQUEST['stm_lms_plan_group']));
        }

    }

    function stm_lms_fields_alt($fields)
    {
        foreach ($this->levels as $level):
            $slug = $level['id'];
            $fields['stm_courses_settings']['section_accessibility']['fields']['mem_level__' . $slug] = array(
                'pro' => true,
                'type' => 'number',
                'label' => esc_html__($level['name'] . ' Course Price (leave blank to make the course free)', 'masterstudy-lms-learning-management-system'),
                'sanitize' => 'stm_lms_save_number'
            );
        endforeach;

        foreach ($this->ent_levels as $level):
            $slug = $level['id'];
            $fields['stm_enterprise_course']['section_enterprise_group']['fields']['mem_level_ent__' . $slug] = array(
                'pro' => true,
                'type' => 'number',
                'label' => esc_html__($level['name'] . ' Course Price (leave blank to make the course free)', 'masterstudy-lms-learning-management-system'),
                'sanitize' => 'stm_lms_save_number'
            );
        endforeach;

        return $fields;
    }

    function pmpro_levels_arrayf()
    {
        global $wpdb, $pmpro_msg, $pmpro_msgt, $current_user;

        $pmpro_levels = pmpro_getAllLevels(false, true);
        $pmpro_level_order = pmpro_getOption('level_order');

        if (!empty($pmpro_level_order)) {
            $order = explode(',', $pmpro_level_order);

            $reordered_levels = array();
            foreach ($order as $level_id) {
                foreach ($pmpro_levels as $key => $level) {
                    if ($level_id == $level->id)
                        $reordered_levels[] = $pmpro_levels[$key];
                }
            }

            $pmpro_levels = $reordered_levels;
        }
        $pmpro_levels = apply_filters("pmpro_levels_array", $pmpro_levels);
        foreach ($pmpro_levels as $level_number => $level) {
        $enabled = get_option("stm_lms_enable_ent_plan_{$level->id}");
        if($enabled){
            $ent_levels[] = array(
                'id' => $level->id,
                'name' => $level->name,
            );
        }else{
            $levels[] = array(
                'id' => $level->id,
                'name' => $level->name,
            );
        }
        }
        $this->ent_levels = $ent_levels;
        $this->levels = $levels;
    }



//    function stm_mail_settings_page()
//    {
//        add_menu_page(
//            'STM MAIL',
//            'STM MAIL',
//            'manage_options',
//            'stm-mail-settings',
//            array($this, 'stm_mail_settings_page_view')
//        );
//
//    }
//
//    function stm_mail_settings_page_view()
//    {
//        require_once(STM_MAIL_PATH . '/includes/stm-mail-page.php');
//    }
}

new STM_Membership_Plans;