<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

$cert_download_url = add_query_arg(array('tutor_action' => 'download_course_certificate', 'course_id' => get_the_ID()));
$show_certificate = (bool) tutils()->get_option('tutor_course_certificate_view');
$cert_show_url = add_query_arg(array('cert_hash' => $is_completed->completed_hash));
?>

<a href="<?php echo $cert_download_url; ?>" class="certificate-download-btn tutor-button bordered-button"><i class="tutor-icon-mortarboard"></i> <?php _e('Download Certificate', 'tutor-pro'); ?></a>

<?php if ($show_certificate) { ?>
	<style>
		.tutor-view-certificate { text-align:center; margin-top:10px; font-size:16px; text-transform:uppercase; }
	</style>
	<div class="tutor-view-certificate">
		<a href="<?php echo $cert_show_url; ?>"><i class="tutor-icon-detail-link"></i> <?php _e('View Certificate', 'tutor-pro'); ?></a>
	</div>
<?php } ?>