<?php


$engine_dir = dirname(dirname(dirname(__FILE__))). '/engine/';
//  load engine 
require_once $engine_dir . "start.php";

 $index_image_path = "mod/timeline_theme/css/timeline_front_index3.png";

// the three icons
$index_image_one = "mod/timeline_theme/css/photos.png";
$index_image_two = "mod/timeline_theme/css/share.png";
$index_image_three = "mod/timeline_theme/css/search.png";

$footer = elgg_view('page/elements/footer', $vars);

if (elgg_is_logged_in())
{
forward('activity');
}

// Set the content type
header("Content-type: text/html; charset=UTF-8");

$lang = get_current_language();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $lang; ?>" lang="<?php echo $lang; ?>">
<head>

	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" /> 
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" /> 
    
      <link rel="stylesheet" type="text/css" href="mod/timeline_theme/css/demo.css" /> 
        <link rel="stylesheet" type="text/css" href="mod/timeline_theme/css/style.css" />  
	<link rel="stylesheet" type="text/css" href="mod/timeline_theme/css/animate-custom.css" /> 
	<link rel="stylesheet" type="text/css" href="mod/timeline_theme/css/topbar.css" />

   
        <?php echo elgg_view('page/elements/head', $vars); ?>
</head>


    
            
    
    
    <body>
    
    

      
            <!--  top bar -->
    
    	<?php if (!elgg_is_logged_in()) { ?>
    	
    	<div class="timeline_top_bar">												<!-- main top_black bar -->
    <div class="timeline_clear_fix_it timeline_wrapper">                                                                        <!-- wrapper div -->
        <div class="timeline_inner">                                                                                             <!-- opening inner_box -->

<?php

$site = elgg_get_site_entity();

echo "<div id=\"timeline-header-logo\">";
echo elgg_view('output/url', array(
'href' => '/',
'text' => $site->name,
));
echo "</div>";

echo elgg_view_form('login', array('id' => 'timeline-header-login'));

?>

	



</div>
</div>
</div>



										<!-- closing main top bar -->

 <?php } ?>
 
    
 
            </div><!--/ top bar -->
			
	<div class="containerme">		
			

        <div class="container">
            <!--  top bar -->
         
         

        
         
            </div><!--/ top bar -->
            
            
            

         
            
            
            
            
            
            			
                <div  >
                
         <div class="wrapperIndex"><div class="wrappergradientContent"><div class="clearandfix"> <!-- Start of the middle container for our index -->
                
           
            <div class="messagesheading"> <!-- TM: Open error message -->
            
 
 
 
			 
 
            
            
            
			<?php 
		
			
$hereisthemesa = $_SESSION['msg'];
$okebemangongo = elgg_view('page/elements/messages', array(
                        'object' => $_SESSION['msg'],
                        
                        
                        
                        
                        ));			
			
			
			
			
   if (!empty($hereisthemesa)) {

$message = elgg_echo($okebemangongo);
echo "<h3 class=\" error message\">$message</h3>";
} else {

}

unset($_SESSION['msg']);


?>    
                
 </div> <!-- TM: Close error message -->               
                
                
                
                    <!-- hidden anchor to stop jump http://www.css3create.com/Astuce-Empecher-le-scroll-avec-l-utilisation-de-target#wrap4  -->
                    <a class="hiddenanchor" id="toregister"></a>
                    <a class="hiddenanchor" id="tologin"></a>
                    
                  
                    <div id="wrapperimage">       <!--open  The wrapper that will contain our site image -->
                    
                    
                   <div class="wrapperheading"> We can help you share with the people that matters in your life.</div> 
    
    <div >                 

<div id="top_timeline_left">

 
 <?php
 
  $users = elgg_get_entities_from_metadata(array(

 //"icontime" => "", "user" => "", "0" => "", "10" // Original
       // TM: New members added
       'metadata_names' => 'icontime',
	'types' => 'user',
	'limit' => 15,
	//'full_view' => false,
	//'pagination' => false,
	//'list_type' => 'gallery',
	//'gallery_class' => 'elgg-gallery-users',
	//'size' => 'small',

         ));   

       // get the user's main profile picture
         if($users){
         foreach($users as $user){
         echo elgg_view("profile/icon",array(
         'class' => 'tom_homepage_members_link', //Tm: Added Css class for elgg
         'entity' => $user,        
         'full_view' => false,
	 'pagination' => false,
         'list_type' => 'gallery',
	 'gallery_class' => 'elgg-gallery-users',
   //      'size' => 'small',
       'size' => 'medium',
         'override' => 'true'));   
                   }
	           }
                
                // TM: End of edit

   ?>
   
   </div>
      </div>
	      
	      
	      <!--  The defination of the site                -->
	      <div class="timeline_theme_1d timeline_theme_2b"><div class="timeline_theme_1a timeline_theme_1b timeline_theme_1c" style="text-align: center; width: 55px"><img class="img" src="<?php echo elgg_get_site_url(); ?><?php echo  $index_image_one?>" alt="" style="vertical-align: middle"></div><div class="timeline_theme_1a timeline_theme_1b timeline_theme_desc"><span class="timeline_theme_1d timeline_theme_1e timeline_theme_1f timeline_theme_1g timeline_theme_1h"> Browse photos and new updates </span><span class="timeline_theme_2c timeline_theme_2d timeline_theme_2e timeline_theme_2f"> from your friends in News Dashboard. </span></div></div>
	      
	      



<div class="timeline_theme_1d timeline_theme_2b"><div class="timeline_theme_1a timeline_theme_1b timeline_theme_1c" style="text-align: center; width: 55px"><img class="img" src="<?php echo elgg_get_site_url(); ?><?php echo  $index_image_two?>" alt="" style="vertical-align: middle"></div><div class="timeline_theme_1a timeline_theme_1b timeline_theme_desc"><span class="timeline_theme_1d timeline_theme_1e timeline_theme_1f timeline_theme_1g timeline_theme_1h"> Update and see what's new </span><span class="timeline_theme_2c timeline_theme_2d timeline_theme_2e timeline_theme_2f"> in your profile Timeline. </span></div></div>





<div class="timeline_theme_1d timeline_theme_2b"><div class="timeline_theme_1a timeline_theme_1b timeline_theme_1c" style="text-align: center; width: 55px"><img class="img" src="<?php echo elgg_get_site_url(); ?><?php echo  $index_image_three?>" alt="" style="vertical-align: middle"></div><div class="timeline_theme_1a timeline_theme_1b timeline_theme_desc"><span class="timeline_theme_1d timeline_theme_1e timeline_theme_1f timeline_theme_1g timeline_theme_1h"> Get the latest updates and messages </span><span class="timeline_theme_2c timeline_theme_2d timeline_theme_2e timeline_theme_2f"> from your family and friends. </span></div></div>


	      
	      
	      
	      
	      
	      <!--   Close the defination of the site                  -->
	      
	      
	      
	      
                    
                    </div>         <!-- close The wrapper that will contain our site image -->
                    
                    
                    <div id="wrapper">
                    
                    
                    
                    
                    
                     
                    
                     <!-- open the login form -->
                     
 							<?php
$ts = time();
$token = generate_action_token($ts);
?>                    
                    
                         
                        
                        
                         <!-- close the login form -->
                        
                        

                        <div id="register" class="animate form">
                            <form  method="post" action="action/register" class="elgg-form" autocomplete="on"> 
							
							<input type="hidden" name="__elgg_token" value="<?php echo $token; ?>" />
<input type="hidden" name="__elgg_ts" value="<?php echo $ts; ?>" />
                               
                              <div class="timeline_theme_1i timeline_theme_1j timeline_theme_1k-"><div class="timeline_theme_1l timeline_theme_1m timeline_theme_1n timeline_theme_1o timeline_theme_1p">
                              Sign Up
                              </div><div class=" timeline_theme_1w timeline_theme_1x timeline_theme_1y timeline_theme_1z ">
                              The social networking is at your fingertips.
                              </div></div>
                               
                               
                                 
								
				 <p>			
                                    <label for="usernamesignup" class="uname" data-icon="u">Your display name</label>
                                    <input id="usernamesignup" class="timeline_theme_2a"   name="name" required="required" type="text" placeholder="Enter Display Name" />
                               
                             </p>
                                 <p> 
                                 	
                                    <label for="usernamesignup" class="uname" data-icon="u">Your username</label>
                                    <input id="usernamesignup" class="timeline_theme_2a" name="username" required="required" type="text" placeholder="Enter Username" />
                                
                               
                                 
                                 </p>
                                 
                                <p> 
                                    <label for="emailsignup" class="youmail" data-icon="e" > Your email</label>
                                    <input id="emailsignup"  class="timeline_theme_2a"  name="email" required="required" type="email" placeholder="Enter Email"/> 
                                </p>
                                <p> 
                                    <label for="passwordsignup" class="youpasswd" data-icon="p">Your password </label>
                                    <input id="passwordsignup" class="timeline_theme_2a"   name="password" required="required" type="password" placeholder="Enter Password"/>
                                </p>
                                <p> 
                                    <label for="passwordsignup_confirm" class="youpasswd" data-icon="p">Please confirm your password </label>
                                    <input id="passwordsignup_confirm" class="timeline_theme_2a"   name="password2" required="required" type="password" placeholder="Repeat Password"/>
                                </p>
								
							
					<?php  echo elgg_view('register/extend');?>			
								
								
					
					<input type="hidden" name="friend_guid" />
<input type="hidden" name="invitecode" />
<input type="hidden" value="register" name="action" />
                                <p class="signin button"> 
									<input type="submit" value="Sign up"  name="submit" class="elgg-button elgg-button-submit"/> 
								</p>
                                <p class="change_link">  
									Already a member ?
									<a href="login" class="to_register"> Go and log in </a>
								</p>
                            </form>
                        </div>
						
                    </div>
                    
                    
                    </div>  </div> </div>  <!-- Closing the middle container for our index -->
                    
                   </div>
                   
                   
                   




          	
             	<div class="._elgg_timeline">
<div id="elgg_timelineFooterContent">


</div>

</div> <!-- closing _elgg_timeline -->





<div class="elgg-page-footer">
<div class="elgg-inner">

<?php echo $footer; ?>

</div>
</div>

</div>
<?php echo elgg_view('page/elements/foot'); ?>


</body>
</html>