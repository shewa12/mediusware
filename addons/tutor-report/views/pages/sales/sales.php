<?php

global $wpdb;

$salesCount = (int) $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_type = 'tutor_enrolled' ;");

$per_page = 50;
$total_items = $salesCount;
$current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
$start =  max( 0,($current_page-1)*$per_page );

$sales_report = $wpdb->get_results("SELECT ID as id, post_parent, post_author, post_status, post_date, meta.meta_value as order_id 
							FROM {$wpdb->posts}
							JOIN {$wpdb->postmeta} meta 
							ON ID = meta.post_id
							WHERE meta.meta_key = '_tutor_enrolled_by_order_id' AND post_type = 'tutor_enrolled'
							ORDER BY ID DESC LIMIT {$start},{$per_page};");
?>

<div class="tutor-bg-white box-padding">

    <h3><?php _e('Sales', 'tutor-pro'); ?></h3>

    <p><?php echo sprintf(__('Total Order  %d', 'tutor-pro'), $salesCount) ?></p>

    <table class="widefat tutor-report-table">
        <tr>
            <th><?php _e('Order', 'tutor-pro'); ?> </th>
            <th><?php _e('Course', 'tutor-pro'); ?> </th>
            <th><?php _e('Price', 'tutor-pro'); ?> </th>
            <th><?php _e('Instructor', 'tutor-pro'); ?> </th>
            <th><?php _e('Student', 'tutor-pro'); ?> </th>
            <th><?php _e('Date', 'tutor-pro'); ?> </th>
			<th><?php _e('Status', 'tutor-pro'); ?> </th>
        </tr>
		<?php
		if (is_array($sales_report) && count($sales_report)){
			foreach ($sales_report as $report){
				$order = wc_get_order( $report->order_id );
				$order_items = $order->get_items();
				?>
                <tr>
					<!-- Order -->
                    <?php edit_post_link( '#'.$report->order_id , '<td>', '</td>', $report->order_id, null ); ?>

					<!-- Course -->
                    <td>
						<a target="_blank" href="<?php echo get_permalink($report->post_parent); ?>"><?php echo get_the_title($report->post_parent); ?></a>
					</td>

					<!-- Price -->
                    <td>
						<?php echo $order->get_total(); ?>(<?php echo $order->get_item_count(); ?>)
					</td>

					<!-- Instructor -->
                    <td>
						<?php 
							$instructor = get_post_field( 'post_author', $report->post_parent );
							$user = get_userdata($instructor);
							echo $user->display_name; 
						?>
					</td>

					<!-- Student -->
                    <td>
						<?php 
							$user = get_userdata($report->post_author);
							echo $user->display_name;
						?>
					</td>

					<!-- Date -->
                    <td>
						<?php echo $report->post_date; ?>
					</td>

					<!-- Status -->
                    <td>
						<?php echo $report->post_status; ?>
                    </td>
                </tr>
				<?php
			}
		}
		?>
    </table>

    <div class="tutor-pagination" >
		<?php
		echo paginate_links( array(
			'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_report&sub_page=sales&paged=%#%" ),
			'current' => max( 1, $current_page ),
			'total' => ceil($total_items/$per_page)
		) );
		?>
    </div>
</div>