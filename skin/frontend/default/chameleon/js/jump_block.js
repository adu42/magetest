jQuery(function(){
	jQuery('.div_hint').css("display","none");
		jQuery('.div_hint_block').hover(
			function () {
			jQuery(this).find('.div_hint').css("display","block");
			jQuery(this).find('.div_hint').stop(true, true).animate({
 
				top:-50,opacity:1
			//	display:'block'
			  }, 400, function() {
				// Animation complete.
			  });
			 },
			 function () {
			 	
			jQuery(this).find('.div_hint').animate({

												   	top:0,opacity:0
												   });

			 })		
	});