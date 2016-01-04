<?php
/**
 * CSS buttons
 *
 * @package Elgg.Core
 * @subpackage UI
 */
?>


/**
 * Topbar Search From
 */

.timeline-search-topbar {
/*	margin: 5px 25px 0 15px;*/
	height: 37px;
	width: 552;
}


.elgg-search.timeline-search-topbar input[type=text] {	
	border: 1px solid #ccc;
}

.homepage_search_link {

	position: absolute;
	bottom: 110px; 
	right: 8px;


}
/**************************************
Timeline branding user menu

**************************************/

#usermenubranding {
	padding-bottom: 1px;
	position: relative;
        top: -216px; /* TM: FIREFOX Move the whole top banner down from top bar */ 
        z-index: 1;
	margin: 0 auto;
	height: 38px;
	width: 990px;
	border-width: 1px 1px 2px;
	-webkit-border-radius: 0px 0px 3px 3px;
    -moz-border-radius: 0px 0px 3px 3px;
    border-radius: 0px 0px 3px 3px
}

.homepage_join_link {
	display:block;
	position: absolute;
	right: 298px;	
}

.homepage_color_link {
	display:block;
	position: absolute;
	right: 175px;

}

.homepage_profile_link {
	display:block;
	position: absolute;
	right: 60px;

}





/* <style>
/* **************************
	BUTTONS
************************** */



.tom-button + .tom-button {
	margin-left: 4px;
}


/* Base */
.tom-button {
	color: #333;
	font-weight: bold;
	text-decoration: none;
	width: auto;
	margin: 0;
	font-size: 11px;
	line-height: 16px;
	
	padding: 2px 6px;
	cursor: pointer;
	outline: none;
	z-index: 60000; /* To adjust the button aganist branding capaigne */
	text-align: center;
	white-space: nowrap;

	-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #fff;
	-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #fff;
	box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #fff;

	border: 1px solid #999;
	border-bottom-color: #888;

    background: #eee;
    background: -webkit-gradient(linear, 0 0, 0 100%, from(#f5f6f6), to(#e4e4e3));
    background: -moz-linear-gradient(#f5f6f6, #e4e4e3);
    background: -o-linear-gradient(#f5f6f6, #e4e4e3);
    background: linear-gradient(#f5f6f6, #e4e4e3);
}

.tom-button:hover {
	color:#333;
	text-decoration:none;
}

.tom-button:active {
	background: #ddd;
	border-bottom-color:#999;
	
	box-shadow: none;
	-webkit-box-shadow: none;
	-moz-box-shadow: none;
}

.tom-button.tom-state-disabled {
	background: #F2F2F2;
	border-color: #C8C8C8;
	color: #B8B8B8;
	cursor: default;
	
	box-shadow: none;
	-webkit-box-shadow: none;
	-moz-box-shadow: none;
}

/* Submit: This button should convey, "you're about to take some definitive action" */
.tom-button-submit {
	color: #fff !important;
    background: #5B74A8;
    background: -webkit-gradient(linear, 0 0, 0 100%, from(#637bad), to(#5872a7));
    background: -moz-linear-gradient(#637bad, #5872a7);
    background: -o-linear-gradient(#637bad, #5872a7);
    background: linear-gradient(#637bad, #5872a7);
	border-color: #29447E #29447E #1A356E;
	-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #8a9cc2;
	-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #8a9cc2;
	box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #8a9cc2;
	
}

.tom-button-submit:active {
	background: #4f6aa3;
	border-bottom-color: #29447e;
}

.tom-button-submit.tom-state-disabled {
	background: #ADBAD4;
	border-color: #94A2BF;
}


/* Delete: This button should convey "be careful before you click me" */
.tom-button-delete {
	background: #444; /*  #d14836;*/
	border: 1px solid #333;
	color: #eee !important;
	-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #999;
	-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #999;
	box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #999;
}

.tom-button-delete:active {
	background: #111;
}

.tom-button-delete.tom-state-disabled {
	background: #999;
	border-color: #888;
}

/* Special: This button should convey "please click me!" */
.tom-button-special {
	color:white !important;
    background: #69a74e;
    background: -webkit-gradient(linear, 0 0, 0 100%, from(#75ae5c), to(#67a54b));
    background: -moz-linear-gradient(#75ae5c, #67a54b);
    background: -o-linear-gradient(#75ae5c, #67a54b);
    background: linear-gradient(#75ae5c, #67a54b);
	border-color: #3b6e22 #3b6e22 #2c5115;
	-webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #98c286;
	-moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #98c286;
	box-shadow: 0 1px 0 rgba(0, 0, 0, 0.10), inset 0 1px 0 #98c286;
}

.tom-button-special:active {
	background:#609946;
	border-bottom-color:#3b6e22;
}

.tom-button-special.tom-state-disabled {
	background: #B4D3A7;
	border-color: #9DB791;
}

/* Other button modifiers */
.tom-button-dropdown {
	color: white;
	border:1px solid #71B9F7;
}

.tom-button-dropdown:after {
	content: " \25BC ";
	font-size: smaller;
}

.tom-button-dropdown:hover {
	background-color:#71B9F7;
}

.tom-button-dropdown.tom-state-active {
	background: #ccc;
	color: #333;
	border:1px solid #ccc;
}

.tom-button-large {
	font-size: 13px;
	line-height: 19px;
}


/***********************************************
	Timeline Tobar and site Navigation css
****************************************************/
#sitemenubranding {
	padding-bottom: 0px;
	position: relative;
  /*      top: -216px; */ /* TM: FIREFOX Move the whole top banner down from top bar */ 
        z-index: 998;
	margin: 0 auto;
	height: 30px;
	width: 990px;
	border-width: 1px 1px 2px;
	-webkit-border-radius: 0px 0px 3px 3px;
    -moz-border-radius: 0px 0px 3px 3px;
    border-radius: 0px 0px 3px 3px
    
   
}


a{ color:#4a5775; outline:none; text-decoration:none;}
ul,li{ list-style-type:none;}
p{ line-height:16px;}
.left{ float:left; }
.right{ float:right; }
.clear{ clear:both; }

#outers{
	margin:0;
	padding:0;
	width:100%;
	}
.wrappers{
	width:100%;
	padding:0;
	margin:0 auto;
	}
.inners{
	width:auto;
	padding:0 10px;
	margin:0;
	}
	
/* --------------------------------- main_navigation_area ------------------ */
.main_navigation{
	display:inline-block;
	width:100%;
	height:30px; /* original 55px; */
        z-index: 999;
        position: relative;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/nav_bar_bg.jpg) repeat-x;
	}
a.home_btn{
	float:left;
	display:block;
	width:21px;
	height:21px;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/home.jpg) no-repeat top center;
	text-indent:-999999px;
	margin:4px 0;   /* TM: home up or down  margin:15px 0; */
	padding:0 30px 0 0; /* TM:  padding:0 30px 0 0; */
	}
a.home_btn:hover{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/home.jpg) no-repeat bottom center;
	}
ul.top_navigation{
	display:block;
	float:left;
	margin:0;
	padding:0;
	}
ul.top_navigation li{
	display:inline;
	float:left;
	margin:0;
	padding:0;
	line-height:30px; /*TM: 55px;  */
	}
ul.top_navigation li.splitter{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/splitter.jpg) no-repeat;
	height:30px;  /*TM: 55px;  */
	width:2px;
	}
ul.top_navigation li a{
	display:block;
	float:left;
	margin:0;
	padding:0 16px; /* TM:   padding:0 16px; */
	color:#fff;
	font-size:14px;
	font-weight:bold;
	}
ul.top_navigation li a:hover{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/nav_bar_hover_bg.jpg) repeat-x;
        text-decoration: none;
	}

/* More > section on navigation section */

.top_navigation > li > ul {
        border:none;
        display:none;
        padding: 0;
        top: 30px; /*Moves the more buttons up and down 54 px */
        position: absolute;
}
.top_navigation > li:hover > ul {
	display: block;
}
.top_navigation > li > ul > li {
        display: block;
        float: none;
        line-height: 16px; /* line-height: 20px; */
        position: relative;
}
.top_navigation > li > ul > li a {
        background: #4E5C79;
        padding:8px;
        width:100%;
}
.top_navigation > li > ul > li a:hover {
        background: #6E7891;
}
.elgg-more {
        position: relative;
        display: block;
}
.elgg-more li {
        *margin-bottom: -5px !important; /*  *margin-bottom: -5px !important; */
}
.elgg-more ul{
        *margin-top: -4px !important; /* *margin-top: -4px !important; */
}
}


