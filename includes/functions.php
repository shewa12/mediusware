<?php

/**
 * @param string $type
 * @param int $ref_id
 * @param int $user_id
 *
 * @return array|bool|null|object|void
 *
 * @since v.1.4.2
 */

if ( ! function_exists('get_generated_gradebook')) {
	function get_generated_gradebook( $type = 'final', $ref_id = 0, $user_id = 0 ) {
		global $wpdb;

		$user_id = tutils()->get_user_id( $user_id );

		$res = false;
		if ( $type === 'all' ) {
			$res = $wpdb->get_results( "SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND course_id = {$ref_id} 
					AND result_for != 'final' " );

		} elseif ( $type === 'quiz' ) {

			$res = $wpdb->get_row( "SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND quiz_id = {$ref_id} 
					AND result_for = 'quiz' " );

		} elseif ( $type === 'assignment' ) {
			$res = $wpdb->get_row( "SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND assignment_id = {$ref_id} 
					AND result_for = 'assignment' " );
		}elseif ($type === 'final'){
			$res = $wpdb->get_row( "SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE user_id = {$user_id} 
					AND course_id = {$ref_id} 
					AND result_for = 'final' " );

		}

		return $res;
	}
}


function get_assignment_gradebook_by_course($course_id = 0, $user_id = 0 ){
	global $wpdb;

	$user_id = tutils()->get_user_id($user_id);

	$res = $wpdb->get_row( "SELECT AVG({$wpdb->tutor_gradebooks_results}.earned_percent) as earned_percent,
                AVG({$wpdb->tutor_gradebooks_results}.grade_point) as earned_grade_point,
 grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
 
					WHERE user_id = {$user_id} AND result_for = 'assignment' " );

	return $res;
}

function get_quiz_gradebook_by_course($course_id = 0, $user_id = 0 ){
	global $wpdb;

	$user_id = tutils()->get_user_id($user_id);

	$res = $wpdb->get_row( "SELECT AVG({$wpdb->tutor_gradebooks_results}.earned_percent) as earned_percent,
                AVG({$wpdb->tutor_gradebooks_results}.grade_point) as earned_grade_point,
 grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
 
					WHERE user_id = {$user_id} AND result_for = 'quiz' " );

	return $res;

}


function get_gradebook_by_percent($percent = 0){
	global $wpdb;
	$gradebook = $wpdb->get_row("SELECT * FROM {$wpdb->tutor_gradebooks} 
		WHERE percent_from <= {$percent} 
		AND percent_to >= {$percent} ORDER BY gradebook_id ASC LIMIT 1  ");

	return $gradebook;
}

/**
 * @param $grade
 *
 * @return mixed|void
 *
 * Generate Grade HTML
 */

if ( ! function_exists('tutor_generate_grade_html')) {
	function tutor_generate_grade_html( $grade ) {
		if ( ! is_object( $grade ) ) {
			global $wpdb;

			$grade = $wpdb->get_row( "SELECT {$wpdb->tutor_gradebooks_results} .*, grade_config FROM {$wpdb->tutor_gradebooks_results} 
					LEFT JOIN {$wpdb->tutor_gradebooks} ON {$wpdb->tutor_gradebooks_results}.gradebook_id = {$wpdb->tutor_gradebooks}.gradebook_id
					WHERE gradebook_result_id = {$grade} " );
		}

		ob_start();

		if ( $grade ) {
			$config                       = maybe_unserialize( $grade->grade_config );
			$gradebook_enable_grade_point = get_tutor_option( 'gradebook_enable_grade_point' );
			$gradebook_show_grade_scale   = get_tutor_option( 'gradebook_show_grade_scale' );
			$gradebook_scale_separator    = get_tutor_option( 'gradebook_scale_separator' );
			$gradebook_scale              = get_tutor_option( 'gradebook_scale' );

			$grade_name = '';
			if ( ! empty($grade->grade_name)){
				$grade_name = $grade->grade_name;
			}else{
				$new_grade = get_gradebook_by_percent($grade->earned_percent);
				if ($new_grade){
					$grade_name = $new_grade->grade_name;
				}

				$config = maybe_unserialize( $new_grade->grade_config );
			}
			?>
			<span class="gradename-bg" style="background-color: <?php echo tutils()->array_get( 'grade_color', $config ); ?>;">
				<?php echo $grade_name; ?>
			</span>
			<?php
			$grade_point = ! empty($grade->earned_grade_point) ? $grade->earned_grade_point : $grade->grade_point;
			if ( $gradebook_enable_grade_point ) {
				echo "<span class='gradebook-earned-grade-point'>{$grade_point}</span>";
			}
			if ( $gradebook_show_grade_scale ) {
				echo "<span class='gradebook-scale-separator'>{$gradebook_scale_separator}</span><span class='gradebook_scale'>{$gradebook_scale}</span>";
			}
		}
		$output = apply_filters( 'tutor_gradebook_grade_output_html', ob_get_clean(), $grade );

		return $output;
	}
}