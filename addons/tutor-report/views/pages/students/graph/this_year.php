<?php
if ( ! defined( 'ABSPATH' ) )
exit;

global $wpdb;
$currentYear = date('Y');

$enrolledQuery = $wpdb->get_results( 
    "SELECT COUNT(ID) as total_enrolled, MONTHNAME(post_date)  as month_name 
    FROM {$wpdb->posts} 
    WHERE post_type = 'tutor_enrolled' 
    AND YEAR(post_date) = {$currentYear} 
    GROUP BY MONTH (post_date) 
    ORDER BY MONTH(post_date) ASC ;"
);

$total_enrolled = wp_list_pluck($enrolledQuery, 'total_enrolled');
$months = wp_list_pluck($enrolledQuery, 'month_name');
$monthWiseEnrolled = array_combine($months, $total_enrolled);

$emptyMonths = array();
for ($m=1; $m<=12; $m++) {
	$emptyMonths[date('F', mktime(0,0,0,$m, 1, date('Y')))] = 0;
}
$chartData = array_merge($emptyMonths, $monthWiseEnrolled);

$enrolledProduct = $wpdb->get_results( 
    "SELECT COUNT(enrolled.ID) as total_enrolled, DATE(enrolled.post_date)  as date_format, course.ID, course.post_title 
    FROM {$wpdb->posts} enrolled
    LEFT JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
    WHERE enrolled.post_type = 'tutor_enrolled' 
    AND YEAR(enrolled.post_date) = {$currentYear} 
    GROUP BY course.ID
    ORDER BY total_enrolled DESC LIMIT 0,50 ;"
);


include TUTOR_REPORT()->path.'views/pages/students/graph/body.php';