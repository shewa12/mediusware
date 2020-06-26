<div class="tutor-report-student-data">
	

	<?php 
		$_search = isset($_GET['search']) ? $_GET['search'] : '';
		$_student = isset($_GET['student_id']) ? $_GET['student_id'] : '';
	?>

	<?php if(!$_student){ ?>
		<!-- .report-date-filter -->
		<div class="report-date-filter">
			<div class="menu-label"><?php _e('User Search', 'tutor'); ?></div>
			<div class="date-range-input">
				<input type="text" class="tutor-report-search" value="<?php echo $_search; ?>"  placeholder="<?php _e('Search', 'tutor-pro'); ?>" />
				<i class="tutor-icon-magnifying-glass-1 tutor-report-search-action"></i>
			</div>
		</div>
		<!-- /.report-date-filter -->

		<!-- .report-review -->
		<div class="tutor-list-wrap report-review">
			<div class="tutor-list-header">
				<div class="heading"><?php _e('Students', 'tutor-pro'); ?></div>
			</div>
			<div class="report-review-wrap">
				<table class="tutor-list-table">
					<thead>
						<tr>
							<th><?php _e('ID', 'tutor-pro'); ?></th>
							<th><?php _e('Name', 'tutor-pro'); ?></th>
							<th><?php _e('Username', 'tutor-pro'); ?></th>
							<th><?php _e('Email', 'tutor-pro'); ?></th>
							<th><?php _e('Registered', 'tutor-pro'); ?></th>
							<th><?php _e('Course Taken', 'tutor-pro'); ?></th>
							<th><?php _e('Progress', 'tutor-pro'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$per_page = 1;
						$current_page = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
						$start =  max( 0,($current_page-1)*$per_page );
						$total_items = count(tutor_utils()->get_students(0, 10000, $_search));
						$students_list = tutor_utils()->get_students($start, $per_page, $_search);
						foreach ($students_list as $student) { ?>
							<tr>
								<td><?php echo $student->ID; ?></td>
								<td>
									<span class="instructor-icon"><?php echo get_avatar($student->ID, 30); ?></span>
									<span class="instructor-name"><?php echo $student->display_name; ?></span>
								</td>
								<td><?php echo $student->user_login; ?></td>
								<td><?php echo $student->user_email; ?></td>
								<td><?php echo date('j M, Y. h:i a', strtotime($student->user_registered)); ?></td>
								<td><?php echo count(tutor_utils()->get_enrolled_courses_ids_by_user($student->ID)); ?></td>
								<td>
									<a href="<?php echo admin_url('admin.php?page=tutor_report&sub_page=students&student_id='.$student->ID); ?>"><?php _e('Details', 'tutor') ?></a>
									<a target="_blank" href="<?php echo tutor_utils()->profile_url($student->ID); ?>"><?php _e('Profile', 'tutor-pro'); ?></a>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="tutor-list-footer ">
				<div class="tutor-report-count">
					<div class="tutor-report-count">
						<?php printf(__('Items <strong>%s</strong> of <strong>%s</strong> total"', 'tutor-pro'), $total_items, count($students_list)); ?>
					</div>	
				</div>
				<div class="tutor-pagination">
					<?php
						echo paginate_links( array(
							'base' => str_replace( 1, '%#%', "admin.php?page=tutor_report&sub_page=students&paged=%#%" ),
							'current' => max( 1, $current_page ),
							'total' => ceil($total_items/$per_page)
						));
					?>
				</div>
			</div>
		</div>
		<!-- // .report-review -->

	<?php } else { ?>
		<!-- .report-student-profile -->
		<?php $user_info = get_userdata($_student);
		
		// echo '<pre>';
		// print_r($user_info);
		// echo '</pre>';
		?>
		<div class="report-student-profile">
			<div class="report-student-profile-wrap">
				<div class="profile">
					<div class="thumb">
						<img src="<?php echo get_avatar_url($user_info->ID, array('size' => 90)); ?>" alt="<?php _e('tutor student profile photo', 'tutor-pro'); ?>">
					</div>
					<div>
						<div class="name"><?php echo $user_info->display_name; ?></div>
						<div class="meta">
							<div class="date"><?php _e('Created:', 'tutor-pro'); ?> <span><?php echo date('j M, Y. h:i a', strtotime($user_info->user_registered)); ?></span></div>
							<?php $last_time = get_user_meta($user_info->ID, 'wc_last_active', true); ?>
							<div class="activity"><?php _e('Last Activity:', 'tutor-pro'); ?> 
								<span>
									<?php
										if ($last_time) {
											echo human_time_diff( $last_time, current_time( 'timestamp', 1 ) ).' '.__('Ago');
										} else {
											_e('Never Login Before', 'tutor-pro');
										}
									?>
								</span>
							</div>
						</div>
					</div>
					<div class="show-profile">
						<a target="_blank" href="<?php echo tutor_utils()->profile_url($user_info->ID) ?>" class="btn show-profile-btn"><?php _e('View Profile', 'tutor-pro'); ?></a>
					</div></div>
				<div class="profile-table">
					<table>
						<tbody>
							<tr>
								<th>
									<div><span><?php _e('Display Name', 'tutor-pro'); ?></span> <br> <?php echo $user_info->display_name; ?></div>
								</th>
								<th>
									<div><span><?php _e('User Name', 'tutor-pro'); ?></span> <br> <?php echo $user_info->user_login; ?></div>
								</th>
								<th>
									<div><span><?php _e('Email ID', 'tutor-pro'); ?></span> <br> <?php echo $user_info->user_email; ?> <a href="mailto:<?php echo $user_info->user_email;?>"><i class="fas fa-external-link-alt"></i></a></div>
								</th>
								<th>
									<div><span><?php _e('User ID', 'tutor-pro'); ?></span> <br><?php echo $user_info->ID;?></div>
								</th>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- /.report-student-profile -->
		
		<!-- .report-stats -->
		<div class="report-stats">
			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-mortarboard"></i></div>
					<div class="box-stats-text">
						<h3>
							<?php 
								$enrolled_course = tutor_utils()->get_enrolled_courses_by_user($user_info->ID);
								echo $enrolled_course->found_posts;
							?></h3>
						<p><?php _e('Enrolled Courses', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>

			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-graduate"></i></div>
					<div class="box-stats-text">
						<h3>
							<?php 
								$completed_course = tutor_utils()->get_completed_courses_ids_by_user($user_info->ID);
								echo count($completed_course);
							?>
						</h3>
						<p><?php _e('Completed Courses', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>

			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-open-book-1"></i></div>
					<div class="box-stats-text">
						<h3><?php echo ($enrolled_course->found_posts - count($completed_course)); ?></h3>
						<p><?php _e('Course Continue', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>

			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-review"></i></div>
					<div class="box-stats-text">
						<h3>
							<?php
								$review_items = count(tutor_utils()->get_reviews_by_user($user_info->ID));
								echo $review_items;
							?>
						</h3>
						<p><?php _e('Reviews Placed', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>

			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-clipboard"></i></div>
					<div class="box-stats-text">
						<h3>
							<?php
							$lesson = 0;
							$courses_id = tutor_utils()->get_enrolled_courses_ids_by_user($user_info->ID);
							foreach ($courses_id as $course) {
								$lesson += tutor_utils()->get_lesson_count_by_course($course);
							}
							echo $lesson;
							?>
						</h3>
						<p><?php _e('Total Lesson', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>

			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-professor"></i></div>
					<div class="box-stats-text">
						<h3><?php echo tutor_utils()->get_total_quiz_attempts($user_info->user_email); ?></h3>
						<p><?php _e('Take Quiz', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>

			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-student"></i></div>
					<div class="box-stats-text">
						<h3>
							<?php
								global $wpdb;
								$total_assignments = 0;
								if (!empty($courses_id)) {
									$str_course = implode(',', $courses_id);
									$total_assignments = $wpdb->get_var("SELECT COUNT(ID) FROM {$wpdb->postmeta} post_meta
											INNER JOIN {$wpdb->posts} assignment ON post_meta.post_id = assignment.ID AND post_meta.meta_key = '_tutor_course_id_for_assignments'
											where post_type = 'tutor_assignments' AND post_meta.meta_value IN ({$str_course}) ORDER BY ID DESC ");	
								}
								echo $total_assignments;
							?>
						</h3>
						<p><?php _e('Assignment', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>

			<div class="report-stat-box">
				<div class="report-stat-box-body">
					<div class="box-icon"><i class="tutor-icon-conversation-1"></i></div>
					<div class="box-stats-text">
						<h3>
						<?php
						global $wpdb;
						$total_discussion = $wpdb->get_var("SELECT COUNT(comment_ID) FROM {$wpdb->comments}
							WHERE comment_author = '{$user_info->user_login}' AND comment_type = 'tutor_q_and_a'");	
						echo $total_discussion;
						?>
						</h3>
						<p><?php _e('Total Discussion', 'tutor-pro'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<!-- /.report-stats -->

		<!-- .report-course-list -->
		<div class="tutor-list-wrap report-course-list">
			<div class="tutor-list-header report-course-list-header">
				<div class="heading"><?php _e('Course List', 'tutor-pro'); ?></div>
				<div class="status">
					<span class="complete"><?php _e('Complete', 'tutor-pro'); ?></span>
					<span class="running"><?php _e('Running', 'tutor-pro'); ?></span>
					<span class="incomplete"><?php _e('Incomplete', 'tutor-pro'); ?></span>
				</div>
			</div>
			<div class="report-course-list-wrap">
				<table class="tutor-list-table">
					<thead>
						<tr>
							<th>#</th>
							<th><?php _e('Course', 'tutor-pro'); ?></th>
							<th><?php _e('Enroll Date', 'tutor-pro'); ?></th>
							<th><?php _e('Lesson', 'tutor-pro'); ?></th>
							<th><?php _e('Quiz', 'tutor-pro'); ?></th>
							<th><?php _e('Assignment', 'tutor-pro'); ?></th>
							<th><?php _e('Percentage', 'tutor-pro'); ?></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>1</td>
							<td>Nutrition: Build Your Perfect Diet & Meal Plan <a href="#" class="course-link"><i class="fas fa-external-link-alt"></i></a></td>
							<td>11 May, 2020</td>
							<td><span class="complete">18</span><span class="total">/36</span></td>
							<td><span class="complete">05</span><span class="total">/12</span></td>
							<td><span class="complete">04</span><span class="total">/04</span></td>
							<td><div class="course-percentage" style="--percent: 50%;"></div></td>
							<td>50%</td>
							<td><a href="#" class="details-link"><i class="fas fa-angle-down"></i></a></td>
						</tr>
						<tr>
							<td>2</td>
							<td>Help Finding Information Online <a href="#" class="course-link"><i class="fas fa-external-link-alt"></i></a></td>
							<td>11 May, 2020</td>
							<td><span class="complete">18</span><span class="total">/36</span></td>
							<td><span class="complete">05</span><span class="total">/12</span></td>
							<td><span class="complete">04</span><span class="total">/04</span></td>
							<td><div class="course-percentage" style="--percent: 30%;"></div></td>
							<td>30%</td>
							<td><a href="#" class="details-link"><i class="fas fa-angle-down"></i></a></td>
						</tr>
						<tr>
							<td colspan="9">
								<table>
									<tr>
										<td class="detail">
											<div class="heading">Lesson</div>
											<div class="status">
												<span class="complete">How To Naturally Increase Testosterone </span><br>
												<span class="complete">5 Best Supplements To Boost Immunity</span><br>
												<span class="running">Even More Dieting Tips And Strategies</span><br>
												<span class="running">Intermittent Fasting</span><br>
												<span class="incomplete">Gluten Free Diet Explained</span><br>
												<span class="incomplete">Gluten Free Diet Explained</span><br>
											</div>
										</td>
										<td class="detail">
											<div class="heading">Quiz</div>
											<div class="status">
												<span class="complete">How To Naturally Increase Testosterone </span><br>
												<span class="complete">5 Best Supplements To Boost Immunity</span><br>
												<span class="running">Even More Dieting Tips And Strategies</span><br>
												<span class="running">Intermittent Fasting</span><br>
												<span class="incomplete">Gluten Free Diet Explained</span><br>
											</div>
										</td>
										<td class="detail">
											<div class="heading">Assignment</div>
											<div class="status">
												<span class="complete">How To Naturally Increase Testosterone </span><br>
												<span class="running">5 Best Supplements To Boost Immunity</span><br>
												<span class="incomplete">Intermittent Fasting</span><br>
											</div>
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td>3</td>
							<td>An Ugly Myspace Profile Will Sure Ruin Your Reputation <a href="#" class="course-link"><i class="fas fa-external-link-alt"></i></a></td>
							<td>11 May, 2020</td>
							<td><span class="complete">18</span><span class="total">/36</span></td>
							<td><span class="complete">05</span><span class="total">/12</span></td>
							<td><span class="complete">04</span><span class="total">/04</span></td>
							<td><div class="course-percentage" style="--percent: 70%;"></div></td>
							<td>70%</td>
							<td><a href="#" class="details-link"><i class="fas fa-angle-down"></i></a></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<!-- /.report-course-list -->

		<!-- .report-review -->
		<div class="tutor-list-wrap report-review">
			<div class="tutor-list-header">
				<div class="heading"><?php _e('Review', 'tutor-pro'); ?></div>
			</div>
			<div class="report-review-wrap">
				<table class="tutor-list-table">
					<thead>
						<tr>
							<th><?php _e('No', 'tutor-pro'); ?></th>
							<th><?php _e('Course', 'tutor-pro'); ?></th>
							<th><?php _e('Date', 'tutor-pro'); ?></th>
							<th><?php _e('Rating & Feedback', 'tutor-pro'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							$count = 0;
							$per_review = 1;
							$review_page = isset( $_REQUEST['rp'] ) ? absint( $_REQUEST['rp'] ) : 0;
							$review_start =  max( 0,($review_page-1)*$per_review );
							$total_reviews = tutor_utils()->get_reviews_by_user($user_info->ID, $review_start, $per_review);
						?>
						<?php foreach ($total_reviews as $review) { $count++; ?>
							<tr>
								<td><?php echo $count; ?></td>
								<td><div class="course-title"><?php echo get_the_title($review->comment_post_ID); ?></div></td>
								<td><div class="dates"><?php echo date('j M, Y', strtotime($review->comment_date)); ?><br><span><?php echo date('h:i a', strtotime($review->comment_date)); ?></span></div></td>
								<td>
									<div class="ratings-wrap">
										<div class="ratings">
											<?php tutor_utils()->star_rating_generator($review->rating); ?>
											<span><?php echo $review->rating; ?></span>
										</div>
										<div class="review"><?php echo $review->comment_content; ?></div>
									</div>							
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<div class="tutor-list-footer ">
				<div class="tutor-report-count">
					<div class="tutor-report-count"><?php printf(__('Items <strong> %s </strong> of<strong> %s </strong> total','tutor-pro'), count($total_reviews), $review_items); ?></div>
				</div>
				<div class="tutor-pagination">
					<?php
						echo paginate_links( array(
							'base' => str_replace( $review_page, '%#%', "admin.php?page=tutor_report&sub_page=students&student_id=".$user_info->ID."&rp=%#%" ),
							'current' => max( 1, $review_page ),
							'total' => ceil($review_items/$per_review)
						) );
					?>
				</div>
			</div>
		</div>
		<!-- /.report-review -->
	<?php } ?>
</div>