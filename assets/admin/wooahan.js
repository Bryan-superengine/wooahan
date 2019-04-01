jQuery(document).ready(function(){

	jQuery("form#post").keydown(function(e){
		if(e.keyCode == 13){
			e.preventDefault();
			return false;			
		}
	});

	jQuery("#optionCreate").on('show.bs.modal', function(event){
		var type  		= jQuery("div#set-option").find("input.option-type-input:checked").val();
		var req_count 	= jQuery("div#set-option").find("input.option-required:checked").length;

		var modal = jQuery(this);
			if(type != 'indivisual' && req_count == 0){
				modal.find(".modal-body div.col").html('독립 선택형을 제외하고 필수옵션은 반드시 1개 이상 선택하셔야 합니다.');
				modal.find(".modal-header .modal-title").html('필수옵션 미선택');
				modal.find(".modal-footer button.btn-option-create").hide();
				modal.find(".modal-footer button.btn-secondary").html('확인');
			} else {
				modal.find(".modal-body div.col").html('기존 등록된 옵션 품목들은 모두 초기화 됩니다.<br>해당 품목으로 옵션을 생성 하시겠습니까?');
				modal.find(".modal-header .modal-title").html('옵션 품목추가');
				modal.find(".modal-footer button.btn-option-create").show();
				modal.find(".modal-footer button.btn-secondary").html('취소');
			}	
	});

	jQuery("div#set-basic").find("button.button-save-post-name").click(function(){
		var origin  = jQuery(this);
		var oldName = jQuery(this).attr("data-old-post-name");
		var newName = jQuery(this).parent().find("input.input-post-name").val();
		var postID  = jQuery(this).attr("data-post-id");

		jQuery(this).parent().find("span.ajax-loader").css("display", "inline-block");
		jQuery(this).hide();

		if(oldName != newName){
			//console.log('save');
			jQuery.ajax({
				url : ajaxurl,
				type : 'post',
				dataType : "json",
				data : {
					action  : 'wooahan_change_permalink',
					post_name : newName,
					post_id : postID
				},
				success : function( response ){
					if(response.status == 'success'){
						origin.parent().find("span.permallink").find("span.post-name").html(response.post_name);
						origin.parent().find("a.button-preview").attr("href", response.permalink+response.post_name+"/");
						jQuery("div#set-basic").find("button.button-cancle-post-name").trigger("click");
						origin.parent().find("span.ajax-loader").hide();
					}
				},
				complete : function(){

				}
			});
		} else {
			show_modal('permalerror', '오류!', '기존 주소와 다른 주소를 기입하시기 바랍니다.');
		}
	});

	jQuery("div#set-basic").find("button.button-change-post-name").click(function(){
		jQuery(this).parent().find("span.post-name").hide();
		jQuery(this).parent().find("input.input-post-name").attr("type", "text");
		jQuery(this).parent().find("input.input-post-name:text").select();
		jQuery(this).parent().find("button.button-change-post-name").hide();
		jQuery(this).parent().find("a.button-preview").hide();
		jQuery(this).parent().find("button.button-save-post-name").show();
		jQuery(this).parent().find("button.button-cancle-post-name").show();
	});

	jQuery("div#set-basic").find("button.button-cancle-post-name").click(function(){
		jQuery(this).parent().find("span.post-name").show();
		jQuery(this).parent().find("input.input-post-name").attr("type", "hidden");
		jQuery(this).parent().find("button.button-change-post-name").show();
		jQuery(this).parent().find("a.button-preview").show();
		jQuery(this).parent().find("button.button-save-post-name").hide();
		jQuery(this).parent().find("button.button-cancle-post-name").hide();		
	});

    Vue.directive('sortable', {
        inserted: function (el) {
            var sortable = new Sortable(el, options)

            if (this.arg && !this.vm.sortable) {
                this.vm.sortable = {}
            }

            //  Throw an error if the given ID is not unique
            if (this.arg && this.vm.sortable[this.arg]) {
                console.warn('[vue-sortable] cannot set already defined sortable id: \'' + this.arg + '\'')
            } else if( this.arg ) {
                this.vm.sortable[this.arg] = sortable
            }
        },
        bind: function (el, binding) {
            this.options = binding.value || {};
        }
    });



	var allBadges 	= jQuery.parseJSON(jQuery("div#set-badge").attr("data-badges"));
	var addedBadges = jQuery.parseJSON(jQuery("div#set-badge").attr("data-added-badges"));
	var badgesKeys 	= jQuery.parseJSON(jQuery("div#set-badge").attr("data-added-badges-keys"));
	if(!addedBadges){
		addedBadges = [];
	}
	if(!badgesKeys){
		badgesKeys = [];
	}
	var badge_app = new Vue({
		el : '#set-badge',
		data: {
			badges: allBadges,
			addedBadges : addedBadges,
			addedKeys : badgesKeys,
			searchKeyword : '',
			tempBadges : []
		},
		mounted() {
			jQuery('[data-toggle="popover"]').popover();
		},
		methods : {
			searchBadge(){
				var self = this;
				var origin 	 = self.badges;
				if(self.searchKeyword){
					//console.log(self.searchKeyword.length);
					self.badges = self.badges.filter(function(badge){
						var result = badge.title.match(self.searchKeyword);
						if(result){
							return result;
						}
					});
				} else {
					self.badges = allBadges;
				}
			},
			customBadgeRemove : function(badge_url){
				var self = this;
				//console.log(badge_url);
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  	: 'wooahan_remove_custom_badge',
						badge_url 	: badge_url
					},
					success : function( response ){
						if(response.status == 'success'){
							self.badges 	= response.data;
						}
					},
					complete : function(){

					}
				});	
			},
			badgeUpload : function(){
				var self = this;

				var title = jQuery("input#customBadgeTitle").val();
				var size  = jQuery("input#customBadgeSize").val();
				var file  = jQuery("input#customBadgeFile")[0].files[0];

				if(!title || !size || !file){
					alert('업로드시 필요한 타이틀, 사이즈, 파일을 기입/업로드 해주시기 바랍니다.');
					return false;
				}

				var form = jQuery("form#post")[0];
				var formData = new FormData(form);
				formData.append("action", "wooahan_custom_badge_upload");
				formData.append("badge_file", jQuery("input#customBadgeFile")[0].files[0]);
				formData.append("badge_title", jQuery("input#customBadgeTitle").val());
				formData.append("badge_size", jQuery("input#customBadgeSize").val());

				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					processData: false,
					contentType: false,
					dataType : "json",
					data : formData,
					success : function( response ){
						if(response.status == 'success'){
							self.badges = response.data;
						} else {
							alert(response.message);
						}
					},
					complete : function(){

					}
				});	
			},
			add : function(key){
				//console.log(key);
				//console.log(this.addedKeys.indexOf( key ));
				if(this.addedKeys.indexOf( key*1 ) == -1){
					this.addedBadges.push(this.badges[key]);
					//console.log(this.addedBadges);
					this.addedKeys.push(key);
					setTimeout(function(){
						badge_app.priorityChange();
					}, 500);
					//console.log(this.addedKeys);
				} else {
					alert('이미 등록 되어 있습니다.');
				}
			},
			onUpdate : function(){
				this.priorityChange();
			},
			priorityChange : function(){
				jQuery("ul.added-badges-ul").find("li.added-badge").each(function(i){
					jQuery(this).find("span.badge-priority").html(i+1);
					jQuery(this).find("input.added-badge-url").attr("name", "wooahan[badges]["+i+"][url]");
					jQuery(this).find("input.added-badge-width").attr("name", "wooahan[badges]["+i+"][width]");
					jQuery(this).find("input.added-badge-margin-top").attr("name", "wooahan[badges]["+i+"][margin][top]");
					jQuery(this).find("input.added-badge-margin-right").attr("name", "wooahan[badges]["+i+"][margin][right]");
					jQuery(this).find("input.added-badge-margin-bottom").attr("name", "wooahan[badges]["+i+"][margin][bottom]");
					jQuery(this).find("input.added-badge-margin-left").attr("name", "wooahan[badges]["+i+"][margin][left]");
					jQuery(this).find("input.added-badge-title").attr("name", "wooahan[badges]["+i+"][title]");
				});
			},
			remove: function(key){
				this.addedBadges.splice(key, 1);
				this.addedKeys.splice(key, 1);
			}
		}
	});

	badge_app.priorityChange();

	var categoriesData   = jQuery.parseJSON(jQuery("table#category-table").find("tbody.cat-tbody").attr("data-cat"));
	var addedTerms 		 = jQuery.parseJSON(jQuery("table#category-table").find("tbody.cat-tbody").attr("added-terms"));

	//console.log(addedTerms);

	var firstData 		 = [];
	var secondData 		 = [];
	var thirdData 		 = [];
	jQuery.each(categoriesData, function(k,v){
		//console.log(this);
		if(this.parent == 0 && this.name != '미분류'){
			firstData.push(this);
		}
	});

	//console.log(categoriesData);
	var category_app = new Vue({
		el : '#category-table',
		data: {
			items: {
				first : firstData,
				second : [],
				third : []
			},
			addedCategories : [],
			added : [],
			checkedFirst : 0,
			checkedSecond : 0,
			checkedThird : 0,
			checkedIds : 0
		},
		methods : {
			checkNext : function(type, term_id){
				jQuery("table#category-table").find("li.each-cat-"+term_id).parent().find("li").removeClass("active");
				jQuery("table#category-table").find("li.each-cat-"+term_id).addClass("active");
				secondData 		= [];
				thirdData  		= [];
				jQuery.each(categoriesData, function(k,v){
					//console.log(this);
					if(this.parent == term_id){		
						switch(type){
							case 1 :		
								secondData.push(this);
							break;

							case 2 :
								thirdData.push(this);
							break;
						}
					}
				});

				switch(type){
					case 1 :
						this.checkedFirst = term_id;
						if(secondData.length == 0){
							if(this.isExist(term_id) == false){
								this.addedCategories.push( [ term_id ] );
								//console.log(this.checkedFirst);
								jQuery("table#category-table").find("li").removeClass("active");
							}
							this.items.second = [];
							this.items.third  = [];
						} else {
							this.items.second = secondData;
						}
					break;

					case 2 :
						this.checkedSecond = term_id;
						if(thirdData.length == 0){	
							if(this.isExist(term_id) == false){
								this.addedCategories.push( [this.checkedFirst, term_id ]);
								//console.log(this.checkedFirst+'-'+this.checkedSecond);
								jQuery("table#category-table").find("li").removeClass("active");
							}
							this.items.third = [];
						} else {
							this.items.third = thirdData;
						}
					break;

					case 3 :
						this.checkedThird = term_id;
						if(this.isExist(term_id) == false){	
							this.addedCategories.push( [ this.checkedFirst, this.checkedSecond, term_id ] );
							jQuery("table#category-table").find("li").removeClass("active");
							//console.log(this.checkedFirst+'-'+this.checkedSecond+'-'+this.checkedThird);
						}
					break;
				}

				this.added = [];
				
				jQuery.each(this.addedCategories, function(k,v){
					category_app.added[k] = [];
					category_app.added[k]['cats'] = [];
					jQuery.each(this, function(key, value){
						//console.log(category_app.getTerm(value));
						category_app.added[k]['cats'].push( category_app.getTerm(value) );
					});
				});

				//console.log(this.added);

			},
			getTerm : function(term_id){
				var returnData = [];
				jQuery.each(categoriesData, function(k,v){
					//console.log(this);
					if(this.term_id == term_id){
						returnData = this;
					}
				});
				return returnData;
			},
			isExist : function(term_id){
				var duplicated = 0;
				jQuery.each(this.addedCategories, function(k,v){
					jQuery.each(this, function(key, value){
						if(value == term_id){
							duplicated++;
						}
					});
				});
				//console.log(duplicated);
				if(duplicated == 0){
					return false;
				} else {
					return true;
				}
				
			},
			remove : function(this_key){
				//jQuery("span.selected-categories").find("li.cat-key-"+this_key).remove();
				//console.log(this_key);
				this.addedCategories.splice(this_key*1, 1);
				this.added.splice(this_key*1, 1);
				//console.log(this.addedCategories);
			}
		}
	});

	jQuery("#optionTemplateRegist").on('show.bs.modal', function(event){
		var table = jQuery("table.attributes-table");
		var modal = jQuery(this);
		var template_options = [];
		var added_color 	 = [];
		var added_thumbnail  = [];
			table.find("tbody.option-value-tbody").find("tr").each(function(i){
				var option_name 	= jQuery(this).find("input.added-name").val();
				var option_style 	= jQuery(this).find("input.added-option-style").val();
				var required 		= jQuery(this).find("input.option-required").prop("checked");
				var data_option 	= jQuery(this).find("button.btn-edit-details").attr("data-option");
				if(jQuery(this).find("input.added-color-input").length){
					added_color = [];
					jQuery(this).find("input.added-color-input").each(function(){
						var adding = { 'name' : jQuery(this).attr("data-name"), 'value' : jQuery(this).val() };
						added_color.push(adding);
					});
				} else {
					added_color = [];
				}

				if(jQuery(this).find("input.added-thumbnail-input").length){
					added_thumbnail = [];
					jQuery(this).find("input.added-thumbnail-input").each(function(){
						var adding = { 'name' : jQuery(this).attr("data-name"), 'value' : jQuery(this).val() };
						added_thumbnail.push(adding);
					});
				} else {
					added_thumbnail = [];
				}

				if(jQuery(this).find("input.option-required").attr("readonly") == 'readonly'){
					required 		= false;
				}

				var options 		= [];
				jQuery(this).find("td.option-list").find("div.options").find("span.option-row").each(function(){
					options.push(jQuery(this).find("input.added-value").val());
				});

				var each_options = { 'name' : option_name, 'style' : option_style, 'required' : required, 'options' : options, 'color' : added_color, 'thumbnail' : added_thumbnail, 'jsonOptions' : data_option };
				template_options.push(each_options);
				//console.log(each_options);
			});

			modal.find("button.button-template-regist").click(function(e){
				e.preventDefault();
				var template_code = jQuery("#optionTemplateRegist").find("input.option-template-code").val();
				var template_name = jQuery("#optionTemplateRegist").find("input.option-template-name").val();
				var template_desc = jQuery("#optionTemplateRegist").find("input.option-template-desc").val();

				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  : 'wooahan_option_template_regist',
						code 	: template_code,
						name 	: template_name,
						desc 	: template_desc,
						options : template_options
					},
					success : function( response ){
						jQuery("#optionTemplateRegist").modal('hide');

					},
					complete : function(){

					}
				});	
			});
	});

	jQuery("input[name='wooahan[option_use]']").click(function(){
		//console.log('test');
	});

	jQuery("div#wooahan-wrap").find("div.column-wrapper").find("div.section-wrap").each(function(i){
		if(i == 0){
			jQuery(this).show();
		} else {
			jQuery(this).hide();
		}
	});

	var origin = jQuery("#wooahan-wrap");

	check_option_priority();
	var variations = [];
		variations = jQuery("table.variation-table").attr("data-variations");
		variations = jQuery.parseJSON(variations);
		//console.log(variations);


		Vue.component('colorPicker', {
		    props: ['value'],
		    template: '<input type="text">',
		    mounted: function () {
		        var vm = this;
		        jQuery(this.$el)
		            .val(this.value)
		            // WordPress color picker
		            .wpColorPicker({
		                defaultColor: this.value,
		                    change: function(event, ui) {
		                    // emit change event on color change using mouse
		                    vm.$emit('input', ui.color.toString());
		                }});
		    },
		    watch: {
		        value: function (value) {
		            // update value
		            jQuery(this.$el).wpColorPicker('color', value);
		        }
		    },
		    destroyed: function () {
		        jQuery(this.$el).off().wpColorPicker('destroy'); // (!) Not tested
		    }
		});


		var variation_app = new Vue({
		  el: '#variation-init',
		  data: {
		    items: variations
		  }
		});

		var attributes_app = new Vue({
			el: '#set-option',
			data: {
				variations : variations,
				templates : [],
				attributes : [],
				detail : {
					name : '',
					style : '',
					options : []
				}
			},
			created : function(){
				jQuery(".option-color-picker").wpColorPicker();
			},
			methods : {
				// variation handlers
				optionDetail : function(key, title, type){
					//console.log('test');

					var self 			= this;
					var attr_name 		= title;
					var options 		= self.attributes[key];
					var options_value 	= options.value.split("|");
					var option_colors 		= options.color;
					var option_thumbnails 	= options.thumbnails;
					var option_style 		= options.style;



					var optionArray = [];
					jQuery.each(options_value, function(k,v){

						var key = jQuery.trim(v);

						if(option_colors == undefined || option_colors == 'undefined'){
							var color = '';
						} else {
							var color = option_colors[key];
						}

						if(option_thumbnails == undefined || option_thumbnails == 'undefined'){
							var thumb = '';
						} else {
							var thumb = option_thumbnails[key];
						}
						

						if(color == undefined || color == 'undefined'){
							color = '';
						}
						if(thumb == undefined || thumb == 'undefined'){
							thumb = '';
						}
						optionArray.push({ name : key, color : color, thumbnails : thumb, style : option_style } );
					});

					//console.log(self.detail);

					self.detail = { name : attr_name, style : option_style, options : optionArray };
					//self.detail.name = attr_name;
					//self.detail.style = option_style;
					//self.detail.options = optionArray;
					jQuery("div#optionDetails").find("ul.option-style-ul").find("li").removeClass("active");
					jQuery("div#optionDetails").find("ul.option-style-ul").find("li").each(function(){
						var style = jQuery(this).attr("option-title");
						if(style == option_style){
							jQuery(this).addClass("active");
						}
					});
					//console.log(self.detail);
					jQuery("#optionDetails").find("button.button-option-detail-save").attr("data-key", key);
					jQuery("#optionDetails").find("button.button-option-detail-save").attr("data-type", type);

					jQuery("#optionDetails").modal('show');

				},
				variationHandlers : function(){
					var self = this;
					return {
						selectAll : function(){
							var variation_ids = [];
							jQuery("tbody.variation-tbody").find("input.option-check:checked").each(function(){
								variation_ids.push(jQuery(this).val());
							});
							if(variation_ids == 0){
								alert('옵션을 먼저 선택하시기 바랍니다.');
								jQuery("div#set-option").find("div.option-progress").hide();
								return false;
							}
							return variation_ids;							
						},
						remove: function(){
							jQuery("div#set-option").find("div.option-progress").show();
							var variation_ids = self.variationHandlers().selectAll();
							if(variation_ids != false){							
								jQuery.ajax({
									url : ajaxurl,
									type : 'post',
									dataType : 'JSON',
									data : {
										action : 'wooahan_delete_variation',
										product_id 	 : jQuery("input#post_ID").val(),
										variation_id : variation_ids
									},
									success : function( response ){
										switch(response.status){
											case 'success' :
												self.variations = response.data;
												self.variationHandlers().getVariations();
												jQuery("tbody.variation-tbody").find("input.option-check").prop("checked", false);
												jQuery("div#set-option").find("div.option-progress").hide();
											break;

											case 'failed' :

											break;

											default :

											break;
										}
										//console.log(response);
									},
									complete : function(){
									}
								});	
							}							
						},
						allSave : function(){
							jQuery("tbody.variation-tbody").find("input.option-check").each(function(){
								jQuery(this).prop("checked", true);
							});
							self.variationHandlers().selectedSave();							
						},
						selectedSave : function(){
							jQuery("div#set-option").find("div.option-progress").show();
							var variation_ids = self.variationHandlers().selectAll();

							if(variation_ids != false){
								jQuery.ajax({
									url : ajaxurl,
									type : 'post',
									dataType : 'JSON',
									data : {
										action : 'wooahan_update_variation',
										values : jQuery("form#post").serialize(),
										variation_id : variation_ids
									},
									success : function( response ){
										switch(response.status){
											case 'success' :
												self.variations = response.data;
												self.variationHandlers().getVariations();
												jQuery("tbody.variation-tbody").find("input.option-check").prop("checked", false);
												//jQuery("div#wooahan-wrap").find("tbody.variation-tbody").html(response.data);
												jQuery("div#set-option").find("div.option-progress").hide();
											break;

											case 'failed' :

											break;

											default :

											break;
										}
										//console.log(response);
									},
									complete : function(){
									}
								});		
							}							
						},
						update : function(variation_id){
							jQuery("div#set-option").find("div.option-progress").show();

							jQuery.ajax({
								url : ajaxurl,
								type : 'post',
								dataType : 'JSON',
								data : {
									action : 'wooahan_update_variation',
									values : jQuery("form#post").serialize(),
									variation_id : variation_id
								},
								success : function( response ){
									switch(response.status){
										case 'success' :
											self.variations = response.data;
											self.variationHandlers().getVariations();
											jQuery("div#set-option").find("div.option-progress").hide();
										break;

										case 'failed' :

										break;

										default :

										break;
									}
									//console.log(response);
								},
								complete : function(){
								}
							});	
						},
						getVariations : function(){
							jQuery("div#set-option").find("div.option-progress").show();

							jQuery.ajax({
								url : ajaxurl,
								type : 'post',
								dataType : "json",
								data : {
									action  : 'wooahan_get_variations',
									product_id : jQuery("input#post_ID").val()
								},
								success : function( response ){
									switch(response.status){
										case 'success' :
											self.variations = response.data;
											jQuery("tbody.variation-tbody").find("input.option-check").prop("checked", false);
											jQuery("div#set-option").find("div.option-progress").hide();
										break;

										case 'failed' :

										break;

										default :

										break;
									}
								},
								complete : function(){

								}
							});							
						}
					}
				},
			  	checkAll : function(){
			  		if(jQuery("#template-table").find("input.check-all-option").prop("checked") == true){
			  			jQuery("#template-table").find("input.each-option-checkbox").prop("checked", true);
			  		} else {
			  			jQuery("#template-table").find("input.each-option-checkbox").prop("checked", false);
			  		}
			  	},
			  	select : function(key){
			  		var self = this;
			  		//console.log(this.items[key]);
			  		var selected 		= self.templates[key].options;
			  		var selected_html 	= '';
			  		var option_type 	= jQuery("div#set-option").find("input.option-type-input:checked").val();
			  		self.attributes 	= [];
					jQuery.each(selected, function(k,v){
						var option_values 	= [];
						var optionArray 	= [];
						jQuery.each(this.options, function(okey, oval){
							value = jQuery.trim(oval);
							value = value.replace(" ", "");
							option_values.push(jQuery.trim(value));
							optionArray.push(jQuery.trim(value));
						});
						option_values = option_values.join('|');
						//console.log(option_values);
						var details = '';
						var optionColor = {};
						var optionThumb = {};
						if(this.color != 'undefined'){
							jQuery.each(this.color, function(ckey, cval){
								optionColor[this.name] = this.value;
							});
						}
						if(this.thumbnail != 'undefined'){
							jQuery.each(this.thumbnail, function(tkey, tval){
								optionThumb[this.name] = this.value;		
							});
						}
						var style = '셀렉트박스';
						switch(this.style){
							case 'selectbox' :
								style = '셀렉트박스';
							break;

							case 'color ' :
								 style = '색상선택';
							break;

							case 'thumbnail' :
								style = '썸네일이미지';
							break;

							case 'text' :
								style = '텍스트';
							break;

							default :
								style = this.style;
							break;
						}
						var required = '';
						var readonly = '';
						if(this.required == 'true'){
							required = 'checked';
						}
						if(option_type == 'indivisual'){
							readonly = 'readonly';
						}
						self.attributes.push({ 'name' : this.name, 'style' : style, 'is_required' : this.required, 'value' : option_values, 'color' : optionColor, 'thumbnails' : optionThumb, 'optionArray' : optionArray });

					});	  		

			  		self.updatePriority();
			  		jQuery("#optionTemplate").modal('hide');
			  	},
				size : function(obj){
					var size = 0, key;
					for ( key in obj ){
						if( obj.hasOwnProperty(key)) size++;
					}
					return size;
				},
				getAllCount : function(){
					var allCount = 1;
					jQuery("tbody#attributes-tbody").find("tr").each(function(){
						var eachCount = jQuery(this).find("input.added-value").length;
							allCount  = allCount * eachCount;
					});
					return allCount;
				},
				create : function(){
					var title 			= jQuery("input.input-option-name").val();
					var input_values 	= jQuery("input.input-option-value").val();
						values  		= input_values.split(",");
					var addedCount      = this.getAllCount();
						addedCount 		= addedCount * values.length;
						if(addedCount > 50){
							// 우커머스 최대 등록개수 50개로 제한
							alert("등록할 수 있는 최대 옵션 경우의 수는 50가지 입니다.\n상품을 나누시거나 옵션을 단순화 하시기 바랍니다.");
							return false;
						}
						trimValues 		= [];
						jQuery.each(values, function(k,v){
							trimValues.push(jQuery.trim(v));
						});
						values 			= trimValues;
					let options = values.filter( (item, idx, array) => {
						return array.indexOf( item.trim() )  === idx ;
					});

					var inputOptions    = [];
					var defaultColor    = new Object();
					var defaultThumb  	= new Object();
						for (var i = 0; i < options.length; i++) {
							
							var option_value = options[i].trim();
							if(option_value != ''){
								inputOptions.push(jQuery.trim(option_value));
								defaultColor[jQuery.trim(option_value)] = '';
								defaultThumb[jQuery.trim(option_value)] = '';
							}
						}
						input_values = input_values.replace(/,/gi, "|");

						this.attributes.push({ 'name' : title, 'style' : '셀렉트박스', 'is_required' : 'true', 'value' : input_values, 'color' : defaultColor, 'thumbnails' : defaultThumb, 'optionArray' : inputOptions });

						//console.log(this.attributes);

					jQuery("input.input-option-name").val('');
					jQuery("input.input-option-value").val('');

					this.updatePriority();
				},
				onUpdate : function(){
					this.updatePriority();
				},
				thumbnailUpload : function(key){
					//console.log('test');
					var self   = this;
					var button = jQuery("table.table").find("tr.option-"+key).find("button.thumbnail-upload");
					var option_name = self.detail.options[key].name;
					var data_key 	= key;
					//console.log(option_name);
					custom_uploader = wp.media({
						title : 'Insert thumbnail',
						library : {
							type : 'image'
						},
						button : {
							text : 'Use this image'
						},
						multiple : false
					}).on('select', function(){
						var attachment = custom_uploader.state().get('selection').toJSON()[0];
						//console.log(attachment);
						//console.log(attachment);
						button.parent().find("div.added-thumbnail").html('<img src="'+attachment.sizes.thumbnail.url+'"><input type="hidden" class="added-thumbnail-input" name="wooahan[attributes]['+data_key+'][thumbnails]['+option_name+']" value="'+attachment.sizes.thumbnail.url+'" data-name="'+option_name+'">');
						button.hide();
						button.next("button.button-remove-thumbnail").show();

						jQuery("#optionDetails").find("button.button-remove-thumbnail").click(function(){
							jQuery(this).parent().find("div.added-thumbnail").html('');
							jQuery(this).hide();
							jQuery(this).prev("button.button-option-image-upload").show();
						});
					}).open();
					return false;
				},
				updatePriority(){
					this.$nextTick(function(){

						var tbody = jQuery("tbody#attributes-tbody");
							tbody.find("tr").each(function(i){
								if(jQuery(this).find("input.added-value").length){
									jQuery(this).find("input.added-value").attr("name", "wooahan[attributes]["+i+"][value][]");
								}
								jQuery(this).find("input.added-option-style").attr("name", "wooahan[attributes]["+i+"][style]");
								jQuery(this).find("input.option-required").attr("name", "wooahan[attributes]["+i+"][required]");
								jQuery(this).find("input.added-name").attr("name", "wooahan[attributes]["+i+"][name]");
								jQuery(this).find("input.added-values").attr("name", "wooahan[attributes]["+i+"][values]");
								jQuery(this).find("button.btn-go-up").attr("onclick", "option_go_up("+i+");");
								jQuery(this).find("button.btn-go-down").attr("onclick", "option_go_down("+i+");");
								jQuery(this).find("input.added-color-input").each(function(){
									var name = jQuery(this).attr("data-name");
									//console.log(name);
									jQuery(this).attr("name", "wooahan[attributes]["+i+"][color]["+name+"]");
								});
								jQuery(this).find("input.added-thumbnail-input").each(function(){
									var name = jQuery(this).attr("data-name");
									jQuery(this).attr("name", "wooahan[attributes]["+i+"][thumbnails]["+name+"]");
								});
							});	

					});
				}
			},
			mounted(){

				var self = this;

				var attributes = [];
					attributes = jQuery("table.attributes-table").attr("data-attributes");
					attributes = jQuery.parseJSON(attributes);
				var attrArray  = [];
					jQuery.each(attributes, function(k,v){
						attrArray.push(this);
					});
				this.attributes = attrArray;
				this.updatePriority();

				jQuery("#optionTemplate").on('show.bs.modal', function(event){
					//console.log('optiontemplate');
					jQuery.ajax({
						url : ajaxurl,
						type : 'post',
						dataType : "json",
						data : {
							action  : 'wooahan_get_option_templates'
						},
						success : function( response ){
							self.templates = [];
							jQuery.each(response, function(k,v){
								self.templates.push(v);
							});
							//console.log(self.templates);
						},
						complete : function(){

						}
					});
				});

				jQuery("div#optionDetails").find("button.button-option-detail-save").click(function(){
					var modal 	= jQuery("#optionDetails");
					var key 	= jQuery(this).attr("data-key");
					var type  	= jQuery(this).attr("data-type");

					var selectedColor = [];
					var selectedThumb = [];

					//console.log(key+'-'+type);

					modal.find("input.selected-color").each(function(i){
						var value = jQuery(this).val();
						var title = jQuery(this).attr("data-name");

						self.attributes[key].color[title] = value;
						//console.log(value);
					});

					modal.find("input.added-thumbnail-input").each(function(i){
						var value = jQuery(this).val();
						var title = jQuery(this).attr("data-name");

						self.attributes[key].thumbnails[title] = value;
					});

					modal.find("ul.option-style-ul").find("li").each(function(i){
						if(jQuery(this).hasClass("active") == true){
							self.attributes[key].style = jQuery(this).attr("option-title");
						}
					});

					self.detail = { name : '', style : '', options : [] };
					jQuery("div#optionDetails").find("ul.option-style-ul").find("li").removeClass("active");
					jQuery("div#optionDetails").modal('hide');
					
				});

				jQuery("#optionDetails").on('show.bs.modal', function(event){
					var modal 			= jQuery(this);
					modal.find("ul.option-style-ul").find("li").click(function(){
						var option_style = jQuery(this).attr("option-style");
						var option_title = jQuery(this).attr("option-title");
						modal.find("ul.option-style-ul").find("li").removeClass("active");
						jQuery(this).addClass("active");
					});

				});	

			}
		});

	jQuery(".datepicker").datepicker({
		dateFormat: 'yy-mm-dd',
		beforeShow: function(){
			setTimeout(function(){
				jQuery(".ui-datepicker").css("z-index", 9999999);
			}, 0);
		},
		onSelect:function(d, i){
			jQuery("input.check-sale-unlimited").prop("checked", false);
		}
	});

	jQuery("input.check-all-attr").click(function(){
		if(jQuery(this).prop("checked") == true){
			jQuery("input.attribute-checkbox").prop("checked", true);
		} else {
			jQuery("input.attribute-checkbox").prop("checked", false);
		}
	});

	jQuery("span.gallery").find("span.remove").click(function(){
		var nonImage = jQuery(this).attr("data-none-image");
		jQuery(this).parent().html('<span class="gallery-none"><img src="'+nonImage+'"></span>');
	});

	jQuery("table.variation-table").find("input.option-check-toggle").click(function(){
		if(jQuery(this).prop("checked") == true){
			jQuery("table.variation-table").find("tbody.variation-tbody").find("input.option-check").prop("checked", true);
		} else {
			jQuery("table.variation-table").find("tbody.variation-tbody").find("input.option-check").prop("checked", false);
		}
	});

	jQuery("input.input-option-radio").click(function(){
		if(jQuery(this).prop("checked") == true){
			var id = jQuery(this).attr("data-id");
			jQuery("div.collapse-option").hide();
			jQuery("div#"+id).show();
		}
	});

	jQuery("button.btn-checked-attribute-remove").click(function(){
		jQuery("input.attribute-checkbox").each(function(){
			if(jQuery(this).prop("checked") == true){
				jQuery(this).parent().parent().remove();
			}
		});
	});

	jQuery("table.variation-table").find("label.btn-cal").click(function(){
		var add_type = jQuery(this).find("input.btn-cal").val();
		var input_val = jQuery(this).parent().parent().find("input.input-calculate").val() * 1;
		//console.log(input_val);

		var real_price = jQuery(this).parent().parent().find("input.real-price").attr("data-price") * 1;
		switch(add_type){
			case 'plus' :
				real_price = real_price + (input_val * 1);
			break;
			case 'minus' :
				real_price = real_price - (input_val * 1);
			break;
		}
		jQuery(this).parent().parent().find("input.real-price").val(real_price);
	});

	jQuery("table.variation-table").find("input.input-calculate").change(function(){
		var add_type = jQuery(this).parent().find("input.btn-cal:checked").val();
		var real_price = jQuery(this).parent().find("input.real-price").attr("data-price") * 1;
		switch(add_type){
			case 'plus' :
				real_price = real_price + (jQuery(this).val() * 1);
			break;
			case 'minus' :
				real_price = real_price - (jQuery(this).val() * 1);
			break;
		}
		jQuery(this).parent().find("input.real-price").val(real_price);
	});

	jQuery("button.btn-option-create").click(function(){

		var pre_checker = 0;
		var type = jQuery("div#set-option").find("input.option-type-input:checked").val();

		if(type != 'indivisual'){
			var required_checker = 0;
			jQuery("tbody.option-value-tbody").find("input.option-required").each(function(){
				if(jQuery(this).prop("checked") == true){
					required_checker++;
				}
			});
			if(required_checker == 0){
				jQuery("div#optionCreate").modal('hide');
				alert('조합 일체선택형 또는 조합 분리선택형의 경우 필수옵션을 반드시 하나이상 체크하셔야 합니다.');
				return false;
			}
		}
		
		jQuery("div#set-option").find("div.option-progress").show();
		jQuery.ajax({
			url : ajaxurl,
			type : 'post',
			dataType : 'JSON',
			data : {
				action : 'wooahan_create_variations',
				values : jQuery("form#post").serialize(),
				regular_price : jQuery("div#wooahan-wrap").find("input.regular-price").val(),
				sale_price : jQuery("div#wooahan-wrap").find("input.sale-price").val()
			},
			success : function( response ){
				//console.log(response);
				switch(response.status){
					case 'success' :
						attributes_app.variations = response.data;
						jQuery("tbody.variation-tbody").find("input.option-check").prop("checked", false);
						jQuery("div#set-option").find("div.option-progress").hide();
						jQuery("div#optionCreate").modal('hide');
						jQuery("button.btn-publish").trigger("click");
					break;

					case 'failed' :

					break;

					default :

					break;
				}
				//console.log(response);
			},
			complete : function(){
			}
		});		
	});

	jQuery("div.btn-post-type-toggle").find("label").click(function(){
		jQuery("div.btn-post-type-toggle").find("label").removeClass("btn-success");
		jQuery("div.btn-post-type-toggle").find("label").removeClass("focus");
		jQuery(this).addClass("btn-success");
	});

	jQuery("button.btn-variation-expand").toggle(function(){
		var text = jQuery(this).attr("expand-after");
		jQuery(this).html('<i class="fas fa-times"></i>&nbsp;&nbsp;'+text);
		jQuery("div.expand-col").addClass("expand");
		jQuery("body").css("overflow", "hidden");
	}, function(){
		var text = jQuery(this).attr("expand-before");
		jQuery(this).html('<i class="fas fa-expand"></i>&nbsp;&nbsp;'+text);
		jQuery("div.expand-col").removeClass("expand");
		jQuery("body").css("overflow", "auto");
	});

	jQuery("div.col-added-tags").find("i.fa-times").click(function(){
		jQuery(this).parent().remove();
	});

	jQuery(".btn-upload-gallery").click(function(e){
		e.preventDefault();

		var button = jQuery(this);
		var noneImage = button.attr("data-none-image");
		var removeImage = button.attr("data-remove-image");

		custom_uploader = wp.media({
			title : 'Insert thumbnail',
			library : {
				type : 'image'
			},
			button : {
				text : 'Use this image'
			},
			multiple : true
		}).on('select', function(){
			var attachments = custom_uploader.state().get('selection').toJSON();
			//console.log(attachments);
			var gallery_html = '';
			var count = 0;
			jQuery.each(attachments, function(k, v){
				//console.log(this);
				gallery_html = gallery_html + '<span class="gallery"><span class="gallery-wrapper"><img src="'+this.sizes.thumbnail.url+'" class="gallery"><input type="hidden" name="wooahan[gallery][]" value="'+attachments[k].id+'"><span class="remove" data-none-image="'+noneImage+'"><img src="'+removeImage+'"></span></span></span>';
				count++;
			});

			for (var i = count; i < 10; i++) {
				gallery_html += '<span class="gallery"><span class="gallery-wrapper"><span class="gallery-none"><img src="'+noneImage+'"></span></span></span>';
			}

			button.parent().parent().parent().parent().find("div.uploaded-image").find("div.col").html(gallery_html);

			jQuery("span.gallery").find("span.remove").click(function(){
				var nonImage = jQuery(this).attr("data-none-image");
				jQuery(this).parent().html('<span class="gallery-none"><img src="'+nonImage+'"></span>');
			});
		}).open();
	});

	jQuery(".btn-upload-thumbnail").click(function(e){
		e.preventDefault();

		var button = jQuery(this);

		custom_uploader = wp.media({
			title : 'Insert thumbnail',
			library : {
				type : 'image'
			},
			button : {
				text : 'Use this image'
			},
			multiple : false
		}).on('select', function(){
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			button.parent().parent().parent().parent().find("div.uploaded-image").find("div.col").html('<img src="'+attachment.url+'"><input type="hidden" name="wooahan[thumbnail]" value="'+attachment.id+'">');
			button.parent().parent().parent().hide();
			button.parent().parent().parent().parent().find("div.uploaded-image").find("p.h5").show();
			button.parent().parent().parent().parent().find("div.button-row").show();
		}).open();
	});

	jQuery("button.btn-thumbnail-remove").click(function(){
		jQuery("div.col-4").find("div.uploaded-image").find("div.col").html('');
		jQuery("div.col-4").find("div.uploaded-image").find("p.h5").hide();
		jQuery("div.col-4").find("div.button-row").hide();
		jQuery("div.col-4").find("div.empty-row").show();
	});

	jQuery("button.btn-tags-add").click(function(){
		var tags = jQuery("input.input-tags").val();
		if(!tags){
			show_modal('tagerror', '오류!', '등록하실 태그를 콤마 단위로 구분하여 기입해주시기 바랍니다.');
			return false;
		}

			tags = tags.split(",");
			var trimTags = [];
			jQuery.each(tags, function(k, v){
				trimTags.push(jQuery.trim(v));
			});

			var addedTags = [];
			var newTags   = [];
			jQuery("div.col-added-tags").find("span.tag").each(function(){
				addedTags.push(jQuery.trim(jQuery(this).find("input").val()));
			});


			var uniq = trimTags.slice() // 정렬하기 전에 복사본을 만든다.
			.sort(function(a,b){
				return a - b;
			})
			.reduce(function(a,b){
				if (a.slice(-1)[0] !== b) a.push(b); // slice(-1)[0] 을 통해 마지막 아이템을 가져온다.
				return a;
			},[]); //a가 시작될 때를 위한 비어있는 배열

			//console.log(tags);

			jQuery.each(uniq, function(k,v){
				var tag = jQuery.trim(v);
				if(jQuery.inArray(tag, addedTags) == -1){
					newTags.push(tag);
				}
			});

			jQuery.each(newTags, function(){
				jQuery("div.col-added-tags").append('<span class="tag">'+jQuery.trim(this)+' <input type="hidden" name="wooahan[tags][]" value="'+jQuery.trim(this)+'"><i class="fas fa-times"></i></span>');
			});

			jQuery("input.input-tags").val('');

			jQuery("div.col-added-tags").find("i.fa-times").click(function(){
				jQuery(this).parent().remove();
			});
	});

	jQuery("div.product-type-group").find("label").click(function(){

		jQuery("div.product-type-group").find("label").removeClass("active");
		jQuery("div.product-type-group").find("label").removeClass("btn-info");
		jQuery("div.product-type-group").find("label").addClass("btn-secondary");
		jQuery(this).removeClass("btn-secondary");
		jQuery(this).addClass("btn-info");
	});

	jQuery("input.check-sale-unlimited").click(function(){
		if(jQuery(this).prop("checked") == true){
			jQuery("input.datepicker").val('');
		} else {

		}
	});

	/**
	*	카테고리 추가
	*/
	jQuery("button.btn-cat-add").click(function(){
		var origin 	 = jQuery(this);
		var data_cat = jQuery(this).attr("data-cat");
		var value    = jQuery("input."+data_cat).val();
		var post_id  = jQuery("input#post_ID").val();

		jQuery.ajax({
			url : ajaxurl,
			type : 'post',
			dataType : 'JSON',
			data : {
				action : 'wooahan_add_category',
				id  : post_id,
				cat : data_cat,
				val : value
			},
			success : function( response ){
				switch(response.status){
					case 'success' :
						origin.parent().parent().parent().find("div.selector-container").html(response.data);
					break;

					case 'failed' :

					break;

					default :

					break;
				}
				//console.log(response);
			},
			complete : function(){
			}
		});	

	});

	jQuery("button.btn-option-add").click(function(){
		var option_key 		= jQuery("input.option-key").val();
		var option_value 	= jQuery("input.option-value").val();

		jQuery(".sortable").sortable();
	});

	jQuery("div.modal").find("button.button-indi-cancle").click(function(){
		jQuery("div#set-option").find("input.option-type-input:first").prop("checked", true);
	});

	jQuery("div.modal").find("button.button-indi-reset").click(function(){
		jQuery("tbody.option-value-tbody").find("input.option-required").prop("checked", false);
		jQuery("tbody.option-value-tbody").find("input.option-required").attr("readonly", "readonly");
		jQuery("div.modal-content").find("button.btn-option-create").trigger("click");
		jQuery('div#indivisual-notice').modal('hide');
	});

	jQuery("div#set-option").find("input.option-type-input").click(function(){
		if(jQuery(this).prop("checked") == true){
			var type = jQuery(this).val();
			if(type == 'merge_one'){
				jQuery("tbody.option-value-tbody").find("input.option-required").prop("checked", true);
				jQuery("tbody.option-value-tbody").find("input.option-required").prop("readonly", true);
				jQuery("tbody.option-value-tbody").find("input.option-required").hide();
			}
			if(type == 'merge_sep'){
				jQuery("tbody.option-value-tbody").find("input.option-required").show();
				jQuery("tbody.option-value-tbody").find("input.option-required").removeAttr("readonly");
			}
			if(type == 'indivisual'){
				jQuery("tbody.option-value-tbody").find("input.option-required").prop("checked", false);
				jQuery("tbody.option-value-tbody").find("input.option-required").prop("readonly", true);
				jQuery("tbody.option-value-tbody").find("input.option-required").hide();
			}
		}
		//jQuery("tbody.option-value-tbody").find("input.option-required").removeAttr("readonly");
	});

	jQuery("div#wooahan-wrap").find("nav.navbar").find("a.nav-link").click(function(){
		//console.log('test');
		var menu_id = jQuery(this).attr("data-menu");
		jQuery("div#wooahan-wrap").find("div.section-wrap").hide();
		jQuery("nav.navbar").find("li.nav-item").removeClass("active");
		jQuery(this).parent().addClass("active");
		jQuery("div#"+menu_id).show();
		//console.log(menu_id);
	});

	jQuery("div#wooahan-wrap").find("nav.navbar").find("a.navbar-brand").click(function(){
		jQuery("div#wooahan-wrap").find("div.column-wrapper").find("div.section-wrap").hide();
		jQuery("div#wooahan-wrap").find("div.column-wrapper").find("div#set-basic").show();
		jQuery("div#wooahan-wrap").find("nav.navbar").find("li.nav-item").removeClass("active");
		jQuery("div#wooahan-wrap").find("nav.navbar").find("li.nav-item:first-child").addClass("active");
	});


	jQuery("button.btn-discount-submit").click(function(){
		var discount_rate = jQuery("input.discount_rate").val();
		var regular_price = jQuery("input.regular-price").val();

		if(jQuery.isNumeric(discount_rate) == false){
			show_modal('discountModal', '숫자만 기입하시기 바랍니다.', '할인적용은 숫자만 기입하셔야 합니다.');
			return false;
		}
		if(discount_rate > 99){
			show_modal('discountModal', '오류!', '99% 이상의 할인율을 적용할 수 없습니다.');
			return false;
		}
		if(!regular_price || regular_price == 0){
			show_modal('discountModal', '오류!', '상품금액을 먼저 올바르게 기입하시기 바랍니다.');
			return false;			
		}

		discount_rate = discount_rate * 1;
		regular_price = regular_price * 1;

		var sale_price = regular_price * ((100 - discount_rate)/100);
			sale_price = Math.ceil(sale_price);
		jQuery("input.sale-price").val(sale_price);

	});

	jQuery("button.btn-publish").click(function(e){
		origin = jQuery(this).parent().parent();
		content = CKEDITOR.instances.wooahan_editor.getData();
		var post_status = jQuery("input.radio-post-status:checked").val();
		jQuery("textarea#wooahan_editor").html(content);
		jQuery("input#title").val(origin.find("input.product-title").val());
		jQuery("input[name=post_title]").val(origin.find("input.product-title").val());
		//alert(origin.find("input.product-title").val());
		origin.find("span.save-progress").html('<i class="fas fa-spinner fa-spin"></i>');

		var post_id  = jQuery("input#post_ID").val();

		jQuery('input#publish, input#save-post').trigger("click");

	});

	jQuery("button.btn-added-cat-remove").click(function(){
		jQuery("tfoot").find("span.selected-categories").html('');
	});

});

{
	jQuery("tbody.option-value-tbody").find("tr").each(function(i){
		jQuery(this).removeClass();
		jQuery(this).addClass("number-"+i);
		jQuery(this).find("button.btn-go-up").attr("onclick", "option_go_up("+i+");");
		jQuery(this).find("button.btn-go-down").attr("onclick", "option_go_down("+i+");");
		jQuery(this).find("input, select, button").each(function(j){
			if(jQuery(this).hasClass("added-name") == true){
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][name]");
			}
			if(jQuery(this).hasClass("added-value") == true){
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][values][]");
			}
			if(jQuery(this).hasClass("option-required") == true){
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][required]");
			}
			if(jQuery(this).hasClass("added-option-style") == true){
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][style]");
			}
			if(jQuery(this).hasClass("btn-edit-details") == true){
				jQuery(this).attr("data-key", i);
			}
			if(jQuery(this).hasClass("added-color-input") == true){
				var this_name = jQuery(this).attr("data-name");
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][color]["+this_name+"]");
			}
			if(jQuery(this).hasClass("added-thumbnail-input") == true){
				var this_name = jQuery(this).attr("data-name");
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][thumbnails]["+this_name+"]");
			}
		});
	});

}

function check_option_priority(){

	var tbody = jQuery("tbody#attributes-tbody");
		tbody.find("tr").each(function(i){
			if(jQuery(this).find("input.added-value").length){
				jQuery(this).find("input.added-value").attr("name", "wooahan[attributes]["+i+"][values][]");
			}
			jQuery(this).find("input.added-option-style").attr("name", "wooahan[attributes]["+i+"][style]");
			jQuery(this).find("input.option-required").attr("name", "wooahan[attributes]["+i+"][required]");
			jQuery(this).find("input.added-name").attr("name", "wooahan[attributes]["+i+"][name]");
			jQuery(this).find("button.btn-go-up").attr("onclick", "option_go_up("+i+");");
			jQuery(this).find("button.btn-go-down").attr("onclick", "option_go_down("+i+");");
			jQuery(this).find("input.added-color-input").each(function(){
				var name = jQuery(this).attr("data-name");
				//console.log(name);
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][color]["+name+"]");
			});
			jQuery(this).find("input.added-thumbnail-input").each(function(){
				var name = jQuery(this).attr("data-name");
				jQuery(this).attr("name", "wooahan[attributes]["+i+"][thumbnails]["+name+"]");
			});
		});
}


function wooahanLoadTemplate(id){
	return document.getElementById(id).innerHTML;
}

function replaceWooahanTemplate(templateStr, data){
	//console.log(templateStr);
	var result = templateStr;
	for (var key in data){
		result = result.replace('{'+data+'}', data[key]);
	}
	return result;
}


function show_modal(id, title, text){

	var modal_html = '<div class="modal fade" id="'+id+'" tabindex="-1" role="dialog" aria-labelledby="'+id+'Label" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="'+id+'Label">'+title+'</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">'+text+'</div><div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal">확인</button></div></div></div></div>';

	jQuery('div#'+id).remove();
	jQuery("div#wpfooter").after(modal_html);
	jQuery('div#'+id).modal('show');
}

function option_go_down(this_num){
	var origin 	  = jQuery("tbody.option-value-tbody").find("tr.number-"+this_num);
	var this_html = origin.html();
	var next_html = origin.next("tr").html();
	origin.next("tr").after('<tr>'+this_html+'</tr>');
	origin.remove();
	check_option_priority();
}

function option_go_up(this_num){
	var origin 	  = jQuery("tbody.option-value-tbody").find("tr.number-"+this_num);
	var this_html = origin.html();
	var prev_html = origin.prev("tr").html();
	origin.prev("tr").before('<tr>'+this_html+'</tr>');
	origin.remove();
	check_option_priority();

}


function wooahan_toast_alert(class_name, title, text){
        var html = '';
        html += '<div class="toast '+class_name+'" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">';
        html += '<div class="toast-header">';
        html += '<svg class="bd-placeholder-img rounded mr-2" width="20" height="20" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" focusable="false" role="img"><rect fill="#007aff" width="100%" height="100%"></rect></svg>';
        html += '<strong class="mr-auto">'+title+'</strong>';
        html += '<small class="text-muted">'+''+'</small>';
        html += '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">';
        html += '<span aria-hidden="true">&times;</span>';
        html += '</button>';
        html += '</div>';
        html += '<div class="toast-body">';
        html += text;
        html += '</div>';
        html += '</div>';
        //console.log(class_name);
        jQuery("div#wooahan-toast").html(html);
        jQuery("div#wooahan-toast").show();
        jQuery("div#wooahan-toast").find("div.toast."+class_name).toast('show');

        var toast_count = jQuery("div#wooahan-toast").find(".toast").length;
        //console.log(toast_count);

	        jQuery("div#wooahan-toast").find(".toast").on('hidden.bs.toast', function(e){
	        	e.preventDefault();
	        	jQuery(this).remove();
	        });

	        jQuery("div#wooahan-toast").find(".toast:last").on('hidden.bs.toast', function(e){
	        	e.preventDefault();
	        	jQuery(this).remove();
	            jQuery("div#wooahan-toast").hide();
	        });

}