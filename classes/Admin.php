<?php
namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Admin{

	public function __construct() {
		add_action('admin_menu', array($this, 'register_menu'));
		add_action('admin_action_activate_tutor_free', array($this, 'activate_tutor_free'));
		add_action('admin_init', array($this, 'check_tutor_free_installed'));
		add_action('wp_ajax_install_tutor_plugin', array($this, 'install_tutor_plugin'));
		//add_action('admin_action_install_tutor_free', array($this, 'install_tutor_plugin'));

		add_action('admin_bar_menu', array($this, 'add_toolbar_items'), 100);
	}

	public function register_menu(){
	    if ( ! defined('TUTOR_VERSION')){
		    add_menu_page(__('Tutor LMS Pro', 'tutor-pro'), __('Tutor LMS Pro', 'tutor-pro'), 'manage_tutor_instructor', 'tutor-install', array($this, 'install_activate_tutor_free'),
                'dashicons-welcome-learn-more', 2);
	    }
    }

    public function install_activate_tutor_free(){
	    include tutor_pro()->path.'views/install-tutor.php';
    }

	public function check_tutor_free_installed(){
		$tutor_file = WP_PLUGIN_DIR.'/tutor/tutor.php';

		if (file_exists($tutor_file) && ! is_plugin_active('tutor/tutor.php')){
			add_action( 'admin_notices', array($this, 'free_plugin_installed_but_inactive_notice') );
		}elseif( ! file_exists($tutor_file) ){
			add_action( 'admin_notices', array($this, 'free_plugin_not_installed') );
        }

	}

	public function free_plugin_installed_but_inactive_notice(){
		?>
		<div class="notice notice-error tutor-install-notice">
            <div class="tutor-install-notice-inner">
                <div class="tutor-install-notice-icon">
                    <img src="<?php echo tutor_pro()->url.'assets/images/tutor-logo.jpg'; ?>" alt="">
                </div>
                <div class="tutor-install-notice-content">
                    <h2>Thanks for using Tutor LMS Pro</h2>
                    <p>You must have <a href="https://wordpress.org/plugins/tutor/" target="_blank">Tutor LMS </a> Free version installed and activated on this website in order to use Tutor LMS Pro.</p>
                    <a href="https://www.themeum.com/docs/tutor-introduction/" target="_blank">Learn more about Tutor LMS</a>
                </div>
                <div class="tutor-install-notice-button">
                    <a  class="button button-primary" href="<?php echo add_query_arg(array('action' => 'activate_tutor_free'), admin_url()); ?>">Activate Tutor LMS</a>
                </div>
            </div>
		</div>
		<?php
    }

	public function free_plugin_not_installed(){
		include( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		?>
        <div class="notice notice-error tutor-install-notice">
            <div class="tutor-install-notice-inner">
                <div class="tutor-install-notice-icon">
                    <img src="<?php echo tutor_pro()->url.'assets/images/tutor-logo.jpg'; ?>" alt="">
                </div>
                <div class="tutor-install-notice-content">
                    <h2>Thanks for using Tutor LMS Pro</h2>
                    <p>You must have <a href="https://wordpress.org/plugins/tutor/" target="_blank">Tutor LMS </a> Free version installed and activated on this website in order to use Tutor LMS Pro.</p>
                    <a href="https://www.themeum.com/docs/tutor-introduction/" target="_blank">Learn more about Tutor LMS</a>
                </div>
                <div class="tutor-install-notice-button">
                    <a class="install-tutor-button button button-primary" data-slug="tutor" href="<?php echo add_query_arg(array('action' => 'install_tutor_free'), admin_url()); ?>">Install Tutor LMS</a>
                </div>
            </div>
            <div id="tutor_install_msg"></div>
        </div>
		<?php
    }

	public function activate_tutor_free(){
		activate_plugin('tutor/tutor.php' );
	}


	public function install_tutor_plugin(){
		include(ABSPATH . 'wp-admin/includes/plugin-install.php');
		include(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');

		if ( ! class_exists('Plugin_Upgrader')){
			include(ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php');
		}
		if ( ! class_exists('Plugin_Installer_Skin')) {
			include( ABSPATH . 'wp-admin/includes/class-plugin-installer-skin.php' );
		}

		$plugin = 'tutor';

		$api = plugins_api( 'plugin_information', array(
			'slug' => $plugin,
			'fields' => array(
				'short_description' => false,
				'sections' => false,
				'requires' => false,
				'rating' => false,
				'ratings' => false,
				'downloaded' => false,
				'last_updated' => false,
				'added' => false,
				'tags' => false,
				'compatibility' => false,
				'homepage' => false,
				'donate_link' => false,
			),
		) );

		if ( is_wp_error( $api ) ) {
			wp_die( $api );
		}

		$title = sprintf( __('Installing Plugin: %s'), $api->name . ' ' . $api->version );
		$nonce = 'install-plugin_' . $plugin;
		$url = 'update.php?action=install-plugin&plugin=' . urlencode( $plugin );

		$upgrader = new \Plugin_Upgrader( new \Plugin_Installer_Skin( compact('title', 'url', 'nonce', 'plugin', 'api') ) );
		$upgrader->install($api->download_link);
		die();
	}

	/**
	 * @param $admin_bar
	 *
	 * @return mixed
     *
     * Add admin bar links frontend page edit.
     *
     * @since v.1.4.6
	 */

	public function add_toolbar_items($admin_bar){
	    global $post;

	    $course_id = (int) sanitize_text_field(tutils()->array_get('post', $_GET));
	    $course_post_type = tutor()->course_post_type;

	    if (is_admin() && $post && $course_id && $post->post_type === $course_post_type) {
	        $forntend_course_edit_link = tutils()->course_edit_link($post->ID);
		    $admin_bar->add_menu( array(
			    'id'    => 'tutor-frontend-course-builder',
			    'title' => __( 'Edit With Frontend Course Builder', 'tutor-pro' ),
			    'href'  => $forntend_course_edit_link,
			    'meta'  => array(
				    'title' => __( 'Edit With Frontend Course Builder', 'tutor-pro' ),
				    'target' => '_blank',
			    ),
		    ) );
	    }

	    if (is_single() && $post && $post->post_type === $course_post_type ){
		    $forntend_course_edit_link = tutils()->course_edit_link($post->ID);
		    $admin_bar->add_menu( array(
			    'id'    => 'tutor-frontend-course-builder',
			    'title' => __( 'Edit With Frontend Course Builder', 'tutor-pro' ),
			    'href'  => $forntend_course_edit_link,
			    'meta'  => array(
				    'title' => __( 'Edit With Frontend Course Builder', 'tutor-pro' ),
				    'target' => '_blank',
			    ),
		    ) );
        }

	    return $admin_bar;
	}

}