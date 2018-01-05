				jQuery(document).ready(function(){
				    jQuery("#backTop").hide();
				    jQuery(function () {
				            jQuery(window).scroll(function(){
				            if (jQuery(window).scrollTop()>1000){
				                jQuery("#backTop").fadeIn(1000);
				                }else{
				                jQuery("#backTop").fadeOut(1000);
				                }
				            });
				            jQuery("#backTop").click(function(){
				                jQuery("body,html").animate({scrollTop:0},1000);
				                return false;
				                });
				        });
				});