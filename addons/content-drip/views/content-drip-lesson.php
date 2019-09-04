<?php
$post_id = get_the_ID();
$lesson_id = tutils()->array_get('lesson_id', $_POST);
if ( $lesson_id){
	$post_id = (int) sanitize_text_field($lesson_id);
}

var_dump($post_id);

?>

