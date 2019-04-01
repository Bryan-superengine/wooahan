jQuery(document).ready(function(){

	var variations = [];
	var options    = jQuery.parseJSON(jQuery("form.variations_form").attr("data-attributes"));
	var notReq 	   = jQuery.parseJSON(jQuery("form.variations_form").attr("wooahan-notreq-variations"));
	var type 	   = jQuery("form.variations_form").attr("data-type");

	//console.log(options);

	var options_app = new Vue({
		el:'#optionList',
		data: {
			items: options,
			result: options,
			notreq: notReq,
			selected : {}
		},
		methods: {
			notreq_toggle: function(index){


				var added_variations = [];
				jQuery.each(variation_app.items, function(k,v){
					added_variations.push(this.variation_id);
				});

				//console.log(added_variations);
				
				// 선택된 상품이 있을때 variations 에서 deactive
				if(added_variations.length){
					//console.log(allKeys);
					var origin_notreq = this.notreq;
					jQuery.each(this.notreq, function(k,v){

						jQuery.each(this, function(key,value){
							//console.log(this);
							if(added_variations.indexOf(this.variation_id) != -1){
								//console.log(this.variation_id);
								options_app.notreq[k][key].is_active = false;
							} else {
								//origin_notreq[k][key].is_active = true;
							}
						});
					});
				}

				if(jQuery("#optionList").find("div.select-wrapper-notreq-"+index).hasClass("active")){
					jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
					jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
					jQuery("#optionList").find("div.select-wrapper-notreq-"+index).find("span.dashicons").removeClass("dashicons-arrow-up-alt2");
					jQuery("#optionList").find("div.select-wrapper-notreq-"+index).find("span.dashicons").addClass("dashicons-arrow-down-alt2");
				} else {
					jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
					jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
					jQuery("#optionList").find("div.select-wrapper-notreq-"+index).addClass("active");
					jQuery("#optionList").find("div.select-wrapper-notreq-"+index).find("span.dashicons").removeClass("dashicons-arrow-down-alt2");
					jQuery("#optionList").find("div.select-wrapper-notreq-"+index).find("span.dashicons").addClass("dashicons-arrow-up-alt2");
				}
			},
			toggle : function(index){
				var options_length = jQuery("div#wooahan-variation").find("div.select-wrapper").length * 1;
				if(options_length == 1){

					var added_variations = [];
					jQuery.each(variation_app.items, function(k,v){
						added_variations.push(this.variation_id);
					});


					//console.log(added_variations);
					// 선택된 상품이 있을때 variations 에서 deactive
					if(added_variations.length){
						//console.log(allKeys);
						var variations = jQuery.parseJSON(jQuery("form.variations_form").attr("data-product_variations"));

						jQuery.each(options_app.items, function(k,v){
							//console.log(this);
							jQuery.each(this.options, function(key,value){
								//console.log(this);

								//console.log(value);
								if(added_variations.indexOf(this.variation_id) != -1){
									//console.log(this.variation_id);
									options_app.items[k].options[key].details.variation_is_active = false;
								}
							});
						});

						//console.log(options_app.items);

					}

				}

				if(jQuery("#optionList").find("div.select-wrapper-"+index).prev("div.select-wrapper").length){
					if(!jQuery("#optionList").find("div.select-wrapper-"+index).prev("div.select-wrapper").find("li.option.active").length){
						return false;
					}
				}
				//jQuery("#optionList").find("div.select-wrapper").removeClass("active");
				//jQuery("#optionList").find("div.select-wrapper-"+index).addClass("active");

				if(jQuery("#optionList").find("div.select-wrapper-"+index).hasClass("active")){
					jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
					jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
					jQuery("#optionList").find("div.select-wrapper-"+index).find("span.dashicons").removeClass("dashicons-arrow-up-alt2");
					jQuery("#optionList").find("div.select-wrapper-"+index).find("span.dashicons").addClass("dashicons-arrow-down-alt2");
				} else {
					jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
					jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
					jQuery("#optionList").find("div.select-wrapper-"+index).addClass("active");
					jQuery("#optionList").find("div.select-wrapper-"+index).find("span.dashicons").removeClass("dashicons-arrow-down-alt2");
					jQuery("#optionList").find("div.select-wrapper-"+index).find("span.dashicons").addClass("dashicons-arrow-up-alt2");
				}

			},
			notreq_checker: function(index, key, name, variation_id){

				var variations = jQuery.parseJSON(jQuery("form.variations_form").attr("data-product_variations"));
				var title = name;
				var price;
				var min_qty;
				var max_qty;
				var is_in_stock;
				var qty;

				jQuery.each(variations, function(k,v){
					if(variation_id == this.variation_id){
						price 		= this.display_price;
						min_qty 	= this.min_qty;
						max_qty 	= this.max_qty;
						is_in_stock = this.is_in_stock;
						qty 		= min_qty;						
					}
				});

				var args 				= new Array();
				args['title'] 			= decodeURIComponent(key)+' : '+title;
				args['variation_id'] 	= variation_id;
				args['price'] 			= price;
				args['min_qty'] 		= min_qty;
				args['max_qty'] 		= max_qty;
				args['is_in_stock'] 	= is_in_stock;
				args['qty']				= qty;
				args['result_price']	= price;
				args['formatted_price'] = price.format();

				//중복 검사
				var added_items = variation_app.items;
				var duplicator  = 0;
					for (var i = 0; i < added_items.length; i++) {
						if(variation_id == added_items[i]['variation_id']){
							duplicator++;
						}
					}
				if(duplicator == 0){
					variation_app.items.push(args);
					variation_app.isActive = true;
					check_variations_qty_price();
				} else {
					alert('이미 등록된 옵션 입니다.');
				}

				jQuery("div#wooahan-variation").find("div.select-wrapper").removeClass("active");
				jQuery("div#wooahan-variation").find("div.select-notreq-wrapper").removeClass("active");
				jQuery("#optionList").find("div.select-wrapper-notreq-"+index).find("span.dashicons").removeClass("dashicons-arrow-up-alt2");
				jQuery("#optionList").find("div.select-wrapper-notreq-"+index).find("span.dashicons").addClass("dashicons-arrow-down-alt2");

			},
			variation_checker: function(index, key, item_title, item_name, in_stock, origin_details){
				//console.log(origin_details);
				//index, key, item.name, variation.name, variation.details.is_in_stock, variation

				var variation_type = jQuery("div#wooahan-variation").find("input.variation-type").val();

				var options_length = jQuery("div#wooahan-variation").find("div.select-wrapper").length * 1;

				if(variation_type == 'merge_sep'){
					index = key;
				}

				if(index < options_length -1){
					if(variation_type == 'merge_sep'){
						this.selected[item_title] = item_name;
					} else {
						this.selected[key] = item_name;
					}
					
					//console.log(this.selected);
				}
				//console.log(index+'-'+options_length);
				var variations = jQuery.parseJSON(jQuery("form.variations_form").attr("data-product_variations"));
				var added_variations = [];
				jQuery.each(variation_app.items, function(k,v){
					added_variations.push(this.variation_id);
				});

				var selected_arr = [];
				var allKeys = [];
				var calculated = [];

				if(options_length == 1){
					var last_options = this.items[key].options;
				} else {
					var last_options = this.items[options_length - 1].options;
				}
				//console.log(last_options);
				//console.log(index+'---'+(options_length -2));
				if(index == (options_length - 2)){
					//console.log(variations);
					jQuery.each(variations, function(){
						var this_v = this;					

						allKeys[this_v.variation_id] = {};
						allKeys[this_v.variation_id]['variation_id'] = this_v.variation_id;
						allKeys[this_v.variation_id]['details'] = this_v;

						var cnt = 0;

						//console.log(variations);
						jQuery.each(this.attributes, function(k, v){
							if(cnt <= options_length){

								var each_key = decodeURIComponent(k);

									each_key = each_key.replace("attribute_", "");
									
									//console.log(each_key);
									if(v != ""){
										allKeys[this_v.variation_id][each_key] = v;
									}
									//console.log(allKeys[this_v.variation_id]);
							}
							cnt++;
						});
					});

					allKeys = allKeys.filter(Boolean);
					//console.log(allKeys);
					var result 	= '';
					//console.log(this.selected);
					jQuery.each(this.selected, function(k,v){
						if(result != ''){
							result = result +'/'+ k.toLowerCase() +':' + v;
						} else {
							result = k.toLowerCase() + ':' + v;
						}
					});

					//console.log(result);

					jQuery.each(allKeys, function(k,v){
						var selector = '';

						// 선택된 상품이 있을때 variations 에서 deactive
						if(added_variations.length){
							//console.log(allKeys);
							if(added_variations.indexOf(this.variation_id) != -1){
								this.details.variation_is_active = false;
							}
						}	

						jQuery.each(this, function(key, value){
							if(key != 'variation_id' && key != 'details'){
								//console.log(key);
								if(selector != ''){
									selector = selector +'/'+ key.toLowerCase() +':' + value;
								} else {
									selector = key.toLowerCase() + ':' + value;
								}
							}
						
						});
						// sanitize_text 영향으로 모든 텍스트를 lowercase 로 변환하고 띄어쓰기는 하이푼(-)으로 변경한다.
						result = result.replace(/ /gi, '-');
						//console.log(selector+'######'+result.replace(/ /gi, '-'));
						if(selector.indexOf(result) != -1){
							//console.log(allKeys[k]);
							//console.log(this.items);
							calculated.push(allKeys[k]);
						}
					});
					//console.log(allKeys);
					//console.log(this.selected);
					//console.log(calculated);
					var last_key = this.items[options_length - 1].name;
						last_key = last_key.replace(/ /gi, '-');
					//console.log(last_key);
					var conText = [];
					//console.log(calculated);
					jQuery.each(calculated, function(k,v){
						//var conText = '';
						//console.log(this);
						conText.push({'name' : this[last_key], 'variation_id' : this.variation_id, 'details' : this.details});

						//console.log(conText);
					});

					//console.log(conText);

					this.items[options_length - 1].options = conText;

				}

				if(options_length == 1 || (options_length >= 2 && index == options_length - 1)){
					//console.log('test');

					var variations = jQuery.parseJSON(jQuery("form.variations_form").attr("data-product_variations"));
					var title = '';
					if(options_length >= 2){
						var count = 0;
						jQuery.each(this.selected, function(k,v){
							if(count == 0){
								title = v;
							} else {
								title = title + ' / ' + v;
							}
							count++;
						});
						title = title + ' / ' + item_name;
					} else {
						title = item_name;
						if(variation_type == 'merge_sep'){
							title = item_title + ' : ' + item_name;
						}
					}

					//console.log(title);
					var price;
					var min_qty;
					var max_qty;
					var is_in_stock;
					var qty;
					//console.log(origin_details);
					jQuery.each(variations, function(k,v){
						//console.log(origin_details);
						if(origin_details.variation_id == this.variation_id){
							//console.log(this.variation_id);
							price 		= this.display_price;
							min_qty 	= this.min_qty;
							max_qty 	= this.max_qty;
							is_in_stock = this.is_in_stock;
							qty 		= min_qty;						
						}
					});

					var args 				= new Array();
					args['title'] 			= title;
					args['variation_id'] 	= origin_details.variation_id;
					args['price'] 			= price;
					args['min_qty'] 		= min_qty;
					args['max_qty'] 		= max_qty;
					args['is_in_stock'] 	= is_in_stock;
					args['qty']				= qty;
					args['result_price']	= price;
					args['formatted_price'] = price.format();

					//중복 검사
					var added_items = variation_app.items;

					var duplicator  = 0;
						for (var i = 0; i < added_items.length; i++) {
							if(origin_details.variation_id == added_items[i]['variation_id']){
								duplicator++;
							}
						}

					if(is_in_stock == false){
						alert('해당 상품은 품절 입니다.');
						return false;
					}
					if(duplicator == 0){
						variation_app.items.push(args);
						variation_app.isActive = true;
						check_variations_qty_price();
					} else {
						alert('이미 등록된 옵션 입니다.');
					}
				}
				//console.log(args);
			}
		}
	});
	var variations = [];
	var variation_app = new Vue({
	  el: '#wooahan-add-to-cart',
	  data: {
	  	isActive: false,
	    items: variations,
	    totalPrice: 0,
	    totalQuantity: 0
	  },
	  methods: {
	  	direct_buy: function(){

	  		if(this.items.length == 0){
	  			alert('옵션을 선택하지 않으셨습니다. 옵션을 선택해 주세요.');
	  			return false;
	  		}

	  		var is_variable = jQuery('div#wooahan-add-to-cart').find("input[name=is_variable]").val();
  			var data = {
  				action : 'wooahan_direct_buy',
  				security : wooahanAjax.security,
  				data : jQuery('form.variations_form').serialize(),
  				dataType : 'JSON',
  				type : 'post'
  			};
  			jQuery.post(wooahanAjax.ajaxurl, data, function(response){
  				//console.log(response);
  				response = jQuery.parseJSON(response);
  				//console.log(response);
  				if(response.status == 'success'){
  					location.href=response.callback;
  				}
  			});
	  	},
	  	remove_variation: function (variation_id){
	  		var added_items = this.items;
	  		//console.log('test');
	  		var new_items   = new Array();
			for (var i = 0; i < added_items.length; i++) {
				if(variation_id != added_items[i]['variation_id']){
					new_items.push(added_items[i]);
				}
			}
			if(new_items.length > 0){
				this.isActive = true;
			} else {
				this.isActive = false;
			}
			this.items = new_items;
			check_variations_qty_price();	  		
	  	},
	  	qty_plus : function (variation_id){
	  		var added_items = this.items;
	  		var new_items = new Array();
			for (var i = 0; i < added_items.length; i++) {
				if(variation_id == added_items[i]['variation_id']){
					added_items[i]['qty'] = added_items[i]['qty'] + 1;
					var qty = added_items[i]['qty'] * 1;
					added_items[i]['result_price'] = (added_items[i]['price'] * 1) * qty;
					added_items[i]['formatted_price'] = ((added_items[i]['price'] * 1) * qty).format();
					new_items.push(added_items[i]);
				} else {
					new_items.push(added_items[i]);
				}
				check_variations_qty_price();
				//console.log(added_items[i]);
			}
			this.items = new_items;
	  	},
	  	qty_minus : function (variation_id){
	  		var added_items = this.items;
	  		var new_items = new Array();
			for (var i = 0; i < added_items.length; i++) {
				if(variation_id == added_items[i]['variation_id']){
					if(added_items[i]['qty'] > 1){
						added_items[i]['qty'] = added_items[i]['qty'] - 1;
						var qty = added_items[i]['qty'] * 1;
						added_items[i]['result_price'] = (added_items[i]['price'] * 1) * qty;
						added_items[i]['formatted_price'] = ((added_items[i]['price'] * 1) * qty).format();
					}
					new_items.push(added_items[i]);
				} else {
					new_items.push(added_items[i]);
				}
				check_variations_qty_price();
				//console.log(added_items[i]);
			}
			this.items = new_items;
	  	}
	  }
	});

	jQuery("div#wooahan-add-to-cart").find("button.wooahan-variation-add-to-cart").click(function(){
		if(variation_app.items.length == 0){
			alert('옵션을 선택하지 않으셨습니다. 옵션을 선택해 주세요.');
			return false;
		} else {
			//console.log(wooahanAjax.ajaxurl);
			jQuery.ajax({
				url : wooahanAjax.ajaxurl,
				type : 'post',
				dataType : "json",
				data : {
					action  : 'wooahan_variation_add_to_cart',
					data 	: jQuery("form.variations_form").serialize()
				},
				success : function( response ){
					if(response.status == 'success'){
						if(confirm(response.message) == true){
							location.href=response.callback;
						} else {
							return false;
						}
					}
				},
				complete : function(){

				}
			});		
		}	
	});

	jQuery(document).on('submit', 'form.cart', function(){
		var location_href = '';
		jQuery.ajax({
			url : wooahanAjax.ajaxurl,
			type : 'post',
			dataType : "json",
			data : {
				action  : 'wooahan_variation_add_to_cart',
				data 	: jQuery("form.cart").serialize()
			},
			success : function( response ){
				if(response.status == 'success'){
					//console.log(response.quantity);
					if(confirm(response.message) == true){
						location.href=response.callback;
					} else {
						return false;
					}
				}
			},
			complete : function(){

			}
		});
		if(location_href != ''){
			location.href = location_href;
		} else {
			return false;
		}
	});

	jQuery("form.cart").find("button.button-single-direct-buy").click(function(){
  		var is_variable = jQuery('form.cart').find("input[name=is_variable]").val();
  		//console.log(is_variable);
  		var form = jQuery('form.variations_form');
  		if(is_variable == "false"){
  			form = jQuery('form.cart');
  		}
		var data = {
			action : 'wooahan_direct_buy',
			security : wooahanAjax.security,
			data : form.serialize(),
			dataType : 'JSON',
			type : 'post'
		};
		jQuery.post(wooahanAjax.ajaxurl, data, function(response){
			//console.log(response);
			response = jQuery.parseJSON(response);
			//console.log(response);
			if(response.status == 'success'){
				location.href=response.callback;
			}
		});
	});

	jQuery("div#wooahan-variation").find("div.select-wrapper").find("li.option").click(function(e){

		e.preventDefault();

		if(jQuery(this).hasClass("soldout") == false){
			var first_child  = jQuery("div#wooahan-variation").find("div.select-wrapper:first-child");
			var select_count = jQuery("div#wooahan-variation").find("div.select-wrapper").length;
			var data_title 	 = jQuery(this).parent().parent().find("div.select-text").attr("data-title");
			var data_option  = jQuery(this).attr("data-option");

			if(select_count >= 2){
				jQuery(this).parent().parent().find("div.select-text").html(data_title+' / '+data_option+'<span class="dashicons dashicons-arrow-down-alt2"></span>');
			}
			if(select_count == 1){
				jQuery(this).parent().parent().find("div.select-text").html(data_option+'<span class="dashicons dashicons-arrow-down-alt2"></span>');
			}
				jQuery(this).parent().find("li.option").removeClass("active");
				jQuery(this).addClass("active");
		}
	});

	jQuery("div#wooahan-variation").find("div.select-wrapper:last").find("li.option").click(function(e){
		e.preventDefault();

		if(jQuery(this).hasClass("soldout") == true){
			jQuery(this).parent().find("li.option").removeClass("active");
			jQuery(this).addClass("active");	
			jQuery(this).parent().parent().removeClass("active");
			return false;
		}

		jQuery("div#wooahan-variation").find("div.select-wrapper").find("li.option").each(function(){
			jQuery(this).removeClass("active");
		});
		jQuery("div#wooahan-variation").find("div.select-wrapper").find("div.select-text").each(function(){
			jQuery(this).html(jQuery(this).attr("data-title")+'<span class="dashicons dashicons-arrow-down-alt2"></span>');
		});

	});

	function check_variations_qty_price(){
		var qty 	= 0;
		var price 	= 0;
		jQuery.each(variation_app.items, function(k,v){
			qty = qty + this.qty;
			price = price + (this.price * this.qty);
			//console.log(this);
		});

		//console.log(qty);

		variation_app.totalQuantity = qty;
		variation_app.totalPrice 	= price.format();
	}

});

// 숫자 타입에서 쓸 수 있도록 format() 함수 추가
Number.prototype.format = function(){
    if(this==0) return 0;
 
    var reg = /(^[+-]?\d+)(\d{3})/;
    var n = (this + '');
 
    while (reg.test(n)) n = n.replace(reg, '$1' + ',' + '$2');
 
    return n;
};
 
// 문자열 타입에서 쓸 수 있도록 format() 함수 추가
String.prototype.format = function(){
    var num = parseFloat(this);
    if( isNaN(num) ) return "0";
 
    return num.format();
};