<?php

namespace DiviTorqueLite;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Admin_Notices
 * Handles admin notices with dismissible functionality and screen-specific targeting
 * 
 * @package DiviTorqueLite
 * @since 1.0.0
 */
class Admin_Notices
{
    private $slug;
    private $title;
    private $message;
    private $type;
    private $show_after;
    private $option_key;
    private $buttons;
    private $screens;

    public function __construct($args = array())
    {
        $defaults = array(
            'slug'       => 'admin_notice',
            'title'      => '',
            'message'    => '',
            'type'       => 'info',
            'show_after' => 'week',
            'buttons'    => array(),
            'screens'    => array()
        );

        $args = wp_parse_args($args, $defaults);

        $this->slug       = sanitize_key($args['slug']);
        $this->title      = wp_kses_post($args['title']);
        $this->message    = wp_kses_post($args['message']);
        $this->type       = sanitize_key($args['type']);
        $this->show_after = sanitize_key($args['show_after']);
        $this->buttons    = $this->sanitize_buttons($args['buttons']);
        $this->screens    = array_map('sanitize_key', $args['screens']);
        $this->option_key = "wp_notice_{$this->slug}_dismissed";

        $this->init_hooks();
    }

    private function init_hooks()
    {
        add_action('admin_notices', array($this, 'display_notice'));
        add_action('wp_ajax_dismiss_admin_notice', array($this, 'dismiss_notice'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    private function sanitize_buttons($buttons)
    {
        $sanitized = array();
        foreach ($buttons as $button) {
            $sanitized[] = array(
                'text' => sanitize_text_field($button['text']),
                'url' => esc_url_raw($button['url']),
                'class' => sanitize_html_class($button['class'] ?? ''),
                'target' => in_array($button['target'] ?? '_self', array('_self', '_blank')) ? $button['target'] : '_self'
            );
        }
        return $sanitized;
    }

    public function enqueue_scripts()
    {
        $screen = get_current_screen();
        if (!empty($this->screens) && !in_array($screen->id, $this->screens)) {
            return;
        }

        wp_enqueue_script(
            'divi-torque-lite-admin-notice',
            DIVI_TORQUE_LITE_URL . 'assets/admin/js/admin-notice.js',
            [],
            DIVI_TORQUE_LITE_VERSION,
            true
        );

        wp_localize_script('divi-torque-lite-admin-notice', 'adminNoticeData', array(
            'ajax_url'    => admin_url('admin-ajax.php'),
            'security'    => wp_create_nonce('dismiss_notice_' . $this->slug),
            'notice_slug' => $this->slug,
        ));
    }

    public function display_notice()
    {
        $screen = get_current_screen();
        if (!empty($this->screens) && !in_array($screen->id, $this->screens)) {
            return;
        }

        if ($this->is_time_to_show() && !get_option($this->option_key)) {
            $class = "notice notice-{$this->type} is-dismissible";
?>
            <div id="<?php echo esc_attr($this->slug); ?>" class="<?php echo esc_attr($class); ?>">
                <?php if ($this->title) : ?>
                    <h3><?php echo esc_html($this->title); ?></h3>
                <?php endif; ?>

                <p><?php echo wp_kses_post($this->message); ?></p>

                <?php if (!empty($this->buttons)) : ?>
                    <p class="notice-buttons">
                        <?php foreach ($this->buttons as $button) : ?>
                            <a href="<?php echo esc_url($button['url']); ?>"
                                class="button <?php echo esc_attr($button['class']); ?>"
                                target="<?php echo esc_attr($button['target']); ?>">
                                <?php echo esc_html($button['text']); ?>
                            </a>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <button type="button" class="notice-dismiss" data-notice-id="<?php echo esc_attr($this->slug); ?>">
                    <span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'addons-for-divi'); ?></span>
                </button>
            </div>
<?php
        }
    }

    public function dismiss_notice()
    {
        if (!isset($_POST['notice']) || !isset($_POST['security'])) {
            wp_send_json_error('Invalid request');
        }

        check_ajax_referer('dismiss_notice_' . $_POST['notice'], 'security');

        if ($_POST['notice'] === $this->slug) {
            update_option($this->option_key, true);
            wp_send_json_success();
        }

        wp_send_json_error('Invalid notice ID');
    }

    private function is_time_to_show()
    {
        $install_date = get_option('divitorque_lite_install_date');
        if (!$install_date) {
            $install_date = current_time('timestamp');
            update_option('divitorque_lite_install_date', $install_date);
        }

        $current_date = current_time('timestamp');
        $time_diff    = $current_date - $install_date;

        switch ($this->show_after) {
            case 'minute':
                return $time_diff >= MINUTE_IN_SECONDS;
            case 'hour':
                return $time_diff >= HOUR_IN_SECONDS;
            case 'day':
                return $time_diff >= DAY_IN_SECONDS;
            case 'week':
                return $time_diff >= WEEK_IN_SECONDS;
            case 'month':
                return $time_diff >= MONTH_IN_SECONDS;
            default:
                return false;
        }
    }
}
