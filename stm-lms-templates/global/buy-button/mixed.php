<?php
/**
 * @var $course_id
 */

stm_lms_register_script('buy-button');
stm_lms_register_style('buy-button-mixed', array('masterstudy-lms-learning-management-system'));

$has_course = STM_LMS_User::has_course_access($course_id);
$course_price = STM_LMS_Course::get_course_price($course_id);

$is_prerequisite_passed = true;

if (class_exists('STM_LMS_Prerequisites')) {
    $is_prerequisite_passed = STM_LMS_Prerequisites::is_prerequisite(true, $course_id);
}

$is_affiliate = STM_LMS_Course_Pro::is_external_course($course_id);
if (!$is_affiliate):
    ?>

    <div class="stm-lms-buy-buttons stm-lms-buy-buttons-mixed stm-lms-buy-buttons-mixed-pro">
        <?php if (($has_course or empty($course_price)) and $is_prerequisite_passed): ?>

            <?php $user = STM_LMS_User::get_current_user();

            if (empty($user['id'])) : ?>
                <?php
                stm_lms_register_style('login');
                stm_lms_register_style('register');
                enqueue_login_script();
                enqueue_register_script();
                ?>

                <a href="#"
                   class="btn btn-default"
                   data-target=".stm-lms-modal-login"
                   data-lms-modal="login">
                    <span><?php esc_html_e('Enroll course', 'masterstudy-lms-learning-management-system-pro'); ?></span>
                </a>
            <?php else:
                $user_id = $user['id'];
                $course = STM_LMS_Helpers::simplify_db_array(stm_lms_get_user_course($user_id, $course_id, array('current_lesson_id')));
                $current_lesson = (!empty($course['current_lesson_id'])) ? $course['current_lesson_id'] : '';
                $lesson_url = STM_LMS_Course::item_url($course_id, $current_lesson); ?>
                <a href="<?php echo esc_url($lesson_url); ?>" class="btn btn-default start-course">
                    <span><?php esc_html_e('Start course', 'masterstudy-lms-learning-management-system-pro'); ?></span>
                </a>

            <?php endif; ?>

        <?php else:
            $price = get_post_meta($course_id, 'price', true);
            $sale_price = STM_LMS_Course::get_sale_price($course_id);
            $not_in_membership = get_post_meta($course_id, 'not_membership', true);
            $btn_class = array('btn btn-default');

            if (empty($price) and !empty($sale_price)) {
                $price = $sale_price;
                $sale_price = '';
            }

            if (!empty($price) and !empty($sale_price)) {
                $tmp_price = $sale_price;
                $sale_price = $price;
                $price = $tmp_price;
            }

            if (!empty($sale_price) or !empty($price)) $btn_class[] = 'btn_big heading_font';

            if (is_user_logged_in()) {
                $attributes = array(
                    'data-buy-course="' . intval($course_id) . '"',
                );
            } else {
                stm_lms_register_style('login');
                stm_lms_register_style('register');
                enqueue_login_script();
                enqueue_register_script();
                $attributes = array(
                    'data-target=".stm-lms-modal-login"',
                    'data-lms-modal="login"'
                );
            }

            $subscription_enabled = (empty($not_in_membership) and STM_LMS_Subscriptions::subscription_enabled());

            $dropdown_enabled = $subscription_enabled;
            $dropdown_enabled = is_user_logged_in() && class_exists('STM_LMS_Point_System');

            $mixed_classes = array('stm_lms_mixed_button');

            $mixed_classes[] = ($dropdown_enabled) ? 'subscription_enabled' : 'subscription_disabled';


            ?>

            <?php if ($show_buttons = apply_filters('stm_lms_pro_show_button', true, $course_id)): ?>

            <div class="<?php echo implode(' ', $mixed_classes) ?>">
                <div class="<?php echo esc_attr(implode(' ', $btn_class)); ?>" <?php if (!$dropdown_enabled) echo implode(' ', $attributes); ?>>
                        <span>
                            <?php esc_html_e('Get now', 'masterstudy-lms-learning-management-system-pro'); ?>
                        </span>

                    <?php if (!empty($price) or !empty($sale_price)): ?>
                        <div class="btn-prices">

                            <?php if (!empty($sale_price)): ?>
                                <label class="sale_price"><?php echo STM_LMS_Helpers::display_price($sale_price); ?></label>
                            <?php endif; ?>

                            <?php if (!empty($price)): ?>
                                <label class="price"><?php echo STM_LMS_Helpers::display_price($price); ?></label>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                </div>

                <div class="stm_lms_mixed_button__list">


                    <?php if ($dropdown_enabled):
                        stm_lms_register_style('membership');
                        $sub = STM_Membership_Plans::user_subscriptions();
                        ?>

                        <a class="stm_lms_mixed_button__single" href="#" <?php echo implode(' ', $attributes); ?>>
                            <span><?php esc_html_e('One Time Payment', 'masterstudy-lms-learning-management-system-pro'); ?></span>
                        </a>

                        <?php
                        if ($subscription_enabled):
                            if (!empty($sub->course_number)) : $sub->course_id = get_the_ID(); ?>
                                <button type="button"
                                        data-lms-params='<?php echo json_encode($sub); ?>'
                                        class=""
                                        data-target=".stm-lms-use-subscription"
                                        data-lms-modal="use_subscription">
                                    <span><?php esc_html_e('Enroll with Membership', 'masterstudy-lms-learning-management-system-pro'); ?></span>
                                </button>

                            <?php else: ?>
                                <a href="<?php echo esc_url(STM_LMS_Subscriptions::level_url()); ?>"
                                   class="btn btn-default btn-subscription btn-outline">
                                    <span><?php esc_html_e('Enroll with Membership', 'masterstudy-lms-learning-management-system-pro'); ?></span>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>


                    <?php do_action('stm_lms_after_mixed_button_list', $course_id); ?>

                </div>

            </div>
        <?php else: ?>
            <?php do_action('stm_lms_pro_instead_buttons', $course_id); ?>
        <?php endif; ?>

        <?php endif; ?>

        <?php do_action('stm_lms_buy_button_end', $course_id); ?>

    </div>

<?php endif; ?>