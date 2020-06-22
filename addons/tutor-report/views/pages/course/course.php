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
    $quiz_number = '';

    if($current_id){
        $quiz_number = $wpdb->get_var(
            "SELECT COUNT(ID) FROM {$wpdb->posts}
            WHERE post_parent IN (SELECT ID FROM {$wpdb->posts} WHERE post_type ='topics' AND post_parent = {$current_id} AND post_status = 'publish')
            AND post_type ='tutor_quiz' 
            AND post_status = 'publish'");
    }

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

<div class="tutor-report-single-wrap">
    
    <div class="tutor-report-single-title"><?php echo get_the_title($current_id); ?></div>
    
    <div class="tutor-report-single-subtitle">
        <div class="tutor-report-date">
            <span><?php _e('Created:' ,'tutor-pro'); ?> <?php echo get_the_date('d M, Y', $current_id); ?></span>
            <span><?php _e('Last Update:' ,'tutor-pro'); ?> <?php echo get_the_modified_date('d M, Y', $current_id); ?></span>
        </div>
        <div class="tutor-report-action">
            <a href="<?php echo get_edit_post_link($current_id); ?>" target="_blank"><?php _e('Edit with Builder', 'tutor-pro'); ?></a>
            <a href="<?php the_permalink($current_id); ?>" target="_blank"><?php _e('View Course', 'tutor-pro'); ?></a>
        </div>
    </div>
    
    <div class="tutor-report-single-data-wrap">
        <div class="tutor-report-single-data">
            <div class="tutor-report-info">
                <strong><?php echo tutor_utils()->get_lesson_count_by_course($current_id); ?></strong>
                <div><?php _e('Lesson', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="tutor-report-single-data">
            <div class="tutor-report-info">
                <strong><?php echo $quiz_number; ?></strong>
                <div><?php _e('Total Quiz', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="tutor-report-single-data">
            <div class="tutor-report-info">
                <strong><?php echo tutor_utils()->get_assignments_by_course($current_id)->count; ?></strong>
                <div><?php _e('Assignment', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="tutor-report-single-data">
            <div class="tutor-report-info">
                <strong><?php echo tutor_utils()->count_enrolled_users_by_course($current_id); ?></strong>
                <div><?php _e('Total Learners', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="tutor-report-single-data">
            <div class="tutor-report-info">
                <strong><?php echo $complete_data; ?></strong>
                <div><?php _e('Course Completed', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="tutor-report-single-data">
            <div class="tutor-report-info">
                <strong>
                    <?php 
                        $total_student = tutor_utils()->count_enrolled_users_by_course($current_id);
                        echo $total_student - $complete_data;
                    ?>
                </strong>
                <div><?php _e('Course Continue', 'tutor-pro'); ?></div>
            </div>
        </div>
        <div class="tutor-report-single-data">
            <div class="tutor-report-info">
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


<div class="tutor-report-single-wrap">
    <div class="tutor-report-single-graph">
        Graph
    </div>
    <div class="tutor-report-single-information">
        <div>
            <div><i class="tutor-icon-calendar"></i></div>
            <div>
                <?php

                $group_ids = $wpdb->get_var("SELECT SUM(meta.meta_value) FROM {$wpdb->posts} AS posts
                    LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
                    WHERE meta.meta_key = '_order_total'
                    AND posts.post_type = 'shop_order'
                    AND meta.meta_key = '_tutor_order_for_course_id_{$current_id}'
                    AND posts.post_status IN ( '" . implode( "','", array( 'wc-completed' ) ) . "' )");



                    echo 'AA<pre>';
                    print_r($group_ids);
                    echo '</pre>AA';
                ?>
                <div><?php echo '$8,238'; ?></div>
                <div><?php _e('Total Earning' ,'tutor-pro'); ?></div>
            </div>
        </div>
        <div>
            <div><i class="tutor-icon-calendar"></i></div>
            <div>
                <div><?php echo '$8,238'; ?></div>
                <div><?php _e('Total Discount' ,'tutor-pro'); ?></div>
            </div>
        </div>
        <div>
            <div><i class="tutor-icon-calendar"></i></div>
            <div>
                <div><?php echo '$8,238'; ?></div>
                <div><?php _e('Refund' ,'tutor-pro'); ?></div>
            </div>
        </div>
    </div>
</div>



    <div class="report-stats">
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-mortarboard"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo tutor_utils()->get_lesson_count_by_course($current_id); ?></h3>
                    <p><?php _e('Lesson Number', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-graduate"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $quiz_number; ?></h3>
                    <p><?php _e('Quiz Number', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-open-book-1"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo tutor_utils()->get_assignments_by_course($current_id)->count; ?></h3>
                    <p><?php _e('Assignments Number', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-clipboard"></i>
                </div>
                <div class="box-stats-text">
                    <?php $total_student = tutor_utils()->count_enrolled_users_by_course($current_id); ?>
                    <h3><?php echo $total_student; ?></h3>
                    <p><?php _e('Course Enrolled', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-conversation-1"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $complete_data; ?></h3>
                    <p><?php _e('Course Completed', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-student"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo $total_student - $complete_data; ?></h3>
                    <p><?php _e('Course Continue', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-professor"></i>
                </div>
                <div class="box-stats-text">
                    <?php
                        $instructor = tutor_utils()->get_instructors_by_course($current_id);
                        $instructor = is_array($instructor) ? count($instructor) : 0;
                    ?>
                    <h3><?php echo $instructor; ?></h3>
                    <p><?php _e('Instructors', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
        <div class="report-stat-box">
            <div class="report-stat-box-body">
                <div class="box-icon">
                    <i class="tutor-icon-review"></i>
                </div>
                <div class="box-stats-text">
                    <h3><?php echo count(tutor_utils()->get_course_reviews($current_id)); ?></h3>
                    <p><?php _e('Total Reviews', 'tutor-pro'); ?></p>
                </div>
            </div>
        </div>
    </div>


    <div class="tutor-bg-white box-padding">
        <h3><?php echo get_the_title($current_id); ?></h3>
        <p><?php echo sprintf(__('Total Data  %d', 'tutor-pro'), $totalCount) ?></p>
        <table class="widefat tutor-report-table">
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

    <div>
        <div><?php _e('Search', 'tutor'); ?></div>
        <div>
            <input type="text" class="tutor-report-search" value="<?php echo $_search; ?>" autocomplete="off" placeholder="Search in here." />
            <button class="tutor-report-search-btn"><?php _e('Search' ,'tutor-pro'); ?></button>
        </div>
    </div>

    <div>
        <div><?php _e('Category', 'tutor'); ?></div>
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
        <div><?php _e('Sort By', 'tutor'); ?></div>
        <div>
            <select class="tutor-report-sort">
                <option <?php selected( $order_filter, 'ASC' ); ?>>ASC</option>
                <option <?php selected( $order_filter, 'DESC' ); ?>>DESC</option>
            </select>
        </div>
    </div>

    <div>
        <div><?php _e('Date', 'tutor'); ?></div>
        <div class="date-range-input">
            <input type="text" class="tutor_report_datepicker tutor-report-date" value="<?php echo $_date; ?>" autocomplete="off" placeholder="<?php echo date("Y-m-d", strtotime("last sunday midnight")); ?>" />
            <i class="tutor-icon-calendar"></i>
        </div>
    </div>

    <div class="tutor-bg-white box-padding">
        <h3><?php _e('Course List', 'tutor'); ?></h3>
        <table class="widefat tutor-report-table">
            <tr>
                <th><?php _e('Course', 'tutor-pro'); ?></th>
                <th><?php _e('Lesson', 'tutor-pro'); ?></th>
                <th><?php _e('Quiz', 'tutor-pro'); ?></th>
                <th><?php _e('Assignment', 'tutor-pro'); ?></th>
                <th><?php _e('Total Learners', 'tutor-pro'); ?></th>
                <th><?php _e('Earnings', 'tutor-pro'); ?></th>
                <th><?php _e('Action', 'tutor-pro'); ?></th>
            </tr>
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
                        <a href="<?php echo $course['link']; ?>" target="_blank"><?php _e('Link', 'tutor') ?></a>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <div class="tutor-report-count"><?php printf( __( 'Items %d of %d total', 'tutor-pro' ), $per_page, $total_items ); ?></div>

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

<?php }