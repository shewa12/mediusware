<?php

namespace TUTOR_ZOOM;

if (!defined('ABSPATH'))
    exit;

class Zoom {

    function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('tutor_admin_register', array($this, 'register_menu'));
        
        add_action('wp_head', array($this, 'head'));
        /* add_shortcode('tutor_zoom_conference', array($this, 'add_meeting_shortcode'));
        add_shortcode('tutor_zoom_webinar', array($this, 'add_webinar_shortcode')); */
    }

    /**
     * Enqueue admin scripts
     */
    public function admin_scripts() {
        wp_enqueue_script('tutor_zoom_admin_js', TUTOR_ZOOM()->url . 'assets/js/admin.js', array('jquery'), TUTOR_ZOOM_VERSION, true);
        wp_enqueue_style('tutor_zoom_admin_css', TUTOR_ZOOM()->url . 'assets/css/admin.css', false, TUTOR_ZOOM_VERSION);
    }

    /**
     * Enqueue frontend scripts
     */
    public function frontend_scripts() {
        wp_enqueue_script('tutor_jquery.countdown', TUTOR_ZOOM()->url . 'assets/js/jquery.countdown.js', array('jquery'), TUTOR_ZOOM_VERSION, true);
        wp_enqueue_script('tutor_zoom_frontend_js', TUTOR_ZOOM()->url . 'assets/js/frontend.js', array('jquery'), TUTOR_ZOOM_VERSION, true);
        wp_enqueue_style('tutor_zoom_frontend_css', TUTOR_ZOOM()->url . 'assets/css/frontend.css', false, TUTOR_ZOOM_VERSION);
    }

    public function register_menu() {
		add_submenu_page('tutor', __('Zoom', 'tutor-pro'), __('Zoom', 'tutor-pro'), 'manage_tutor', 'tutor_zoom', array($this, 'tutor_zoom'));
	}

	public function tutor_zoom() {
		include TUTOR_ZOOM()->path.'views/pages/main.php';
	}

    /**
     * Define Frontend Translation Variables
     */
    public function head() {
?>
        <script>
            var daysStr = "<?php esc_html_e('Days', 'eroom-zoom-meetings-webinar'); ?>";
            var hoursStr = "<?php esc_html_e('Hours', 'eroom-zoom-meetings-webinar'); ?>";
            var minutesStr = "<?php esc_html_e('Minutes', 'eroom-zoom-meetings-webinar'); ?>";
            var secondsStr = "<?php esc_html_e('Seconds', 'eroom-zoom-meetings-webinar'); ?>";
        </script>
        <?php
    }

    /**
     * Add Meeting Shortcode
     * @param $atts
     * @return string
     */
    public function add_meeting_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => '',
            'hide_content_before_start' => ''
        ), $atts);
        $content = '';
        $hide_content_before_start = '';
        if (!empty($atts['hide_content_before_start'])) {
            $hide_content_before_start = '1';
        }
        if (!empty($atts['post_id'])) {
            $content = self::add_zoom_content($atts['post_id'], $hide_content_before_start);
        }
        return $content;
    }

    /**
     * Add Webinar Shortcode
     * @param $atts
     * @return string
     */
    public function add_webinar_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_id' => '',
            'hide_content_before_start' => ''
        ), $atts);
        $content = '';
        $hide_content_before_start = '';
        if (!empty($atts['hide_content_before_start'])) {
            $hide_content_before_start = '1';
        }
        if (!empty($atts['post_id'])) {
            $content = self::add_zoom_content($atts['post_id'], $hide_content_before_start, true);
        }
        return $content;
    }

    /**
     * Zoom Meeting Content
     * @param $post_id
     * @param string $hide_content_before_start
     * @return string
     */
    public static function add_zoom_content($post_id, $hide_content_before_start = '', $webinar = false) {
        $content = '';
        if (!empty($post_id)) {
            $post_id        = intval($post_id);
            $meeting_data   = self::meeting_time_data($post_id);
            if (!empty($meeting_data) && !empty($meeting_data['meeting_start']) && !empty($meeting_data['meeting_date'])) {
                $meeting_start = $meeting_data['meeting_start'];
                $meeting_date = $meeting_data['meeting_date'];
                $is_started = $meeting_data['is_started'];
                if (!$is_started) {
                    $content = self::countdown($meeting_date, false,  $webinar);
                    if (empty($hide_content_before_start)) {
                        $content .= self::zoom_content($post_id, $meeting_start, $webinar);
                    }
                } else {
                    $content = self::zoom_content($post_id, $meeting_start, $webinar);
                }
            }
        }
        return $content;
    }

    /**
     * Collect Meeting Data
     * @param $post_id
     * @return array|bool
     */
    public static function meeting_time_data($post_id) {
        if (empty($post_id))
            return false;

        $r = array();
        $post_id        = intval($post_id);
        $start_date     = get_post_meta($post_id, 'tutor_date', true);
        $start_time     = get_post_meta($post_id, 'tutor_time', true);
        $timezone       = get_post_meta($post_id, 'tutor_timezone', true);
        $meeting_start  = strtotime('today', (apply_filters('eroom_sanitize_tutor_date', $start_date) / 1000));

        if (!empty($start_time)) {
            $time = explode(':', $start_time);
            if (is_array($time) and count($time) === 2) {
                $meeting_start = strtotime("+{$time[0]} hours +{$time[1]} minutes", $meeting_start);
            }
        }

        $meeting_start = date('Y-m-d H:i:s', $meeting_start);

        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        $meeting_date   = new DateTime($meeting_start, new DateTimeZone($timezone));
        $meeting_date   = $meeting_date->format('U');
        $is_started     = ($meeting_date > time()) ? false : true;

        $r['meeting_start']   = $meeting_start;
        $r['meeting_date']    = $meeting_date;
        $r['is_started']      = $is_started;

        return $r;
    }

    /**
     * Meeting Countdown
     * @param string $time
     * @param bool $hide_title
     * @return string
     */
    public static function countdown($time = '', $hide_title = false, $webinar = false) {
        if (!empty($time)) {
            $countdown = '<div class="zoom_countdown_wrap">';
            if (!$hide_title) {
                $title = ($webinar) ? esc_html__('Webinar starts in', 'eroom-zoom-meetings-webinar') : esc_html__('Meeting starts in', 'eroom-zoom-meetings-webinar');
                $countdown .= '<h2 class="countdown_title">' . $title . '</h2>';
            }
            $countdown .= '<div class="tutor_zooom_countdown" data-timer="' . esc_attr($time) . '"></div></div>';

            return $countdown;
        }
    }

    /**
     * Zoom Meeting Content Template
     * @param $post_id
     * @param $meeting_start
     * @return string
     */
    public static function zoom_content($post_id, $meeting_start, $webinar = false) {
        if (!empty($post_id)) {
            $zoom_data = get_post_meta($post_id, 'tutor_zoom_data', true);
            if (!empty($zoom_data) && !empty($zoom_data['id'])) {
                $meeting_id = sanitize_text_field($zoom_data['id']);
                $title      = get_the_title($post_id);
                $agenda     = get_post_meta($post_id, 'tutor_agenda', true);
                $password   = get_post_meta($post_id, 'tutor_password', true);

                ob_start();
        ?>
                <div class="tutor_zoom_content">
                    <?php if (has_post_thumbnail($post_id)) { ?>
                        <div class="zoom_image">
                            <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
                        </div>
                    <?php } ?>
                    <div class="zoom_info">
                        <h2><?php esc_html_e($title); ?></h2>
                        <?php if (!empty($meeting_start)) { ?>
                            <div class="date">
                                <span><?php echo ($webinar) ? esc_html_e('Webinar date', 'eroom-zoom-meetings-webinar') : esc_html_e('Meeting date', 'eroom-zoom-meetings-webinar'); ?> </span>
                                <b>
                                    <?php

                                    $date_format    = get_option('date_format', 'd M Y H:i');
                                    $time_format    = get_option('time_format', 'H:i');
                                    $format         = $date_format . ' ' . $time_format;
                                    $date           = strtotime($meeting_start);
                                    $date           = date_i18n($format, $date);
                                    esc_html_e($date);

                                    ?>
                                </b>
                            </div>
                        <?php } ?>
                        <?php if (!empty($password)) { ?>
                            <div class="password">
                                <span><?php esc_html_e('Password: ', 'eroom-zoom-meetings-webinar'); ?></span>
                                <span class="value"><?php esc_html_e($password); ?></span>
                            </div>
                        <?php } ?>
                        <a href="<?php echo add_query_arg(array('show_meeting' => '1'), get_permalink($post_id)); ?>" class="btn stm-join-btn join_in_menu" target="_blank">
                            <?php esc_html_e('Join in browser', 'eroom-zoom-meetings-webinar'); ?>
                        </a>
                        <a href="https://zoom.us/j/<?php echo esc_attr($meeting_id); ?>" class="btn stm-join-btn outline" target="_blank">
                            <?php esc_html_e('Join in zoom app', 'eroom-zoom-meetings-webinar'); ?>
                        </a>
                    </div>
                    <div class="zoom_description">
                        <?php if (!empty($agenda)) { ?>
                            <div class="agenda">
                                <?php echo wp_kses_post($agenda); ?>
                            </div>
                        <?php } ?>
                        <div id="zmmtg-root"></div>
                        <div id="aria-notify-area"></div>
                    </div>
                </div>
<?php
                $content = ob_get_clean();

                return $content;
            }
        }
    }

    /**
     * Get Zoom Users from Zoom API
     * @return array
     */
    public static function tutor_zoom_get_users() {
        $users      = get_transient('tutor_zoom_users');
        $settings   = get_option('tutor_zoom_settings', array());

        if (empty($users)) {
            $api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
            $api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';
            if (!empty($api_key) && !empty($api_secret)) {
                $users_data = new \Zoom\Endpoint\Users($api_key, $api_secret);
                $users_list = $users_data->userlist();
                if (!empty($users_list) && !empty($users_list['users'])) {
                    $users = $users_list['users'];
                    set_transient('tutor_zoom_users', $users, 36000);
                }
            } else {
                $users = array();
            }
        }
        return $users;
    }

    /**
     * Get Zoom Users
     * @return array
     */
    public static function get_users_options() {
        $users = self::tutor_zoom_get_users();
        if (!empty($users)) {
            foreach ($users as $user) {
                $first_name         = $user['first_name'];
                $last_name          = $user['last_name'];
                $email              = $user['email'];
                $id                 = $user['id'];
                $user_list[$id]   = $first_name . ' ' . $last_name . ' (' . $email . ')';
            }
        } else {
            return array();
        }
        return $user_list;
    }

    /**
     * Get Users for Autocomplete
     * @return array
     */
    static function get_autocomplete_users_options() {
        $users  = self::get_users_options();
        $result = array();
        foreach ($users as $id => $user) {
            $result[] = array(
                'id' => $id,
                'title' => $user,
                'post_type' => ''
            );
        }
        return $result;
    }
}
