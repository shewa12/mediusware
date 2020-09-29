<?php

namespace TUTOR_ZOOM;

if (!defined('ABSPATH'))
    exit;

class Zoom {

    public $api_key;
    public $api_data;
    public $settings_key;
    public $settings_data;
    public $zoom_meeting_post_type;
    public $zoom_meeting_base_slug;

    function __construct() {
        $this->api_key = 'tutor_zoom_api';
        $this->settings_key = 'tutor_zoom_settings';
        $this->api_data = json_decode(get_option($this->api_key), true);
        $this->settings_data = json_decode(get_option($this->settings_key), true);
        $this->zoom_meeting_post_type = 'zoom_meeting';
		$this->zoom_meeting_base_slug = 'zoom-meeting';

        add_action('init', array($this, 'register_zoom_post_types'));

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
        add_action('tutor_admin_register', array($this, 'register_menu'));

        // Saving zoom settings
		add_action('wp_ajax_tutor_save_zoom_api', array($this, 'tutor_save_zoom_api'));
		add_action('wp_ajax_tutor_save_zoom_settings', array($this, 'tutor_save_zoom_settings'));
        add_action('wp_ajax_tutor_check_api_connection', array($this, 'tutor_check_api_connection'));
        
        // Add meeting button options
        add_action('edit_form_after_editor', array($this, 'add_meeting_option'), 9, 0 );
        add_action('tutor_course_builder_after_btn_group', array($this, 'add_meeting_option_in_topic'));

        // Meeting modal form and save action 
        add_action('wp_ajax_tutor_zoom_meeting_modal_content', array($this, 'tutor_zoom_meeting_modal_content'));
        add_action('wp_ajax_tutor_zoom_save_meeting', array($this, 'tutor_zoom_save_meeting'));

        
        add_action('wp_head', array($this, 'head'));
    }

    public function register_zoom_post_types() {

		$labels = array(
			'name'               => _x( 'Meetings', 'post type general name', 'tutor-pro' ),
			'singular_name'      => _x( 'Meeting', 'post type singular name', 'tutor-pro' ),
			'menu_name'          => _x( 'Meetings', 'admin menu', 'tutor-pro' ),
			'name_admin_bar'     => _x( 'Meeting', 'add new on admin bar', 'tutor-pro' ),
			'add_new'            => _x( 'Add New', $this->zoom_meeting_post_type, 'tutor-pro' ),
			'add_new_item'       => __( 'Add New Meeting', 'tutor-pro' ),
			'new_item'           => __( 'New Meeting', 'tutor-pro' ),
			'edit_item'          => __( 'Edit Meeting', 'tutor-pro' ),
			'view_item'          => __( 'View Meeting', 'tutor-pro' ),
			'all_items'          => __( 'Meetings', 'tutor-pro' ),
			'search_items'       => __( 'Search Meetings', 'tutor-pro' ),
			'parent_item_colon'  => __( 'Parent Meetings:', 'tutor-pro' ),
			'not_found'          => __( 'No Meeting found.', 'tutor-pro' ),
			'not_found_in_trash' => __( 'No Meetings found in Trash.', 'tutor-pro' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'tutor-pro' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->zoom_meeting_base_slug ),
			'menu_icon'         => 'dashicons-list-view',
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor'),
		);

		register_post_type($this->zoom_meeting_post_type, $args );
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

    public function add_meeting_option() {
        global $post;
        $settings   = json_decode(get_option('tutor_zoom_api'), true);
        $api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
        $api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';
        if ($post->post_type == tutor()->course_post_type && !empty($api_key) && !empty($api_secret)) {
        ?>
            <div class="tutor-zoom-create-meeting">
                <div class="zoom-icon">
                    <img src="<?php echo TUTOR_ZOOM()->url.'assets/images/zoom-icon.svg'; ?>" alt="Zoom"/>
                    <div><?php _e('Connect with your students using Zoom', 'tutor-pro'); ?></div>
                </div>
                <div class="zoom-icon-button">
                    <a class="button button-primary tutor-create-zoom-meeting-btn" data-topic-id="0"><img src="<?php echo TUTOR_ZOOM()->url.'assets/images/meeting.svg'; ?>" alt="Zoom"/> <?php _e('Create a Zoom Meeting', 'tutor-pro'); ?></a>
                </div>
            </div>
        <?php
        }
    }
    
    public function add_meeting_option_in_topic($topic_id) {
        $settings   = json_decode(get_option('tutor_zoom_api'), true);
        $api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
        $api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';
        if (!empty($api_key) && !empty($api_secret)) {
        ?>
            <a href="javascript:;" class="tutor-create-zoom-meeting-btn" data-topic-id="<?php echo $topic_id; ?>">
                <i class="tutor-icon-plus-square-button"></i>
                <?php _e('Zoom Meeting',	'tutor-pro'); ?>
            </a>
        <?php
        }
    }
    

    public function tutor_zoom_meeting_modal_content() {

		$meeting_id = (int) tutor_utils()->avalue_dot('meeting_id', $_POST);
        $course_id = (int) sanitize_text_field( $_POST['course_id'] );
		$topic_id = (int) sanitize_text_field( $_POST['topic_id'] );

		if ($meeting_id) {
		    $post = get_post($meeting_id);
		}

		ob_start();
		include  TUTOR_ASSIGNMENTS()->path.'views/modal/meeting.php';
		$output = ob_get_clean();

		wp_send_json_success(array('output' => $output));

    }

	/**
	 * Save meeting
	 */
	public function tutor_zoom_save_meeting(){
        $meeting_id = (int) sanitize_text_field(tutor_utils()->avalue_dot('meeting_id', $_POST));
        
        $settings   = json_decode(get_option('tutor_zoom_api'), true);
        $api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
        $api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';
        if (!empty($api_key) && !empty($api_secret)) {
            $host_id                    = ! empty( $_POST[ 'meeting_host' ] ) ? sanitize_text_field( $_POST[ 'meeting_host' ] ) : '';
            $title                      = ! empty( $_POST[ 'meeting_title' ] ) ? sanitize_text_field( $_POST[ 'meeting_title' ] ) : '';
            $summery                    = ! empty( $_POST[ 'meeting_summery' ] ) ? sanitize_text_field( $_POST[ 'meeting_summery' ] ) : '';
            $timezone                   = ! empty( $_POST[ 'meeting_timezone' ] ) ? sanitize_text_field( $_POST[ 'meeting_timezone' ] ) : '';
            $start_date                 = ! empty( $_POST[ 'meeting_date' ] ) ? apply_filters('tutor_sanitize_meeting_date', $_POST['meeting_date']) : '';
            $start_time                 = ! empty( $_POST[ 'meeting_time' ] ) ? sanitize_text_field( $_POST[ 'meeting_time' ] ) : '';
            $duration                   = ! empty( $_POST[ 'meeting_duration' ] ) ? intval( $_POST[ 'meeting_duration' ] ) : 60;
            $password                   = ! empty( $_POST[ 'meeting_password' ] ) ? sanitize_text_field( $_POST[ 'meeting_password' ] ) : '';
            
            $join_before_host           = ($this->get_settings('join_before_host')) ? true : false;
            $host_video                 = ($this->get_settings('host_video')) ? true : false;
            $participants_video         = ($this->get_settings('participants_video')) ? true : false;
            $mute_participants          = ($this->get_settings('mute_participants')) ? true : false;
            $enforce_login              = ($this->get_settings('enforce_login')) ? true : false;
            $auto_recording             = !empty( $_POST[ 'auto_recording' ] ) ? true : false;
    
            $start_date = $this->current_date($start_date, $timezone);

            $meeting_start = strtotime( 'today', ( ( $start_date ) / 1000 ) );
            if ( ! empty( $start_time ) ) {
                $time = explode( ':', $start_time );
                if ( is_array( $time ) and count( $time ) === 2 ) {
                    $meeting_start = strtotime( "+{$time[0]} hours +{$time[1]} minutes", $meeting_start );
                }
            }
            $meeting_start = date( 'Y-m-d\TH:i:s', $meeting_start );
            $data = array(
                'topic'         => $title,
                'type'          => 2,
                'start_time'    => $meeting_start,
                'timezone'      => $timezone,
                'duration'      => $duration,
                'password'      => $password,
                'settings'      => array(
                    'join_before_host'  => $join_before_host,
                    'host_video'        => $host_video,
                    'participant_video' => $participants_video,
                    'mute_upon_entry'   => $mute_participants,
                    'auto_recording'    => $auto_recording,
                    'enforce_login'    => $enforce_login,
                )
            );

            //save post
            $post_content = array(
                'ID'            => $meeting_id,
                'post_title'    => $title,
                'post_name'     => sanitize_title($title),
                'post_content'  => $summery,
                'post_type'     => $this->zoom_meeting_post_type,
            );

            $post_id = wp_insert_post($post_content);

            $meeting_data = get_post_meta( $post_id, 'tutor_zoom_data', true );

            //save zoom meeting
            if ( !empty( $api_key ) && !empty( $api_secret ) && !empty( $host_id ) ) {
                $zoom_endpoint = new \Zoom\Endpoint\Meetings( $api_key, $api_secret );
                if ( empty( $meeting_data[ 'id' ] ) ) {
                    $new_meeting = $zoom_endpoint->create( $host_id, $data );
                    update_post_meta( $post_id, 'tutor_zoom_data', $new_meeting );
                    do_action( 'tutor_zoom_after_save_meeting', $post_id );
                } else {
                    $meeting_id = $meeting_data[ 'id' ];
                    $zoom_endpoint->update( $meeting_id, $data );
                    do_action( 'tutor_zoom_after_update_meeting', $post_id );
                }
            }
            
            wp_send_json(array(
                'success' => true,
                'post_id' => $post_id,
                'msg' => __('Meeting Successfully Saved', 'tutor-pro'),
            ));
        } else {
            wp_send_json(array(
                'success' => false,
                'post_id' => false,
                'msg' => __('Invalid Api Credentials', 'tutor-pro'),
            ));
        }
    }

    public function current_date($start_date, $timezone) {
        if (empty($start_date)) {
            $start_date= strtotime('today') . "000";
        }
        return $start_date;
    }
    
    private function get_option_data($key, $data) {
        if (empty($data) || !is_array($data)) {
			return false;
		}
		if (!$key) {
			return $data;
		}
		if (array_key_exists($key, $data)) {
			return apply_filters($key, $data[$key]);
		}
    }

    private function get_api($key = null) {
		return $this->get_option_data($key, $this->api_data);
    }
    
    private function get_settings($key = null) {
		return $this->get_option_data($key, $this->settings_data);
    }

	public function tutor_zoom() {
		include TUTOR_ZOOM()->path.'views/pages/main.php';
    }

    public function tutor_save_zoom_api() {
		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'tutor_zoom_settings' ) ){
			exit();
		}
		do_action('tutor_save_zoom_api_before');
		$api_data = (array) isset($_POST[$this->api_key]) ? $_POST[$this->api_key] : array();
		$api_data = apply_filters('tutor_zoom_api_input', $api_data);
		update_option($this->api_key, json_encode($api_data));
		do_action('tutor_save_zoom_api_after');
		wp_send_json_success( array('msg' => __('Settings Updated', 'tutor') ) );
	}
    
    public function tutor_save_zoom_settings() {
		if ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce( $_POST['_wpnonce'], 'tutor_zoom_settings' ) ){
			exit();
		}
		do_action('tutor_save_zoom_settings_before');
		$settings = (array) isset($_POST[$this->settings_key]) ? $_POST[$this->settings_key] : array();
		$settings = apply_filters('tutor_zoom_settings_input', $settings);
		update_option($this->settings_key, json_encode($settings));
		do_action('tutor_save_zoom_settings_after');
		wp_send_json_success( array('msg' => __('Settings Updated', 'tutor') ) );
    }
    
    public function tutor_check_api_connection() {
        delete_transient('tutor_zoom_users');
        $users = self::tutor_zoom_get_users();
        if ( !empty($users) ) {
            wp_send_json( __('API Connection is good', 'tutor') );
        } else {
            wp_send_json( __('Please Enter Valid Credentials', 'tutor') );
        }
		wp_die();
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
        $users = get_transient('tutor_zoom_users');
        $settings = json_decode(get_option('tutor_zoom_api'), true);

        if (empty($users)) {
            $api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
            $api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';
            if (!empty($api_key) && !empty($api_secret)) {
                $users = array();
                // $users_data = new \Zoom\Endpoint\Users($api_key, $api_secret);
                // $users_list = $users_data->userlist();
                // if (!empty($users_list) && !empty($users_list['users'])) {
                //     $users = $users_list['users'];
                //     set_transient('tutor_zoom_users', $users, 36000);
                // }
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
