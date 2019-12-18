<?php
/**
 * @var $course_id
 * @var $price
 */

$has_course = STM_LMS_User::has_course_access($course_id);

if (is_user_logged_in()):

    stm_lms_register_style('enterprise-course');
    stm_lms_register_script('enterprise-course');

    ?>

    <span class="or heading_font enterprise-or">- <?php esc_html_e("For Business", 'masterstudy-lms-learning-management-system-pro'); ?> -</span>

    <div class="stm-lms-buy-buttons stm-lms-buy-buttons-enterprise"
         data-lms-params='<?php echo json_encode(compact('course_id')); ?>'
         data-lms-modal="buy-enterprise"
         data-target=".stm-lms-modal-buy-enterprise">
        <div class="btn btn-default btn_big heading_font text-center">
            <span><?php esc_html_e('Buy for group', 'masterstudy-lms-learning-management-system-pro'); ?></span>
        </div>
    </div>

<!--    <div class="stm_lms_mixed_button__list">-->


        <?php
            stm_lms_register_style('membership');
            $sub = STM_Membership_Plans::user_subscriptions();
            ?>

            <?php
            if ($sub->enable_ent_plan):
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
<!--    </div>-->

<?php endif;