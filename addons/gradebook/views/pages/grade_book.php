<?php
$page = "final_gradebooks.php";

$sub_page = sanitize_text_field(tutils()->array_get('sub_page', $_GET));
if ($sub_page){
	$page = $sub_page.".php";
}
include TUTOR_GB()->path."views/pages/{$page}";
?>
