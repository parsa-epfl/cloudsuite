#searchtimeline {
/*	border-top: 2px solid #bbb;*/
	padding-bottom: 6px;
	position: relative;
/*	top: -45; *//* TM: CROME BROWSER Move the whole top banner down from top bar */
/*        top: 2px; *//* TM: FIREFOX Move the whole top banner down from top bar */ 
        z-index: 1;
	margin: 0 auto;
	height: 38px;
	width: 950px;
/*	background: none repeat scroll 0 0 #FFFFFF;*/
/*    border: 0px solid #C4CDE0; */
	border-width: 1px 1px 2px;
	-webkit-border-radius: 0px 0px 3px 3px;
    -moz-border-radius: 0px 0px 3px 3px;
    border-radius: 0px 0px 3px 3px
}


.searchk_area{
	width:984px; /* width:162px; */
/*	margin:0 5px 0 0 ;  *//* margin:14px 20px 0 0 ;*/
	
	margin: 0 auto;
	
	padding:2px;
	
	
 /*	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/search_box.jpg) no-repeat; */
	
	 position: relative;   /*TM: add */
	/*  position: absolute; */  /*TM: add  and moves the serch with other divs*/
	border-spacing: 6px;
        border-color: gray;
	
	
       height: 35px;
     
       
	
	
	}
input.searchk_txt{
	display:block;
	background:none;
     /*	border:none; */
	width:752px; /*TM: Original:  width:115px;  but TM: tried 554px; */
	height:32px;
	/* margin:5px 8px; */
	/* padding:2px; */
	font-family: arial,sans-serif;
	color: #0054A7; /* color:#a0a0a0;    */
	font-size:21px;
	-webkit-border-radius: 0px;
	-moz-border-radius: 0px;
	/* border-radius: 0px; */
	-webkit-box-sizing: content-box;
	-moz-box-sizing: content-box;
	box-sizing: content-box;
	
	border-radius: 10px;
        border: 1px solid #71b9f7;
       /* color: white;*/
        padding: 1px 1px 1px 1px; 
        margin:0 0 0 0;
        
        text-shadow: 2px 2px 2px #000;
	
	}
* html INPUT.searchk_txt {
	margin-left:4px;
	
	}
input.searchk_btn{
	display:block;
	width:225px; /*width:26px;*/
	height:34px;
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/search_btn.png) no-repeat top left;
/*	border:none;  */
	cursor:pointer;
/*	background:black ; *//*TM added for testing*/
	
 /*	position: absolute;  */ /*TM: add */

        left: 660px; /*TM: Original left: 272px; */
        margin:1px 0 0 0;
        
        
        color: #333;
	font-weight: bold;
	text-decoration: none;

	
	font-size: 11px;
	line-height: 16px;
	
	padding: 2px 6px;

	outline: none;
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
input.searchk_btn:hover{
	background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/search_btn.png) no-repeat bottom left;
	}