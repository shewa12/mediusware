<?php

global $wpdb;

$course_type = tutor()->course_post_type;

if(isset($_GET['course_id'])){
    // single
    $all_data = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type ='{$course_type}' AND post_status = 'publish' ");
    $current_id = isset($_GET['course_id']) ? $_GET['course_id'] : (isset($all_data[0]) ? $all_data[0]->ID : '');

    $totalCount = (int) $wpdb->get_var("SELECT COUNT(ID) from {$wpdb->posts} WHERE post_parent = {$current_id} AND post_type = 'tutor_enrolled';");

    $per_page = 50;
    $total_items = $totalCount;
    $current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
    $start =  max( 0,($current_page-1)*$per_page );


    $lesson_type = tutor()->lesson_post_type;

    $course_completed = $wpdb->get_results(
            "SELECT ID, post_author, meta.meta_value as order_id from {$wpdb->posts} 
            JOIN {$wpdb->postmeta} meta 
            ON ID = meta.post_id
            WHERE post_type = 'tutor_enrolled' 
            AND meta.meta_key = '_tutor_enrolled_by_order_id'
            AND post_parent = {$current_id} 
            AND post_status = 'completed' 
            ORDER BY ID DESC LIMIT {$start},{$per_page};");

    // echo '<pre>';
    // print_r( $start );
    // echo '</pre>';

    $complete_data = 0;
    $course_single = array();
    if(is_array($course_completed) && !empty($course_completed)){
        $complete = 0;
        foreach ($course_completed as $data) {
            $var = array();
            $var['order_id'] = $data->order_id;
            $var['post_id'] = $current_id;
            $var['complete'] = tutor_utils()->get_course_completed_percent($current_id, $data->post_author);
            $var['user_id'] = $data->post_author;
            $course_single[] = $var;
            if($var['complete'] == 100){ $complete_data++; }
        }
    } else {
        $complete_data = 0;
    }
    ?>

<div class="tutor-list-wrap tutor-report-course-details">
    
    <div class="tutor-list-header"><div class="heading"><?php echo get_the_title($current_id); ?></div>
    
        <div class="header-meta">
            <div class="date">
                <span><?php _e('Created:' ,'tutor-pro'); ?> <strong><?php echo get_the_date('d M, Y', $current_id); ?></strong></span>
                <span><?php _e('Last Update:' ,'tutor-pro'); ?> <strong><?php echo get_the_modified_date('d M, Y', $current_id); ?></strong></span>
            </div>
            <div class="action">
                <a class="tutor-report-btn default" href="<?php echo get_edit_post_link($current_id); ?>" target="_blank"><?php _e('Edit with Builder', 'tutor-pro'); ?></a>
                <a class="tutor-report-btn primary" href="<?php the_permalink($current_id); ?>" target="_blank"><?php _e('View Course', 'tutor-pro'); ?></a>
            </div>
        </div>
    </div>
    
    <div class="course-details-wrap">
        <div class="course-details-item">
            <div class="info">
                <strong>
                    <?php 
                        $info_lesson = tutor_utils()->get_lesson_count_by_course($current_id);
                        echo $info_lesson;
                    ?>
                </strong>
                <div><?php _e('Lesson', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="course-details-item">
            <div class="info">
                <strong>
                    <?php 
                        $info_quiz = '';
                        if($current_id){
                            $info_quiz = $wpdb->get_var(
                                "SELECT COUNT(ID) FROM {$wpdb->posts}
                                WHERE post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_type='topics' AND post_parent = {$current_id} AND post_status = 'publish')
                                AND post_type ='tutor_quiz' 
                                AND post_status = 'publish'");
                        }
                        echo $info_quiz;
                    ?>
                </strong>
                <div><?php _e('Total Quiz', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="course-details-item">
            <div class="info">
                <strong>
                    <?php 
                        $info_assignment = tutor_utils()->get_assignments_by_course($current_id)->count; 
                        echo $info_assignment;
                    ?>
                </strong>
                <div><?php _e('Assignment', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="course-details-item">
            <div class="info">
                <strong>
                    <?php
                        $info_learners = tutor_utils()->count_enrolled_users_by_course($current_id);
                        echo $info_learners;
                    ?>
                </strong>
                <div><?php _e('Total Learners', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="course-details-item">
            <div class="info">
                <strong><?php echo $complete_data; ?></strong>
                <div><?php _e('Course Completed', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="course-details-item">
            <div class="info">
                <strong>
                    <?php 
                        $total_student = tutor_utils()->count_enrolled_users_by_course($current_id);
                        echo $total_student - $complete_data;
                    ?>
                </strong>
                <div><?php _e('Course Continue', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="course-details-item">
            <div class="info">
                <strong>
                    <?php 
                        $course_rating = tutor_utils()->get_course_rating($current_id);
                        tutor_utils()->star_rating_generator($course_rating->rating_avg);
                    ?>
                </strong>
                <div><?php printf('%d (%d %s)',$course_rating->rating_avg ,$course_rating->rating_count, __('Ratings' ,'tutor-pro')); ?></div>
            </div>
        </div>
    </div>

</div>


<div class="tutor-report-graph-earnings">
    <div class="tutor-list-wrap tutor-report-graph">
        <div class="tutor-list-header">
            <div class="heading">Sales Graph</div>
        </div>
        <div class="tutor-report-graph-wrap">
            <img src="https://image.prntscr.com/image/66IfKwLdTna_fzXfqJq4EQ.png" alt="Sales Graph">
        </div>
    </div>
    <div class="tutor-list-wrap tutor-report-earnings">
        <div class="tutor-list-header tutor-report-single-graph">
            <div class="heading">Earnings</div>
        </div>
        <div class="tutor-report-earnings-wrap">
            <div class="earnings-item">
                <div class="icon"><i class="tutor-icon-professor"></i></div>
                <div class="text">
                    <div>
                        <?php
                        $total_price = $wpdb->get_var("SELECT SUM(meta.meta_value) FROM {$wpdb->posts} AS posts
                            LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
                            LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
                            WHERE meta.meta_key = '_order_total'
                            AND posts.post_type = 'shop_order'
                            AND meta2.meta_key = '_tutor_order_for_course_id_{$current_id}'
                            AND posts.post_status IN ( '" . implode( "','", array( 'wc-completed' ) ) . "' )");
        
                            if (function_exists('wc_price')) {
                                echo wc_price($total_price);
                            } else {
                                echo '$'.$total_price;
                            }
                        ?>
                    </div>
                    <div><?php _e('Total Earning' ,'tutor-pro'); ?></div>
                </div>
            </div>
            <div class="earnings-item">
                <div class="icon"><i class="tutor-icon-graduate"></i></div>
                <div class="text">
                    <div>
                        <?php
                        $discount_price = $wpdb->get_var( "SELECT SUM(meta.meta_value) FROM {$wpdb->posts} AS posts
                            LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
                            LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
                            WHERE meta.meta_key = '_cart_discount'
                            AND posts.post_type = 'shop_order'
                            AND meta2.meta_key = '_tutor_order_for_course_id_{$current_id}'
                            AND posts.post_status IN ( '" . implode( "','", array( 'wc-completed' ) ) . "' )" );

                            if (function_exists('wc_price')) {
                                echo wc_price($discount_price);
                            } else {
                                echo '$'.$discount_price;
                            }
                        ?>
                    </div>
                    <div><?php _e('Total Discount' ,'tutor-pro'); ?></div>
                </div>
            </div>
            <div class="earnings-item">
                <div class="icon"><i class="tutor-icon-student"></i></div>
                <div class="text">
                    <div>
                        <?php
                        $refunded_price = $wpdb->get_var( "SELECT SUM(meta.meta_value) FROM {$wpdb->posts} AS posts
                            LEFT JOIN {$wpdb->posts} AS posts2 ON posts.ID = posts2.post_parent
                            LEFT JOIN {$wpdb->postmeta} AS meta ON posts2.ID = meta.post_id
                            WHERE meta.meta_key = '_refund_amount'
                            AND posts.post_type = 'shop_order'
                            AND posts.post_status IN ( '" . implode( "','", array( 'wc-refunded' ) ) . "' )" );

                            if (function_exists('wc_price')) {
                                echo wc_price($refunded_price);
                            } else {
                                echo '$'.$refunded_price;
                            }
                        ?>
                    </div>
                    <div><?php _e('Refund' ,'tutor-pro'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="tutor-list-wrap tutor-report-learners">
    <div class="tutor-list-header"><div class="heading"><?php _e('Learners' ,'tutor-pro'); ?></div></div>
    <div class="tutor-list-data">
        <?php
        $per_learner = 1;
        $learner_page = isset( $_REQUEST['lp'] ) ? absint( $_REQUEST['lp'] ) : 0;
        $start_learner =  max( 0,($learner_page-1)*$per_learner );

        $learner_items =$wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->posts} AS posts
            WHERE posts.post_type = 'tutor_enrolled'
            AND posts.post_status = 'completed'
            AND posts.post_parent = {$current_id}"
        );

        $learner_list = $wpdb->get_results( "SELECT ID, post_author, post_date, post_parent FROM {$wpdb->posts} AS posts
            WHERE posts.post_type = 'tutor_enrolled'
            AND posts.post_status = 'completed'
            AND posts.post_parent = {$current_id}
            ORDER BY ID DESC LIMIT {$start_learner},{$per_learner}");
        ?>
        <table class="tutor-list-table">
            <tr>
                <th><?php _e('ID', 'tutor-pro'); ?></th>
                <th><?php _e('Name', 'tutor-pro'); ?></th>
                <th><?php _e('Enroll Date', 'tutor-pro'); ?></th>
                <th><?php _e('Lesson', 'tutor-pro'); ?></th>
                <th><?php _e('Quiz', 'tutor-pro'); ?></th>
                <th><?php _e('Assignment', 'tutor-pro'); ?></th>
                <th><?php _e('Progress', 'tutor-pro'); ?></th>
            </tr>
            <?php foreach ($learner_list as $learner) { ?>
                <tr>
                    <td><?php echo $learner->ID; ?></td>
                    <td>
                        <div class="instructor">
                            <div class="instructor-thumb">
                                <?php $user_info = get_userdata($learner->post_author); ?>
                                <span class="instructor-icon"><?php echo get_avatar($user_info->ID, 50); ?></span>
                            </div>
                            <div class="instructor-meta">
                                <span class="instructor-name">
                                    <?php echo $user_info->display_name; ?> <a target="_blank" href="<?php echo tutor_utils()->profile_url($user_info->ID); ?>"><i class="fas fa-external-link-alt"></i></a>
                                </span>
                                <span class="instructor-email"><?php echo $user_info->user_email; ?></span>
                            </div>
                        </div>
                    </td>
                    <td><?php echo date('j M, Y - h:i a', strtotime($learner->post_date)); ?></td>
                    <td><?php echo $info_lesson; ?></td>
                    <td><?php echo $info_quiz; ?></td>
                    <td><?php echo $info_assignment; ?></td>
                    <td><?php echo tutor_utils()->get_course_completed_percent($current_id); ?>%</td>
                </tr>
            <?php } ?>
        </table>

        <!-- <?php printf( __('Items %s of %s total'), count($learner_list), $learner_items ); ?>
        <div class="tutor-pagination">
            <?php
            echo paginate_links( array(
                'base' => str_replace( $learner_page, '%#%', "admin.php?page=tutor_report&sub_page=course&course_id=".$current_id."&lp=%#%" ),
                'current' => max( 1, $learner_page ),
                'total' => ceil($learner_items/$per_learner)
            ) );
            ?>
        </div> -->

    </div>
    <div class="tutor-list-footer">
        <div class="tutor-report-count">
            <div class="tutor-report-count"><?php printf( __('Items <strong> %s </strong> of <strong> %s </strong> total'), count($learner_list), $learner_items ); ?></div>	
        </div>
        <div class="tutor-pagination">
            <?php
                echo paginate_links( array(
                    'base' => str_replace( $learner_page, '%#%', "admin.php?page=tutor_report&sub_page=course&course_id=".$current_id."&lp=%#%" ),
                    'current' => max( 1, $learner_page ),
                    'total' => ceil($learner_items/$per_learner)
                ) );
            ?>           
        </div>
    </div>
</div>


<div class="tutor-list-wrap tutor-report-mentors">
    <div class="tutor-list-header"><div class="heading"><?php _e('Mentors' ,'tutor-pro'); ?></div></div>
    <div class="tutor-list-data">
        <?php $instructors = tutor_utils()->get_instructors_by_course($current_id); ?>
        <table class="tutor-list-table">
            <tr>
                <th><?php _e('ID', 'tutor-pro'); ?></th>
                <th><?php _e('Name', 'tutor-pro'); ?></th>
                <th><?php _e('Rating', 'tutor-pro'); ?></th>
                <th><?php _e('Total Courses', 'tutor-pro'); ?></th>
                <th><?php _e('Total Learners', 'tutor-pro'); ?></th>
                <th></th>
            </tr>
            <?php 
            $count = 0;
            foreach ($instructors as $instructor) { 
                $count++;
                $authorTag = '';
                $instructor_crown_src = tutor()->url.'assets/images/crown.svg';
                if (get_post_field('post_author', $instructor->ID) == $instructor->ID) {
                    $authorTag = '<img src="'.$instructor_crown_src.'" />';
                }
                $user_info = get_userdata($instructor->ID);
                ?>
                <tr>
                    <td><?php echo $instructor->ID; ?> </td>
                    <td>
                        <div class="instructor">
                            <div class="instructor-thumb">
                                <span class="instructor-icon"><?php echo get_avatar($instructor->ID, 50); ?></span>
                            </div>
                            <div class="instructor-meta">
                                <span class="instructor-name">
                                    <?php echo $instructor->display_name.' '.$authorTag; ?>
                                </span>
                                <span class="instructor-email"><?php echo $user_info->user_email; ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php
                         $rating = tutor_utils()->get_instructor_ratings($instructor->ID);
                         tutor_utils()->star_rating_generator($rating->rating_avg);
                        ?>
                        <span class="instructor-rating"><?php printf( __('%s (%s Ratings)', 'tutor-pro'), $rating->rating_avg, $rating->rating_count ); ?></span>
                    </td>
                    <td><?php echo tutor_utils()->get_course_count_by_instructor($instructor->ID); ?></td>
                    <td><?php echo tutor_utils()->get_total_students_by_instructor($instructor->ID); ?></td>
                    <td>
                        <a class="tutor-report-btn default" target="_blank" href="<?php echo tutor_utils()->profile_url($instructor->ID); ?>"><?php _e('View Profile', 'tutor-pro'); ?> 
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>


<div class="tutor-list-wrap tutor-report-reviews">
    <div class="tutor-list-header"><div class="heading"><?php _e('Reviews' ,'tutor-pro'); ?></div></div>
    <div class="tutor-list-data">
        <table class="tutor-list-table">
            <tr>
                <th><?php _e('No', 'tutor-pro'); ?> </th>
                <th><?php _e('Name', 'tutor-pro'); ?> </th>
                <th><?php _e('Date', 'tutor-pro'); ?> </th>
                <th><?php _e('Rating & Feedback', 'tutor-pro'); ?> </th>
            </tr>
            <?php
                $count = 0;
                $per_review = 1;
                $review_page = isset( $_REQUEST['rp'] ) ? absint( $_REQUEST['rp'] ) : 0;
                $review_start =  max( 0,($review_page-1)*$per_review );
                $review_items = count(tutor_utils()->get_course_reviews($current_id));
                $total_reviews = tutor_utils()->get_course_reviews($current_id, $review_start, $per_review);

                foreach ($total_reviews as $review) {
                    $count++;
                ?>
                <tr>
                    <td><?php echo $count; ?></td>
                    <td>
                        <div class="instructor">
                            <div class="instructor-thumb">
                                <span class="instructor-icon"><?php echo get_avatar($review->user_id, 50); ?></span>
                            </div>
                            <div class="instructor-meta">
                                <span class="instructor-name"><?php echo $review->display_name; ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="dates">
                            <span><?php echo date('j M, Y', strtotime($review->comment_date)); ?></span><br>
                            <span><?php echo date('h:i a', strtotime($review->comment_date)); ?></td></span>
                        </div>
                    <td>
                        <div class="ratings-wrap">
                            <div class="ratings">
                                <?php tutor_utils()->star_rating_generator($review->rating); ?>
                                <span><?php echo $review->rating; ?></span>
                            </div>
                            <div class="review">
                                <?php echo $review->comment_content; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
        
        <!-- <?php printf( __('Items %s of %s total'), count($total_reviews), $review_items ); ?>
        <div class="tutor-pagination">
            <?php
            echo paginate_links( array(
                'base' => str_replace( $review_page, '%#%', "admin.php?page=tutor_report&sub_page=course&course_id=".$current_id."&rp=%#%" ),
                'current' => max( 1, $review_page ),
                'total' => ceil($review_items/$per_review)
            ) );
            ?>
        </div> -->
    </div>
    <div class="tutor-list-footer">
        <div class="tutor-report-count"><?php printf( __('Items <strong> %s </strong> of <strong> %s </strong> total'), count($total_reviews), $review_items ); ?></div>
        <div class="tutor-pagination">
            <?php
                echo paginate_links( array(
                    'base' => str_replace( $review_page, '%#%', "admin.php?page=tutor_report&sub_page=course&course_id=".$current_id."&rp=%#%" ),
                    'current' => max( 1, $review_page ),
                    'total' => ceil($review_items/$per_review)
                ) );
            ?>            
        </div>
    </div>
</div>




    <!-- <div class="tutor-list-wrap tutor-bg-white box-padding ">
        <h3><?php echo get_the_title($current_id); ?></h3>
        <p><?php echo sprintf(__('Total Data  %d', 'tutor-pro'), $totalCount) ?></p>
        <table class="widefat tutor-report-table tutor-list-table">
            <tr>
                <th><?php _e('Order', 'tutor-pro'); ?> </th>
                <th><?php _e('Course', 'tutor-pro'); ?> </th>
                <th><?php _e('Student', 'tutor-pro'); ?> </th>
                <th><?php _e('Complete', 'tutor-pro'); ?> </th>
            </tr>
            <?php
            if (is_array($course_single) && count($course_single)){
                foreach ($course_single as $course){
                    $order = wc_get_order( $course['order_id'] );
                    $order_items = $order->get_items();
                    ?>
                    <tr>
                        <?php edit_post_link( '#'.$course['order_id'] , '<td>', '</td>', $course['order_id'], null ); ?>
                        <td>
                            <a target="_blank" href="<?php echo get_permalink($course['post_id']); ?>"><?php echo get_the_title($course['post_id']); ?></a>
                        </td>
                        <td>
                            <?php 
                                $user = get_userdata($course['user_id']);
                                echo $user->display_name; 
                            ?>
                        </td>
                        <td>
                            <?php echo $course['complete']; ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>

        <div class="tutor-pagination">
            <?php
            echo paginate_links( array(
                'base' => str_replace( $current_page, '%#%', "admin.php?page=tutor_report&sub_page=sales&paged=%#%" ),
                'current' => max( 1, $current_page ),
                'total' => ceil($total_items/$per_page)
            ) );
            ?>
        </div>
        
    </div> -->

<?php } else {

    // Pagination
    $per_page = 5;
    $current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
    $start =  max( 0,($current_page-1)*$per_page );

    // Order Filter
    $order_filter = isset($_GET['order']) ? $_GET['order'] : 'DESC';

    // Date Filter
    $date_filter = '';
    $_date = isset($_GET['date']) ? $_GET['date'] : ''; 
    if($_date){
        $date_filter = DateTime::createFromFormat('Y-m-d', $_date);
        $date_filter = "AND (post_date BETWEEN '{$date_filter->modify('-1 day')->format('Y-m-d')}' AND '{$date_filter->modify('+2 day')->format('Y-m-d')}')";
    }

    // Search Filter
    $search_sql = '';
    $_search = isset($_GET['search']) ? $_GET['search'] : ''; 
    if($_search){
        $search_sql = "AND {$wpdb->posts}.post_title LIKE '%{$_search}%' ";
    }

    // Category Filter
    $category_sql = '';
    $_cat = isset($_GET['cat']) ? $_GET['cat'] : ''; 
    if($_cat){
        $category_sql = "SELECT {$wpdb->posts}.ID
        FROM {$wpdb->posts}, {$wpdb->term_relationships}, {$wpdb->terms}
        WHERE {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
        AND {$wpdb->terms}.term_id = {$wpdb->term_relationships}.term_taxonomy_id
        AND {$wpdb->terms}.term_id = {$_cat}";
        $category_sql = "AND {$wpdb->posts}.ID IN ({$category_sql}) ";
    }

    $all_data = $wpdb->get_results(
        "SELECT ID, post_title FROM {$wpdb->posts} 
        WHERE post_type ='{$course_type}' 
        AND post_status = 'publish'
        {$search_sql}
        {$category_sql}
        {$date_filter}
        ORDER BY ID {$order_filter} LIMIT {$start},{$per_page};");

    $total_items = count($wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type ='{$course_type}' AND post_status = 'publish' {$search_sql} {$category_sql} {$date_filter};"));

    function quiz_number($current_id){
        global $wpdb;
        $quiz_number = $wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts}
            WHERE post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_type ='topics' AND post_parent = {$current_id} AND post_status = 'publish')
            AND post_type ='tutor_quiz' 
            AND post_status = 'publish'");
            return $quiz_number;
    }

    $complete_data = 0;
    $course_single = array();
    if(is_array($all_data) && !empty($all_data)){
        $complete = 0;
        foreach ($all_data as $data) {
            $var = array();
            $var['id'] = $data->ID;
            $var['link'] = get_permalink($data->ID);
            $var['course'] = $data->post_title;
            $var['lesson'] = tutor_utils()->get_lesson_count_by_course($data->ID);
            $var['quiz'] = quiz_number($data->ID);
            $var['assignment'] = tutor_utils()->get_assignments_by_course($data->ID)->count;
            $var['learners'] = tutor_utils()->count_enrolled_users_by_course($data->ID);

            $total_sales = 0;
            $product_id = get_post_meta($data->ID, '_tutor_course_product_id', true);
            if($product_id){
                $total_sales = $wpdb->get_var( "SELECT SUM( order_item_meta__line_total.meta_value) as order_item_amount 
                FROM {$wpdb->posts} AS posts
                INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__line_total ON (order_items.order_item_id = order_item_meta__line_total.order_item_id)
                    AND (order_item_meta__line_total.meta_key = '_line_total')
                INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__product_id_array ON order_items.order_item_id = order_item_meta__product_id_array.order_item_id 
                WHERE posts.post_type IN ( 'shop_order' )
                AND posts.post_status IN ( 'wc-completed' ) AND ( ( order_item_meta__product_id_array.meta_key IN ('_product_id','_variation_id') 
                AND order_item_meta__product_id_array.meta_value IN ('{$product_id}') ) );" );
            }
            if(function_exists('wc_price')){
                $total_sales = wc_price($total_sales);
            }
            $var['earnings'] = $total_sales;
            $course_single[] = $var;
        }
    } else {
        $complete_data = 0;
    }
    ?>
    <div class="tutor-report-content-menu">
        <div>
            <div>
                <input type="text" class="tutor-report-search" value="<?php echo $_search; ?>" autocomplete="off" placeholder="Search in here." />
                <button class="tutor-report-search-btn"><i class="fas fa-search"></i></button>
            </div>
        </div>

        <div>
            <div class="menu-label"><?php _e('Category', 'tutor'); ?></div>
            <div>
                <select class="tutor-report-category">
                    <?php
                        $terms = get_terms( 'course-category', array( 'hide_empty' => true) );
                        if (!empty($terms)) {
                            array_unshift($terms, (object)['term_id' => '', 'name' => 'All']);
                            foreach ($terms as $key => $val) {
                                echo '<option '.($_cat == $val->term_id ? "selected" : "").' value="'.$val->term_id.'">'.$val->name.'</option>';
                            }
                        }
                    ?>
                </select>
            </div>
        </div>

        <div>
            <div class="menu-label"><?php _e('Sort By', 'tutor'); ?></div>
            <div>
                <select class="tutor-report-sort">
                    <option <?php selected( $order_filter, 'ASC' ); ?>>ASC</option>
                    <option <?php selected( $order_filter, 'DESC' ); ?>>DESC</option>
                </select>
            </div>
        </div>

        <div>
            <div class="menu-label"><?php _e('Date', 'tutor'); ?></div>
            <div class="date-range-input">
                <input type="text" class="tutor_report_datepicker tutor-report-date" value="<?php echo $_date; ?>" autocomplete="off" placeholder="<?php echo date("Y-m-d", strtotime("last sunday midnight")); ?>" />
                <i class="tutor-icon-calendar"></i>
            </div>
        </div>
    </div>

    <div class="tutor-list-wrap tutor-report-course-list">
        <div class="tutor-list-header">
            <div class="heading"><?php _e('Course List', 'tutor'); ?></div>
        </div>
        <table class="tutor-list-table">
            <thead>
                <tr>
                    <th><?php _e('Course', 'tutor-pro'); ?></th>
                    <th><?php _e('Lesson', 'tutor-pro'); ?></th>
                    <th><?php _e('Quiz', 'tutor-pro'); ?></th>
                    <th><?php _e('Assignment', 'tutor-pro'); ?></th>
                    <th><?php _e('Total Learners', 'tutor-pro'); ?></th>
                    <th><?php _e('Earnings', 'tutor-pro'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($course_single as $key => $course) { ?>
                    <tr>
                        <td><?php echo $course['course']; ?></td>
                        <td><?php echo $course['lesson']; ?></td>
                        <td><?php echo $course['quiz']; ?></td>
                        <td><?php echo $course['assignment']; ?></td>
                        <td><?php echo $course['learners']; ?></td>
                        <td><?php echo $course['earnings']; ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=tutor_report&sub_page=course&course_id='.$course['id']); ?>"><?php _e('Details', 'tutor') ?></a>
                            <a href="<?php echo $course['link']; ?>" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <div class="tutor-list-footer">
            <div class="tutor-report-count">
                <?php printf( __('Items <strong> %s </strong> of <strong> %s </strong> total'), count($per_page), $total_items ); ?>
            </div>
            <div class="tutor-pagination">
                <?php
                echo paginate_links( array(
                    'base' => str_replace( 1, '%#%', "admin.php?page=tutor_report&sub_page=course&paged=%#%" ),
                    'current' => max( 1, $current_page ),
                    'total' => ceil($total_items/$per_page)
                ) );
                ?>
            </div>
        </div>

    </div>

<?php }