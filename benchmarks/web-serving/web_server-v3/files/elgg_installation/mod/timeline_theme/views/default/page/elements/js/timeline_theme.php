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
