jQuery(document).ready(function(){
	jQuery("select.sumoselect").SumoSelect();

	var url = 'http://rocket9.cafe24.com/';

	jQuery("button.button-shipping-locations").click(function(){
		jQuery("div.modal-shipping-location-list").show();
	});

	jQuery("button.button-close-modal").click(function(){
		jQuery("div.modal-shipping-location-list").hide();
	});

	jQuery("select.wooahan-select-memo").change(function(){
		jQuery("input.wooahan-input-memo").val(jQuery(this).val());
	});

	jQuery("div.modal-shipping-location-list").find("button.button-insert-location").click(function(){
		var location = jQuery(this).attr("data-location");
		var receiver = jQuery(this).attr("data-receiver");
		var address1 = jQuery(this).attr("data-address1");
		var address2 = jQuery(this).attr("data-address2");
		var phone1 	 = jQuery(this).attr("data-phone1");
		var phone2 	 = jQuery(this).attr("data-phone2");
		var postcode = jQuery(this).attr("data-postcode");
		var memo 	 = jQuery(this).attr("data-memo");

		var phone1_1 = '';
		var phone1_2 = '';
		var phone1_3 = '';

		var phone2_1 = '';
		var phone2_2 = '';
		var phone2_3 = '';

		jQuery("input.wooahan-input-receiver").val(receiver);
		jQuery("input.wooahan-input-location").val(location);
		var phone1_arr = phone1.split("-");
		var phone2_arr = phone2.split("-");

		jQuery.each(phone1_arr, function(i){
			if(i == 0){
				phone1_1 = phone1_arr[i];
				jQuery("select.wooahan-input-phone1-num1")[0].sumo.selectItem(phone1_1);
				jQuery("select.wooahan-input-phone1-num1")[0].sumo.reload();
			}
			if(i == 1){
				phone1_2 = phone1_arr[i];
				jQuery("input.wooahan-input-phone1-num2").val(phone1_2);
			}
			if(i == 2){
				phone1_3 = phone1_arr[i];
				jQuery("input.wooahan-input-phone1-num3").val(phone1_3);
			}
		});


		jQuery.each(phone2_arr, function(i){
			if(i == 0){
				phone2_1 = phone2_arr[i];
				jQuery("select.wooahan-input-phone2-num1")[0].sumo.selectItem(phone2_1);
				jQuery("select.wooahan-input-phone2-num1")[0].sumo.reload();
			}
			if(i == 1){
				phone2_2 = phone2_arr[i];
				jQuery("input.wooahan-input-phone2-num2").val(phone2_2);
			}
			if(i == 2){
				phone2_3 = phone2_arr[i];
				jQuery("input.wooahan-input-phone2-num3").val(phone2_3);
			}
		});

		jQuery("input.post-code").val(postcode);
		jQuery("input.address-1").val(address1);
		jQuery("input.address-2").val(address2);
		jQuery("input.wooahan-input-memo").val(memo);


		jQuery("button.button-close-modal").trigger("click");
	});

	jQuery("button.button-pay").click(function(){
		if(jQuery("div#wooahan-form-fields").length){

			jQuery("div#wooahan-order-pay").find("div.wooahan-pay-loader").show();

			var receiver 	= '';
			var location 	= '';
			var phone1_1	= '';
			var phone1_2 	= '';
			var phone1_3 	= '';

			var phone2_1 	= '';
			var phone2_2 	= '';
			var phone2_3 	= '';

			var postcode 	= '';
			var address1 	= '';
			var address2 	= '';
			var memo 		= '';

			var agree 		= false;
			var locationAdd = false;
			var defaultChk  = false;

			var form 		= jQuery("form#order_review");
			var order_id 	= jQuery("input.order-id").val();
			var fields 		= jQuery("div#wooahan-form-fields");
				receiver 	= fields.find("input.wooahan-input-receiver").val();
				location 	= fields.find("input.wooahan-input-location").val();
				phone1_1  	= fields.find("select.wooahan-input-phone1-num1").val();
				phone1_2  	= fields.find("input.wooahan-input-phone1-num2").val();
				phone1_3  	= fields.find("input.wooahan-input-phone1-num3").val();
				phone2_1  	= fields.find("select.wooahan-input-phone2-num1").val();
				phone2_2 	= fields.find("input.wooahan-input-phone2-num2").val();
				phone2_3 	= fields.find("input.wooahan-input-phone2-num3").val();
				postcode 	= fields.find("input.post-code").val();
				address1	= fields.find("input.address-1").val();
				address2 	= fields.find("input.address-2").val();
				memo 		= fields.find("input.wooahan-input-memo").val();
				agree 		= jQuery("input.wooahan-checkbox-pay-agree").prop("checked");
				locationAdd = jQuery("input.wooahan-checkbox-location-add").prop("checked");
				defaultChk  = jQuery("input.wooahan-checkbox-default-location").prop("checked");

			if(!receiver) {
				alert('수령인을 기입하시기 바랍니다.');
				return false;
			}

			if(fields.find("input.wooahan-input-location").length && !location){
				alert('배송지명을 기입하시기 바랍니다.');
				return false;
			}
			if(!phone1_2 || !phone1_3){
				alert('연락처를 기입하시기 바랍니다.');
				return false;
			}

			if(phone2_1 != "0" && (!phone2_2 || !phone2_3)){
				alert('연락처2를 올바르게 기입하시기 바랍니다.');
				return false;
			}

			if(!postcode || !address1){
				alert('우편번호 버튼을 클릭하여 우편번호와 주소를 올바르게 기입하시기 바랍니다.');
				return false;
			}

			if(agree == false){
				alert('결제진행 동의에 체크하셔야 합니다.');
				return false;
			}

			//console.log(jQuery("ul.methods").find("input[name='payment_method']").val());

			jQuery.ajax({
				url : wooahanAjax.ajaxurl,
				type : 'post',
				dataType : 'json',
				data : {
					action : 'wooahan_save_shipping_info',
					order_id 	: order_id,
					receiver 	: receiver,
					location 	: location,
					phone1 		: phone1_1+'-'+phone1_2+'-'+phone1_3,
					phone2 		: phone2_1+'-'+phone2_2+'-'+phone2_3,
					postcode 	: postcode,
					address1 	: address1,
					address2 	: address2,
					agree 	 	: agree,
					locationAdd : locationAdd,
					defaultChk 	: defaultChk,
					memo 		: memo,
					payment_method : jQuery("ul.methods").find("input[name='payment_method']:checked").val()
				},
				success : function( response ){;
					//console.log(response);
					jQuery("div#wooahan-order-pay").find("div.wooahan-pay-loader").hide();
					form.submit();
				},
				complete : function(){

				}
			});	


		}
	});
});




