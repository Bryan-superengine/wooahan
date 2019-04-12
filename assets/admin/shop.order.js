jQuery(document).ready(function(){
	var Element = jQuery('#wooahanShopOrder');
	var category_app = new Vue({
		el : '#wooahanShopOrder',
		data: {
			items: [],
			page : 1,
			posts_per_page : 20,
			totalCount : 0,
			navCount : 0,
			orderStatus : 'wc-processing',
			title	: jQuery("div.navbar-nav").find("a:first").attr("data-title"),
			listCount : '',
			clickedID : 0,
			trackingList : [],
			count : [],
			details : {
				id : '',
				customer_name : '',
				order_status : '',
				total : '',
				shipping_cost: '',
				payment_method : '',
				itemCount : 0,
				customer : '',
				order_items : [],
				shipping_number : '',
				memo : ''
			}
		},
		mounted: function(){
			this.getList();
			var self = this;

			self.count = jQuery.parseJSON(jQuery("div#wooahanShopOrder").attr("order-count"));

			jQuery(".wooahan-date-picker").datepicker({
				dateFormat: 'yy-mm-dd'
			});
			jQuery("div.navbar-nav").find("a.nav-item").click(function(){
				jQuery("div.navbar-nav").find("a.nav-item").removeClass("active");
				jQuery(this).addClass("active");
			});
			jQuery("nav.navbar").find("a.navbar-brand").click(function(){
				jQuery("div.navbar-nav").find("a.nav-item").removeClass("active");
				jQuery("div.navbar-nav").find("a.nav-item:first-child").addClass("active");				
			});
		},
		methods : {
			csvUpload : function(){
				var self 		 = this;
				var form_data 	 = new FormData();
				var file 		 = jQuery("div#csvModal").find("input.input-file-csv").prop('files')[0];
				if(!file){
					alert('배송처리 할 CSV 파일을 업로드 하시기 바랍니다.');
					return false;
				}
				if(file.type != 'text/csv'){
					alert('파일은 반드시 CSV 포맷으로 업로드 하셔야 합니다.');
					return false;
				}
				form_data.append('file', file);
				form_data.append('action', 'wooahan_csv_shipping_number_regist');
				jQuery('#csvModal').find("div.modal-content").find("div.modal-loading-progress").show();
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					processData : false,
					contentType : false,
					data : form_data,
					success : function( response ){

						alert(response.result_msg);

						self.getList();
						jQuery('#csvModal').find("div.modal-content").find("div.modal-loading-progress").hide();
						jQuery('#csvModal').modal('hide');
					}
				});	

			},
			reset : function(){
				jQuery("table.search-table").find("input.input-search-keyword").val('');
				jQuery("table.search-table").find("input.order-date-start").val('');
				jQuery("table.search-table").find("input.order-date-end").val('');
				jQuery("table.search-table").find("select.select-search-type").val('id');
				jQuery("table.search-table").find("select.select-search-date").val('order');
				this.getList('');
			},
			removeOrders : function(){
				var self = this;
				var ids = this.checkedOrders();
				if( ids != false ){
					if( confirm( "선택된 모든 상품이 영구적으로 삭제 됩니다.\n(삭제된 주문은 복구할 수 없습니다.)\n정말 삭제하시겠습니까?" ) == false){
						jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
						return false;
					}
					jQuery.ajax({
						url : ajaxurl,
						type : 'post',
						dataType : "json",
						data : {
							action  	: 'wooahan_order_remove',
							order_ids   : ids
						},
						success : function( response ){
							//console.log(response);
							if(response.status == 'success'){
								self.getList();
							} else {
								alert(response.message);
							}
							//jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
						}
					});					
				}
			},
			statusChange : function( status ){
				var ids = this.checkedOrders();
				if( ids != false ){
					if( status == 'processing' ){
						if( confirm( "선택된 상품을 상품준비중 으로 상태를 변경하시겠습니까?" ) == false){
							jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
							return false;
						}						
					}
					if( status == 'completed' ){
						if( confirm("선택된 상품을 배송완료 상태로 변경하시겠습니까?") == false){
							jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
							return false;
						}
					}
					if ( status == 'cancelled' ){
						if( confirm("선택된 상품을 환불처리 하시겠습니까?") == false){
							
							return false;
						}						
					}
					if( status == 'shipping-gone' ){
						if( confirm("선택된 상품을 배송중 상태로 변경하시겠습니까?\n미배송 상품은 무시 됩니다.") == false ){
							jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
							return false;
						}
					}
					this.orderStatusChange(ids, status);
				}
			},
			paid : function(){
				var ids = this.checkedOrders();
				if( ids != false ){
					if( confirm( "입금확인은 결제수단을 일괄 [임의등록] 으로 처리 됩니다.\n입금처리 하시겠습니까?" ) == false){
						jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
						return false;
					}	
					this.orderStatusChange(ids, 'processing');	
				}
			},
			refund_received : function(order_id){
				var self = this;
				if(confirm("주문번호 : "+order_id+" 의 반품상품을 고객으로부터 수령 하셨습니까?\n확인시 해당 주문은 자동으로 환불 처리 됩니다.") == false){
					return false;
				}
				jQuery("div#wooahanShopOrder").find("div.loading-progress").show();
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  	: 'wooahan_return_product_received',
						order_id 	: order_id
					},
					success : function( response ){
						//console.log(response);
						if(response.status == 'success'){
							var ids = [];
								ids.push(order_id);							
							self.orderStatusChange(ids, 'cancelled');
							//self.getList();
						} else {
							alert(response.message);
						}
						//jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
					}
				});	
			},
			exchange_received : function(order_id){
				var self = this;
				if(confirm("주문번호 : "+order_id+" 의 교환상품을 고객으로부터 수령 하셨습니까?") == false){
					return false;
				}
				jQuery("div#wooahanShopOrder").find("div.loading-progress").show();
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  	: 'wooahan_return_product_received',
						order_id 	: order_id
					},
					success : function( response ){
						//console.log(response);
						if(response.status == 'success'){
							self.getList();
						} else {
							alert(response.message);
						}
						//jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
					}
				});					
			},
			idChange: function(id){
				this.clickedID = id;
			},
			modalOpenEvent : function(){
				var self = this;

				jQuery('#trackingModal').on('show.bs.modal', function(e){
					var button  = jQuery(e.relatedTarget);
					var id 		= button.data('id');
					var corp 	= button.data('corp');
					var number 	= button.data('number');
					jQuery('#trackingModal').find("div.modal-content").find("div.modal-loading-progress").show();
					jQuery.ajax({
						url : ajaxurl,
						type : 'post',
						dataType : "json",
						data : {
							action  	: 'wooahan_get_tracking_list',
							order_id 	: id,
							corp 		: corp,
							number 		: number
						},
						success : function( response ){
							//console.log(response);
							if(response.status == 'success'){
								self.trackingList = response.data;
							} else {
								alert(response.message);
							}
							//console.log(self.trackingList);
							jQuery('#trackingModal').find("div.modal-content").find("div.modal-loading-progress").hide();
						}
					});	


				});

				jQuery('#detailModal').on('show.bs.modal', function (e) {
					//e.preventDefault();
					var button 	= jQuery(e.relatedTarget);
					var id 		= self.clickedID;
					var modal 	= jQuery(this);
					//console.log(id);
					self.details.itemCount = 0;

					jQuery("textarea.memo-textarea").val('');
					jQuery("select.memo-type").val('private');
					jQuery("div.shipping-number-box").find("input.input-shipping-number").val('');
					//jQuery("table.shipping-number-table").find("select.select-corp").val();

					jQuery.each(self.items, function(k,v){
						if(this.ID == id){
							//console.log(this);
							jQuery.each(this.order_items, function(k,v){
								self.details.itemCount += this.items.length;
							});
							self.details.id = id;
							self.details.customer_name 		= this.customer_name;
							self.details.order_status  		= this.order_status;
							self.details.total 				= this.total;
							self.details.shipping_cost 		= this.shipping_cost;
							self.details.payment_method 	= this.payment_method;
							self.details.order_items 		= this.order_items;
							self.details.customer 			= this.customer;
							self.details.shipping_number 	= this.shipping_number;
							self.details.memo 				= this.memo;
							self.details.item_total 		= this.item_total;
							self.details.return_received 	= this.return_received;
						}
					});
					//console.log(self.details);
					//console.log(button);

				});
			},
			itemChecker : function(){
				var orderTable 	   = jQuery("table.order-detail-table");
				if(orderTable.find("input.checker").prop("checked") == true){
					orderTable.find("input.each-check").prop("checked", true);
				} else {
					orderTable.find("input.each-check").prop("checked", false);
				}
			},
			shippingNumberRegist : function(order_id){
				var self = this;
				//console.log(order_id);
				var shippingNumber = jQuery("div.shipping-number-box").find("input.input-shipping-number").val();
				var shippingCorp   = jQuery("table.shipping-number-table").find("select.select-corp").val();
				var shippingText   = jQuery("table.shipping-number-table").find("select.select-corp").children("option").filter(":selected").text();
				var orderTable 	   = jQuery("table.order-detail-table");
				var itemCount 	   = orderTable.find("input.each-check").length;
				var addedIds 	   = [];

				if(shippingNumber == ''){
					alert('등록하실 운송장번호를 기입하세요.');
					return false;
				}
				if(isNaN(shippingNumber)){
					alert('운송장 번호는 숫자만 기입하시기 바랍니다.');
					return false;
				}
				if(orderTable.find("input.each-check:checked").length == 0){
					alert("상단 상품 주문내역에서 배송될 상품을 선택하세요.\n일부 선택시 부분배송으로 처리 됩니다.");
					return false;
				}
				if(itemCount != orderTable.find("input.each-check:checked").length){
					if(confirm("택배사 : "+shippingText+"\n기입하신 운송장번호 : "+shippingNumber+"\n\n전체 상품 중 일부를 선택하셨습니다. 선택된 상품만 배송처리 하시겠습니까?\n부분배송으로 처리되며 상단 부분배송 조회에서 확인 가능합니다.") == false){
						return false
					}
				}
				if(itemCount == orderTable.find("input.each-check:checked").length){
					if(confirm("택배사 : "+shippingText+"\n기입하신 운송장번호 : "+shippingNumber+"\n\n위 정보로 송장등록 및 배송처리 하시겠습니까?") == false){
						return false;
					}	
				}

				orderTable.find("input.each-check:checked").each(function(){
					addedIds.push(jQuery(this).val());
				});

				//console.log(addedIds);

				jQuery("div.modal-content").find("div.modal-loading-progress").show();
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  	: 'wooahan_shipping_number_regist',
						order_id 	: order_id,
						number 		: shippingNumber,
						corp 		: shippingCorp,
						item_ids 	: addedIds
					},
					success : function( response ){
						//console.log(response);
						if(response.status == 'success'){
							var ids = [];
								ids.push(order_id);
							self.details.shipping_number = response.shipping_number;
							self.getList();
							jQuery("#detailModal").modal('hide');
						} else {
							alert(response.message);
						}
						jQuery("div.modal-content").find("div.modal-loading-progress").hide();
					}
				});	
			},
			submitNote : function(order_id){
				var self = this;
				if(order_id == 'undefined' || !order_id){
					return false;
				}

				jQuery("div.modal-content").find("div.modal-loading-progress").show();

				var memo 	 = jQuery("textarea.memo-textarea").val();
				var memoType = jQuery("select.memo-type").val();

				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  	: 'wooahan_insert_note',
						order_id 	: order_id,
						memo 		: memo,
						memo_type 	: memoType
					},
					success : function( response ){
						//console.log(response);
						if(response.status == 'success'){
							self.details.memo = response.memo;
						} else {
							alert(response.message);
						}
						jQuery("div.modal-content").find("div.modal-loading-progress").hide();
					}
				});	

			},
			removeNote : function(order_id, note_id){
				var self = this;
				if(!note_id || note_id == 'undefined'){
					return false;
				}
				if(order_id == 'undefined' || !order_id){
					return false;
				}
				jQuery("div.modal-content").find("div.modal-loading-progress").show();
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  : 'wooahan_remove_note',
						note_id : note_id,
						order_id : order_id
					},
					success : function( response ){
						//console.log(response);
						if(response.status == 'success'){
							self.details.memo = response.memo;
						} else {
							alert(response.message);
						}
						jQuery("div.modal-content").find("div.modal-loading-progress").hide();
					}
				});				
			},
			exportCSV : function(){

				var form = document.createElement("form");

				document.body.appendChild(form);

				form.method = "POST";
				form.action = "";

				var elm1 = document.createElement("input");
				elm1.value = "wooahan_download_csv";
				elm1.name  = "wooahan_download_csv";
				form.appendChild(elm1);

				var ids = this.checkedOrders();
					if(ids == false){
						return false;
					}
					ids = ids.join();

				var elm2 = document.createElement("input");
				elm2.value = this.title;
				elm2.name  = "title";
				form.appendChild(elm2);

				var elm3 = document.createElement("input");
				elm3.value = ids;
				elm3.name  = "orders";
				form.appendChild(elm3);

				//console.log(form);

				form.submit();

				jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
			},
			list : function(status, title){
				this.orderStatus = status;
				this.title = title;
				this.page = 1;
				this.getList();
			},
			shippingPending : function(){
				var ids = this.checkedOrders();
				if(ids != false){
					this.orderStatusChange(ids, 'shipping-pending');
				}
			},
			shippingStandby : function(){
				var ids = this.checkedOrders();
				if(ids != false){
					this.orderStatusChange(ids, 'shipping-standby');
				}
			},
			checkedOrders : function(){
				jQuery("div#wooahanShopOrder").find("div.loading-progress").show();
				var ids 	= [];
				jQuery("ul.body").find("input.check-each").each(function(){
					if(jQuery(this).prop("checked") == true){
						var id = jQuery(this).val();
						ids.push(id);
					}
				});
				if(ids.length == 0){
					alert('주문을 먼저 선택하시기 바랍니다.');
					jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
					return false;					
				}
				return ids;
			},
			orderStatusChange : function(ids, order_status){
				var self = this;
				if(!order_status || order_status == 'undefined'){
					return false;
				}
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  : 'wooahan_order_status_change',
						orders : ids,
						order_status : order_status
					},
					success : function( response ){
						if(response.status == 'success'){
							self.getList();
						} else {
							alert(response.message);
						}
						//console.log(response.status);
					},
					complete : function(){

					}
				});
			},
			setDate : function(type){
				var start   = '';
				var end 	= '';
				var today 	= new Date();
				var endDay  = new Date();
				var dd 	 	= today.getDate();
				var mm 		= today.getMonth() + 1;
				var yyyy 	= today.getFullYear();

					start 	= yyyy+'-'+mm+'-'+dd;

				var d 	= new Date();
				var cal = endDay.getDate();

				switch(type){
					case 'today' :
						d.setDate(cal);
					break;

					case 'yesterday' :
						d.setDate(cal - 1);
					break;

					case '3days' :
						d.setDate(cal - 3);
					break;

					case '7days' :
						d.setDate(cal - 7);
					break;

					case '15days' :
						d.setDate(cal - 15);
					break;

					case '1month' :
						d.setDate(cal - 30);
					break;

					case '3month' :
						d.setDate(cal - 90);
					break;

					case '6month' :
						d.setDate(cal - 180);
					break;
				}

				var year 	= d.getFullYear();
				var month 	= d.getMonth() + 1;
				var day 	= d.getDate();

				if(month < 10){
					month = '0'+month;
				}
				if(day < 10){
					day = '0' + day;
				}

				if(type == 'yesterday'){
					end   = year + '-' + month + '-' + day;
					start = end;
				} else {
					end 	= start;
					start   = year + '-' + month + '-' + day;
				}

				jQuery("table.search-table").find("input.order-date-start").val(start);
				jQuery("table.search-table").find("input.order-date-end").val(end);

				//console.log(end);
			},
			search : function(){
				var searchType 		= jQuery("select.select-search-type").val();
				var inputKeyword 	= jQuery("input.input-search-keyword").val();
				var dateType 		= jQuery("table.search-table").find("select.select-search-date").val();
				var dateStart 		= jQuery("table.search-table").find("input.order-date-start").val();
				var dateEnd 		= jQuery("table.search-table").find("input.order-date-end").val();

				var search 			= { 'searchType' : searchType, 'keyword' : inputKeyword, 'dateType' : dateType, 'dateStart' : dateStart, 'dateEnd' : dateEnd };

				this.getList(search);
			},
			check : function(key){
				if(jQuery("input.check-each-"+key).prop("checked") == true){
					jQuery("ul.body").find("li.item-li-"+key).addClass("active");
				} else {
					jQuery("ul.body").find("li.item-li-"+key).removeClass("active");
				}
			},
			refresh : function(){
				this.page = 1;
				this.getList();
			},
			goPage : function(page){
				if(page != 'plus' && page != 'minus'){
					this.page = page;
				} else {
					switch(page){
						case 'plus' :
							this.page = this.page + 1;
						break;

						case 'minus' :
							this.page = this.page - 1;
						break;
					}
				}
				this.getList();
			},
			checkToggle : function(){
				if(jQuery("input.check-all").prop("checked") == true){
					jQuery("input.check-each").prop("checked", true);
					jQuery("ul.body").find("li.item").addClass("active");
					jQuery("ul.body").find("li.item-none").removeClass("active");
				} else {
					jQuery("input.check-each").prop("checked", false);
					jQuery("ul.body").find("li.item").removeClass("active");
				}
			},
			getList : function(search){
				var self = this;
				if(search === 'undefined' || search == undefined){
					var search = '';
				}
				jQuery("div#wooahanShopOrder").find("div.loading-progress").show();
				jQuery.ajax({
					url : ajaxurl,
					type : 'post',
					dataType : "json",
					data : {
						action  : 'get_wooahan_orders',
						page : self.page,
						posts_per_page : self.posts_per_page,
						search : search,
						order_status : self.orderStatus
					},
					success : function( response ){
						if(response.status == 'success'){
							//console.log(response.data);
							self.items = response.data;
							//console.log(response.data);
							self.count = response.orderCount;
							self.totalCount = response.count;
							self.listCount = self.size(response.data);
							self.navCount = Math.ceil(self.totalCount / self.posts_per_page);
							jQuery("input.check-all").prop("checked", false);
							self.checkToggle();
							//console.log(self.items);
						}
						jQuery('#detailModal').on('show.bs.modal').unbind();
						jQuery('#trackingModal').on('show.bs.modal').unbind();
						self.modalOpenEvent();
						jQuery('#detailModal').find('button.close').click(function(){
							jQuery('#detailModal').modal('hide');
						});
						jQuery("div#wooahanShopOrder").find("div.loading-progress").hide();
					},
					complete : function(){

					}
				});
				//setTimeout(function(){ this.getList(search); console.log(search); }.bind(this), 30000);
			},
			size : function(obj){
				var size = 0, key;
				for ( key in obj ){
					if( obj.hasOwnProperty(key)) size++;
				}
				return size;
			}
		}
	});
});