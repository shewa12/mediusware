<?php
$check_api = tutor_zoom_check_api_connection();
$currentSubPage = ($check_api) ? 'meetings' : 'set_api';
$currentName = ($check_api) ? 'All Meetings' : 'Set Api';;
$subPages = array(
    'meetings' => __('All Meetings', 'tutor-pro'),
    'set_api' => __('Set API', 'tutor-pro'),
    'settings' => __('Settings', 'tutor-pro'),
    'help' => __('Help', 'tutor-pro'),
);

if (!empty($_GET['sub_page'])) {
    $currentSubPage = sanitize_text_field($_GET['sub_page']);
    if(!$check_api) {
        $currentSubPage = 'set_api';
    }
    $currentName = isset($subPages[$currentSubPage]) ? $subPages[$currentSubPage] : '';
}
?>

<div class="wrap">
    <div class="report-main-wrap">
        <div class="tutor-report-left-menus">
            <div class="tutor-report-title">
                <strong><?php _e('Zoom', 'tutor-pro'); ?></strong>
                <span>/ <?php echo $currentName; ?></span>
            </div>
            <div class="tutor-report-menu">
                <ul>
                    <?php
                    foreach ($subPages as $pageKey => $pageName) {
                        $activeClass = ($pageKey === $currentSubPage) ? 'active' : '';
                        echo "<li class='{$activeClass}'><a href='" . add_query_arg(array('page' => 'tutor_zoom', 'sub_page' => $pageKey), admin_url('admin.php')) . "'>{$pageName}</a></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>

        <div class="tutor-zoom-content">
            <?php
            $page = sanitize_text_field($currentSubPage);
            $view_page = TUTOR_ZOOM()->path . 'views/pages/';

            if (file_exists($view_page . "/{$page}.php")) {
                include $view_page . "/{$page}.php";
            }
            ?>
        </div>
    </div>
</div>