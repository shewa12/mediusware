<?php
if ( ! defined( 'ABSPATH' ) )
exit;
?>
<div class="tutor-report-chart">
    <div class="tutor-list-wrap">
        <div class="tutor-report-graph-wrap">
            <?php
            echo '<div class="report-graph-title">';
            switch ($sub_page){
                case 'this_year';
                    echo sprintf(__("Showing results for the year %s", 'tutor-pro'), $currentYear);
                    break;
                case 'last_year';
                    echo sprintf(__("Showing results for the year %s", 'tutor-pro'), $lastYear);
                    break;
                case 'last_month';
                    echo sprintf(__("Showing results for the month of %s", 'tutor-pro'), date("F, Y", strtotime($start_date)));
                    break;
                case 'this_month';
                    echo sprintf(__("Showing results for the month of %s", 'tutor-pro'), date("F, Y"));
                    break;
                case 'last_week';
                    echo sprintf(__("Showing results from %s to %s", 'tutor-pro'), $begin->format('d F, Y'), $end->format('d F, Y'));
                    break;
                case 'this_week';
                    echo sprintf(__("Showing results from %s to %s", 'tutor-pro'), $begin->format('d F, Y'), $end->format('d F, Y'));
                    break;
                case 'date_range';
                    echo sprintf(__("Showing results from %s to %s", 'tutor-pro'), $begin->format('d F, Y'), $end->format('d F, Y'));
                    break;
            }
            echo '</div>';

            if ($course_id){
                echo '<h4>'.__('Results for course : ', 'tutor-pro').get_the_title($course_id).'</h4>';
            }
            ?>

            <p class="text-muted">
                <?php _e('Total Enrolled Courses:', 'tutor-pro'); ?> <?php echo array_sum($chartData); ?>
                <span class="report-download-csv-icon">
                    <a href="<?php echo add_query_arg(array('tutor_report_action' => 'download_course_enrol_csv')); ?>"><i class="tutor-icon-file"></i> <?php _e('Download as CSV');
                    ?></a>
                </span>
            </p>

            <?php include TUTOR_REPORT()->path.'views/pages/students/graph/top_menu.php'; ?>

            <canvas id="myChart" style="width: 100%; height: 400px;"></canvas>
            <script>
                var ctx = document.getElementById("myChart").getContext('2d');
                var myChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode(array_keys($chartData)); ?>,
                        datasets: [{
                            label: 'Enrolled',
                            backgroundColor: '#3057D5',
                            borderColor: '#3057D5',
                            data: <?php echo json_encode(array_values($chartData)); ?>,
                            borderWidth: 2,
                            fill: false,
                            lineTension: 0,
                        }]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    min: 0, // it is for ignoring negative step.
                                    beginAtZero: true,
                                    callback: function(value, index, values) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            }]
                        },
                        legend: {
                            display: false
                        }
                    }
                });
            </script>
        </div>
    </div>
</div>
<?php
if (! $course_id){
	?>
	<div class="top-course-enrolled">
        <div class="tutor-list-wrap">
            <div class="tutor-list-header">
                <div class="heading"><?php _e('Highest enrolled courses', 'tutor-pro'); ?></div>
            </div>

            <table class="tutor-list-table ">
                <tr>
                    <th><?php _e('Course', 'tutor-pro'); ?></th>
                    <th><?php _e('Total Enrolled', 'tutor-pro'); ?></th>
                    <th><?php _e('Action', 'tutor-pro'); ?> </th>
                </tr>
                <?php
                foreach ($enrolledProduct as $course){
                    ?>
                    <tr>
                        <td><div class="course-link"><a href="<?php echo add_query_arg(array('course_id' => $course->ID)) ?>"><?php echo $course->post_title; ?></a> </div></td>
                        <td><?php echo $course->total_enrolled; ?></td>
                        <td><a class="tutor-report-btn default" href="<?php echo get_the_permalink($course->ID) ?>" target="_blank">View </a> </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
    </div>
        
<?php } ?>