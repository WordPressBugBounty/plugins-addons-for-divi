<?php

namespace DiviTorqueLite;

class Review
{
    // Hold the single instance of the class
    private static $instance = null;

    // Default configurable options
    private $options = array(
        'message'       => '',
        'plugin_name'   => '',
        'review_url'    => '',
        'image_url'     => '',
        'cookie_name'   => 'review_notice_shown',
        'option_name'   => 'review_notice_shown',
        'nonce_action'  => 'dismiss-review',
        'screen_bases'  => ['dashboard', 'plugins']
    );

    // Private constructor to prevent instantiation from outside
    private function __construct($custom_options = [])
    {
        // Merge custom options with default options
        $this->options = array_merge($this->options, $custom_options);

        // Hook the necessary actions
        add_action('admin_notices', array($this, 'show_leave_review_notice'));
        add_action('wp_ajax_dismiss_review_notice', array($this, 'dismiss_review_notice_callback'));
    }

    // Get or create the singleton instance
    public static function get_instance($options = array())
    {
        if (self::$instance === null) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    // Method to display the review notice
    public function show_leave_review_notice()
    {
        $screen = get_current_screen();

        // Use dynamic cookie name
        if (isset($_COOKIE[$this->options['cookie_name']])) {
            update_option($this->options['option_name'], true);
            setcookie($this->options['cookie_name'], '', time() - 3600, '/');
        }

        if (!current_user_can('manage_options') || get_option($this->options['option_name'], false) || !in_array($screen->base, $this->options['screen_bases'])) {
            return;
        }

        $activation_timestamp = get_option('divitorque_activation_time', false);
        $three_days_ago = strtotime('-3 days');

        if ($activation_timestamp && $activation_timestamp < $three_days_ago) :
?>
            <div id="message" class="updated fade notice is-dismissible review-notice" data-type="later">
                <div class="review-image">
                    <img width="80px" src="<?php echo esc_url($this->options['image_url']); ?>" alt="review-logo">
                </div>
                <div class="review-info">
                    <h2><?php esc_html_e("You have been using " . $this->options['plugin_name'] . " for some time now. Thank you! 💕", 'addons-for-divi'); ?></h2>
                    <p>
                        <?php esc_html_e("If you have a minute, can you write a little review for me? That would really bring me joy and motivation! 💫 </br>Don't hesitate to share your feature requests with the review, I always check them and try my best.", 'addons-for-divi'); ?>
                    </p>

                    <ul class="review-buttons">
                        <li>
                            <a href="<?php echo esc_url($this->options['review_url']); ?>" class="button button-primary review-button" target="_blank">
                                <?php esc_html_e('Leave a review', 'addons-for-divi'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="review-dismiss button button-secondary review-button" data-type="dismiss">
                                <?php esc_html_e("Don't show again", 'addons-for-divi'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="review-dismiss button button-secondary review-button" data-type="later">
                                <?php esc_html_e('Maybe later', 'addons-for-divi'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <script type="text/javascript">
                (function($) {
                    $(document).ready(function() {
                        function createCookie(name, value, days) {
                            var expires = "";
                            if (days) {
                                var date = new Date();
                                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                                expires = "; expires=" + date.toUTCString();
                            }
                            document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
                        }

                        function dismissReview(type) {
                            if (type == 'later') {
                                createCookie('<?php echo $this->options['cookie_name']; ?>', '1', 30);
                                $('.review-notice').remove();

                                console.log('later');

                            } else {
                                $.post(ajaxurl, {
                                    action: 'dismiss_review_notice',
                                    sec: '<?php echo wp_create_nonce($this->options['nonce_action']); ?>',
                                    type: 'dismiss'
                                }, function(response) {
                                    $('.review-notice').remove();
                                });
                            }
                        }

                        $('.review-dismiss').on('click', function(e) {
                            e.preventDefault();
                            var type = $(this).data('type');
                            dismissReview(type);
                        });

                        $(".review-notice").on("click", ".notice-dismiss", function() {
                            createCookie('<?php echo $this->options['cookie_name']; ?>', '1', 30);
                        });
                    });

                })(jQuery);
            </script>
<?php
        endif;
    }

    public function dismiss_review_notice_callback()
    {
        check_ajax_referer($this->options['nonce_action'], 'sec');
        $type = sanitize_text_field($_POST['type']);

        if ($type === 'dismiss') {
            update_option($this->options['option_name'], true);
        }

        wp_send_json_success();
    }
}
