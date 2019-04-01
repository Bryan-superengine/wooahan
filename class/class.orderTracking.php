<?php

	class wooahanOrderTracking {
		private $apiKey;
		private $baseUrl;
		private $destUrl;
		public  $corp;
		public  $delay;
		private $trackingUrl;
		function __construct(){

			$this->corp    = $this->get_tcorp();						// 택배사 넘버 (테스트 : 로젠택배)
			$this->apiKey  = $this->get_sweettracker_api_key(); 		// sweettracker api key
			$this->delay   = $this->get_sweettracker_api_delay_hour();	// api connection delay hour
			$this->baseUrl = 'https://info.sweettracker.co.kr'; 		// base url
			$this->destUrl = '/api/v1/trackingInfo'; 					// destination url

			add_action( 'init', array($this, 'tracking') );
		}

		public function get_sweettracker_api_delay_hour(){
			$hour = get_option('wc_settings_tab_wooahan_sweettracker_delay', true);
			return $hour;
		}

		public function get_tcorp(){
			$tcorp = get_option('wc_settings_tab_wooahan_tcorp', true);
			return $tcorp;
		}

		public function get_sweettracker_api_key(){
			$key = get_option('wc_settings_tab_wooahan_sweettracker_key', true);
			return $key;
		}

		public function tracking(){
			global $wpdb;

			$orders = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'shop_order' AND post_status = 'wc-shipping-gone'");

			//print_r($orders);
			if($orders){
				foreach($orders as $order){
					$order_id = $order->ID;
					$_order   = new WC_Order($order_id);
					$trackingNumber = get_post_meta($order_id, 'wooahan_shipping_number', true);
					if($trackingNumber){
						$count = 0;
						$total = count($trackingNumber);
						foreach($trackingNumber as $tnum_arr){
							$this->corp 	 = (isset($tnum_arr['code'])) ? $tnum_arr['code'] : $this->corp;
							$tracking_number = $tnum_arr['number'];
							$responseData 	 = $this->getCURL($tracking_number);
							$responseData    = json_decode($responseData);
							if($responseData->status == 'success'){
								//print_r($responseData);
								$returnData = $responseData->data;
								if(isset($returnData->lastDetail)){
									$status = $returnData->complete;
									//print_r($status);
									if($status == true || $status == 'true'){
										$count++;
									}
								}
							} 
						}
						//print_r($count.'-'.$total);
						if($count == $total){
				            if(!empty($_order)){
				                $_order->update_status( 'completed' );
				            }							
						}
					}
				}
			}
		}

		public function urlGenerate($invoice = null){
			$url = $this->baseUrl.$this->destUrl.'/?t_key='.$this->apiKey.'&t_code='.$this->corp.'&t_invoice='.$invoice;
			return $url;
		}

		public function get_by_data( $tracking_number ){
			global $wpdb;

			$table = $wpdb->prefix.'wooahan_shipping_data';

			$data = $wpdb->get_row("SELECT * FROM {$table} WHERE tcorp = '".$this->corp."' AND tnum = '".$tracking_number."'");
			if($data){
				$value = json_decode($data->value);
			} else {
				$value = false;
			}
			return $value;
		}

		public function check_shipping_data( $tracking_number ){

			global $wpdb;

			$delay = $this->delay; //시간 단위, db 에 등록된 시간에서 00시간이 흘렀다면 다시 체크
			if(!$delay){
				$delay = 3;
			}
			$table = $wpdb->prefix.'wooahan_shipping_data';
			$data  = $wpdb->get_row("SELECT * FROM {$table} WHERE tcorp = '".$this->corp."' AND tnum = '".$tracking_number."'");
			$value = 'success';
			if($data){
				$old_time = $data->strtotime;
				$now_time = strtotime(date('Y-m-d H:i:s'));
				$diff     = ($now_time - $old_time);
				if($diff >= ($delay * 60 * 60)){
					$value = 'overed';
				}

				// 배송완료건은 무조건 DB 에서 끌어다 쓰는걸로...
				$data 	  = $data->value;
				$data 	  = json_decode($data, true);
				if($data['lastDetail']['kind'] == '배송완료' || $data['lastDetail']['kind'] == '배달완료' || $data['lastDetail']['kind'] == '인수자등록'){
					$value = 'success';
				}

			} else {
				$value = 'failed';
			}
			return $value;
		}

		public function update_shipping_to_data( $tracking_number, $data ){
			global $wpdb;
			$table = $wpdb->prefix.'wooahan_shipping_data';
			if($this->check_shipping_data( $tracking_number ) == 'failed'){
				$insert = $wpdb->insert( 
					$table,
					array(
						'tcorp' => $this->corp,
						'tnum'  => $tracking_number,
						'date'  => date('Y-m-d H:i:s'),
						'strtotime' => strtotime(date('Y-m-d H:i:s')),
						'value' => $data
					),
					array( '%s', '%s', '%s', '%s', '%s' )
				);
			} else {
				$update = $wpdb->update(
					$table,
					array(
						'date'	=> date('Y-m-d H:i:s'),
						'strtotime' => strtotime(date('Y-m-d H:i:s')),
						'value' => $data
					),
					array(
						'tcorp' => $this->corp,
						'tnum'  => $tracking_number
					),
					array( '%s', '%s', '%s' )
				);
			}
		}

		public function getCURL($tracking_number){
			$result['status'] 	= 'failed';
			$delay_checker      = $this->check_shipping_data( $tracking_number );

			// 데이터베이스에 등록된 배송정보가 정해진 delay 시간보다 지났거나 데이터베이스에 정보가 없다면 다시 api 불러옴
			if($delay_checker == 'overed' || $delay_checker == 'failed'){
				$header_data = [];
				$header_data[] = 'Authorization: Bearer '.$this->apiKey;
			    $resultUrl  		= $this->urlGenerate($tracking_number);
			    $ch 				= 	curl_init();
			    						curl_setopt($ch, CURLOPT_URL, $resultUrl ); //url 지정
			    						curl_setopt($ch, CURLOPT_POST, 0 ); // 0이 default, post 통신위해 1로 지정
			    						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			    						curl_setopt($ch, CURLOPT_HEADER, true);//헤더 정보를 보내도록 함(*필수)
			    						curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data); //header 지정하기

			    $res 				= 	curl_exec ($ch);    
				$header_size 		= curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header 			= substr($res, 0, $header_size);
				$body 				= substr($res, $header_size);
				$body 				= json_decode($body);
			    if( curl_error($ch) ){
			        $result['message'] = __('CURL 통신에러 : ', 'wooahan').curl_errno( $ch );
			    } else {
			    	if(isset($body->code)){
			    		$result['message'] = $body->msg;
			    	} else {
			        	$result['status'] = 'success';
			        	$result['data']   = $body;
			        	$this->update_shipping_to_data( $tracking_number, json_encode($body) );
			    	}
			    }
			    curl_close($ch);
			} else {
				$result['status'] = 'success';
				$result['data']   = $this->get_by_data($tracking_number);
			}
		    return json_encode($result);			
		}

	}

	new wooahanOrderTracking();
?>