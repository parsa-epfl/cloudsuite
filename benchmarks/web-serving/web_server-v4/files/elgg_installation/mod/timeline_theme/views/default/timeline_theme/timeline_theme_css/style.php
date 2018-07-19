/* remove codrops styles and reset the whole thing */
#container_demo{
	 text-align: left;
	 margin: 0;
	 padding: 0;
	 margin: 0 auto;
	 font-family: "Trebuchet MS","Myriad Pro",Arial,sans-serif;
}

/** fonts used for the icons **/ 
@font-face {
    font-family: 'FontomasCustomRegular';
    src: url('fonts/fontomas-webfont.eot');
    src: url('fonts/fontomas-webfont.eot?#iefix') format('embedded-opentype'),
         url('fonts/fontomas-webfont.woff') format('woff'),
         url('fonts/fontomas-webfont.ttf') format('truetype'),
         url('fonts/fontomas-webfont.svg#FontomasCustomRegular') format('svg');
    font-weight: normal;
    font-style: normal;
}
@font-face {
    font-family: 'FranchiseRegular';
    src: url('fonts/franchise-bold-webfont.eot');
    src: url('fonts/franchise-bold-webfont.eot?#iefix') format('embedded-opentype'),
         url('fonts/franchise-bold-webfont.woff') format('woff'),
         url('fonts/franchise-bold-webfont.ttf') format('truetype'),
         url('fonts/franchise-bold-webfont.svg#FranchiseRegular') format('svg');
    font-weight: normal;
    font-style: normal;

}
a.hiddenanchor{
	display: none;
}

/** The wrapper that will contain our site middle contents **/

.clearandfix:after {
    font-size: 0px;
    height: 0px;
    line-height: 0;
    visibility: hidden;
    clear: both;
    content: ".";
  /*  display: block; */
}
element {
}

/*
.clearandfix {

zoom:1;
}
*/

/*TM: Handling messages  from   http://red-team-design.com/cool-notification-messages-with-css3-jquery/    */

.messagesheading {
    color: #0E385F;
    font-size: 20px;
    font-weight: bold;
    line-height: 29px;
    margin-top: 0px;
    width: 450px;
    word-spacing: -1px;
    font-family: 'lucida grande',tahoma,verdana,arial,sans-serif;
    text-align: left;
    
    width: 945px;
}



.message{
    background-size: 40px 40px;
    background-image: linear-gradient(135deg, rgba(255, 255, 255, .05) 25%, transparent 25%,
                        transparent 50%, rgba(255, 255, 255, .05) 50%, rgba(255, 255, 255, .05) 75%,
                        transparent 75%, transparent);                                      
     box-shadow: inset 0 -1px 0 rgba(255,255,255,.4);
     width: 945px;
     border: 1px solid;
     color: #fff;
     padding: 15px;
     position: fixed;
     _position: absolute;
     text-shadow: 0 1px 0 rgba(0,0,0,.5);
     animation: animate-bg 5s linear infinite;
     
     
}

.info{
     background-color: #4ea5cd;
     border-color: #3b8eb5;
}

.error{
     background-color: #de4343;
     border-color: #c43d3d;
}
     
.warning{
     background-color: #eaaf51;
     border-color: #d99a36;
}

.success{
     background-color: #61b832;
     border-color: #55a12c;
}

.message h3{
     margin: 0 0 5px 0; 
     font-size: 28px;
                                                      
}

.message p{
     margin: 0;                                                  
}

@keyframes animate-bg {
    from {
        background-position: 0 0;
    }
    to {
       background-position: -80px 0;
    }
}

/*
@todo The top_left stuff needs cleaned up.
*/

#top_timeline_left {
	position: relative;
	min-height: 240px;
	-webkit-border-radius: 8px;
	-moz-border-radius: 8px;
	border-radius: 8px;
	border: 1px solid silver;
	margin-right: 28px;
	padding: 5px 9px;

}

/* TM: to display members icons  on elgg Timeline theme*/
#top_timeline_left .tom_homepage_members_link {
    /* 	display:block;   */
	display:inline-block;
    /*	position: absolute;  */
	position: relative;
	top:  2px;
   /*	bottom: 80px; */
	left: 1px;
        width: 100px; 
        height: 100px; 
        right: 0;
        margin: 0;
	
	
     
}
/* TM: End of display Icons */





/* TM: End of the the error massages */



/* TM the idnex body inner content area div */
.wrapperIndex  {
  /* min-width: 980px; */
	width: 981px;
	margin: 0 auto;
}


/* sign up div heading    */
.timeline_theme_1i {
    padding-top: 20px;
    padding-bottom: 20px;
    
    
}

.timeline_theme_1j.timeline_theme_1k- .timeline_theme_1m {
    font-size: 36px;
}
 .timeline_theme_1n {
    font-size: 14px;
    font-family: 'Freight Sans','lucida grande',tahoma,verdana,arial,sans-serif !important;
}
.timeline_theme_1j .timeline_theme_1m {
    color: #141823;
    font-family: 'Freight Sans Bold','lucida grande',tahoma,verdana,arial,sans-serif;
    font-size: 40px;
    font-weight: normal;
    white-space: nowrap;
}

/*  The social networking is at your fingertips.  */
.timeline_theme_1w {
     padding-top: 20px;
}


.timeline_theme_1x {
font-weight: normal !important;
text-rendering: optimizelegibility;
/* font-family: 'Freight Sans','lucida grande',tahoma,verdana,arial,sans-serif !important; */

 
}

.timeline_theme_1y {
    line-height: 126%;
    font-size: 19px;
    font-family: 'Freight Sans','lucida grande',tahoma,verdana,arial,sans-serif !important;
    
    color: #333;
    margin: 0px;
        margin-top: 20px;
        margin-right-value: 0px;
        margin-bottom: 0px;
        margin-left-value: 0px;
        margin-left-ltr-source: physical;
        margin-left-rtl-source: physical;
        margin-right-ltr-source: physical;
        margin-right-rtl-source: physical;
    padding: 0px;
        padding-top: 0px;
        padding-right-value: 0px;
        padding-bottom: 0px;
        padding-left-value: 0px;
        padding-left-ltr-source: physical;
        padding-left-rtl-source: physical;
        padding-right-ltr-source: physical;
        padding-right-rtl-source: physical;

    
    
    
}

/* timeline footer area */

._elgg_timeline {
    background-color: #F6F7F8;
    font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;
    font-size: 12px;
    padding-bottom: 34px;
    margin-top: 800px;
    margin-bottom: 0px;
}

._elgg_timeline #elgg_timelineFooterContent {
    background-color: #F6F7F8;
    border-bottom: 1px solid #DCDEE1;
    margin: 0px auto 36px;
    width: 924px;
}
#elgg_timelineFooterContent {
    border-bottom: 1px solid #CCC;
    font-size: 1px;
    height: 45px; /* Tm: higher moves the footer line down */
    margin-bottom: 8px;
}


._elgg_timeline  {
    font-weight: bold;
    margin: 12px auto;
}






/* End of elgg timeline footer area */
/* TM: singup end  */

/*  The site description    */

.timeline_theme_1d {
    margin-top: 20px;
}
.timeline_theme_2b {
    padding-bottom: 10px;
}

.timeline_theme_desc {
    width: 440px;
}
.timeline_theme_1b {
    vertical-align: middle;
}
.timeline_theme_1a {
    display: inline-block;
}

/* small site description  */

.timeline_theme_1e {
    font-family: 'Freight Sans Bold','lucida grande',tahoma,verdana,arial,sans-serif;
    font-weight: normal !important;
    text-rendering: optimizelegibility;
}

.timeline_theme_1h {
    font-weight: bold;
}
.timeline_theme_1g {
    color: #333;
}
.timeline_theme_1f {
    font-size: 17px;
    line-height: 22px;
}

.timeline_theme_2c {
    margin-left: 10px;
        margin-left-value: 10px;
        margin-left-ltr-source: physical;
        margin-left-rtl-source: physical;
}
.timeline_theme_2f {
    font-weight: normal;
}
.timeline_theme_2e {
    color: #666;
}
.timeline_theme_2d {
    font-size: 15px;
    line-height: 20px;
}

/* index icons alignment */


element {
    text-align: center;
    width: 55px;
}


.timeline_theme_1c {
    margin-right: 20px;
}






/* ENd site description  */


/* TM: open Signup inputs */

body, button, input, label, select, td, textarea {
    font-family: 'lucida grande',tahoma,verdana,arial,sans-serif;
    font-size: 11px;
}




.timeline_theme_2j .timeline_theme_2a {
    width: 172px;
}

.timeline_theme_2a, .timeline_theme_2k .timeline_theme_2a, .timeline_theme_2k .uiTwizanexPlaceholderInput .placeholder {
   font-size: 18px;
    padding: 8px 10px;
    padding-top: 8px;
    padding-right-value: 10px;
    padding-bottom: 8px;
    padding-left-value: 10px;
    padding-left-ltr-source: physical;
    padding-left-rtl-source: physical;
    padding-right-ltr-source: physical;
    padding-right-rtl-source: physical;
}
.timeline_theme_2k .timeline_theme_2a {
  border-color: #BDC7D8;
    border-top-color: #BDC7D8;
    border-right-color-value: #BDC7D8;
    border-bottom-color: #BDC7D8;
    border-left-color-value: #BDC7D8;
    border-left-color-ltr-source: physical;
    border-left-color-rtl-source: physical;
    border-right-color-ltr-source: physical;
    border-right-color-rtl-source: physical;
    border-radius: 5px;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
    border-bottom-left-radius: 5px;
    margin: 0px;
    margin-top: 0px;
    margin-right-value: 0px;
    margin-bottom: 0px;
    margin-left-value: 0px;
    margin-left-ltr-source: physical;
    margin-left-rtl-source: physical;
    margin-right-ltr-source: physical;
    margin-right-rtl-source: physical;
    width: 377px;
}
.uiTwizanexPlaceholderInput input, .uiTwizanexPlaceholderInput textarea {
    background-color: transparent;
    position: relative;
}

/* TM: Enter your name and Username section */
.timeline_theme_2j .uiTwizanexPlaceholderInput {
    width: 194px;
}
.timeline_theme_2k .uiTwizanexPlaceholderInput {
    background: none repeat scroll 0% 0% #FFF;
    border-radius: 5px;
    width: 399px;
}
.uiTwizanexPlaceholderInput {
    display: inline-block;
    position: relative;
}



.clearfix:after {
    clear: both;
    content: ".";
    display: block;
    font-size: 0px;
    height: 0px;
    line-height: 0;
    visibility: hidden;
}

.clearfix {
}
.timeline_theme_2j {
    width: 399px;
}







.timeline_theme_2k .timeline_theme_2a, .timeline_theme_2k .timeline_theme_2a, .timeline_theme_2k .uiTwizanexPlaceholderInput .placeholder {
    font-size: 18px;
    padding: 8px 10px;
        padding-top: 8px;
        padding-right-value: 10px;
        padding-bottom: 8px;
        padding-left-value: 10px;
        padding-left-ltr-source: physical;
        padding-left-rtl-source: physical;
        padding-right-ltr-source: physical;
        padding-right-rtl-source: physical;
}
.timeline_theme_2k .uiTwizanexPlaceholderInput .placeholder {
    -moz-box-sizing: border-box;
    overflow: hidden;
        overflow-x: hidden;
        overflow-y: hidden;
    padding-left: 11px;
        padding-left-value: 11px;
        padding-left-ltr-source: physical;
        padding-left-rtl-source: physical;
    text-overflow: ellipsis;
    white-space: nowrap;
}
div.uiStickyPlaceholderEmptyInput .placeholder {
    display: block;
}
.uiTwizanexPlaceholderInput .placeholder {
    color: #999;
    cursor: text;
    display: none;
    height: 100%;
    left: 0px;
    padding: 4px 0px 0px 5px;
        padding-top: 4px;
        padding-right-value: 0px;
        padding-bottom: 0px;
        padding-left-value: 5px;
        padding-left-ltr-source: physical;
        padding-left-rtl-source: physical;
        padding-right-ltr-source: physical;
        padding-right-rtl-source: physical;
    position: absolute;
    top: 0px;
    width: 100%;
}


/* TM End sign up inputs

/* index Page footer */

#indexFooter {
    color: #737373;
    font-size: 11px;
    margin: 0px auto;
    margin-top: 0px;
    margin-right-value: auto;
    margin-bottom: 0px;
    margin-left-value: auto;
    margin-left-ltr-source: physical;
    margin-left-rtl-source: physical;
    margin-right-ltr-source: physical;
    margin-right-rtl-source: physical;
    width: 980px;
}
body, button, input, label, select, td, textarea {
    font-family: 'lucida grande',tahoma,verdana,arial,sans-serif;
    font-size: 11px;
}



/* TM: close index page footer */



}
.wrapperIndex .wrappergradient {
    background: -moz-linear-gradient(center top , #FFF, #D3D8E8) repeat scroll 0% 0% transparent;
        background-color: transparent;
        background-image: -moz-linear-gradient(center top , #FFF, #D3D8E8);
        background-repeat: repeat;
        background-attachment: scroll;
        background-position: 0% 0%;
        background-clip: border-box;
        background-origin: padding-box;
        background-size: auto auto;
        
        
      
 
        
        
        
}




.wrapperIndex .wrappergradient . {
       margin: 0px auto;
        margin-top: 0px;
        margin-right-value: auto;
        margin-bottom: 0px;
        margin-left-value: auto;
        margin-left-ltr-source: physical;
        margin-left-rtl-source: physical;
        margin-right-ltr-source: physical;
        margin-right-rtl-source: physical;
      width: 980px;
         position: relative; 
}


/** The wrapper that will contain our site image **/

.wrapperheading {
    color: #0E385F;
    font-size: 20px;
    font-weight: bold;
    line-height: 29px;
    margin-top: 40px;
    width: 450px;
    word-spacing: -1px;
    
    
    font-family: 'lucida grande',tahoma,verdana,arial,sans-serif;
    text-align: left;
    
    
}



#wrapperimage{
  margin-top: 1px; /* TM: move up*/
       float:left;
       margin-left:2px;
        background:url(<?php echo elgg_get_site_url(); ?>mod/timeline_theme/css/timeline_front_index.png);
	width: 565px;
	right: 0px;
		
		
	/* margin: 0px auto;	 */
	 height: 400px;  
	position: relative;
	
	
       z-index: 30;
		
		
}


/** The wrapper that will contain our two forms **/
#wrapper{
 /*     float:right; */
      margin-top: 0px; /* TM: move up*/
       margin-left: 565px;

	width: 60%;
	right: 0px;
	min-height: 780px;	
	/* margin: 0px auto;	 */
	width: 412px; 
	position: relative;	
}
/**** Styling the form elements **/

/**** general text styling ****/
#wrapper a{
	color: rgb(95, 155, 198);
	text-decoration: underline;
}

#wrapper h1{
	font-size: 48px;
	color: rgb(6, 106, 117);
	padding: 2px 0 10px 0;
	font-family: 'FranchiseRegular','Arial Narrow',Arial,sans-serif;
	font-weight: bold;
	text-align: center;
	padding-bottom: 30px;
}
/** For the moment only webkit supports the background-clip:text; */
#wrapper h1{
    background: -webkit-repeating-linear-gradient(-45deg, 
	rgb(18, 83, 93) , 
	rgb(18, 83, 93) 20px, 
	rgb(64, 111, 118) 20px, 
	rgb(64, 111, 118) 40px, 
	rgb(18, 83, 93) 40px);
	-webkit-text-fill-color: transparent;
	-webkit-background-clip: text;
}
#wrapper h1:after{
	content: ' ';
	display: block;
	width: 100%;
	height: 2px;
	margin-top: 10px;
	background: -moz-linear-gradient(left, rgba(147,184,189,0) 0%, rgba(147,184,189,0.8) 20%, rgba(147,184,189,1) 53%, rgba(147,184,189,0.8) 79%, rgba(147,184,189,0) 100%); 
	background: -webkit-gradient(linear, left top, right top, color-stop(0%,rgba(147,184,189,0)), color-stop(20%,rgba(147,184,189,0.8)), color-stop(53%,rgba(147,184,189,1)), color-stop(79%,rgba(147,184,189,0.8)), color-stop(100%,rgba(147,184,189,0))); 
	background: -webkit-linear-gradient(left, rgba(147,184,189,0) 0%,rgba(147,184,189,0.8) 20%,rgba(147,184,189,1) 53%,rgba(147,184,189,0.8) 79%,rgba(147,184,189,0) 100%); 
	background: -o-linear-gradient(left, rgba(147,184,189,0) 0%,rgba(147,184,189,0.8) 20%,rgba(147,184,189,1) 53%,rgba(147,184,189,0.8) 79%,rgba(147,184,189,0) 100%); 
	background: -ms-linear-gradient(left, rgba(147,184,189,0) 0%,rgba(147,184,189,0.8) 20%,rgba(147,184,189,1) 53%,rgba(147,184,189,0.8) 79%,rgba(147,184,189,0) 100%); 
	background: linear-gradient(left, rgba(147,184,189,0) 0%,rgba(147,184,189,0.8) 20%,rgba(147,184,189,1) 53%,rgba(147,184,189,0.8) 79%,rgba(147,184,189,0) 100%); 
}

#wrapper p{
/*	margin-bottom:15px;*/
        margin-bottom:3px;

}
#wrapper p:first-child{
	margin: 0px;
}
#wrapper label{
	color: rgb(64, 92, 96);
	position: relative;
}

/**** advanced input styling ****/
/* placeholder */
::-webkit-input-placeholder  { 
	color: rgb(190, 188, 188); 
	font-style: italic;
}
input:-moz-placeholder,
textarea:-moz-placeholder{ 
	color: rgb(190, 188, 188);
	font-style: italic;
} 
input {
  outline: none;
}

/* all the input except submit and checkbox */
#wrapper input:not([type="checkbox"]){
	width: 92%;
	margin-top: 4px;
	padding: 10px 5px 10px 32px;	
	border: 1px solid rgb(178, 178, 178);
	-webkit-appearance: textfield;
	-webkit-box-sizing: content-box;
	  -moz-box-sizing : content-box;
	       box-sizing : content-box;
	-webkit-border-radius: 3px;
	   -moz-border-radius: 3px;
	        border-radius: 3px;
	-webkit-box-shadow: 0px 1px 4px 0px rgba(168, 168, 168, 0.6) inset;
	   -moz-box-shadow: 0px 1px 4px 0px rgba(168, 168, 168, 0.6) inset;
	        box-shadow: 0px 1px 4px 0px rgba(168, 168, 168, 0.6) inset;
	-webkit-transition: all 0.2s linear;
	   -moz-transition: all 0.2s linear;
	     -o-transition: all 0.2s linear;
	        transition: all 0.2s linear;
}
#wrapper input:not([type="checkbox"]):active,
#wrapper input:not([type="checkbox"]):focus{
	border: 1px solid rgba(91, 90, 90, 0.7);
	background: rgba(238, 236, 240, 0.2);	
	-webkit-box-shadow: 0px 1px 4px 0px rgba(168, 168, 168, 0.9) inset;
	   -moz-box-shadow: 0px 1px 4px 0px rgba(168, 168, 168, 0.9) inset;
	        box-shadow: 0px 1px 4px 0px rgba(168, 168, 168, 0.9) inset;
} 

/** the magic icon trick ! **/
[data-icon]:after {
    content: attr(data-icon);
    font-family: 'FontomasCustomRegular';
    color: rgb(106, 159, 171);
    position: absolute;
    left: 10px;
    top: 35px;
	width: 30px;
}

/*styling both submit buttons */
#wrapper p.button input{
	width: 30%;
	cursor: pointer;	
	background: rgb(61, 157, 179);
	padding: 8px 5px;
	font-family: 'BebasNeueRegular','Arial Narrow',Arial,sans-serif;
	color: #fff;
	font-size: 24px;	
	border: 1px solid rgb(28, 108, 122);	
	margin-bottom: 10px;	
	text-shadow: 0 1px 1px rgba(0, 0, 0, 0.5);
	-webkit-border-radius: 3px;
	   -moz-border-radius: 3px;
	        border-radius: 3px;	
	-webkit-box-shadow: 0px 1px 6px 4px rgba(0, 0, 0, 0.07) inset,
	        0px 0px 0px 3px rgb(254, 254, 254),
	        0px 5px 3px 3px rgb(210, 210, 210);
	   -moz-box-shadow:0px 1px 6px 4px rgba(0, 0, 0, 0.07) inset,
	        0px 0px 0px 3px rgb(254, 254, 254),
	        0px 5px 3px 3px rgb(210, 210, 210);
	        box-shadow:0px 1px 6px 4px rgba(0, 0, 0, 0.07) inset,
	        0px 0px 0px 3px rgb(254, 254, 254),
	        0px 5px 3px 3px rgb(210, 210, 210);
	-webkit-transition: all 0.2s linear;
	   -moz-transition: all 0.2s linear;
	     -o-transition: all 0.2s linear;
	        transition: all 0.2s linear;
}
#wrapper p.button input:hover{
	background: rgb(74, 179, 198);
}
#wrapper p.button input:active,
#wrapper p.button input:focus{
	background: rgb(40, 137, 154);
	position: relative;
	top: 1px;
	border: 1px solid rgb(12, 76, 87);	
	-webkit-box-shadow: 0px 1px 6px 4px rgba(0, 0, 0, 0.2) inset;
	   -moz-box-shadow: 0px 1px 6px 4px rgba(0, 0, 0, 0.2) inset;
	        box-shadow: 0px 1px 6px 4px rgba(0, 0, 0, 0.2) inset;
}
p.login.button,
p.signin.button{
	text-align: right;
	margin: 5px 0;
}


/* styling the checkbox "keep me logged in"*/
.keeplogin{
	margin-top: -5px;
}
.keeplogin input,
.keeplogin label{
	display: inline-block;
	font-size: 12px;	
	font-style: italic;
}
.keeplogin input#loginkeeping{
	margin-right: 5px;
}
.keeplogin label{
	width: 80%;
}


/*styling the links to change from one form to another */

p.change_link{
	position: absolute;
	color: rgb(127, 124, 124);
/*	left: 0px;  */
	height: 20px;
/*	width: 412px; */
	padding: 17px 30px 20px 30px;
	font-size: 16px	;
	text-align: right;
	border-top: 1px solid rgb(219, 229, 232);
	-webkit-border-radius: 0 0  5px 5px;
	   -moz-border-radius: 0 0  5px 5px;
	        border-radius: 0 0  5px 5px;
/*	background: rgb(225, 234, 235);
	background: -moz-repeating-linear-gradient(-45deg, 
	rgb(247, 247, 247) , 
	rgb(247, 247, 247) 15px, 
	rgb(225, 234, 235) 15px, 
	rgb(225, 234, 235) 30px, 
	rgb(247, 247, 247) 30px
	);
	background: -webkit-repeating-linear-gradient(-45deg, 
	rgb(247, 247, 247) , 
	rgb(247, 247, 247) 15px, 
	rgb(225, 234, 235) 15px, 
	rgb(225, 234, 235) 30px, 
	rgb(247, 247, 247) 30px
	);
	background: -o-repeating-linear-gradient(-45deg, 
	rgb(247, 247, 247) , 
	rgb(247, 247, 247) 15px, 
	rgb(225, 234, 235) 15px, 
	rgb(225, 234, 235) 30px, 
	rgb(247, 247, 247) 30px
	);
	background: repeating-linear-gradient(-45deg, 
	rgb(247, 247, 247) , 
	rgb(247, 247, 247) 15px, 
	rgb(225, 234, 235) 15px, 
	rgb(225, 234, 235) 30px, 
	rgb(247, 247, 247) 30px
	);
	
	
*/	
	
}
#wrapper p.change_link a {
	display: inline-block;
	font-weight: bold;
	background: rgb(247, 248, 241);
	padding: 2px 6px;
	color: rgb(29, 162, 193);
	margin-left: 10px;
	text-decoration: none;
	-webkit-border-radius: 4px;
	-moz-border-radius: 4px;
	border-radius: 4px;
	border: 1px solid rgb(203, 213, 214);
	-webkit-transition: all 0.4s linear;
	-moz-transition: all 0.4s  linear;
	-o-transition: all 0.4s linear;
	-ms-transition: all 0.4s  linear;
	transition: all 0.4s  linear;
}
#wrapper p.change_link a:hover {
	color: rgb(57, 191, 215);
	background: rgb(247, 247, 247);
	border: 1px solid rgb(74, 179, 198);
}
#wrapper p.change_link a:active{
	position: relative;
	top: 1px;
}
/** Styling both forms **/
#register, 
#login{
	position: absolute;
	top: 0px;
	width: 88%;	
	padding: 18px 6% 60px 6%;
	margin: 0 0 35px 0;
/*	background: rgb(247, 247, 247);
*/
/*	border: 1px solid rgba(147, 184, 189,0.8); 
 
	-webkit-box-shadow: 0pt 2px 5px rgba(105, 108, 109,  0.7),	0px 0px 8px 5px rgba(208, 223, 226, 0.4) inset;
	   -moz-box-shadow: 0pt 2px 5px rgba(105, 108, 109,  0.7),	0px 0px 8px 5px rgba(208, 223, 226, 0.4) inset;
	        box-shadow: 0pt 2px 5px rgba(105, 108, 109,  0.7),	0px 0px 8px 5px rgba(208, 223, 226, 0.4) inset;
	-webkit-box-shadow: 5px;
	
	
*/	
	-moz-border-radius: 5px;
		 border-radius: 5px;

}

#register{	
/*	z-index: 21; */
/*	opacity: 0;  *//* TM: to  make shure the first label is positioned properly  */

}
#login{
	z-index: 22;
}
#toregister:target ~ #wrapper #register,
#tologin:target ~ #wrapper #login{
	z-index: 22;
	-webkit-animation-name: fadeInLeft;
	-moz-animation-name: fadeInLeft;
	-ms-animation-name: fadeInLeft;
	-o-animation-name: fadeInLeft;
	animation-name: fadeInLeft;
	-webkit-animation-delay: .1s;
	-moz-animation-delay: .1s;
	-o-animation-delay: .1s;
	-ms-animation-delay: .1s;
	animation-delay: .1s;
}
#toregister:target ~ #wrapper #login,
#tologin:target ~ #wrapper #register{
	-webkit-animation-name: fadeOutLeft;
	-moz-animation-name: fadeOutLeft;
	-ms-animation-name: fadeOutLeft;
	-o-animation-name: fadeOutLeft;
	animation-name: fadeOutLeft;
}

/** the actual animation, credit where due : http://daneden.me/animate/ ***/
.animate{
	-webkit-animation-duration: 0.5s;
	-webkit-animation-timing-function: ease;
	-webkit-animation-fill-mode: both;
	
	-moz-animation-duration: 0.5s;
	-moz-animation-timing-function: ease;
	-moz-animation-fill-mode: both;
	
	-o-animation-duration: 0.5s;
	-o-animation-timing-function: ease;
	-o-animation-fill-mode: both;
	
	-ms-animation-duration: 0.5s;
	-ms-animation-timing-function: ease;
	-ms-animation-fill-mode: both;
	
	animation-duration: 0.5s;
	animation-timing-function: ease;
	animation-fill-mode: both;
}

/** yerk some ugly IE fixes 'cause I know someone will ask "why does it look ugly in IE?", no matter how many warnings I will put in the article */

.lt8 #wrapper input{
	padding: 10px 5px 10px 32px;
    width: 92%;
}
.lt8 #wrapper input[type=checkbox]{
	width: 10px;
	padding: 0;
}
.lt8 #wrapper h1{
	color: #066A75;
}
.lt8 #register{	
	display: none;
}
.lt8 p.change_link,
.ie9 p.change_link{
	position: absolute;
	height: 90px;
	background: transparent;
}