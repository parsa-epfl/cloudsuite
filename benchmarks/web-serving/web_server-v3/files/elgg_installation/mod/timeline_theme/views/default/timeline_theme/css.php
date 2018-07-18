<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */






?>
@charset "utf-8";
/* CSS Document */

*{	margin:0;
	padding:0;
	}
body{
	margin:0;
	padding:0;
	font-family:"Arial",Verdana,Sanserif,Helvetica;
	font-size:12px;
        height: 100%;
	background-color:#f7f7f7;
        *background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/footer_bg.jpg) repeat-x left bottom #f7f7f7;
	}

.clear{
	clear:both;}

img{ border:none; }
a{ color:#4a5775; outline:none; text-decoration:none;}
ul,li{ list-style-type:none;}
p{ line-height:16px;}
.left{ float:left; }
.right{ float:right; }
.clear{ clear:both; }

#outer{
	margin:0;
	padding:0;
	width:100%;
	}
.wrapper{
	width:960px;
	padding:0;
	margin:0 auto;
	}
.inner{
	width:auto;
	padding:0 10px;
	margin:0;
	}
.top_bar{
	background:#2b2929;
	height:81px;
	margin:0;
	padding:0;
	}
h1.logo{
	display:inline-block;
	margin:0;
	padding:0;
	width:218px;
	height:81px;
	}
h1.logo a{
	display:block;
	width:100%;
	height:100%;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/logo.jpg) no-repeat;
	text-indent:-999999px;
	}
.login_section{
	width:432px;
	margin:25px 0 0 ;
	}
input.login_txt{
	display:block;
	float:left;
	width:136px;
	height:13px;
	margin:0 7px 0 0;
	padding:4px;
	color:#acacac;
	font-size:11px;
	border:medium none;
	-webkit-border-radius: 0px;
	-moz-border-radius: 0px;
	border-radius: 0px;
	-webkit-box-sizing: content-box;
	-moz-box-sizing: content-box;
	box-sizing: content-box;
	}
input.login_btn{
	display:block;
	float:left;
	width:58px;
	height:21px;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/login_btn.jpg) no-repeat top center;
	border:none;
	color:#fff;
	font-size:11px;
	text-transform:uppercase;
	margin:0 7px 0 0;
	cursor:pointer;
        padding: 0px;
	}
input.login_btn:hover{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/login_btn.jpg) no-repeat bottom center;
	}
input.chek_box{
	display:block;
	float:left;
	margin:10px 0 0 151px;
	clear:left;
	}
* html INPUT.chek_box{
	display:inline;
	margin-left:145px;
	}
*:first-child+html input.chek_box{
	margin:5px 0 0 147px;
	}
label.forgot_txt{
	display:block;
	float:left;
	margin:10px 0 0 150px;
	color:#838282;
	font-size:11px;
	vertical-align:middle;
	cursor:pointer;
        font-weight: normal;
	}
*:first-child+html label.forgot_txt{
	margin:10px 0 0 150px;
	}

/* --------------------------------- main_navigation_area ------------------ */
.main_navigation{
	display:inline-block;
	width:100%;
	height:55px;
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
	margin:15px 0;
	padding:0 30px 0 0;
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
	line-height:55px;
	}
ul.top_navigation li.splitter{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/splitter.jpg) no-repeat;
	height:55px;
	width:2px;
	}
ul.top_navigation li a{
	display:block;
	float:left;
	margin:0;
	padding:0 16px;
	color:#fff;
	font-size:14px;
	font-weight:bold;
	}
ul.top_navigation li a:hover{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/nav_bar_hover_bg.jpg) repeat-x;
        text-decoration: none;
	}
.search_area{
	width:162px;
	margin:14px 20px 0 0 ;
	padding:0;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/search_box.jpg) no-repeat;
	}
input.search_txt{
	display:block;
	background:none;
	border:none;
	width:115px;
	height:14px;
	margin:5px 8px;
	padding:2px;
	color:#a0a0a0;
	font-size:11px;
	-webkit-border-radius: 0px;
	-moz-border-radius: 0px;
	border-radius: 0px;
	-webkit-box-sizing: content-box;
	-moz-box-sizing: content-box;
	box-sizing: content-box;
	}
* html INPUT.search_txt {
	margin-left:4px;
	}
input.search_btn{
	display:block;
	width:26px;
	height:27px;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/search_btn.jpg) no-repeat top left;
	border:none;
	cursor:pointer;
	}
input.search_btn:hover{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/search_btn.jpg) no-repeat bottom left;
	}
.main_container{
	width:100%;
/*	background:#f7f7f7 url(<?php //echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/container_bg.jpg) repeat-x top left; */
	padding:24px 0 22px ;
	display:inline-block;
        margin: -5px 0;
	}
.left_area{
	width:554px;
	}
.right_area{
	width:377px;
	margin:0 0 0 9px;
	}
.single_column{
	width:184px;
	margin:0 9px 0 0;
	}
.n_margin{ margin:0;}
.main_img{
	width:auto;
	}
p.main_para{
	display:inline-block;
	font-size:11px;
	color:#6c6c6c;
	padding:12px 0;
        margin-bottom: 0px;
	}
.blue{
	color:#344e8b;
	font-size:12px;
	font-weight:bold;
	}
.black{
	color:#333;
	font-weight:bold;
	font-size:12px;
	}
.header_bg{
	width:100%;
	height:27px;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/header_bg.jpg) repeat-x;
	line-height:27px;
	color:#fff;
	}
.header_bg h2{
	font-size:14px;
	font-weight:bold;
	padding:0 0 0 8px;
        color:#fff;
        line-height: inherit;
        height: 27px;
        line-height: 27px;
	}

/* Elgg profile to limit avatar show  */


#profile-owner-block .elgg-avatar-large img {
        width: 0px;  
        height: 0px;
}


/* Elgg profile ends */



/* timeline*/



article, aside, details, figcaption, figure,
footer, header, hgroup, menu, nav, section {
	display: block;
}

#footer-logo{
	height: 50px;
    margin: 0 auto;
    width: 109px;
	}

/* TM: Stop commentihg up to here. */

/* =Structure
----------------------------------------------- */

body {
	padding: 0em;
}


#page {
	margin: 0em auto;
	max-width: 990px;
/*	padding-top: 40px; */ /* TM: sawasawa */
         padding-top: 0px;
	/* top: -65px;*/
}


#branding hgroup {
	float: left;
    margin: 0 1.6%;
    width: 445px;
}

.menu{
	display: block;
    margin: 0 auto !important;
    width: 1000px;
	}

#primary {
	float: left;
	margin: 0 -26.4% 0 0;
	width: 100%;
}
#content {
	margin: 0px;
    width: 100%;
}
#secondary {
	float: left;
    margin-left: 10px;
    padding-top: 10px;
    width: 22%;
}

/* Timeline */

/* Header containter */

#timeline-hearder-container{
	margin: 0;
	padding: 0;
	border: 0;
	outline: 0;

}

#timeline-bar{
	width: 950px;
	margin: 0 auto;	
	}
	
	
	
.timeline-separator{
	background-image: url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/bg-timeline.jpg);
	background-repeat:repeat-y;
	background-position: 50% 50%;
	
	}	
	
	
#box-scrool-bar{
	height: 268px;
/*    margin-left: 1170px; *//* TM: moves the right scrool-bar "NOW" to the right or left */
    margin-right: 0 auto;; /* TM: moves the right scrool-bar "NOW" to the right or left */
    overflow: hidden;
    position: fixed;
    top: 75px;
	}	
	
.timeline-scroll-bar{
	float: right;
 /*   padding-left: 13px;  *//* Original */
 
     padding-left: 13px; /* TM: Move all bar to the right */
    padding-right: 0;
    padding-top: 4%;
    width: 116px;
}	
	
.timeline-scroll-bar ul{
	list-style-type: none;
	margin: 0;
    padding: 0;
	}		
	
	
.timeline-scroll-bar li{
	border-left: 5px solid #C8D1E2;
    float: none !important;
    margin: 1px;
    padding-left: 5px;
}		
	
.timeline-scroll-bar li:hover{
	border-left: 5px solid #627AAD;
    float: none !important;
    margin: 1px;
    padding-left: 5px;
	background-color: #E7EBF2 !important;

}			
	
.timeline-scroll-bar li a{
	color: #94A0C1 !important;
	font-size: 11px;
	line-height: 25px !important;
	text-shadow: 0px 0px 0px #000000 !important;
	padding: 0px !important;
	background-color: #E7EBF2 !important;

}
.timeline-scroll-bar li a:hover{
	color: #627AAD !important;
	background-color: #E7EBF2 !important;

}

li.anchor_post_current {
    border-color: #627AAD;
}


li.anchor_post_current a{
    color: #627AAD !important;
    font-weight: bold;
}






/* Headings */
h1,h2,h3,h4,h5,h6 {
	clear: both;
}
hr {
	background-color: #ccc;
	border: 0;
	height: 1px;
	margin-bottom: 1.625em;
}

/* Text elements */
p {
	margin-bottom: 1.625em;
	font-size: 12px;
}

br{
    display: none;
    margin: 0;
    padding: 0;
	}

ul, ol {
	margin: 0 0 1.625em 2.5em;
}
ul {
	list-style: square;
}
ol {
	list-style-type: decimal;
}
ol ol {
	list-style: upper-alpha;
}
ol ol ol {
	list-style: lower-roman;
}
ol ol ol ol {
	list-style: lower-alpha;
}
ul ul, ol ol, ul ol, ol ul {
	margin-bottom: 0;
}
dl {
	margin: 0 1.625em;
}
dt {
	font-weight: bold;
}
dd {
	margin-bottom: 1.625em;
}
strong {
	font-weight: bold;
}
cite, em, i {
	font-style: italic;
}
blockquote {
	border-left: 10px solid #C8D1E2;
    font-family: Georgia,"Bitstream Charter",serif;
    font-style: italic;
    font-weight: normal;
    margin: 0 1em;
    padding-left: 10px;
}
blockquote em, blockquote i, blockquote cite {
	font-style: normal;
}
blockquote cite {
	color: #666;
	font: 12px "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-weight: 300;
	letter-spacing: 0.05em;
	text-transform: uppercase;
}
pre {
	background: #f4f4f4;
	font: 13px "Courier 10 Pitch", Courier, monospace;
	line-height: 1.5;
	margin-bottom: 1.625em;
	overflow: auto;
	padding: 0.75em 1.625em;
}
code, kbd {
	font: 13px Monaco, Consolas, "Andale Mono", "DejaVu Sans Mono", monospace;
}
abbr, acronym, dfn {
	border-bottom: 1px dotted #666;
	cursor: help;
}
address {
	display: block;
	margin: 0 0 1.625em;
}
ins {
	background: #fff9c0;
	text-decoration: none;
}
sup,
sub {
	font-size: 10px;
	height: 0;
	line-height: 1;
	position: relative;
	vertical-align: baseline;
}
sup {
	bottom: 1ex;
}
sub {
	top: .5ex;
}



/* Links */
a {
	color: #3B5998;
	text-decoration: none;
}
a:focus,
a:active,
a:hover {
	text-decoration: underline;
}

/* Assistive text */
.assistive-text {
	position: absolute !important;
	clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
	clip: rect(1px, 1px, 1px, 1px);
}
#access a.assistive-text:active,
#access a.assistive-text:focus {
	background: #eee;
	border-bottom: 1px solid #ddd;
	color: #1982d1;
	clip: auto !important;
	font-size: 12px;
	position: absolute;
	text-decoration: underline;
	top: 0;
	left: 7.6%;
}





/* =Header
----------------------------------------------- */

#branding {
/*	border-top: 2px solid #bbb; */
	padding-bottom: 14px;
	 
	
	
/*	top: -45px; *//* TM: CROME BROWSER Move the whole top banner down from top bar */
  /*     top: -55px; */ /* TM: FIREFOX Move the whole top banner down from top bar */ 
        z-index: 2;
        width: 990px;
 /*       height: 570px;*/
          height: 470px !important;
/*       margin-left: -6px; */
	margin: 0 auto;
	background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #C4CDE0;
	border-width: 1px 1px 2px;
	-webkit-border-radius: 0px 0px 3px 3px;
    -moz-border-radius: 0px 0px 3px 3px;
    border-radius: 0px 0px 3px 3px
    
 
    
}



#site-title {
	float: left;
    margin-left: 145px;
    padding: 0.66em 0 0;
}

#site-title a {
	color: #3B5998;
	font-size: 30px;
	font-weight: bold;
	line-height: 36px;
	text-decoration: none;
}
#site-title a:hover,
#site-title a:focus,
#site-title a:active {
	color: #94A0C1;
}




#site-description {
	color: #7A7A7A;
  
    float: left;
    font-size: 14px;
    margin-left: 145px;

}

#site-featured{
	float: right;

    height: 123px;
    width: 440px;
}



/* ***************************************
	AVATAR ICONS
*************************************** */
.gutwa-avatar {
	position: relative;
	display: inline-block;
}
.gutwa-avatar > a > img {
	display: block;
}
.gutwa-avatar-medium > a > img {
	width: 100px;
	height: 100px;
}



/* ***************************************
	header ICONS
*************************************** */
#photo-header{
    background-color: #FFFFFF;
    border: 1px solid #CCCCCC;
    float: left;
    height: 125px;
    width: 125px;
    padding: 3px;
    position: absolute;
    top: 207px;
    	
	border-radius: 2px 2px 2px 2px;
	-webkit-border-radius: 2px 2px 2px 2px;
	-moz-border-radius: 2px 2px 2px 2px;

	}
	
/* TM: This is ID to load owner photo */
#photo-elggheader{
	background-color: #FFFFFF;
    border: 1px solid #CCCCCC;
    float: left;
 /*  margin-left: 165px; *//* TM: CHROME ok */
    margin-left: 25px;  
    height: 100px; 
    padding: 3px;
    position: absolute;
    z-index: 1000;  /* TM: Make sure icon is on top of everything else */
    
    
    top: 247px;
 /*   top: 277px; *//* TM: CHROME ok */
    width: 100px; 	
    
    
	border-radius: 2px 2px 2px 2px;
	-webkit-border-radius: 2px 2px 2px 2px;
	-moz-border-radius: 2px 2px 2px 2px;
     
	}
	/* TM: */
#photo-elggheader > a > img
{
width:100px; 
height:100px;
margin:auto;
display:block;
z-index: 1003;
}

/* TM: This is ID to load owner group photo */
#photo-elgggroupheader{
	background-color: #FFFFFF;
    border: 1px solid #CCCCCC;
    float: left;
 /*  margin-left: 165px; *//* TM: CHROME ok */
    margin-left: 25px;  
    height: 100px;
    width: 100px;  
    padding: 3px;
    position: absolute;
    z-index: 1000;  /* TM: Make sure group icon is on top of everything else */
    
    
    top: 247px;
 /*   top: 277px; *//* TM: CHROME ok */
    	
    
    
	border-radius: 2px 2px 2px 2px;
	-webkit-border-radius: 2px 2px 2px 2px;
	-moz-border-radius: 2px 2px 2px 2px;
     
	}
	/* TM: */
#photo-elgggroupheader > a > img
{
width: 100px; 
height: 100px;
margin:auto;
display:block;
z-index: 1003;
}







.featured-box .featured-size{
	border: 1px solid #CCCCCC;
    border-radius: 2px 2px 2px 2px;
    height: 80px !important;
    margin-top: 5px;
    padding: 1px;
    width: 95px !important;
}





.featured-box{
	float: left;
    height: 120px;
    overflow: hidden;
    width: 109px;
}

.featured-box .title-featured{
	color: #3B5998;
    display: block;
    font-size: 10px;
    line-height: 11px;
    padding-top: 5px;
}

#site-featured .featured-box a:hover{
	text-decoration: none;
	color: #94A0C1;
}

#branding img {
         
	height: auto;
	margin-bottom: -7px;
	width: 100%;
}



/* =Social Icons
-------------------------------------------------------------- */

#social-icons{
	float: right;
    height: 40px;
    margin-bottom: 10px;
    padding-right: 10px;
    padding-top: 20px;
    text-align: right;
    width: 420px;
}

#social-icons img {
	margin: 0px 4px 0px 0px;
	width: 5%;
}


#social-icons a img{
	margin: 0px 4px 0px 0px;
	opacity: 1;	
	-moz-transition: opacity 1s ease 0s;
	-webkit-transition: opacity 1s ease 0s;
	-o-transition: opacity 1s ease 0s;
	transition: opacity 1s ease 0s;
}


#social-icons a:hover img{
	opacity: 0.6;
}

h3.social-icons-title{
	float: left;
    font-size: 10px;
    letter-spacing: 0.1em;
	color: #999999;
    line-height: 2em;
    text-transform: uppercase;
    width: 200px;
}


/* =Category Top
-------------------------------------------------------------- */
.category-top{
	clear: left;
    float: left;
    height: 70px; /* TM: default height is   height: 72px; */
    overflow: hidden; 
    padding: 8px 0px; /* TM: The default was  padding: 15px 0px; */
    width: 430px;
	}
	
.category-top ul{
	list-style: none outside none;
    margin: 0;
    padding: 0;
	
	}


.category-top li{
	border-left: 5px solid #C8D1E2;
	float: left;
	margin: 2px;
	font-size: 12px;
	color: #94A0C1;
    padding-left: 5px;
	width: 90px;
	
	}



.category-top li:hover {
    border-left: 5px solid #627AAD;
    margin: 2px;
    padding-left: 5px;
}


.category-top li a {
    color: #94A0C1;
    font-size: 11px;
    line-height: 25px;
    padding: 0;
}

.category-top li a:hover {
    color: #627AAD;
	text-decoration: none;
}


.category-archive-meta{
	padding-left: 20px;
    width: 455px;
	
	}
	
	
/*  ELGG MORE SITE Navigation  */

.category-menu-site {
        z-index: 1;
}

.category-menu-site > li > a {
        font-weight: bold;
        padding: 3px 13px 0px 13px;
        height: 20px;
}

.category-menu-site > li > a:hover {
        text-decoration: none;
}

.category-menu-site-default {
        position: relative;;   /* absolute; make it move outside <hgroup> area */
        bottom: 0;
        left: 0;
        height: 23px; 
        
        
}

.category-menu-site-default > li {
        float: left; 
        margin-right: 1px;
        
}

.category-menu-site-default > li > a {
        color: white;
        
}

.category-menu-site > li > ul {
        display: none;
        background-color: white;
}

.category-menu-site > li:hover > ul {
        display: block;
        
}

.category-menu-site-default > .elgg-state-selected > a,
.category-menu-site-default > li:hover > a {
        background: white;
        color: #555;
        box-shadow: 2px -1px 1px rgba(0, 0, 0, 0.25);
        border-radius: 4px 4px 0 0;
}


.category-menu-site-more {
        position: relative;
        left: -1px;
        width: 100%;
        min-width: 150px;
        border: 1px solid #999;
        border-top: 0;
        border-radius: 0 0 4px 4px;
        box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.25);
}

.category-menu-site-more > li > a {
        background-color: white;
        color: #555;
        border-radius: 0;
        box-shadow: none;
        
        
}

.category-menu-site-more > li > a:hover,
.category-menu-site-more > li > a:focus {
        background: #4690D6;
        color: red;
        
        
}

.category-menu-site-more > li:last-child > a,
.category-menu-site-more > li:last-child > a:hover {
        border-radius: 0 0 4px 4px;
}
.category-more-more > a:before {
        content: "\25BC";
        font-size: smaller;
        margin-right: 4px;
}

/*	
.category-more-more	{
 z-index: 999; 	
}	
*/
/*  TM: Controls More <ul> list- items - NOTE: category-top-more class is in USE at the moment*/

.category-top-more{
	clear: left;
    float: left;
    height: 6px; /* category-top-more height + category-top height = 72px   */
/*    overflow: hidden; */
    padding: -6px 0px;
    width: 426px; /* TM: Default is 430 */
	}
	
.category-top-more ul{
/*
	list-style: none outside none;
	
*/	
    margin: 0;
    padding: 0;
	
		
	}



.category-top-more li{
	border-left: 5px solid #C8D1E2;
	float: left;
	margin: 2px;
	font-size: 12px;
	color: #94A0C1;
    padding-left: 5px;
	width: 90px;
	
	}



.category-top-more li:hover {
    border-left: 5px solid #627AAD;
    margin: 2px;
    padding-left: 5px;
}


.category-top-more li a {
    color: #94A0C1;
    font-size: 11px;
    line-height: 25px;
    padding: 0;
}

.category-top-more li a:hover {
    color: #627AAD;
	text-decoration: none;
}

/* =Menu  Check for deprection
-------------------------------------------------------------- */

#access {
	background: #3B5998; /* Show a solid color for older browsers */
	border-bottom: 1px solid #133783;
	clear: both;
	display: block;
	float: left;
	margin: 0 auto 6px;
	z-index: 8888;
	height: 40px;
	position: fixed;
	width: 100%;
}
#access ul {
	font-size: 13px;
	list-style: none;
	margin: 0 0 0 -0.8125em;
	padding-left: 0;
}
#access li {
	float: left;
	position: relative;
}
#access a {
	color: #eee;
	display: block;
	line-height: 3.15em;
	padding: 0 1.2125em;
	text-decoration: none;
	text-shadow: 1px 1px 1px #333333;
	-moz-text-shadow: 1px 1px 1px #333333;
	-webkit-text-shadow: 1px 1px 1px #333333;
	
}

#access ul ul {
	-moz-box-shadow: 0 3px 3px rgba(0,0,0,0.2);
	-webkit-box-shadow: 0 3px 3px rgba(0,0,0,0.2);
	box-shadow: 0 3px 3px rgba(0,0,0,0.2);
	display: none;
	float: left;
	margin: 0;
	position: absolute;
	top: 3.15em;
	left: 0;
	width: 188px;
	z-index: 99999;
}
#access ul ul ul {
	left: 100%;
	top: 0;
}
#access ul ul a {
	background: #3B5998;
	border-bottom: 1px solid #133783;
	color: #FFFFFF;
	font-size: 13px;
	font-weight: normal;
	height: auto;
	line-height: 1.4em;
	padding: 10px 10px;
	width: 168px;
}
#access li:hover > a,
#access ul ul :hover > a,
#access a:focus {
	background-color: #4B67A1;
}
#access li:hover > a,
#access a:focus {
	background-color: #4B67A1; /* Show a solid color for older browsers */
	color: #FFFFFF;
}
#access ul li:hover > ul {
	display: block;
}
#access .current_page_item > a,
#access .current_page_ancestor > a {
	font-weight: bold;
	background-color: #4B67A1;

}


#menu-item-55 a,
#menu-item-55 a:focus{
	background-image: url(images/down.png);
	background-repeat:no-repeat;
	background-position: 105% 50%;
	}

/* Search Form */
#branding #searchform {
	position: absolute;
    right: 1.6%;
    text-align: right;
	top: 22em;
	color: #FFFFFF;
}
#branding #searchform div {
	margin: 0;
}
#branding #s {
	float: right;
	-webkit-transition-duration: 400ms;
	-webkit-transition-property: width, background;
	-webkit-transition-timing-function: ease;
	-moz-transition-duration: 400ms;
	-moz-transition-property: width, background;
	-moz-transition-timing-function: ease;
	-o-transition-duration: 400ms;
	-o-transition-property: width, background;
	-o-transition-timing-function: ease;
	width: 72px;
}
#branding #s:focus {
	background-color: #f9f9f9;
	width: 196px;
}
#branding #searchsubmit {
	display: none;
}
#branding .only-search #searchform {
	top: 5px;
	z-index: 1;
}
#branding .only-search #s {
	background-color: #666;
	border-color: #000;
	color: #222;
}
#branding .only-search #s,
#branding .only-search #s:focus {
	width: 85%;
}
#branding .only-search #s:focus {
	background-color: #bbb;
}
#branding .with- #searchform {
	top: auto;
	bottom: -27px;
	max-width: 195px;
}
#branding .only-search + #access div {
	padding-right: 205px;
}

/* =Content
----------------------------------------------- */

#main {
	clear: both;
	padding: 1.625em 0 0;
	width: 980px;
}
.page-title {
	color: #666;
	font-size: 10px;
	font-weight: 500;
	letter-spacing: 0.1em;
	line-height: 2.6em;
	margin: 0 0 2.6em;
	text-transform: uppercase;
}
.page-title a {
	font-size: 12px;
	font-weight: bold;
	letter-spacing: 0;
	text-transform: none;
}

.page-in-title{
	color: #666;
	font-size: 10px;
	font-size: 11px;
	font-weight: 500;
	letter-spacing: 0.1em;
	line-height: 2.6em;
	margin: 0 0 2.6em;
	text-transform: uppercase;
	padding-left: 20px;
	}

.hentry,
.no-results {
	background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #C4CDE0;
	margin: 13px;
	width: 45%;
	padding: 10px;
	-webkit-border-radius: 3px 3px 3px 3px;
    -moz-border-radius: 3px 3px 3px 3px;
    border-radius: 3px 3px 3px 3px;
	border-width: 1px 1px 2px;
}

.no-results{
	margin: 100px auto 300px;
}

.align-left {
	float:left;
	clear: left;
	}

.align-right {
	float:right;
	clear: right;
	}
	

.corner-left{
	background-image: url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/corner-left.png);
    background-repeat: no-repeat;
    display: block;
    height: 15px;
	left: 450px;
    position: relative;
    top: 15px;
    width: 18px;
	}	
	
	
.corner-right{
    background-image: url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/corner-right.png);
    background-repeat: no-repeat;
    display: block;
    height: 15px;
    left: -28px;
    position: relative;
    top: 60px;
    width: 18px;
	}		

.hentry:last-child,
.no-results {
}
.blog .sticky .entry-header .entry-meta {
	clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
	clip: rect(1px, 1px, 1px, 1px);
	position: absolute !important;
}
.entry-title,
.entry-header .entry-meta {
	padding-right: 76px;
}
.entry-title {
	clear: both;
	color: #222;
    font-size: 20px;
    font-weight: bold;
    line-height: 0.9em;
    padding-bottom: 0.3em;
    padding-top: 15px;
}
.entry-title,
.entry-title a {
	color: #3B5998;
	text-decoration: none;
}
.entry-title a:hover,
.entry-title a:focus,
.entry-title a:active {
	color: #94A0C1;
	text-decoration:none;
}
.entry-meta {
	color: #666;
	clear: both;
	font-size: 12px;
	line-height: 18px;
	margin-bottom: 5px !important;
	padding: 5px;
}

footer.entry-meta {
	background-color: #EDEFF4;
}

.entry-meta a {

}
.single-author .entry-meta .by-author {
	display: none;
}
.entry-content,
.entry-summary {
	padding: 1.625em 0.4em 0;
	text-align: justify;
}
.entry-content h1,
.entry-content h2,
.comment-content h1,
.comment-content h2 {
	color: #94A0C1;
	font-weight: bold;
	font-size: 2em;
	margin: 0 0 .8125em;
}
.entry-content h3,
.comment-content h3 {
	color: #3B5998;
    font-size: 11px;
    font-weight: bold;
    letter-spacing: 0.1em;
    line-height: 2.6em;
    text-transform: uppercase;
}
.entry-content table,
.comment-content table {
	border-bottom: 1px solid #ddd;
	margin: 0 0 1.625em;
	width: 100%;
}
.entry-content th,
.comment-content th {
	color: #666;
	font-size: 10px;
	font-weight: 500;
	letter-spacing: 0.1em;
	line-height: 2.6em;
	text-transform: uppercase;
}
.entry-content td,
.comment-content td {
	border-top: 1px solid #ddd;
	padding: 6px 10px 6px 0;
}
.entry-content #s {
	width: 75%;
}
.comment-content ul,
.comment-content ol {
	margin-bottom: 1.625em;
}
.comment-content ul ul,
.comment-content ol ol,
.comment-content ul ol,
.comment-content ol ul {
	margin-bottom: 0;
}
dl.gallery-item {
	margin: 0;
}
.page-link {
	clear: both;
	display: block;
	margin: 0 0 1.625em;
}
.page-link a {
	background: #eee;
	color: #373737;
	margin: 0;
	padding: 2px 3px;
	text-decoration: none;
}
.page-link a:hover {
	background: #888;
	color: #fff;
	font-weight: bold;
}
.page-link span {
	margin-right: 6px;
}
.entry-meta .edit-link a,
.commentlist .edit-link a {
	background: #94A0C1;
	-moz-border-radius: 3px;
	border-radius: 3px;
	color: #FFF;
	float: right;
	font-size: 12px;
	line-height: 1.5em;
	font-weight: 300;
	text-decoration: none;
	padding: 0 8px;
}
.entry-meta .edit-link a:hover,
.commentlist .edit-link a:hover {
	background: #3B5998;
	color: #fff;
}
.entry-content .edit-link {
	clear: both;
	display: block;
}

.sub-image{
	float: left;
    margin: 4px;
    padding: 0;
    width: 30%;
	}

/* s */
.entry-content img,
.featured-box img,
.comment-content img,
.widget img,
.wp_bannerize_homepage img{
   border: 1px solid #C4CDE0;
	height: auto;
    max-width: 99%;
    padding: 2px;
	
	opacity: 1;
	-moz-transition: opacity 1s ease 0s;
	-webkit-transition: opacity 1s ease 0s;
	-o-transition: opacity 1s ease 0s;
	transition: opacity 1s ease 0s;
}




.entry-content img:hover,
.featured-box img:hover,
.comment-content img:hover,
.widget img:hover,
.wp_bannerize_homepage img:hover {
	opacity: 0.8;
}


img[class*="align"],
img[class*="wp--"] {
	height: auto; /* Make sure s with WordPress-added height and width attributes are scaled correctly */
}
img.size-full {
	max-width: 97.5%;
	width: auto; /* Prevent stretching of full-size s with height and width attributes in IE8 */
}
.entry-content img.wp-smiley {
	border: none;
	margin-bottom: 0;
	margin-top: 0;
	padding: 0;
}
img.alignleft,
img.alignright,
img.aligncenter {
	margin-bottom: 1.625em;
}
p img,
.wp-caption {
	margin-top: 0.4em;
}
.wp-caption {
	background: #eee;
	margin-bottom: 1.625em;
	max-width: 96%;
	padding: 9px;
}
.wp-caption img {
	display: block;
	margin: 0 auto;
	max-width: 98%;
}
.wp-caption .wp-caption-text,
.gallery-caption {
	color: #666;
	font-family: Georgia, serif;
	font-size: 12px;
}
.wp-caption .wp-caption-text {
	margin-bottom: 0.6em;
	padding: 10px 0 5px 40px;
	position: relative;
}
.wp-caption .wp-caption-text:before {
	color: #666;
	content: '\2014';
	font-size: 14px;
	font-style: normal;
	font-weight: bold;
	margin-right: 5px;
	position: absolute;
	left: 10px;
	top: 7px;
}
#content .gallery {
	margin: 0 auto 1.625em;
}
#content .gallery a img {
	border: none;
}
img#wpstats {
	display: block;
	margin: 0 auto 1.625em;
}
#content .gallery-columns-4 .gallery-item {
	width: 23%;
	padding-right: 2%;
}
#content .gallery-columns-4 .gallery-item img {
	width: 100%;
	height: auto;
}

/*  borders */
img[class*="align"],
img[class*="wp--"],
#content .gallery .gallery-icon img {/* Add fancy borders to all WordPress-added s but not things like badges and icons and the like */
	border: 1px solid #ddd;
	padding: 2px;
}
.wp-caption img {
	border-color: #eee;
}
a:focus img[class*="align"],
a:hover img[class*="align"],
a:active img[class*="align"],
a:focus img[class*="wp--"],
a:hover img[class*="wp--"],
a:active img[class*="wp--"],
#content .gallery .gallery-icon a:focus img,
#content .gallery .gallery-icon a:hover img,
#content .gallery .gallery-icon a:active img {/* Add some useful style to those fancy borders for linked s ... */
	background: #eee;
	border-color: #bbb;
}
.wp-caption a:focus img,
.wp-caption a:active img,
.wp-caption a:hover img {/* ... including captioned s! */
	background: #fff;
	border-color: #ddd;
}

/* Password Protected Posts */
.post-password-required .entry-header .comments-link {
	margin: 1.625em 0 0;
}
.post-password-required input[type=password] {
	margin: 0.8125em 0;
}
.post-password-required input[type=password]:focus {
	background: #f7f7f7;
}

/* Author Info */
#author-info {
	font-size: 12px;
	overflow: hidden;
}

.author-info{
	border-bottom: 1px solid #E5E5E5;
    height: 40px;
	font-size: 12px;
	}
	
	
.author-info .avatar {
	float: left;
    padding-right: 5px;

	}
	
	
.name-author{
	font-weight: bold;
	}
	
.date-post{
	color: #999999;
    font-size: 11px;
    line-height: 11px;
	}	
	
.singular #author-info {
	background: #f9f9f9;
	border-top: 1px solid #ddd;
	border-bottom: 1px solid #ddd;
	margin: 2.2em -35.6% 0 -35.4%;
	padding: 20px 35.4%;
}
.archive #author-info {
	padding-left: 20px;
    width: 450px;
	float: left;
	padding-top: 13px;
}
#author-avatar {
	float: left;
	margin-right: -78px;
}
#author-avatar img {
	background: #fff;
	-moz-border-radius: 3px;
	border-radius: 3px;
	-webkit-box-shadow: 0 1px 2px #bbb;
	-moz-box-shadow: 0 1px 2px #bbb;
	box-shadow: 0 1px 2px #bbb;
	padding: 3px;
}
#author-description {
	float: left;
    margin-left: 80px;
	padding: 5px;
		
	background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #C4CDE0;
	-webkit-border-radius: 3px 3px 3px 3px;
    -moz-border-radius: 3px 3px 3px 3px;
    border-radius: 3px 3px 3px 3px;
	border-width: 1px 1px 2px;
}
#author-description h2 {
	color: #94A0C1;
	font-size: 15px;
	font-weight: bold;
	margin: 5px 0 10px;

}

/* Comments link */
.entry-header .comments-link a {
	background: #eee url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/comment-bubble.png) no-repeat;
	color: #666;
	font-size: 13px;
	font-weight: normal;
	line-height: 35px;
	overflow: hidden;
	padding: 0 0 0;
	position: absolute;
	top: 1.5em;
	right: 0;
	text-align: center;
	text-decoration: none;
	width: 43px;
	height: 36px;
}
.entry-header .comments-link a:hover,
.entry-header .comments-link a:focus,
.entry-header .comments-link a:active {
	background-color: #1982d1;
	color: #fff;
	color: rgba(255,255,255,0.8);
}
.entry-header .comments-link .leave-reply {
	visibility: hidden;
}

/*
Post Formats Headings
To hide the headings, display: none the ".entry-header .entry-format" selector,
and remove the padding rules below.
*/
.entry-header .entry-format {
	color: #666;
	font-size: 10px;
	font-weight: 500;
	letter-spacing: 0.1em;
	line-height: 2.6em;
	position: absolute;
	text-transform: uppercase;
	top: -5px;
}
.entry-header hgroup .entry-title {
	padding-top: 15px;
}
article.format-aside .entry-content,
article.format-link .entry-content,
article.format-status .entry-content {
	padding: 0px;
}
.recent-posts .entry-header .entry-format {
	display: none;
}
.recent-posts .entry-header hgroup .entry-title {
	padding-top: 0;
}

/* Singular content styles for Posts and Pages */
.singular .hentry {
	width: 93%;
	position: relative;
}

.edit-link-single{
    float: left !important;
    left: 600px;
    position: relative;
    top: -77px;
    width: 100px;
	}
	
.edit-link-page{
    float: left !important;
    left: 600px;
    position: relative;
    top: -77px;
    width: 100px;
	}	

.singular.page .hentry {
	padding: 10px;
}
.singular .entry-title {
	color: #94A0C1;
	font-size: 36px;
	font-weight: bold;
	padding: 30px 0 0;
}
.singular .entry-title,
.singular .entry-header .entry-meta {
	padding-right: 0;
}
.singular .entry-header .entry-meta {
	left: 2px;
    position: absolute;
    top: 2px;
}
blockquote.pull {
	font-size: 21px;
	font-weight: bold;
	line-height: 1.6125em;
	margin: 0 0 1.625em;
	text-align: center;
}
.singular blockquote.pull {
	margin: 0 -22.25% 1.625em;
}
.pull.alignleft {
	margin: 0 1.625em 0 0;
	text-align: right;
	width: 33%;
}
.singular .pull.alignleft {
	margin: 0 1.625em 0 -22.25%;
}
.pull.alignright {
	margin: 0 0 0 1.625em;
	text-align: left;
	width: 33%;
}
.singular .pull.alignright {
	margin: 0 -22.25% 0 1.625em;
}
.singular blockquote.pull.alignleft,
.singular blockquote.pull.alignright {
	width: 33%;
}
.singular .entry-meta .edit-link a {
	bottom: auto;
    left: 655px;
    position: absolute;
    right: auto;
    top: 20px;
}


/* =Aside
----------------------------------------------- */

.format-aside .entry-title,
.format-aside .entry-header .comments-link {
	display: none;
}
.singular .format-aside .entry-title {
	display: block;
}
.format-aside .entry-content {
	padding: 0;
}
.singular .format-aside .entry-content {
	padding: 1.625em 0 0;
}


/* =Link
----------------------------------------------- */

.format-link .entry-title,
.format-link .entry-header .comments-link {
	display: none;
}
.singular .format-link .entry-title {
	display: block;
}
.format-link .entry-content {
	padding: 0;
}
.singular .format-link .entry-content {
	padding: 1.625em 0 0;
}


/* =Gallery
----------------------------------------------- */

.format-gallery .gallery-thumb {
	float: left;
	display: block;
	margin: .375em 1.625em 0 0;
}


.singular #gallery {
    margin: 0;
    width: 970px;
}

.post-gallery{
	background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #C4CDE0;
	margin: 0 13px;
	width: 932px;
	padding: 0px 10px;
	-webkit-border-radius: 3px 3px 3px 3px;
    -moz-border-radius: 3px 3px 3px 3px;
    border-radius: 3px 3px 3px 3px;
	border-width: 1px 1px 2px;
	
	}
	
.post-gallery-comment{
	background: none repeat scroll 0 0 #FFFFFF;
    border: 1px solid #C4CDE0;
 	float: left;
    margin: 13px 0 13px 13px;
	padding: 10px;
	-webkit-border-radius: 3px 3px 3px 3px;
    -moz-border-radius: 3px 3px 3px 3px;
    border-radius: 3px 3px 3px 3px;
	border-width: 1px 1px 2px;
	width: 690px;
	
	}	

.sidebar-post-gallery{
	float: left;
    width: 240px;
	padding-top: 5px;
	
	}
	
	
/* ----------- Gallery style -------------*/

.ngg-galleryoverview {
	clear: both !important;
    display: block !important;
    margin: 0 auto;
    overflow: hidden !important;
    width: 93% !important;
}

.ngg-galleryoverview .desc {
/* required for description */
   margin:0px 10px 10px 0px;
   padding:5px;
}

.ngg-gallery-thumbnail-box {
	float: left;
}

.ngg-gallery-thumbnail {
	loat: left !important;
    margin: 11px !important;
    text-align: center !important;
}

.ngg-gallery-thumbnail img {
	background-color:#FFFFFF !important;
	border:1px solid #CCCCCC !important;
	display:block !important;  
	margin:4px 0px 4px 5px !important;
	padding:4px !important;
	position:relative !important;
}

.ngg-gallery-thumbnail img:hover {
	background-color: #C4CDE0;
} 

.ngg-gallery-thumbnail span {
	/* Images description */
	font-size:90%;
	padding-left:5px;
	display:block;
}

.ngg-clear {
	clear: both;
}	


/* =Showcase
----------------------------------------------- */

h1.showcase-heading {
	color: #666;
	font-size: 10px;
	font-weight: 500;
	letter-spacing: 0.1em;
	line-height: 2.6em;
	text-transform: uppercase;
}



/* Featured post */
section.featured-post {
	float: left;
	margin: -1.625em -8.9% 1.625em;
	padding: 1.625em 8.9% 0;
	position: relative;
	width: 100%;
}
section.featured-post .hentry {
	border: none;
	color: #666;
	margin: 0;
}
section.featured-post .entry-meta {
	clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
	clip: rect(1px, 1px, 1px, 1px);
	position: absolute !important;
}

/* Small featured post */
section.featured-post .attachment-small-feature {
	float: right;
	height: auto;
	margin: 0 -8.9% 1.625em 0;
	max-width: 59%;
	position: relative;
	right: -15px;
}
section.featured-post.small {
	padding-top: 0;
}
section.featured-post .attachment-small-feature:hover,
section.featured-post .attachment-small-feature:focus,
section.featured-post .attachment-small-feature:active {
	opacity: .8;
}
article.feature-.small {
	float: left;
	margin: 0 0 1.625em;
	width: 45%;
}
article.feature-.small .entry-title {
	line-height: 1.2em;
}
article.feature-.small .entry-summary {
	color: #555;
	font-size: 13px;
}
article.feature-.small .entry-summary p a {
	background: #222;
	color: #eee;
	display: block;
	left: -23.8%;
	padding: 9px 26px 9px 85px;
	position: relative;
	text-decoration: none;
	top: 20px;
	width: 180px;
	z-index: 1;
}
article.feature-.small .entry-summary p a:hover {
	background: #1982d1;
	color: #eee;
	color: rgba(255,255,255,0.8);
}

/* Large featured post */
section.feature-.large {
	border: none;
	max-height: 288px;
	padding: 0;
	width: 100%;
}
section.feature-.large .showcase-heading {
	display: none;
}
section.feature-.large .hentry {
	border-bottom: none;
	left: 9%;
	margin: 1.625em 9% 0 0;
	position: absolute;
	top: 0;
}
article.feature-.large .entry-title a {
	background: #222;
	background: rgba(0,0,0,0.8);
	-moz-border-radius: 3px;
	border-radius: 3px;
	color: #fff;
	display: inline-block;
	font-weight: 300;
	padding: .2em 20px;
}
section.feature-.large:hover .entry-title a,
section.feature-.large .entry-title:hover a {
	background: #eee;
	background: rgba(255,255,255,0.8);
	color: #222;
}
article.feature-.large .entry-summary {
	display: none;
}
section.feature-.large img {
	display: block;
	height: auto;
	max-width: 117.9%;
	padding: 0 0 6px;
}

/* Featured Slider */
.featured-posts {
	border-bottom: 1px solid #ddd;
	display: block;
	height: 328px;
	margin: 1.625em -8.9% 20px;
	max-width: 1000px;
	padding: 0;
	position: relative;
	overflow: hidden;
}
.featured-posts .showcase-heading {
	padding-left: 8.9%;
}
.featured-posts section.featured-post {
	background: #fff;
	height: 288px;
	left: 0;
	margin: 0;
	position: absolute;
	top: 30px;
	width: auto;
}
.featured-posts section.featured-post.large {
	max-width: 100%;
	overflow: hidden;
}
.featured-posts section.featured-post {
	-webkit-transition-duration: 200ms;
	-webkit-transition-property: opacity, visibility;
	-webkit-transition-timing-function: ease;
	-moz-transition-duration: 200ms;
	-moz-transition-property: opacity, visibility;
	-moz-transition-timing-function: ease;
}
.featured-posts section.featured-post {
	opacity: 0;
	visibility: hidden;
}
.featured-posts #featured-post-1 {
	opacity: 1;
	visibility: visible;
}
.featured-post .feature-text:after,
.featured-post .feature-.small:after {
	content: ' ';
	background: -moz-linear-gradient(top, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,0)), color-stop(100%,rgba(255,255,255,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top, rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top, rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); /* Opera11.10+ */
	background: -ms-linear-gradient(top, rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); /* IE10+ */
	filter: progid:DXTransform.Microsoft.gradient( startColorstr='#00ffffff', endColorstr='#ffffff',GradientType=0 ); /* IE6-9 */
	background: linear-gradient(top, rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); /* W3C */
	width: 100%;
	height: 45px;
	position: absolute;
	top: 230px;
}
.featured-post .feature-.small:after {
	top: 253px;
}
#content .feature-slider {
	top: 5px;
	right: 8.9%;
	overflow: visible;
	position: absolute;
}
.feature-slider ul {
	list-style-type: none;
	margin: 0;
}
.feature-slider li {
	float: left;
	margin: 0 6px;
}
.feature-slider a {
	background: #3c3c3c;
	background: rgba(60,60,60,0.9);
	-moz-border-radius: 12px;
	border-radius: 12px;
	-webkit-box-shadow: inset 1px 1px 5px rgba(0,0,0,0.5), inset 0 0 2px rgba(255,255,255,0.5);
	-moz-box-shadow: inset 1px 1px 5px rgba(0,0,0,0.5), inset 0 0 2px rgba(255,255,255,0.5);
	box-shadow: inset 1px 1px 5px rgba(0,0,0,0.5), inset 0 0 2px rgba(255,255,255,0.5);
	display: block;
	width: 14px;
	height: 14px;
}
.feature-slider a.active {
	background: #1982d1;
	-webkit-box-shadow: inset 1px 1px 5px rgba(0,0,0,0.4), inset 0 0 2px rgba(255,255,255,0.8);
	-moz-box-shadow: inset 1px 1px 5px rgba(0,0,0,0.4), inset 0 0 2px rgba(255,255,255,0.8);
	box-shadow: inset 1px 1px 5px rgba(0,0,0,0.4), inset 0 0 2px rgba(255,255,255,0.8);
	cursor: default;
	opacity: 0.5;
}

/* Recent Posts */
section.recent-posts {
	padding: 0 0 1.625em;
}
section.recent-posts .hentry {
	border: none;
	margin: 0;
}
section.recent-posts .other-recent-posts {
	border-bottom: 1px solid #ddd;
	list-style: none;
	margin: 0;
}
section.recent-posts .other-recent-posts li {
	padding: 0.3125em 0;
	position: relative;
}
section.recent-posts .other-recent-posts .entry-title {
	border-top: 1px solid #ddd;
	font-size: 17px;
}
section.recent-posts .other-recent-posts a[rel="bookmark"] {
	color: #373737;
	float: left;
	max-width: 84%;
}
section.recent-posts .other-recent-posts a[rel="bookmark"]:after {
	content: '-';
	color: transparent;
	font-size: 11px;
}
section.recent-posts .other-recent-posts a[rel="bookmark"]:hover {
}
section.recent-posts .other-recent-posts .comments-link a,
section.recent-posts .other-recent-posts .comments-link > span {
	border-bottom: 2px solid #999;
	bottom: -2px;
	color: #444;
	display: block;
	font-size: 10px;
	font-weight: 500;
	line-height: 2.76333em;
	padding: 0.3125em 0 0.3125em 1em;
	position: absolute;
	right: 0;
	text-align: right;
	text-transform: uppercase;
	z-index: 1;
}
section.recent-posts .other-recent-posts .comments-link > span {
	border-color: #bbb;
	color: #888;
}
section.recent-posts .other-recent-posts .comments-link a:hover {
	color: #1982d1;
	border-color: #1982d1;
}
section.recent-posts .other-recent-posts li:after {
	clear: both;
	content: '.';
	display: block;
	height: 0;
	visibility: hidden;
}




/* =Navigation
-------------------------------------------------------------- */

#content nav {
	clear: both;
	overflow: hidden;
	padding: 0 0 1.625em;
}
#content nav a {
	font-size: 12px;
	font-weight: bold;
	line-height: 2.2em;
}
#nav-above {
	padding: 0 0 1.625em;
}
#nav-above {
	display: none;
}
.paged #nav-above {
	display: block;
}
.nav-previous {
	float: left;
	width: 50%;
}
.nav-next {
	float: right;
	text-align: right;
	width: 50%;
}
#content nav .meta-nav {
	font-weight: normal;
}

/* Singular navigation */
#nav-single {
	float: right;
	position: relative;
	top: -0.3em;
	text-align: right;
	width: 100%;
	z-index: 1;
}
#nav-single .nav-previous,
#nav-single .nav-next {
	float: none;
	width: auto;
}
#nav-single .nav-next {
	padding-left: .5em;
}




/* Maps */

.maps{
	 height: 30em;
	 width: 99%;
	 border: 1px solid #C4CDE0;
	 padding: 2px;
	}




/* Twitter */
.widget_twitter li {
	list-style-type: none;
	margin-bottom: 14px;
}
.widget_twitter .timesince {
	display: block;
	font-size: 11px;
	margin-right: -10px;
	text-align: right;
}

/* Widget  */
.widget_ img {
	height: auto;
	max-width: 100%;
}

/* Calendar Widget */

.widget_calendar #wp-calendar {
	color: #555;
	width: 95%;
	text-align: center;
}
.widget_calendar #wp-calendar caption,
.widget_calendar #wp-calendar td,
.widget_calendar #wp-calendar th {
	text-align: center;
}
.widget_calendar #wp-calendar caption {
	font-size: 11px;
	font-weight: 500;
	padding: 5px 0 3px 0;
	text-transform: uppercase;
}
.widget_calendar #wp-calendar th {
	background: #f4f4f4;
	border-top: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	font-weight: bold;
}
.widget_calendar #wp-calendar tfoot td {
	background: #f4f4f4;
	border-top: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
}





/* =wpcf7-form
----------------------------------------------- */

.wpcf7-form input{
	color: #94A0C1;
    float: right;
    font-size: 17px;
    padding: 7px;
	margin-right: 8px;
	}
	
.wpcf7-form p{
	font-size: 14px;
	}

.wpcf7-form textarea {
    font-size: 17px;
    padding: 5px;
	color: #94A0C1;

}

/* ***************************************
    ELGG RESPONSIVE
*****************************************/
/*
@media (max-width: 820px) {
#branding {
min-width: 0;
}
*/





/* =Responsive Structure
----------------------------------------------- */

@media (max-width: 800px) {
	/* Simplify the basic layout */
	#main #content {
		margin: 0 7.6%;
		width: auto;
	}
	#nav-below {
		border-bottom: 1px solid #ddd;
		margin-bottom: 1.625em;
	}
	#main #secondary {
		float: none;
		margin: 0 7.6%;
		width: auto;
	}
	/* Simplify the showcase template */
	.page-template-showcase-php .featured-posts {
		min-height: 280px;
	}
	.featured-posts section.featured-post {
		height: auto;
	}
	.page-template-showcase-php section.recent-posts {
		float: none;
		margin: 0;
		width: 100%;
	}
	.page-template-showcase-php #main .widget-area {
		float: none;
		margin: 0;
		width: auto;
	}
	.page-template-showcase-php .other-recent-posts {
		border-bottom: 1px solid #ddd;
	}
	/* Simplify the showcase template when small feature */
	section.featured-post .attachment-small-feature,
	.one-column section.featured-post .attachment-small-feature {
		border: none;
		display: block;
		float: left;
		height: auto;
		margin: 0.625em auto 1.025em;
		max-width: 30%;
		position: static;
	}
	article.feature-.small {
		float: right;
		margin: 0 0 1.625em;
		width: 64%;
	}
	.one-column article.feature-.small .entry-summary {
		height: auto;
	}
	article.feature-.small .entry-summary p a {
		left: 0;
		padding-left: 20px;
		padding-right: 20px;
		width: auto;
	}
	/* Remove the margin on singular articles */
	.singular .entry-header,
	.singular .entry-content,
	.singular footer.entry-meta,
	.singular #comments-title {
		width: 100%;
	}
	/* Simplify the pullquotes and pull styles */
	.singular blockquote.pull {
		margin: 0 0 1.625em;
	}
	.singular .pull.alignleft {
		margin: 0 1.625em 0 0;
	}
	.singular .pull.alignright {
		margin: 0 0 0 1.625em;
	}
	.singular .entry-meta .edit-link a {
		left: 0;
		position: absolute;
		top: 40px;
	}
	.singular #author-info {
		margin: 2.2em -8.8% 0;
		padding: 20px 8.8%;
	}
	/* Make sure we have room for our comment avatars */
	.commentlist {
		width: 100%;
	}
	.commentlist > li.comment,
	.commentlist .pingback {
		margin-left: 102px;
		width: auto;
	}
	/* And a full-width comment form */
	#respond {
		width: auto;
	}
	/* No need to float footer widgets at this size */
	#colophon #supplementary .widget-area {
		float: none;
		margin-right: 0;
		width: auto;
	}
	/* No need to float 404 widgets at this size */
	.error404 #main .widget {
		float: none;
		margin-right: 0;
		width: auto;
	}
	/* Make sure embeds fit their containers */
	embed,
	object {
		max-width: 100%;
	}

}
@media (max-width: 650px) {
	/* @media (max-width: 650px) Reduce font-sizes for better readability on smaller devices */
	body, input, textarea {
		font-size: 13px;
	}
	#site-title a {
		font-size: 24px;
	}
	#site-description {
		font-size: 12px;
	}
	#access ul {
		font-size: 12px;
	}
	article.intro .entry-content {
		font-size: 12px;
	}
	.entry-title {
		font-size: 21px;
	}
	.featured-post .entry-title {
		font-size: 14px;
	}
	.singular .entry-title {
		font-size: 28px;
	}
	.entry-meta {
		font-size: 12px;
	}
	blockquote {
		margin: 0;
	}
	blockquote.pull {
		font-size: 17px;
	}
	/* Reposition the site title and description slightly */
	#site-title {
		padding: 5.30625em 0 0;
	}
	#site-title,
	#site-description {
		margin-right: 0;
	}
	/* Make sure the logo and search form don't collide */
	#branding #searchform {
		top: 1.625em !important;
	}
	/* Floated content doesn't work well at this size */
	.alignleft,
	.alignright {
		float: none;
		margin-left: 0;
		margin-right: 0;
	}
	/* Make sure the post-post navigation doesn't collide with anything */
	#nav-single {
		display: block;
		position: static;
	}
	.singular .hentry {
		padding: 1.625em 0 0;
	}
	.singular.page .hentry {
		padding: 1.625em 0 0;
	}
	/* Talking avatars take up too much room at this size */
	.commentlist > li.comment,
	.commentlist > li.pingback {
		margin-left: 0 !important;
	}
	.commentlist .avatar {
		background: transparent;
		display: block;
		padding: 0;
		position: static;
	}
	.commentlist .children .avatar {
		background: none;
		left: 2.2em;
		padding: 0;
		position: absolute;
		top: 2.2em;
	}
	/* Use the available space in the smaller comment form */
	#respond input[type="text"] {
		width: 95%;
	}
	#respond .comment-form-author .required,
	#respond .comment-form-email .required {
		left: 95%;
	}
	#content .gallery-columns-3 .gallery-item {
		width: 31%;
		padding-right: 2%;
	}
	#content .gallery-columns-3 .gallery-item img {
		width: 100%;
		height: auto;
	}

}
@media (max-width: 450px) {
	#content .gallery-columns-2 .gallery-item {
		width: 45%;
		padding-right: 4%;
	}
	#content .gallery-columns-2 .gallery-item img {
		width: 100%;
		height: auto;
	}

}
@media only screen and (min-device-width: 320px) and (max-device-width: 480px) {
	body {
		padding: 0;
	}
	#page {
		margin-top: 0;
	}
	#branding {
		border-top: none;
	}

}


/* =Print
----------------------------------------------- */

@media print {
	body {
		background: none !important;
		font-size: 10pt;
	}
	footer.entry-meta a[rel=bookmark]:link:after,
	footer.entry-meta a[rel=bookmark]:visited:after {
		content: " [" attr(href) "] "; /* Show URLs */
	}
	#page {
		clear: both !important;
		display: block !important;
		float: none !important;
		max-width: 100%;
		position: relative !important;
	}
	#branding {
		border-top: none !important;
		padding: 0;
	}
	#branding hgroup {
		margin: 0;
	}
	#site-title a {
		font-size: 21pt;
	}
	#site-description {
		font-size: 10pt;
	}
	#branding #searchform {
		display: none;
	}
	#branding img {
		display: none;
	}
	#access {
		display: none;
	}
	#main {
		border-top: none;
		box-shadow: none;
	}
	#primary {
		float: left;
		margin: 0;
		width: 100%;
	}
	#content {
		margin: 0;
		width: auto;
	}
	.singular #content {
		margin: 0;
		width: 100%;
	}
	.singular .entry-header .entry-meta {
		position: static;
	}
	.entry-meta .edit-link a {
		display: none;
	}
	#content nav {
		display: none;
	}
	.singular .entry-header,
	.singular .entry-content,
	.singular footer.entry-meta,
	.singular #comments-title {
		margin: 0;
		width: 100%;
	}
	.singular .hentry {
		padding: 0;
	}
	.entry-title,
	.singular .entry-title {
		font-size: 21pt;
	}
	.entry-meta {
		font-size: 10pt;
	}
	.entry-header .comments-link {
		display: none;
	}
	.page-link {
		display: none;
	}
	.singular #author-info {
		background: none;
		border-bottom: none;
		border-top: none;
		margin: 2.2em 0 0;
		padding: 0;
	}
	#respond {
		display: none;
	}
	.widget-area {
		display: none;
	}
	#colophon {
		display: none;
	}

	/* Comments */
	.commentlist > li.comment {
		background: none;
		border: 1px solid #ddd;
		-moz-border-radius: 3px 3px 3px 3px;
		border-radius: 3px 3px 3px 3px;
		margin: 0 auto 1.625em;
		padding: 1.625em;
		position: relative;
		width: auto;
	}
	.commentlist .avatar {
		height: 39px;
		left: 2.2em;
		top: 2.2em;
		width: 39px;
	}
	.commentlist li.comment .comment-meta {
		line-height: 1.625em;
		margin-left: 50px;
	}
	.commentlist li.comment .fn {
		display: block;
	}
	.commentlist li.comment .comment-content {
		margin: 1.625em 0 0;
	}
	.commentlist .comment-edit-link {
		display: none;
	}
	.commentlist > li::before,
	.commentlist > li.bypostauthor::before {
		content: '';
	}
	.commentlist .reply {
		display: none;
	}

	/* Post author highlighting */
	.commentlist > li.bypostauthor {
		color: #444;
	}
	.commentlist > li.bypostauthor .comment-meta {
		color: #666;
	}
	.commentlist > li.bypostauthor:before {
		content: none;
	}

	/* Post Author threaded comments */
	.commentlist .children > li.bypostauthor {
		background: #fff;
		border-color: #ddd;
	}
	.commentlist .children > li.bypostauthor > article,
	.commentlist .children > li.bypostauthor > article .comment-meta {
		color: #666;
	}

}


/* =IE7
----------------------------------------------- */

#ie7 article.intro {
	margin-left: -7.6%;
	margin-right: -7.6%;
	padding-left: -7.6%;
	padding-right: -7.6%;
	max-width: 1000px;
}
#ie7 section.featured-post {
	margin-left: -7.6%;
	margin-right: -7.6%;
	max-width: 850px;
}
#ie7 section.recent-posts {
	margin-right: 7.6%;
}