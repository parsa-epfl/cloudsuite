<?php 
	$group = $vars["group"];
	$layout = $vars["layout"];
	
	$background_url = "";
	if($layout->enable_background == "yes"){
	//	$background_url = elgg_get_site_url() . "group_timeline_layout/get_background/" . $layout->getGUID() . "/" . $layout->time_updated . ".jpg";
	}
 
	if(!empty($background_url)) { ?>
		body {
			background-image: url(<?php echo $background_url; ?>) !important;
			background-attachment: fixed;
		}
	<?php 
	}

	if($layout->enable_colors == "yes") { ?>
		/* Widget Manager CSS */
		.elgg-module-widget > .elgg-head {
			border: 1px solid <?php echo $layout->border_color; ?>;
			background: <?php echo $layout->background_color; ?>; 
		}
		
		.elgg-module-widget > .elgg-body {
			border-left: 1px solid <?php echo $layout->border_color; ?>; 
			border-bottom: 1px solid <?php echo $layout->border_color; ?>; 
			border-right: 1px solid <?php echo $layout->border_color; ?>;
			background: <?php echo $layout->background_color; ?>;

			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
		}
		
		.elgg-module-widget > .elgg-head h3,
		.elgg-module-widget > .elgg-head h3 a {
			color: <?php echo $layout->title_color; ?>;
		}	
		
		/* Default Elgg group CSS */
		.elgg-module-group > .elgg-head {
			border: 1px solid <?php echo $layout->border_color; ?>;
			background: <?php echo $layout->background_color; ?>; 
			margin-bottom: 0px;
		}
		
		.elgg-module-group > .elgg-body {
			border-left: 1px solid <?php echo $layout->border_color; ?>; 
			border-bottom: 1px solid <?php echo $layout->border_color; ?>; 
			border-right: 1px solid <?php echo $layout->border_color; ?>;
			background: <?php echo $layout->background_color; ?>;
			padding: 10px;
		}
		
		.elgg-module-group > .elgg-head h3,
		.elgg-module-group > .elgg-head h3 a {
			color: <?php echo $layout->title_color; ?>;
		}
		
		<?php
	}