<?php
if (!defined('ABSPATH'))
    exit;
?>
<style type="text/css">
.tutor-zoom-card-title >h3 {
    font-weight: bold;
} 
.tutor-zoom-card-content {
    display: flex;
}    
.tutor-zoom-card {
    max-width: 620px;
    margin:auto;
}  
.tutor-zoom-card-body {
    background-color:  #fff;
    margin-bottom: 10px;
}  
.tutor-zoom-card-content {
    padding: 10px;
}
.card-icon {
    margin-right: 50px;
}
.card-content li {
    list-style: none;
    font-size: 16px;
    font-weight: bold;
}
.card-content p {
    font-size: 16px;

}
.open{
    display: block;
}
.close {
    display: none;
}
.zt-accordion {
    cursor: pointer;
}
</style>
    <div class="tutor-zoom-card">
        <div class="tutor-zoom-card-title">
            <h3><?php _e('FAQ')?></h3>
        </div>

        <div class="tutor-zoom-card-body">
            <div class="tutor-zoom-card-content">
                <div class="card-icon zt-accordion">icon</div>
                <div class="card-content">
                    <li>How to integrate Zoom</li>
                    <p class="close">
                        In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without 
                    </p>
                </div>
            </div>
        </div>        

        <div class="tutor-zoom-card-body">
            <div class="tutor-zoom-card-content">
                <div class="card-icon zt-accordion">icon</div>
                <div class="card-content">
                    <li>How to integrate Zoom</li>
                    <p class="close">
                        In publishing and graphic design, Lorem ipsum is a placeholder text commonly used to demonstrate the visual form of a document or a typeface without 
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!--tutor-zoom-card end-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>    
<script type="text/javascript">

$(document).ready(function(){
    $('.zt-accordion').on('click',function(){

        $(this).siblings("div").children("p").toggle(300);
        $(this).siblings("div").children("p").toggleClass('close');

    });
})   

</script>