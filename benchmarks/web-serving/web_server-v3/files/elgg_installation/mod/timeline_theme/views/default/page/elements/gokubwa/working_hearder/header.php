<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

 
 /**
* Elgg header logo
*/
global $CONFIG;


$site = elgg_get_site_entity();
$site_name = $site->name;
$site_url = elgg_get_site_url();

//$getontext = elgg_pop_context();
//$getthecurrentguid = get_entity($getontext);
$keronche = get_entity_dates();

//$keronchetime = elgg_get_entity_time_where_sql();

//print_r ($getthecurrentguid);	
 
/**
* Elgg title element
*
* @uses $vars['title'] The page title
* @uses $vars['class'] Optional class for heading
*/

$class= '';
if (isset($vars['class'])) {
        $class = " class=\"{$vars['class']}\"";
} 



/**
* Export handler.
*
* @package Elgg.Core
* @subpackage Export
*/

//require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/start.php");



 $pageonwerguid = elgg_get_page_owner_guid();
 
 
 ///////////////////////////////////// Testing the page owner//////////////////////////////////////////////
 
    $tomtomme = timeline_get_page_content_list();
    
  //  elgg_echo($tomtomme);
  $current_user = elgg_get_logged_in_user_entity();
  
//  [owner_guid] => 0 [site_guid] => 1 [container_guid] => 0

// $owner_guid = ['owner_guid'];
 
// $container_guid = ['container_guid'];

//  $options =  elgg_get_page_site_guid();
  
  $options = elgg_get_page_owner_entity()->username;
//  $container = $blog->getContainerEntity();
 
 
// $owner_owner = get_user_by_username();
 
           if (!$pageonwerguid) {
              
              print_r(Yessana);
               
           }
/*
if ($owner_guid && ($container_guid = 0)) {

  print_r($tomtomme );

}
 */  
 //print_r( $owner_owner);
 

 // print_r( );
 
 //////////////////////////////////////////////////
 
    
?>

<?php if (!elgg_is_logged_in()) { ?>

<div class="top_bar">												<!-- main top_black bar -->
    <div class="wrapper">                                                                                       <!-- wrapper div -->
        <div class="inner">                                                                                             <!-- opening inner_box -->
            
            <h1 class="logo left">                                                                                      <!-- logo section -->
                <a href="<?php echo elgg_get_site_url(); ?>"><?php echo elgg_echo('timeline:Finder');?></a>
            </h1>                                                                                                                   <!-- closing logo section -->
            
            
            <form method="post" action="<?php echo elgg_get_site_url(); ?>action/login">
                <div class="login_section right">                                                       <!-- opening login section -->
                
                    <input type="text" value="Email" name="username" class="login_txt left" onblur="if(this.value=='')this.value=this.defaultValue;" onfocus="if(this.value==this.defaultValue)this.value='';" />	<!-- email text box -->

                    <input type="password" value="Password" name="password" class="login_txt left" onblur="if(this.value=='')this.value=this.defaultValue;" onfocus="if(this.value==this.defaultValue)this.value='';" />	<!-- password text box -->

                    <input type="submit" value="LOGIN" class="login_btn left" /> 	<!-- login button -->
                    
                    <a href="<?php echo elgg_get_site_url(); ?>register" title="User Sign up">
                        <input type="button" value="SIGN UP" class="login_btn left" />	<!-- sign up button -->
                    </a>
                    
                    <a href="<?php echo elgg_get_site_url(); ?>forgotpassword" title="Forgot password?">
                        <label for="forgot" class="forgot_txt">						<!-- forgot text _ label -->
                        <?php echo elgg_echo('forget:password');?>
                        </label>													<!-- closing forgot_ text label -->
                    </a>
                    
                </div>														<!-- closing login section -->
                <div class="clear"></div>
                <?php echo elgg_view('input/securitytoken');?>
            </form>
           
            
        </div>														<!-- inner div -->
    </div>															<!-- closing wrapper div -->
</div>
																<!-- closing main top bar -->
 <?php } ?><!-- closing main top bar -->
 
 <?php 
 
 if (!elgg_is_logged_in()) {

 ?><!-- close container to holder -- the headers contentents  -->

 <div id="sitemenubranding" >  <!-- open stie div menu -->
 	
<div class="main_navigation clear">									<!-- opening main navigation -->
    <div class="wrappers">											<!-- wrapper div -->
        <div class="inners">											<!-- opening inner_box -->

            <a href="<?php echo elgg_get_site_url(); ?>" class="home_btn left"><?php echo elgg_echo('home');?></a>
            <?php
            // insert site-wide navigation
            echo elgg_view_menu ('site');
       
       
            ?>
            

        </div>														<!-- closing inner div -->
    </div>															<!-- closing -->
</div>														 <!-- closing main navigation -->

</div>	 <!-- closing main div navigation -->

<?php	
}
 ?>

<!-- open container to holder -- the headers contentents  -->
<?php 
 
 if ($pageonwerguid) {



 ?> <!-- open container to holder -- the headers contentents  -->


<div id="timeline-hearder-container" > <!-- open container to holder -- the headers contentents  -->




<!-- TM: The Javascript is causing issues with elgg widgets so don't uncomment unless you are testing or deburging   <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script> -->


<script>
$(document).ready(function(){

	// hide #footer-logo
	$("#footer-logo").hide();
	
	// fade in #footer-logo
	$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('#footer-logo').fadeIn();
			} else {
				$('#footer-logo').fadeOut();
			}
		});

		// scroll body to 0px on click
		$('#footer-logo a').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
		
	
		// scroll body to 0px on click
		$('#back-top').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
		
	});
	
	

	$(".anchor_post").each(function () {
		var id = this.id;
		
		$("#"+id).click(function (){
			
			
			$(".anchor_post").parent().removeClass("anchor_post_current");
			$(this).parent().addClass("anchor_post_current");
			
			if(id=='back-top') 
				return
			
			anchor_href = $("#"+id).attr('href'); // #mm_yyyy
			if($(anchor_href).length==false){
				location.href='/date/'+anchor_href.substring(4)+'/'+anchor_href.substring(1,3)
			}
			else
			{
				var p = $(anchor_href);
				var position = p.position();


				$('body,html').animate({
					scrollTop: (position.top-60)
				}, 800);
			}
			
		});
	});
	
	
	
	

});
</script>


<!-- TM: THe Javascript to load the page to the top was loading here but now extended to the footer -->

<?php

// let us grab the onwner icons or groups icons

$owner = elgg_get_page_owner_entity(); // depending on which view you are using this function in




if ($owner instanceof ElggEntity) {

$icon = elgg_view("profile/icon",array('entity' => $owner, 'size' => 'medium'));

//TM:  Elgg users and groups have their own icons now let get them and run with them to the finish line. Is that not something! :)
if ($owner instanceof ElggUser ) {

$display = "<div id=\"photo-elggheader\">" . $icon . "</div>";

	// let us grab the default timeline files "banners"
	
	$wallpaper_path = "mod/timeline_theme/graphics/wallpapers";
	$imageArray = array();	
	$dir_handle = opendir($CONFIG->path . $wallpaper_path);
	while (($file = readdir($dir_handle)) !== false) {
		if ($file!='.' && $file!='..' && !is_dir($dir.$entry)){
			$dotPosition = strrpos($file,".");
			if($dotPosition){
				$shortFileName = substr($file,0,$dotPosition);
				$imageArray[elgg_echo($shortFileName)] = $wallpaper_path . "/" . str_replace(" ", "%20",$file);
			}
		}
	}
	
	// load default images
	foreach($imageArray as $name=>$image){
		if($i == 4) {
			$i = 0;
		}
    


}
//$current_user = get_loggedin_userid();
  
		$current_user = elgg_get_page_owner_guid();
                 $currentConfig = get_timeline_style_from_metadata($current_user, 'timelinestyletimeline');

			// check for previously uploaded timeline
		$filehandler = new ElggFile();
		$filehandler->owner_guid = $current_user;
		$filehandler->setFilename('customtimeline');
		if($filehandler->exists()){
			$imageUrl = 'pg/timeline_theme/getbackground?id=' . $current_user;

}

}
  
 
// echo $display; // just for testing

}





?>



<!--[if lt IE 9]>
<script src="<?php echo elgg_get_site_url(); ?>/mod/timeline_theme/views/default/js/html5.js" type="text/javascript"></script>
<![endif]-->


		                    
            <div id="box-scrool-bar">

            <div class="timeline-scroll-bar">
                
                <li class="anchor_post_current"><a id="back-top" class='anchor_post' href="#top">Now</a></li>
              
            <?php 
                    
                // only users can have archives at present

 //     if ($owner instanceof ElggUser || $owner instanceof ElggGroup) {  
     if ($owner instanceof ElggUser) {     
             $loggedin_user = elgg_get_logged_in_user_entity();
  
             $page_owner = elgg_get_page_owner_entity();
    

     if (elgg_instanceof($page_owner, 'user')) {
	$url_segment = 'blog/archive/' . $page_owner->username;
        } else {
	$url_segment = 'blog/group/' . $page_owner->getGUID() . '/archive';
         }

        // This is a limitation of the URL schema.
      if ($page_owner && $vars['page'] != 'friends') {
	$dates = get_entity_dates('object', 'blog', $page_owner->getGUID());
	
	if ($dates) {
//		$title = elgg_echo('show:timeline');
		foreach ($dates as $date) {
			$timestamplow = mktime(0, 0, 0, substr($date,4,2) , 1, substr($date, 0, 4));
			$timestamphigh = mktime(0, 0, 0, ((int) substr($date, 4, 2)) + 1, 1, substr($date, 0, 4));

			$link = elgg_get_site_url() . $url_segment . '/' . $timestamplow . '/' . $timestamphigh;
			
			$month = elgg_echo('date:month:' . substr($date, 4, 2), array(substr($date, 0, 4))) ;
        
            
                      $okebe .= "<li><a href=\"$link\" title=\"$month\">$month</a></li>";
			
			
		}

		echo elgg_view_module('aside', $title, $okebe);
	}
        }  
               
           }
    
               ?>
               
            </div><!-- #timeline-scroll-bar -->
            </div><!-- #box-scrool-bar -->
            
            
            
            
  


<div id="page" class="hfeed timeline-separator">
      
 
     <?php  
     
   //  TM: Warning! DO NOT MOVE THIS DOWN FUTURE: ICON CAN NOT DISPLAY BELOW HEADER: It took me ten minutes to find out
     
       echo $display; // Icon display
     
     
      ?>   
           
<div id="branding"  role="banner">

 

	
<?php
	
			// check for previously uploaded timeline_theme
		$filehandler = new ElggFile();
		$filehandler->owner_guid = $current_user;
		$filehandler->setFilename('customtimeline'); 
		
		
		//TM: let us now check if the imageurl is equl to the user's configured image
		
		if($imageUrl = $currentConfig["timeline-image"]){
        
	 ?>
	 
	
			
	<a href="<?php echo elgg_get_site_url();?>">
<img src="<?php echo elgg_get_site_url(); ?><?php echo $imageUrl?> " style='position:relative; width:990px; height:288px; top: 0px' alt="" />
							</a>

	<?php }
		else {  // not working well here
           
           
        ?>
        
  <?php  
  
   // let us load the default image

  if($image != $currentConfig['timeline-image']){

  if (!empty($image)) {

$pleaseloaddefaultimage = ' . $CONFIG->wwwroot . $image . ';


?>
    
        
		
	<a href="<?php echo elgg_get_site_url();?>"> <img src= "<?php echo $pleaseloaddefaultimage ?>" style='position:relative; width:990px; height:288px;  top: 0px;' alt="" />
							</a>
			

<?php }} }



?>





<?php  if (elgg_get_site_url() === current_page_url()){?>

<a href="<?php echo elgg_get_site_url();?>">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/headers/pine-cone.jpg" width="1000" height="288" alt="" />
							</a>	

<?php } ?>

			<hgroup>
			
		
		

 
<?php 





 // Let us check if no context let use diplay the title of the page if context is == NULL let us grab site name

// TM: works like charm :) Now take a sip of glass of water - this is cool
  if (elgg_get_site_url() === current_page_url()){?>
  
<div id="photo-header"><img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-photo.jpg"></div>	

            
<h1 id="site-title"><span><a href="<?php echo elgg_get_site_url();?>" title="<?php echo $site_url; ?>" rel="home"><?php echo $site_name; ?></a></span></h1>
<?php } else { ?>


<h1 id="site-description"><span><a href="<?php echo elgg_get_site_url();?>" title="<?php echo $site_url; ?>" rel="home"><?php echo $vars['title']; ?></a></span></h1>

<?php } ?>       
            
    <h2 id="site-description">The Official <?php echo $site_name; ?> Site</h2>
    
    </span></h1>           
                     
        <div>	
        
        <?php
        
        if (!elgg_is_logged_in()) {
        // insert site-wide navigation
        echo elgg_view_menu('site');
        
        }
        
        if (elgg_is_logged_in()) {
        // content links
     $content_menu = elgg_view_menu('owner_block', array(
	'entity' => elgg_get_page_owner_entity(),
	'class' => 'category-top',
           ));
           
           echo $content_menu;

        }
        
        ?>
       </div>                   
            
              
            </hgroup>
            
 <?php
 
//Let us now getting the current Url values of user's external sites from user profile fields 
  
   $facebook = $owner->facebooks;
   $googleplus = $owner->googlepluss;
   $youtube = $owner->youtubes;
   $linkedin = $owner->linkedins;
   $twitter = $owner->twitters;
   $feedburner_email = $owner->feedburner_email;
   $feeds_feedburner_rss = $owner->feeds_feedburner_rss;      
       
 ?>                      


<div id="social-icons">

<h3 class="social-icons-title">Follow me on Social Media</h3>

<a title="Follow on Facebook" href="<?php echo $facebook; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/facebook.gif" /></a> 



<a href="<?php echo $twitter; ?>" title="Follow on Twitter" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/twitter.gif" /></a> 



<a title="Follow on Linkedin" href="<?php echo $linkedin; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/linkedin.gif" /></a> 



<a title="Follow on YouTube" href="<?php echo $youtube; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/youtube.gif" /></a> 



<a title="Follow on Google+" href="<?php echo $googleplus; ?>"  target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/google-plus.gif" /></a> 


<a title="Follow with Email" href="<?php echo $feedburner_email; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/email-rss.gif" /></a> 



<a href="<?php echo $feeds_feedburner_rss; ?>" title="Follow with Feed RSS" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/feed-rss.gif" /></a>


</div>


<div id="site-featured">
<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Timeline Lightbox"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-screen-150x150.jpg" class="featured-size wp-post-image" alt="timeline-screen" title="timeline-screen" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>"> Video Box</a> </div>

<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Timeline Map"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-place-150x150.jpg" class="featured-size wp-post-image" alt="timeline-place" title="timeline-place" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>">Site Map</a></div>

<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Timeline Post"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-friends-150x150.jpg" class="featured-size wp-post-image" alt="timeline-friends" title="timeline-friends" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>"> Wire Posts</a></div>

<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Music on Timeline"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-music-150x150.jpg" class="featured-size wp-post-image" alt="timeline-music" title="timeline-music" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>">Popular Music</a></div>            
      </div>
                      

                       

</div>	<!-- #branding -->

	

<script>
function myFunction()
{
alert("If you like to use this profile photo photo on your profile timeline,  remember to click save button");
}

</script>
<script>

function uploadtimelineFunction()
{
alert("Please upload a cover photo file with width= 1000 pixels by height= 288 pixels for better results. Remember to click save button");
}




</script>	



</div>

<div id="usermenubranding" >  <!-- open user menu -->
	<?php 
	
	
	if (elgg_is_logged_in()) {
	
		$user = elgg_get_logged_in_user_entity();
		
		if ($owner->guid == $user->guid) {
		
					echo elgg_view('output/url', array(
					       //   'id'  => 'usermenubranding',
						'class' => 'homepage_join_link tom-button tom-button-special',
						'href' => '/mod/timeline_theme/background.php', 
						'text' => elgg_view_icon('users') . elgg_echo('Add Cover photo'),
					//	'link_class' => 'tom-button tom-button-delete',
					
					'contexts' => array('profile'),
					
					'priority' => 1,
					
					));


                                            echo elgg_view('output/url', array(
						'class' => 'homepage_color_link tom-button tom-button-special',
						'href' => '/mod/timeline_theme/colors.php', 
						'text' => elgg_view_icon('users') . elgg_echo('Change Color'),
					//	'link_class' => 'tom-button tom-button-delete',
					
					'contexts' => array('profile'),
					
					'priority' => 2,// priority don't work at the moment
					
					));


                                    

                                            echo elgg_view('output/url', array(
						'class' => 'homepage_profile_link tom-button tom-button-special',
						'href' => '/profile/$user->username', 
						
						'name' => 'editprofile',
                                                'href' => "/profile/$user->username/edit",
						'text' => elgg_view_icon('users') . elgg_echo('profile:edit'),
						
						
					//	'link_class' => 'tom-button tom-button-delete',
					
					'contexts' => array('profile'),
					
					'priority' => 3,// priority don't work at the moment
					
					));
                                     }
                                     
                                     }

			// Our shearch bar
	//	echo elgg_view('search/header',array(

?>

 </div>  <!-- close user buttons   -->






<div>
<!-- TM start modify  -->

           <?php
           
       
           
           if (array_key_exists('value', $vars)) {
                    $value = $vars['value'];
            } elseif ($value = get_input('q', get_input('tag', NULL))) {
                    $value = $value;
            } else {
                    $value = elgg_echo('Search for places, people, and many more...');
            }
            

            // @todo - why the strip slashes?
            $value = stripslashes($value);

            // @todo - create function for sanitization of strings for display in 1.8
            // encode <,>,&, quotes and characters above 127
            if (function_exists('mb_convert_encoding')) {
                    $display_query = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
            } else {
                    // if no mbstring extension, we just strip characters
                    $display_query = preg_replace("/[^\x01-\x7F]/", "", $value);
            }
            $display_query = htmlspecialchars($display_query, ENT_QUOTES, 'UTF-8', false);
           
           
           
            ?>
      
      <?php
      if (elgg_is_logged_in()) {
         ?>   
        <div id="searchbranding" >    
         
            <form action="<?php echo elgg_get_site_url(); ?>search" method="get">
                <div class="searchk_area">
                    <input type="text" class="searchk_txt left" name="q" maxlength="59" spellcheck="false" autocomplete="off"value="<?php echo elgg_echo('Search for places, people, and many more...'); ?>" onblur="if (this.value=='') { this.value='<?php echo elgg_echo('Search for places, people, and many more...'); ?>' }" onfocus="if (this.value=='<?php echo elgg_echo('Search for places, people, and many more...'); ?>') { this.value='' };" />
                    <input type="submit" class="searchk_btn right"  value="<?php echo elgg_echo('search:go'); ?>"/>
                </div>
                <div class="clear"></div>
            </form>
 



<!--# TM end search  -->

</div> <!-- # End of search section         -->

<?php
}
?>
	


	</div> <!-- # End of Header  container         -->
	
	
	
	
<?php }


 else {






 ?> <!-- close else condition container to holder -- the headers contentents  -->
 
 
 
 
 
 
 <!-- Open the Group section -- the headers contentents  -->
 
 
 
 <!-- open container to holder -- the headers contentents  -->
<?php 
 
	$group = elgg_get_page_owner_entity();

if (!empty($group) && elgg_instanceof($group, "group")) {



 ?> <!-- open container to holder -- the headers contentents  -->


<div id="timeline-hearder-container" > <!-- open container to holder -- the headers contentents  -->




<!-- TM: The Javascript is causing issues with elgg widgets so don't uncomment unless you are testing or deburging   <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js"></script> -->


<script>
$(document).ready(function(){

	// hide #footer-logo
	$("#footer-logo").hide();
	
	// fade in #footer-logo
	$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('#footer-logo').fadeIn();
			} else {
				$('#footer-logo').fadeOut();
			}
		});

		// scroll body to 0px on click
		$('#footer-logo a').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
		
	
		// scroll body to 0px on click
		$('#back-top').click(function () {
			$('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
		
	});
	
	

	$(".anchor_post").each(function () {
		var id = this.id;
		
		$("#"+id).click(function (){
			
			
			$(".anchor_post").parent().removeClass("anchor_post_current");
			$(this).parent().addClass("anchor_post_current");
			
			if(id=='back-top') 
				return
			
			anchor_href = $("#"+id).attr('href'); // #mm_yyyy
			if($(anchor_href).length==false){
				location.href='/date/'+anchor_href.substring(4)+'/'+anchor_href.substring(1,3)
			}
			else
			{
				var p = $(anchor_href);
				var position = p.position();


				$('body,html').animate({
					scrollTop: (position.top-60)
				}, 800);
			}
			
		});
	});
	
	
	
	

});
</script>


<!-- TM: THe Javascript to load the page to the top was loading here but now extended to the footer -->

<?php

// let us grab the onwner icons or groups icons

$owner = elgg_get_page_owner_entity(); // depending on which view you are using this function in




if ($owner instanceof ElggEntity) {

$icon = elgg_view("profile/icon",array('entity' => $owner, 'size' => 'medium'));

//TM:  Elgg users and groups have their own icons now let get them and run with them to the finish line. Is that not something! :)
if ($owner instanceof ElggUser || $owner instanceof ElggGroup) {

$display = "<div id=\"photo-elggheader\">" . $icon . "</div>";

	// let us grab the default timeline files "banners"
	
	$wallpaper_path = "mod/timeline_theme/graphics/wallpapers";
	$imageArray = array();	
	$dir_handle = opendir($CONFIG->path . $wallpaper_path);
	while (($file = readdir($dir_handle)) !== false) {
		if ($file!='.' && $file!='..' && !is_dir($dir.$entry)){
			$dotPosition = strrpos($file,".");
			if($dotPosition){
				$shortFileName = substr($file,0,$dotPosition);
				$imageArray[elgg_echo($shortFileName)] = $wallpaper_path . "/" . str_replace(" ", "%20",$file);
			}
		}
	}
	
	// load default images
	foreach($imageArray as $name=>$image){
		if($i == 4) {
			$i = 0;
		}
    


}
//$current_user = get_loggedin_userid();
  
		$current_user = elgg_get_page_owner_guid();
                 $currentConfig = get_timeline_style_from_metadata($current_user, 'timelinestyletimeline');

			// check for previously uploaded timeline
		$filehandler = new ElggFile();
		$filehandler->owner_guid = $current_user;
		$filehandler->setFilename('customtimeline');
		if($filehandler->exists()){
			$imageUrl = 'pg/timeline_theme/getbackground?id=' . $current_user;

}

}
  
 
// echo $display; // just for testing

}





?>



<!--[if lt IE 9]>
<script src="<?php echo elgg_get_site_url(); ?>/mod/timeline_theme/views/default/js/html5.js" type="text/javascript"></script>
<![endif]-->


		                    
            <div id="box-scrool-bar">

            <div class="timeline-scroll-bar">
                
                <li class="anchor_post_current"><a id="back-top" class='anchor_post' href="#top">Now</a></li>
              
            <?php 
                    
                // only users can have archives at present

      if ($owner instanceof ElggUser || $owner instanceof ElggGroup) {  
       
             $loggedin_user = elgg_get_logged_in_user_entity();
  
             $page_owner = elgg_get_page_owner_entity();
    

     if (elgg_instanceof($page_owner, 'user')) {
	$url_segment = 'blog/archive/' . $page_owner->username;
        } else {
	$url_segment = 'blog/group/' . $page_owner->getGUID() . '/archive';
         }

        // This is a limitation of the URL schema.
      if ($page_owner && $vars['page'] != 'friends') {
	$dates = get_entity_dates('object', 'blog', $page_owner->getGUID());
	
	if ($dates) {
//		$title = elgg_echo('show:timeline');
		foreach ($dates as $date) {
			$timestamplow = mktime(0, 0, 0, substr($date,4,2) , 1, substr($date, 0, 4));
			$timestamphigh = mktime(0, 0, 0, ((int) substr($date, 4, 2)) + 1, 1, substr($date, 0, 4));

			$link = elgg_get_site_url() . $url_segment . '/' . $timestamplow . '/' . $timestamphigh;
			
			$month = elgg_echo('date:month:' . substr($date, 4, 2), array(substr($date, 0, 4))) ;
        
            
                      $okebe .= "<li><a href=\"$link\" title=\"$month\">$month</a></li>";
			
			
		}

		echo elgg_view_module('aside', $title, $okebe);
	}
        }  
               
           }
    
               ?>
               
            </div><!-- #timeline-scroll-bar -->
            </div><!-- #box-scrool-bar -->
            
            
            
            
  


<div id="page" class="hfeed timeline-separator">
      
 
     <?php  
     
   //  TM: Warning! DO NOT MOVE THIS DOWN FUTURE: ICON CAN NOT DISPLAY BELOW HEADER: It took me ten minutes to find out
     
       echo $display; // Icon display
     
     
      ?>   
           
<div id="branding"  role="banner">

 

	
<?php
	
			// check for previously uploaded timeline_theme
		$filehandler = new ElggFile();
		$filehandler->owner_guid = $current_user;
		$filehandler->setFilename('customtimeline'); 
		
		
		//TM: let us now check if the imageurl is equl to the user's configured image
		
		if($imageUrl = $currentConfig["timeline-image"]){
        
	 ?>
	 
	
			
	<a href="<?php echo elgg_get_site_url();?>">
<img src="<?php echo elgg_get_site_url(); ?><?php echo $imageUrl?> " style='position:relative; width:990px; height:288px; top: 0px' alt="" />
							</a>

	<?php }
		else {  // not working well here
           
           
        ?>
        
  <?php  
  
   // let us load the default image

  if($image != $currentConfig['timeline-image']){

  if (!empty($image)) {

$pleaseloaddefaultimage = ' . $CONFIG->wwwroot . $image . ';


?>
    
        
		
	<a href="<?php echo elgg_get_site_url();?>"> <img src= "<?php echo $pleaseloaddefaultimage ?>" style='position:relative; width:990px; height:288px;  top: 0px;' alt="" />
							</a>
			

<?php }} }



?>





<?php  if (elgg_get_site_url() === current_page_url()){?>

<a href="<?php echo elgg_get_site_url();?>">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/headers/pine-cone.jpg" width="1000" height="288" alt="" />
							</a>	

<?php } ?>

			<hgroup>
			
		
		

 
<?php 





 // Let us check if no context let use diplay the title of the page if context is == NULL let us grab site name

// TM: works like charm :) Now take a sip of glass of water - this is cool
  if (elgg_get_site_url() === current_page_url()){?>
  
<div id="photo-header"><img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-photo.jpg"></div>	

            
<h1 id="site-title"><span><a href="<?php echo elgg_get_site_url();?>" title="<?php echo $site_url; ?>" rel="home"><?php echo $site_name; ?></a></span></h1>
<?php } else { ?>


<h1 id="site-description"><span><a href="<?php echo elgg_get_site_url();?>" title="<?php echo $site_url; ?>" rel="home"><?php echo $vars['title']; ?></a></span></h1>

<?php } ?>       
            
    <h2 id="site-description">The Official <?php echo $site_name; ?> Site</h2>
    
    </span></h1>           
                     
        <div>	
        
        <?php
        
        if (!elgg_is_logged_in()) {
        // insert site-wide navigation
        echo elgg_view_menu('site');
        
        }
        
        if (elgg_is_logged_in()) {
        // content links
     $content_menu = elgg_view_menu('owner_block', array(
	'entity' => elgg_get_page_owner_entity(),
	'class' => 'category-top',
           ));
           
           echo $content_menu;

        }
        
        ?>
       </div>                   
            
              
            </hgroup>
            
 <?php
 
//Let us now getting the current Url values of user's external sites from user profile fields 
  
   $facebook = $owner->facebooks;
   $googleplus = $owner->googlepluss;
   $youtube = $owner->youtubes;
   $linkedin = $owner->linkedins;
   $twitter = $owner->twitters;
   $feedburner_email = $owner->feedburner_email;
   $feeds_feedburner_rss = $owner->feeds_feedburner_rss;      
       
 ?>                      


<div id="social-icons">

<h3 class="social-icons-title">Follow me on Social Media</h3>

<a title="Follow on Facebook" href="<?php echo $facebook; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/facebook.gif" /></a> 



<a href="<?php echo $twitter; ?>" title="Follow on Twitter" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/twitter.gif" /></a> 



<a title="Follow on Linkedin" href="<?php echo $linkedin; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/linkedin.gif" /></a> 



<a title="Follow on YouTube" href="<?php echo $youtube; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/youtube.gif" /></a> 



<a title="Follow on Google+" href="<?php echo $googleplus; ?>"  target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/google-plus.gif" /></a> 


<a title="Follow with Email" href="<?php echo $feedburner_email; ?>" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/email-rss.gif" /></a> 



<a href="<?php echo $feeds_feedburner_rss; ?>" title="Follow with Feed RSS" target="_blank">
<img src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/images/social-icons/feed-rss.gif" /></a>


</div>


<div id="site-featured">
<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Timeline Lightbox"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-screen-150x150.jpg" class="featured-size wp-post-image" alt="timeline-screen" title="timeline-screen" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>"> Video Box</a> </div>

<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Timeline Map"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-place-150x150.jpg" class="featured-size wp-post-image" alt="timeline-place" title="timeline-place" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>">Site Map</a></div>

<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Timeline Post"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-friends-150x150.jpg" class="featured-size wp-post-image" alt="timeline-friends" title="timeline-friends" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>"> Wire Posts</a></div>

<div class="featured-box"><a href="<?php echo elgg_get_site_url();?>" title="Music on Timeline"><img width="150" height="150" src="<?php echo elgg_get_site_url(); ?>mod/timeline_theme/graphics/icons/timeline-music-150x150.jpg" class="featured-size wp-post-image" alt="timeline-music" title="timeline-music" /></a><a class="title-featured" href="<?php echo elgg_get_site_url();?>">Popular Music</a></div>            
      </div>
                      

                       

</div>	<!-- #branding -->

	

<script>
function myFunction()
{
alert("If you like to use this profile photo photo on your profile timeline,  remember to click save button");
}

</script>
<script>

function uploadtimelineFunction()
{
alert("Please upload a cover photo file with width= 1000 pixels by height= 288 pixels for better results. Remember to click save button");
}




</script>	



</div>

<div id="usermenubranding" >  <!-- open user menu -->
	<?php 
	
	if (elgg_is_logged_in()) {
	
			$group = elgg_get_page_owner_entity();

        	if (!empty($group) && elgg_instanceof($group, "group")) {
		if (group_custom_layout_allow($group) && $group->canEdit()) {
		
		// add menu item for group admins to edit layout
                     elgg_register_menu_item("page", array(
                         //                   echo elgg_view('output/url', array(
						'class' => 'homepage_profile_link tom-button tom-button-special',
						
						
						'name' => 'group_layout',
                                                'href' => "group_custom_layout/" . $group->getGUID(),
						'text' => elgg_view_icon('users') . elgg_echo('group_custom_layout:edit'),
								
					//	'link_class' => 'tom-button tom-button-delete',
					
					        'contexts' => array('groups'),
					
					        'priority' => 1,// priority don't work at the moment
					
					        ));
                                     }
                                     
                                     
                                     }
                                     
                                     }

			// Our shearch bar
	//	echo elgg_view('search/header',array(

?>

 </div>  <!-- close user buttons   -->






<div>
<!-- TM start modify  -->

           <?php
           
       
           
           if (array_key_exists('value', $vars)) {
                    $value = $vars['value'];
            } elseif ($value = get_input('q', get_input('tag', NULL))) {
                    $value = $value;
            } else {
                    $value = elgg_echo('Search for places, people, and many more...');
            }
            

            // @todo - why the strip slashes?
            $value = stripslashes($value);

            // @todo - create function for sanitization of strings for display in 1.8
            // encode <,>,&, quotes and characters above 127
            if (function_exists('mb_convert_encoding')) {
                    $display_query = mb_convert_encoding($value, 'HTML-ENTITIES', 'UTF-8');
            } else {
                    // if no mbstring extension, we just strip characters
                    $display_query = preg_replace("/[^\x01-\x7F]/", "", $value);
            }
            $display_query = htmlspecialchars($display_query, ENT_QUOTES, 'UTF-8', false);
           
           
           
            ?>
      
      <?php
      if (elgg_is_logged_in()) {
         ?>   
        <div id="searchbranding" >    
         
            <form action="<?php echo elgg_get_site_url(); ?>search" method="get">
                <div class="searchk_area">
                    <input type="text" class="searchk_txt left" name="q" maxlength="59" spellcheck="false" autocomplete="off"value="<?php echo elgg_echo('Search for places, people, and many more...'); ?>" onblur="if (this.value=='') { this.value='<?php echo elgg_echo('Search for places, people, and many more...'); ?>' }" onfocus="if (this.value=='<?php echo elgg_echo('Search for places, people, and many more...'); ?>') { this.value='' };" />
                    <input type="submit" class="searchk_btn right"  value="<?php echo elgg_echo('search:go'); ?>"/>
                </div>
                <div class="clear"></div>
            </form>
 



<!--# TM end search  -->

</div> <!-- # End of search section         -->

<?php
}
?>
	


	</div> <!-- # End of Header  container         -->
	
	
	
	
<?php }
 
 } // close condition else
 
 ?>   <!-- close Groups here  buttons   -->
 
 
