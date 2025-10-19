jQuery(document).ready(function() {
	
	jQuery(".wff-more-link").click(function(){
	
		jQuery(this).parent().parent(".wff-post-text").hide();
		jQuery(this).parent().parent(".wff-post-text").next(".more-content").css("display", "block");
		jQuery(this).hide();
		jQuery(this).parent().parent(".wff-post-text").next(".more-content").find(".wff-less-link").show();
		
	});

	jQuery(".wff-less-link").click(function(){
		
		jQuery(this).parent().parent(".more-content").hide();
		jQuery(this).parent().parent().parent('.wff-fb-item').find(".wff-post-text").show();
		jQuery(this).hide();
		jQuery(document).find(".wff-more-link").show();
	});	
}); 