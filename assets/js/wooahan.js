jQuery(document).ready(function(){



	jQuery("body").click(function(e){
		if(!jQuery(e.target).hasClass("select-text") && !jQuery(e.target).hasClass("dashicons")){
			jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
			jQuery("div#wooahan-variation").find("div.select-text").find("span.dashicons").removeClass("dashicons-arrow-up-alt2");
			jQuery("div#wooahan-variation").find("div.select-text").find("span.dashicons").addClass("dashicons-arrow-down-alt2");
		}
	});

	jQuery('div#wooahan-variation').find("div.select-notreq-wrapper").find('div.select-text').click(function(e){
		e.preventDefault();

		if(jQuery(this).parent().hasClass("active")){
			jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
			jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
			jQuery(this).find("span.dashicons").removeClass("dashicons-arrow-up-alt2");
			jQuery(this).find("span.dashicons").addClass("dashicons-arrow-down-alt2");
		} else {
			jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
			jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
			jQuery(this).parent().addClass("active");
			jQuery(this).find("span.dashicons").removeClass("dashicons-arrow-down-alt2");
			jQuery(this).find("span.dashicons").addClass("dashicons-arrow-up-alt2");
		}
	});

	jQuery('div#wooahan-variation').find("div.select-wrapper").find('div.select-text').click(function(){

		var select_count = jQuery("div#wooahan-variation").find("div.select-wrapper").length;

		if(select_count > 1 && jQuery(this).parent().prev("div.select-wrapper").length && !jQuery(this).parent().prev("div.select-wrapper").find("li.option.active").length){
			return false;
		}
		if(jQuery(this).parent().hasClass("active")){
			jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
			jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
			jQuery(this).find("span.dashicons").removeClass("dashicons-arrow-up-alt2");
			jQuery(this).find("span.dashicons").addClass("dashicons-arrow-down-alt2");
		} else {
			jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
			jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
			jQuery(this).parent().addClass("active");
			jQuery(this).find("span.dashicons").removeClass("dashicons-arrow-down-alt2");
			jQuery(this).find("span.dashicons").addClass("dashicons-arrow-up-alt2");
		}
	});

});