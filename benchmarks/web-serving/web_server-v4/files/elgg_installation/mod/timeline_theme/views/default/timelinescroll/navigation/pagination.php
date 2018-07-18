<?php
$hidden_paginator = elgg_extract('hidden_paginator', $vars);
$time = time();

?>

<div class="timelinePaginatorWrapper">
    <div class="timelineLoadMoreBtn">
        <a href="javascript:void(0)" data-pagination="<?php echo $time ?>" class='next_pagination-link'>
            <?php echo elgg_echo('timeline:link:load_more') ?>
        </a>
        <span class="timelineAjaxLoader hidden">
            <img src="<?php echo $vars['url'] ?>mod/timeline_theme/graphics/ajax-loader.gif" alt="loading ..."/>
            <?php echo elgg_echo('timeline:link:loading'); ?>
        </span>
    </div>

    <div class="timelineHiddenPaginator" data-pager="<?php echo $time ?>">
        <?php echo $hidden_paginator; ?>
    </div>

</div>