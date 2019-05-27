<?php
namespace TUTOR_PRO;

if ( ! defined( 'ABSPATH' ) )
	exit;

class Admin{

	public function __construct() {
		add_action('admin_action_activate_tutor_free', array($this, 'activate_tutor_free'));

		add_action('admin_init', array($this, 'check_tutor_free_installed'));

		add_action('wp_ajax_install_tutor_plugin', array($this, 'install_tutor_plugin'));
		//add_action('admin_action_install_tutor_free', array($this, 'install_tutor_plugin'));
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
		<div class="notice notice-error">
			<p>
				You must have <a href="https://wordpress.org/plugins/tutor/" target="_blank">Tutor LMS </a> Free version installed and activated on this website in order to use Tutor LMS Pro. You <a href="<?php echo add_query_arg(array('action' => 'activate_tutor_free'), admin_url()); ?>">can activate Tutor LMS</a> .
			</p>
		</div>
		<?php
    }

	public function free_plugin_not_installed(){
		include( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		?>
        <div class="notice notice-error">
            <p>
                You must have <a href="https://wordpress.org/plugins/tutor/" target="_blank">Tutor LMS </a> Free version installed and activated on this website in order to use Tutor LMS Pro. You  .
            </p>


            <p>
                <a class="install-tutor-button button" data-slug="tutor" href="<?php echo add_query_arg(array('action' => 'install_tutor_free'),
                    admin_url()); ?>">Install Tutor LMS Now</a>
            </p>

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
	}

}