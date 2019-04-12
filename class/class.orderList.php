<?php
	class wooahanOrderList {

		public $allOrders;
		public $packing;
		public $shippingReady;
		public $pageCount;
		public $status;
		public $defaultCorp;
		function __construct(){
			$defaultCorp = $this->get_default_corp();
		}


		/**
		 * [설정된 택배사를 불러온다.]
		 * @return [varchar] $corp [택배사 코드]
		 */
		public function get_default_corp(){
			$corp = get_option('wc_settings_tab_wooahan_tcorp', true);
			return $corp;
		}

		/**
		* 전체 주문 리스트
		*/
		public function getList($order_status, $limit, $search){
			global $wpdb;
			$page 			= $limit[0];
			$posts_per_page = $limit[1];

			if($page == 1){
				$start = 0;
				$end   = $posts_per_page;
			} else {
				$start 		= ($page - 1) * $posts_per_page + 1;
				$end 		= ($page - 1) * $posts_per_page;
			}

			$searchSql = $this->createSearchSql( $search );

			$statusSql = '';
			if($order_status != ''){
				$this->status = $order_status;

				if($order_status == 'wc-pending'){
					$statusSql = "AND post_status IN( '".$this->status."', 'wc-awaiting-vbank' ) ";
				} else {
					$statusSql = "AND post_status = '".$this->status."' ";
				}
			}

			if($search != ''){
				$start = 0;
				$end   = 9999;
			}
				
			$allOrders = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'shop_order' ".$searchSql." ".$statusSql." ORDER BY post_date DESC LIMIT ".$start.",".$end);
			return $this->makeWooahanOrders($allOrders);

		}

		public function get_order_count(){
			$this->status 		= 'wc-processing';
			$processing_count  	= $this->getListCount('');
			$this->status 		= 'wc-shipping-standby';
			$standby_count 	   	= $this->getListCount('');
			$this->status 		= 'wc-shipping-partial';
			$partial_count 	   	= $this->getListCount('');
			$this->status 		= 'wc-shipping-gone';
			$gone_count 	   	= $this->getListCount('');
			$this->status 		= 'wc-completed';
			$completed_count   	= $this->getListCount('');	
			$this->status 		= 'wc-shipping-pending';
			$pending_count 	   	= $this->getListCount('');
			$this->status 		= 'wc-refund-request';
			$refrequest_count  	= $this->getListCount('');
			$this->status 		= 'wc-exchange-request';
			$excrequest_count  	= $this->getListCount('');
			$this->status 		= 'wc-refunded';
			$refunded_count    	= $this->getListCount('');
			$this->status 		= 'wc-pending';
			$awaiting_count 	= $this->getListCount('');

			$return_array = array(
				'processing' 	=> $processing_count,
				'standby'		=> $standby_count,
				'partial'		=> $partial_count,
				'gone'			=> $gone_count,
				'completed'		=> $completed_count,
				'pending'		=> $pending_count,
				'refrequest'	=> $refrequest_count,
				'excrequest'	=> $excrequest_count,
				'refunded'		=> $refunded_count,
				'awaiting'		=> $awaiting_count
			);
			return $return_array;
		}

		public function createSearchSql( $search ){
			$searchSql = '';
			if($search){
				if($search['searchType'] == 'id' && $search['keyword'] != ''){
					$searchSql .= 'AND ID = '.$search['keyword'].' ';
				}
				if($search['searchType'] == 'customer' && $search['keyword'] != ''){
					$customer_name = $search['keyword'];

					// wp_query 를 통해 shop_order 의 메타태그에서 커스토머 이름을 색인으로 검색
					// 전체 ID 를 array 형태로 묶고 노출
					$customer_orders = wc_get_orders( array(
						'limit' => -1,
					));

					foreach($customer_orders as $order){
						
						$order_id = $order->get_id();
						$order_customer = get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true);
						$order_customer = trim($order_customer);
						if($order_customer == $customer_name){
							$ids[] = $order_id;
						}

					}
					$searchSql .= "AND ID IN (".join(",", $ids).") "; 
				}
				if($search['dateType'] == 'order' && $search['dateStart'] != '' && $search['dateEnd'] != ''){
					$searchSql .= "AND post_date >= '".$search['dateStart']." 00:00:00' AND post_date <= '".$search['dateEnd']." 23:59:59' ";
				}
			}
			return $searchSql;			
		}

		public function getListCount($search){
			global $wpdb;

			$searchSql = $this->createSearchSql( $search );
			$statusSql = '';
			if($this->status != ''){
				if($this->status == 'wc-pending'){
					$statusSql = "AND post_status IN( '".$this->status."', 'wc-awaiting-vbank' ) ";
				} else {
					$statusSql = "AND post_status = '".$this->status."' ";
				}
			}

			$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'shop_order' ".$searchSql." ".$statusSql);
			return $count;
		}

		public function getPageCount($results){
			return count($results);
		}

		private function getOrderStatus( $text ){
			switch($text){
				case 'All' :
					$return = '';
				break;

				default :
					$return = $text;
				break;
			}
			return $return;
		}

		public function get_not_shipping_count( $order_id ){
			$order = new WC_Order( $order_id );
			$count = 0;
			foreach($order->get_items() as $item_id => $item_value){
				$shipping = wc_get_order_item_meta($item_id, 'wooahan_shipping', true);
				if($shipping != 'yes'){
					$count++;
				}
			}
			return $count;
		}

		public function get_shipping_count( $order_id ){
			$order = new WC_Order( $order_id );
			$count = 0;
			foreach($order->get_items() as $item_id => $item_value){
				$shipping = wc_get_order_item_meta($item_id, 'wooahan_shipping', true);
				if($shipping == 'yes'){
					$count++;
				}
			}
			return $count;
		}

		public function get_total_item_count($orders){
			$order = new WC_Order( $order_id );
			$count = 0;
			foreach($order->get_items() as $item_id => $item_value){
				$count++;
			}
			return $count;
		}

		/**
		* 택배사 코드를 통해 택배사 이름을 얻는다.
		*/
		public function get_tracking_company_name( $code ){
			$companies = $this->get_tracking_companies();
			$name 	   = '';
			foreach($companies as $company){
				if($company['code'] == $code){
					$name = $company['name'];
				}
			}
			return $name;
		}

		/**
		* 택배사 이름을 통해 택배사 코드를 얻는다.
		*/
		public function get_tracking_company_code( $name ){
			$companies = $this->get_tracking_companies();
			$code = 0;
			foreach($companies as $company){
				if($company['name'] == $name){
					$code = $company['code'];
				}
			}
			return $code;
		}

		/**
		* 전체 택배사를 불러온다.
		*/
		public function get_tracking_companies(){

			// 국내택배사
			$tcorp[] = array( 'code' => '18', 'name' => '건영택배' );
			$tcorp[] = array( 'code' => '23', 'name' => '경동택배' );
			$tcorp[] = array( 'code' => '54', 'name' => '홈픽택배' );
			$tcorp[] = array( 'code' => '40', 'name' => '굿투럭' );
			$tcorp[] = array( 'code' => '53', 'name' => '농협택배' );
			$tcorp[] = array( 'code' => '22', 'name' => '대신택배' );
			$tcorp[] = array( 'code' => '06', 'name' => '로젠택배' );
			$tcorp[] = array( 'code' => '08', 'name' => '롯데택배' );
			$tcorp[] = array( 'code' => '52', 'name' => '세방' );
			$tcorp[] = array( 'code' => '43', 'name' => '애니트랙' );
			$tcorp[] = array( 'code' => '01', 'name' => '우체국택배' );
			$tcorp[] = array( 'code' => '11', 'name' => '일양로지스' );
			$tcorp[] = array( 'code' => '17', 'name' => '천일택배' );
			$tcorp[] = array( 'code' => '20', 'name' => '한덱스' );
			$tcorp[] = array( 'code' => '16', 'name' => '한의사랑택배' );
			$tcorp[] = array( 'code' => '05', 'name' => '한진택배' );
			$tcorp[] = array( 'code' => '32', 'name' => '합동택배' );
			$tcorp[] = array( 'code' => '45', 'name' => '호남택배' );
			$tcorp[] = array( 'code' => '04', 'name' => 'CJ대한통운' );
			$tcorp[] = array( 'code' => '46', 'name' => 'CU편의점택배' );
			$tcorp[] = array( 'code' => '24', 'name' => 'CVSnet 편의점택배' );
			$tcorp[] = array( 'code' => '56', 'name' => 'KGB택배' );
			$tcorp[] = array( 'code' => '30', 'name' => 'KGL네트웍스' );
			$tcorp[] = array( 'code' => '44', 'name' => 'SLX' );
			$tcorp[] = array( 'code' => '58', 'name' => '하이택배' );
			// 국제택배사
			$tcorp[] = array( 'code' => '99', 'name' => '롯데글로벌 로지스' );
			$tcorp[] = array( 'code' => '37', 'name' => '범한판토스' );
			$tcorp[] = array( 'code' => '29', 'name' => '에어보이익스프레스' );
			$tcorp[] = array( 'code' => '38', 'name' => 'APEX(ECMS Express)' );
			$tcorp[] = array( 'code' => '42', 'name' => 'CJ대한통운 국제특송' );
			$tcorp[] = array( 'code' => '57', 'name' => 'Cway Express' );
			$tcorp[] = array( 'code' => '13', 'name' => 'DHL' );
			$tcorp[] = array( 'code' => '33', 'name' => 'DHL Global Mail' );
			$tcorp[] = array( 'code' => '12', 'name' => 'EMS' );
			$tcorp[] = array( 'code' => '21', 'name' => 'Fedex' );
			$tcorp[] = array( 'code' => '41', 'name' => 'GSI Express' );
			$tcorp[] = array( 'code' => '28', 'name' => 'GSMNtoN(인로스)' );
			$tcorp[] = array( 'code' => '34', 'name' => 'i-Parcel' );
			$tcorp[] = array( 'code' => '25', 'name' => 'TNT Express' );
			$tcorp[] = array( 'code' => '55', 'name' => 'EuroParcel(유로택배)' );
			$tcorp[] = array( 'code' => '14', 'name' => 'UPS' );
			$tcorp[] = array( 'code' => '26', 'name' => 'USPS' );

			return apply_filters( 'wooahan_tracking_comapanies', $tcorp );
		}

		public function makeWooahanOrders($orders){
			$wooahanOrders = array();
			if($orders){
				foreach($orders as $order){
					$order_id = $order->ID;
					$_order = wc_get_order($order_id);
					$customer = $_order->get_user();
					$originMethod  = $_order->get_payment_method();
					$paymentMethod = $this->paymentText($_order->get_payment_method());
					$order_status  = $this->orderStatusText($_order->get_status());
					$customer_id    = $_order->get_user_id();

					$return_received = get_post_meta($order_id, 'wooahan_return_product_received', true);

					$wooahanOrders[] = array(
						'ID' 				=> $_order->get_id(),
						'total'				=> number_format($_order->get_total()),
						'item_total'		=> number_format($this->getOrderItem($_order, 'total')),
						'user_id'			=> $_order->get_user_id(),
						'customer'			=> array(
							'name'	=> $_order->get_billing_first_name().' '.$_order->get_billing_last_name(),
							'phone' => $_order->get_billing_phone(),
							'address' => $_order->get_billing_address_1().' '.$_order->get_billing_address_2(),
							'message' => $_order->get_customer_note()
						),
						'customer_name'		=> $_order->get_billing_first_name().' '.$_order->get_billing_last_name(),
						'quantity'			=> number_format($this->getOrderItem($_order, 'quantity')),
						'order_status'		=> $order_status,
						'currency'			=> $_order->get_currency(),
						'order_items'		=> $this->getOrderItem($_order, 'title'),
						'payment_title'		=> $_order->get_payment_method_title(),
						'payment_method'	=> $paymentMethod,
						'origin_method'		=> $originMethod,
						'date_created'		=> $_order->get_date_created()->date('Y-m-d H:i:s'),
						'date_modified'		=> $_order->get_date_modified()->date('Y-m-d H:i:s'),
						'shipping_cost'		=> $_order->get_shipping_total(),
						'shipping_number'   => get_post_meta($order_id, 'wooahan_shipping_number', true),
						'memo'				=> $this->get_private_order_notes($order_id),
						'not_shipping'		=> $this->get_not_shipping_count( $order_id ),
						'shipping_gone'		=> $this->get_shipping_count( $order_id ),
						'return_received'	=> $return_received
					);
				}
				$this->pageCount = count($orders);
			}
			return (object) $wooahanOrders;
		}

		public function get_private_order_notes( $order_id ){
			global $wpdb;
		    $table_perfixed = $wpdb->prefix . 'comments';
		    $results = $wpdb->get_results("
		        SELECT *
		        FROM $table_perfixed
		        WHERE  `comment_post_ID` = $order_id
		        AND  `comment_type` LIKE  'order_note' 
		        AND `comment_approved` = 1  
		        ORDER BY comment_date DESC 
		    ");
		    $order_note = array();
		    foreach($results as $note){
				$is_customer_note = get_comment_meta($note->comment_ID, 'is_customer_note', true);
				if($is_customer_note == '1'){
					$is_customer_note = true;
				} else {
					$is_customer_note = false;
				}
				if( $note->comment_author == 'woocommerce' || $note->comment_author == '우커머스' ){
					$label = __('시스템', 'wooahan');
				} else {
					$label = $note->comment_author;
				}
		        $order_note[]  = array(
		            'note_id'      => $note->comment_ID,
		            'note_date'    => $note->comment_date,
		            'note_author'  => $note->comment_author,
		            'note_content' => $note->comment_content,
		            'is_customer_note' => $is_customer_note,
		            'label'		   => $label
		        );
		    }
		    return $order_note;			
		}

		public function orderStatusText($order_status){
			switch($order_status){
				case 'processing' :
					$text = __('상품준비중', 'wooahan');
				break;

				case 'on-hold' :
					$text = __('보류중', 'wooahan');
				break;

				case 'awaiting-vbank' :
				case 'pending' :
					$text = __('결제대기중', 'wooahan');
				break;

				case 'cancelled' :
					$text = __('취소됨', 'wooahan');
				break;

				case 'refunded' :
					$text = __('환불됨', 'wooahan');
				break;

				case 'completed' :
					$text = __('완료됨', 'wooahan');
				break;

				case 'shipping-standby' :
					$text = __('배송준비중', 'wooahan');
				break;

				case 'shipping-pending' :
					$text = __('배송보류', 'wooahan');
				break;

				case 'shipping-gone' :
					$text = __('배송중', 'wooahan');
				break;

				case 'shipping-partial' :
					$text = __('부분배송', 'wooahan');
				break;

				case 'refund-request' :
					$text = __('반품요청', 'wooahan');
				break;

				case 'exchange-request' :
					$text = __('교환요청', 'wooahan');
				break;

				case 'change' :
					$text = __('교환', 'wooahan');
				break;

				default :
					$text = $order_status;
				break;
			}

			return apply_filters('wooahan_order_status_text', $text, $order_status);
		}

		public function paymentText($method){
			switch($method){
				case 'bacs' :
					$text = __('계좌이체', 'wooahan');
				break;
				case 'cheque' :
					$text = __('수표결제', 'wooahan');
				break;
				case 'paypal' :
					$text = __('페이팔', 'wooahan');
				break;
				case 'iamport_card' :
					$text = __('카드결제', 'wooahan');
				break;
				case 'iamport_vbank' :
					$text = __('가상계좌', 'wooahan');
				break;
				case 'programmatically' :
					$text = __('임의등록', 'wooahan');
				break;
				case 'wooahan-holding' :
					$text = __('구매대기', 'wooahan');
				break;
				default :
					$text = $method;
				break;
			}
			return apply_filters('wooahan_payment_text', $text, $method);
		}

		public function getOrderItem($order, $type){
			if(!$order){ return false; }
			$quantity = 0;
			$total 	  = 0;
			$title 	  = array();

			foreach($order->get_items() as $item_id => $item_value){
				$product 	  = $item_value->get_product();
				$quantity 	 += $item_value->get_quantity();
				$total 	  	 += $item_value->get_total();
				$product_id   = $item_value->get_product_id();
				$is_shipping  = wc_get_order_item_meta( $item_id, 'wooahan_shipping', true );
				if($is_shipping == 'yes'){
					$is_shipping = true;
				} else {
					$is_shipping = false;
				}
				$title[$product_id]['items'][] = array(
					'is_shipping' => $is_shipping,
					'item_id'	  => $item_id,
					'item_title'  => $item_value->get_name(),
					'item_qty'	  => $item_value->get_quantity(),
					'item_price'  => $item_value->get_total(),
					'item_formatted_price' => number_format($item_value->get_total())
				);
				$_product = wc_get_product($product_id);
				if($_product){
					$title[$product_id]['is_type'] = $_product->get_type();
				} else {
					$title[$product_id]['is_type'] = 'simple';
				}
				$title[$product_id]['product_title'] 		= get_the_title($product_id);
				$title[$product_id]['product_url'] 			= get_the_permalink($product_id);
				$title[$product_id]['product_thumbnail'] 	= wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' )[0];
			}

			switch($type){
				case 'quantity' :
					return $quantity;
				break;
				case 'total' :
					return $total;
				break;
				case 'title' :
					return $title;
				break;
				default :
					return array( 'quantity' => $quantity, 'total' => $total );
				break;
			}

		}

	}
?>