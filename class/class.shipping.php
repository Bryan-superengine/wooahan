<?php
	
	class wooahanShipping {

		function __construct(){

		}

		/**
		* 해당 아이템이 배송 제외로 등록되었는지 아닌지 체크
		* return : true or false
		*/
		public function is_exclude( $exclude, $item_id ){

			if(!$exclude || !is_array($exclude) || !$item_id){
				return false;
			}

			if(in_array($item_id, $exclude)){
				return true;
			}

			return false;
		}

		public function get_exclude_ids( $order_id, $item_ids ){
			$order = new WC_Order($order_id);
			$exclude = array();
		    foreach($order->get_items() as $item_id => $item_value){
		    	// 아이템 아이디에 없다면 취집
		    	if(in_array($item_id, $item_ids) == false){
		    		$exclude[] = $item_id;
		    	}
		    }
		    return $exclude;
		}

		public function updateShippingInfo($order_id, $shipping_info){
		    $added_number    = get_post_meta($order_id, 'wooahan_shipping_number', true);
		    $duplicate       = 0;
		    $return 		 = true;
		    if(is_array($added_number)){
		    	foreach($added_number as $info){
		    		if($info['code'] == $shipping_info['code'] && $info['number'] == $shipping_info['number']){
		    			$duplicated = 1;
		    		}
		    	}
		    }
		    if($duplicated == 0){
		    	$added_number[] = $shipping_info;
		    	$return 		= update_post_meta($order_id, 'wooahan_shipping_number', $added_number);
		    }
		    return $return;
		}

		/**
		* 송장정보를 등록한다.
		* return : true or false
		* data : array( 'corp' => '택배사명', 'code' => '택배사코드', 'number' => '운송장번호', 'exclude' => '배송제외 아이템' )
		**/
		public function Regist($order_id, $data){
		    
		    if(!$order_id || !isset($data['code']) || !isset($data['number']) ){
		    	return false;
		    }

		    $code 			 = $data['code'];		// 택배사 코드
		    $number 		 = $data['number'];		// 택배사 운송장 번호

		    if(isset($data['item_ids'])){
		    	$exclude = $this->get_exclude_ids( $order_id, $data['item_ids'] );
		    } else {
		    	$exclude = (isset($data['exclude'])) ? $data['exclude'] : array();
		    }

		    $orderList       = new wooahanOrderList();
		    $corp      		 = $orderList->get_tracking_company_name($code);

		    $_order = new WC_Order($order_id);

		    if($_order->has_status('completed')){
		    	return (object) array( 'status' => false, 'message' => $order_id.__('의 주문은 이전에 이미 처리 되었습니다.', 'wooahan') );
		    }
		    
		    $itemCount 		= 0;
		    $shippedCount 	= 0;
		    foreach($_order->get_items() as $item_id => $item_value){
		        if($this->is_exclude($exclude, $item_id) == false){
		        	wc_update_order_item_meta( $item_id, 'wooahan_shipping', 'yes');
		        }
		        if(wc_get_order_item_meta($item_id, 'wooahan_shipping', true) == 'yes'){
		            $shippedCount++;
		        }
		        $itemCount++;
		    }

		    if($itemCount == $shippedCount){
		        $shipping_status = 'shipping-gone';
		    } else {
		        $shipping_status = 'shipping-partial';
		    }

		    $shipping_info  = array(
		        'corp'      => $corp,
		        'code'      => $code,
		        'number'    => $number
		    );

		    $return = $this->updateShippingInfo($order_id, $shipping_info);

		    if($return){
	            if(!empty($_order)){
	                $_order->update_status( $shipping_status );
	            }
		    	return (object) array( 'status' => true, 'shipping_status' => $shipping_status );		    	
		    } else {
		    	return (object) array( 'status' => false, 'message' => $order_id.__('의 운송장 메타 등록에 오류가 발생하였습니다.', 'wooahan') );
		    }
		}

	}

?>