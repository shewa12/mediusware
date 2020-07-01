<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $wpdb;

$salesCount = (int) $wpdb->get_var(
	"SELECT COUNT(ID)
	FROM {$wpdb->posts}
	JOIN {$wpdb->postmeta} meta 
	ON ID = meta.post_id
	WHERE meta.meta_key = '_tutor_enrolled_by_order_id' AND post_type = 'tutor_enrolled'"
);

$per_page = 50;
$total_items = $salesCount;
$current_page = isset( $_GET['paged'] ) ? $_GET['paged'] : 0;
$start =  max( 0,($current_page-1)*$per_page );

$sales_report = $wpdb->get_results(
	"SELECT ID as id, post_parent, post_author, post_status, post_date, meta.meta_value as order_id 
	FROM {$wpdb->posts}
	JOIN {$wpdb->postmeta} meta 
	ON ID = meta.post_id
	WHERE meta.meta_key = '_tutor_enrolled_by_order_id' AND post_type = 'tutor_enrolled'
	ORDER BY ID DESC LIMIT {$start},{$per_page};"
);
?>

<div class="tutor-list-wrap">
	<div class="tutor-list-header">
		<div class="heading">
			<?php _e('Sales', 'tutor-pro'); ?>
		</div>
		<p><?php echo sprintf(__('Total Order  %d', 'tutor-pro'), $salesCount) ?></p>
	</div>	
	
	<?php if (is_array($sales_report) && count($sales_report)) { ?>
    <table class="tutor-list-table">
		<thead>
			<tr>
				<th><?php _e('Order', 'tutor-pro'); ?> </th>
				<th><?php _e('Instructor', 'tutor-pro'); ?> </th>
				<th><?php _e('Course', 'tutor-pro'); ?> </th>
				<th><?php _e('Student', 'tutor-pro'); ?> </th>
				<th><?php _e('Date', 'tutor-pro'); ?> </th>
				<th><?php _e('Price', 'tutor-pro'); ?> </th>
				<th><?php _e('Status', 'tutor-pro'); ?> </th>
			</tr>
		</thead>
		<?php
		foreach ($sales_report as $report){
			$order = wc_get_order( $report->order_id );
			$order_items = $order->get_items();
			?>
				<tr>
					<?php edit_post_link( '#'.$report->order_id , '<td>', '</td>', $report->order_id, null ); ?>
					<td>
						<div class="instructor">
							<div class="instructor-thumb">
								<?php 
								$instructor = get_post_field( 'post_author', $report->post_parent );
								$user_info = get_userdata($instructor); 
								?>
								<span class="instructor-icon"><?php echo get_avatar($user_info->ID, 50); ?></span>
							</div>
							<div class="instructor-meta">
								<span class="instructor-name">
									<?php echo $user_info->display_name; ?> <a target="_blank" href="<?php echo admin_url('admin.php?page=tutor_report&sub_page=students&student_id='.$user_info->ID); ?>"><i class="tutor-icon-link"></i></a>
								</span>
							</div>
						</div>
					</td>
					<td>
						<?php echo get_the_title($report->post_parent); ?>
						<a href="<?php echo get_permalink($report->post_parent); ?>" target="_blank"><i class="tutor-icon-link"></i></a>
					</td>
					<td>
						<?php 
							$user = get_userdata($report->post_author);
							echo $user->display_name;
						?>
					</td>
					<td>
						<?php echo $report->post_date; ?>
					</td>
					<td><?php echo $order->get_total(); ?>(<?php echo $order->get_item_count(); ?>)</td>
					<td>
						<?php echo $report->post_status; ?>
					</td>
				</tr>
		<?php } ?>
	</table>
	<?php } else { ?>
		<h3><?php _e('No Sales Data Found!', 'tutor-pro'); ?></h3>
	<?php } ?>

	<div class="tutor-list-footer">
		<div class="tutor-report-count"></div>
        <div class="tutor-pagination">
		<?php
			echo paginate_links( array(
				'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_report&sub_page=sales&paged=%#%" ),
				'current' => max( 1, $current_page ),
				'total' => ceil($total_items/$per_page)
			) );
			?>
        </div>
    </div>

</div>