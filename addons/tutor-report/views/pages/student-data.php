<div class="tutor-report-student-data">
	
	<!-- .report-student-profile -->
	<div class="report-student-profile">
		<div class="report-student-profile-wrap">
			<div class="profile">
				<div class="thumb">
					<img src="<?php echo get_avatar_url($user_id, array('size' => 90)); ?>" alt="tutor student profile photo">
				</div>
				<div>
					<div class="name">Henry Thompson</div>
					<div class="meta">
						<div class="date">Created: <span>11 May, 2020</span></div>
						<div class="activity">Last Activity: <span>13 Hours ago</span></div>
					</div>
				</div>
				<div class="show-profile">
					<a href="#" class="btn show-profile-btn">View Profile</a>
				</div></div>
			<div class="profile-table">
				<table>
					<tbody>
						<tr>
							<th>
								<div><span>Display Name</span> <br> Henry Thompson</div>
							</th>
							<th>
								<div><span>User Name</span> <br> henry-thompson</div>
							</th>
							<th>
								<div><span>Email ID</span> <br> mehenrythompson@gmail.com <a href="mailto:"><i class="fas fa-external-link-alt"></i></a></div>
							</th>
							<th>
								<div><span>User ID</span> <br>342</div>
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
                <div class="box-icon">
                    <i class="tutor-icon-mortarboard"></i>
                </div>
                <div class="box-stats-text">
                    <h3>15</h3>
                    <p>Courses</p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-graduate"></i>
                </div>
                <div class="box-stats-text">
                    <h3>4</h3>
                    <p>Course Enrolled</p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-open-book-1"></i>
                </div>
                <div class="box-stats-text">
                    <h3>175</h3>
                    <p>Lessons</p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-clipboard"></i>
                </div>
                <div class="box-stats-text">
                    <h3>3</h3>
                    <p>Quiz</p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-conversation-1"></i>
                </div>
                <div class="box-stats-text">
                    <h3>65</h3>
                    <p>Questions</p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-professor"></i>
                </div>
                <div class="box-stats-text">
                    <h3>1</h3>
                    <p>Instructors</p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-student"></i>
                </div>
                <div class="box-stats-text">
                    <h3>2</h3>
                    <p>Students</p>
                </div>
            </div>
        </div>

        <div class="report-stat-box">
            <div class="report-stat-box-body">

                <div class="box-icon">
                    <i class="tutor-icon-review"></i>
                </div>
                <div class="box-stats-text">
                    <h3>22</h3>
                    <p>Reviews</p>
                </div>
            </div>
        </div>
	</div>
	<!-- /.report-stats -->
	
	<!-- .report-date-filter -->
	<div class="report-date-filter">
		<div class="menu-label"><?php _e('Date', 'tutor'); ?></div>
		<div class="date-range-input">
			<input type="text" class="tutor_report_datepicker tutor-report-date" value="<?php echo $_date; ?>" autocomplete="off" placeholder="<?php echo date("Y-m-d", strtotime("last sunday midnight")); ?>" />
			<i class="tutor-icon-calendar"></i>
		</div>
	</div>
	<!-- /.report-date-filter -->

	<!-- .report-course-list -->
	<div class="tutor-list-wrap report-course-list">
		<div class="tutor-list-header report-course-list-header">
			<div class="heading">Course List</div>
			<div class="status">
				<span class="complete">Complete</span>
				<span class="running">Running</span>
				<span class="incomplete">Incomplete</span>
			</div>
		</div>
		<div class="report-course-list-wrap">
			<table class="tutor-list-table">
				<thead>
					<tr>
						<th>#</th>
						<th>Course <i class="fas fa-sort-alpha-down"></i></th>
						<th>Enroll Date <i class="fas fa-sort-amount-up"></i></th>
						<th>Lesson</th>
						<th>Quiz</th>
						<th>Assignment</th>
						<th>Percentage</th>
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
			<div class="heading">Review</div>
		</div>
		<div class="report-review-wrap">
			<table class="tutor-list-table">
				<thead>
					<tr>
						<th>No</th>
						<th>Course</th>
						<th>Date </th>
						<th>Rating & Feedback </th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td><div class="course-title">Nutrition: Build Your Perfect Diet & Meal Plan</div></td>
						<td><div class="dates">11 May, 2020 <br><span>11:34 PM</span></div></td>
						<td>
							<div class="ratings-wrap">
								<div class="ratings">
									<span class="icon">
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
										<i class="far fa-star"></i>
										<i class="far fa-star"></i>
									</span>
									<span>4.6</span>
								</div>
								<div class="review">
									This guy is effing genious. I love his sense of humor. He is really good. This guy is effing genious. I love his sense of humor. He is really good. This guy is effing genious. I love his sense of humor. He is really good.
								</div>
							</div>							
						</td>
					</tr>
					<tr>
						<td>2</td>
						<td><div class="course-title">Nutrition: Build Your Perfect Diet & Meal Plan</div></td>
						<td><div class="dates">11 May, 2020 <br><span>11:34 PM</span></div></td>
						<td>
							<div class="ratings-wrap">
								<div class="ratings">
									<span class="icon">
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
										<i class="far fa-star"></i>
										<i class="far fa-star"></i>
									</span>
									<span>4.6</span>
								</div>
								<div class="review">
									This guy is effing genious. I love his sense of humor. He is really good. This guy is effing genious. I love his sense of humor. He is really good. This guy is effing genious. I love his sense of humor. He is really good.
								</div>
							</div>							
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="tutor-list-footer ">
			<div class="tutor-report-count">
				<div class="tutor-report-count">Items <strong> 5 </strong> of<strong> 15 </strong> total </div>	
			</div>
			<div class="tutor-pagination">
                <a class="prev page-numbers" href="#"><i class="fas fa-angle-left"></i></a>
				<a class="page-numbers" href="#">1</a>
				<span aria-current="page" class="page-numbers current">2</span>
				<a class="page-numbers" href="#">3</a>
				<a class="next page-numbers" href="#"><i class="fas fa-angle-right"></i></a>            
			</div>
		</div>
	</div>
	<!-- /.report-review -->
</div>