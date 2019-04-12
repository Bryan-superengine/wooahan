<?php
	class wooahanMessage {

		const FAILED_TYPE = "LMS"; // 전송 실패시 전송할 메세지 타입 ( SMS, LMS, N(전송안함) )

		public $storeID;					// apistore 아이디
		public $plusFriend;					// 플러스친구 아이디
		public $profileKey;					// 프로파일 키
		public $templateCode;				// 템플릿 코드 (전송할)
		public $message;					// 메세지내용
		public $customer;					// 고객정보
		public $sendnumberURL;				// 발신번호 인증 URL
		public $sendURL; 					// 메세지 전송 URL
		public $templateURL; 				// 템플릿 조회 URL
		public $senderListURL; 				// 발신번호 리스트 조회 URL
		public $sendUse;					// 오더상태 알림톡 전송할 리스트

		public $callBack; 					// 보내는 사람 연락처
		public $phone;						// 받는사람 연락처
		public $failedType; 				// 카카오알림톡 전송 실패 시 전송할 메세지 타입 ( SMS, LMS, N(전송안함) )
		public $failedSubject; 				// 카카오알림톡 전송 실패 시 전송할 제목 (SMS 미사용)
		public $failedMSG; 					// 카카오알림톡 전송 실패 시 전송할 내용
		public $btnTypes; 					// 카카오 알림톡 버튼타입 (웹링크, 앱링크, 봇키워드, 메세지전달, 배송조회), 최대 5개까지 추가 가능하며 타입별 콤마로 구분. (승인된 템플릿과 불일치시 전송실패)
		public $btnTxts; 					// 카카오 알림톡 버튼이름 ($btnTypes 입력 순으로 버튼이름 TEXT 입력, 승인된 템플릿과 불일치시 전송실패), TEXT 별 콤마로 구분.
		public $btnUrls1; 					// 알림톡 버튼링크주소 URL. 버튼 URL 버튼이 2개 이상일 경우 콤마로 구분, 웹링크, 앱링크는 URL 이 필수이며 기타(배송조회, 봇키워드, 메세지전달)은 null값임. (승인된 템플릿과 불일치시 전송실패)
		public $btnUrls2; 					// 알림톡 버튼링크주소 URL. 버튼 URL 버튼이 2개 이상일 경우 콤마로 구분 (승인된 템플릿과 불일치시 전송실패)


		function __construct(){
			$kakao_arr 				= get_option('wooahan_kakao_account', true);
			$this->storeID 			= (isset($kakao_arr['store_id'])) 	? $kakao_arr['store_id'] 	: '';
			$this->plusFriend 		= (isset($kakao_arr['plus_id']))  	? $kakao_arr['plus_id'] 	: '';
			$this->profileKey   	= (isset($kakao_arr['key'])) 	  	? $kakao_arr['key']			: '';
			$this->callBack 		= (isset($kakao_arr['sendnumber'])) ? $kakao_arr['sendnumber'] 	: '';
			$this->sendUse 			= (isset($kakao_arr['send_use'])) 	? $kakao_arr['send_use'] 	: '';

			$this->sendnumberURL 	= 'http://api.apistore.co.kr/kko/2/sendnumber/save/';
			$this->sendURL 			= 'http://api.apistore.co.kr/kko/1/msg/';
			$this->templateURL 		= 'http://api.apistore.co.kr/kko/1/template/list/';
			$this->senderListURL 	= 'http://api.apistore.co.kr/kko/1/sendnumber/list/';
		}

		public function init(){
			add_action( 'woocommerce_order_status_changed', array($this, 'order_status_sending'), 98, 3 );	
		}

		public function messageTrigger(){
			
		}

		public function order_status_sending($order_id, $status_from, $status_to){
			$sendUse 	   = $this->sendUse;
			//update_option('wooahan_test', $sendUse);
			update_option('wooahan_test', $status_from.'/'.$status_to);
			$template_code = '';
			$not_allowed_status = apply_filters('wooahan_message_not_allowed_status', array( 'completed', 'refunded', 'cancelled' ));
			if($status_to == 'processing' && (!in_array($status_from, $not_allowed_status))){
				if($sendUse['paid']['enable'] == 'yes'){
					$template_code = $sendUse['paid']['code'];
				}
			}
			if($status_from == 'processing' && $status_to == 'shipping-standby'){
				if($sendUse['standby']['enable'] == 'yes'){
					$template_code = $sendUse['standby']['code'];
				}
			}
			if($status_from == 'shipping-standby' && $status_to == 'shipping-partial'){
				if($sendUse['partial']['enable'] == 'yes'){
					$template_code = $sendUse['partial']['code'];
				}
			}
			if($status_from == 'shipping-standby' && $status_to == 'shipping-gone'){
				if($sendUse['gone']['enable'] == 'yes'){
					$template_code = $sendUse['gone']['code'];
				}
			}
			if($status_from == 'shipping-gone' && $status_to == 'request-exchange'){
				if($sendUse['exchange']['enable'] == 'yes'){
					$template_code = $sendUse['exchange']['code'];
				}
			}
			if($status_from == 'shipping-gone' && $status_to == 'request-refund'){
				if($sendUse['refund']['enable'] == 'yes'){
					$template_code = $sendUse['refund']['code'];
				}
			}
			if($status_from == 'pending' && $status_to == 'awaiting-vbank'){
				if($sendUse['account']['enable'] == 'yes'){
					$template_code = $sendUse['account']['code'];
				}
			}
			if($template_code != ''){
				//update_option('wooahan_test_2', 'trigger!!');
				$this->send($order_id, $template_code);
			}
		}

		public function resultMessage($code){
			switch($code){
				case '200' : 
					$message = __('성공하였습니다.', 'wooahan');
				break;

				case '300' :
					$message = __('파라미터 에러', 'wooahan');
				break;

				case '400' :
					$message = __('인증 업데이트 중 에러가 발행하였습니다.', 'wooahan');
				break;

				case '500' :
					$message = __('이미 등록된 번호 입니다.', 'wooahan');
				break;

				case '600' :
					$message = __('일치하지 않는 인증번호 입니다.', 'wooahan');
				break;

				case '700' :
					$message = __('핀코드 인증 시간이 만료되었습니다. 다지 인증번호 전송하시기 바랍니다.', 'wooahan');
				break;

				default :
					$message = __('인증에 문제가 발행하였습니다.', 'wooahan');
				break;
			}

			return $message;
		}

		public function templateList(){
			$url = $this->templateURL.$this->storeID;
			$response = wp_remote_post( $url, array(
				'method'  => 'GET',
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
					'x-waple-authorization' => $this->profileKey
				)
			));
			if( is_wp_error( $response ) ){
				$errorMessage = $response->get_error_message();
				return $errorMessage;
			} else {
				if($response['response']['code'] != '200'){
					return false;
				}
				return $response['body'];
			}			
		}

		public function getTemplateMessage($order_id, $code){
			$templateList = json_decode($this->templateList());
			$message 	  = '';
			if($templateList->result_code == '200'){
				foreach($templateList->templateList as $template){
					if($template->template_code == $code){
						$message = $template->template_msg;
					}
				}
			}
			return $this->translate_reserv_key($order_id, $message);
		}

		public function translate_reserv_key($order_id, $message){
			if(!$message){
				return null;
			} else {
				preg_match_all('/\\#\\{([^{}]+)\\}/', $message, $matches);
				//print_r($matches);
				if(isset($matches[0])){
					foreach($matches[0] as $key => $match){
						$message = str_replace($match, $this->get_reserve_keyword_data($order_id, $matches[1][$key]), $message);

					}
				}
			}
			return $message;
		}

		public function get_reserve_keyword_data($order_id, $keyword){
			$order 			= wc_get_order($order_id);
			$customer_id 	= $order->get_customer_id();
			$data 			= '';

			if(!in_array($keyword, $this->reserve_keywords())){
				return false;
			}
			if($keyword == 'shop_name'){
				$data = get_bloginfo('name');
			}
			if($keyword == 'customer_name'){
				$data = $order->get_billing_first_name();
			}
			if($keyword == 'order_number'){
				$data = '#'.$order_id;
			}
			if($keyword == 'customer_phone'){
				$data = $order->get_billing_phone();
			}
			if($keyword == 'order_status'){
				$orderList = new wooahanOrderList();
				$data = $orderList->orderStatusText($order->get_status());
			}
			if($keyword == 'tracking_number'){
				if($order->get_status() == 'shipping-gone' || $order->get_status() == 'completed' || $order->get_status() == 'shipping-partial'){
					$trackingNumbers = get_post_meta($order_id, 'wooahan_shipping_number', true);
					if($trackingNumbers){
						//$tcode  = $trackingNumbers[count($trackingNumbers)-1]['code'];
						$number = (isset($trackingNumbers[count($trackingNumbers)-1]['number'])) ? $trackingNumbers[count($trackingNumbers)-1]['number'] : '';
						$data   = $number;
					}
				}
			}
			if($keyword == 'tracking_company'){
				$trackingNumbers = get_post_meta($order_id, 'wooahan_shipping_number', true);
				if($trackingNumbers){
					//$tcode  = $trackingNumbers[count($trackingNumbers)-1]['code'];
					$name = (isset($trackingNumbers[count($trackingNumbers)-1]['corp'])) ? $trackingNumbers[count($trackingNumbers)-1]['corp'] : '';
					$data   = $name;
				}
			}

			return apply_filters('wooahan_get_reserve_keyword_data', $data, $order_id, $keyword);

		}

		public function reserve_keywords(){
			$keywords = array( 'shop_name', 'customer_name', 'order_number', 'customer_phone', 'order_status', 'tracking_number', 'tracking_company' );
			return apply_filters('wooahan_reserve_keywords', $keywords);
		}

		public function senderList(){
			$url = $this->senderListURL.$this->storeID;
			$response = wp_remote_post( $url, array(
				'method' => 'GET',
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
					'x-waple-authorization' => $this->profileKey
				)
			));
			if( is_wp_error( $response ) ){
				$errorMessage = $response->get_error_message();
				return '$errorMessage';
			} else {
				if($response['response']['code'] != '200'){
					return false;
				}
				return $response['body'];
			}
		}

		public function send($order_id, $templateCode){
			if(!$order_id || !$templateCode){
				return false;
			}
			$url   = $this->sendURL.$this->storeID;
			$order = wc_get_order($order_id);
			$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
						'x-waple-authorization' => $this->profileKey
					),
					'body' => array( 
						'PHONE' 			=> $order->get_billing_phone(),
						'CALLBACK' 			=> $this->callBack,
						'MSG'				=> $this->getTemplateMessage($order_id, $templateCode),
						'TEMPLATE_CODE'		=> $templateCode,
						'FAILED_TYPE'	 	=> 'LMS',
						'FAILED_SUBJECT' 	=> '테스트',
						'FAILED_MSG'		=> '테스트',
						'BTN_TYPES'			=> '',
						'BTN_TXTS'			=> '',
						'BTN_URLS1'			=> ''
					),
				)
			);	

			if( is_wp_error( $response ) ){
				$memo = $templateCode.' 알림톡 전송에 실패하였습니다.';

				$errorMessage = $response->get_error_message();
				return $errorMessage;
			} else {
				$memo = '수취인에게 "'.$this->getTemplateMessage($order_id, $templateCode).'" 알림톡을 전송하였습니다.';
				$order->add_order_note( $memo );

				return $response;
			}

		}

		/**
		 * [numberRegist description]
		 * 전송된 인증번호 기입후 발신번호를 인증/등록 처리
		 * 
		 * @param  [type] $sendnumber [description]
		 * @param  [type] $pincode    [description]
		 * @return [type]             [description]
		 */
		public function numberRegist($sendnumber, $pincode){
			if(!$sendnumber || !$pincode){
				return false;
			}
			$url = $this->sendnumberURL.$this->storeID;
			$comment = '발신번호 인증신청'; 		// 등록 타이틀
			$pintype = 'SMS';					// 인증방법 (SMS, VMS(음성) 중 택일)
			$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
						'x-waple-authorization' => $this->profileKey
					),
					'sendnumber' => $callBack,
					'comment' => $comment,
					'pintype' => $pintype,
					'body' => array( 'sendnumber' => $sendnumber, 'comment' => $comment, 'pintype' => $pintype, 'pincode' => $pincode ),
				)
			);
			if( is_wp_error( $response ) ){
				$errorMessage = $response->get_error_message();
				return $errorMessage;
			} else {
				return $response;
			}			

		}

		/**
		 * [pinVerification description]
		 * 핸드폰으로 전송된 핀넘버와 발신번호를 인증요청한다.
		 * 
		 * @param  [type] $sendnumber [description]
		 * @param  [type] $pinnumber  [description]
		 * @return [type]             [description]
		 */
		public function pinVerification($sendnumber, $pinnumber){
			if(!$this->storeID){
				return false;
			}
			$url = $this->sendnumberURL.$this->storeID;
			$comment = '우아한 등록 발신번호'; 		// 등록 타이틀
			$pintype = 'SMS';					// 인증방법 (SMS, VMS(음성) 중 택일)
			$pincode = $pinnumber;				// 인증번호 - 인증번호 받은 후 pincode 파라미터 사용 (SMS 인증번호(6자리), VMS 인증번호 (2자리))

			$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
						'x-waple-authorization' => $this->profileKey
					),
					'body' => array( 'sendnumber' => $sendnumber, 'comment' => $comment, 'pintype' => $pintype, 'pincode' => $pincode ),
				)
			);
			if( is_wp_error( $response ) ){
				$errorMessage = $response->get_error_message();
				return $errorMessage;
			} else {
				return $response;
			}

		}

		/**
		 * [getPincode description]
		 * 발신번호 인증을 위해 핀코드를 받는다.
		 * 
		 * @return [type] [description]
		 */
		public function getPincode($sendnumber){
			if(!$this->storeID){
				return false;
			}
			// 테스트용 apistore 계정정보
			$url = $this->sendnumberURL.$this->storeID;
			$comment = '우아한 등록 발신번호'; 		// 등록 타이틀
			$pintype = 'SMS';					// 인증방법 (SMS, VMS(음성) 중 택일)
			$pincode = '';						// 인증번호 - 인증번호 받은 후 pincode 파라미터 사용 (SMS 인증번호(6자리), VMS 인증번호 (2자리))

			$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
						'x-waple-authorization' => $this->profileKey
					),
					'body' => array( 'sendnumber' => $sendnumber, 'comment' => $comment, 'pintype' => $pintype ),
				)
			);
			if( is_wp_error( $response ) ){
				$errorMessage = $response->get_error_message();
				return $errorMessage;
			} else {
				return $response;
			}

		}

		public function getCustomer( $order_id ){
			$order 			= wc_get_order($order_id);
			$customer_id 	= $order->get_customer_id();

			if(!$order->get_billing_phone()){
				return false;
			}

			return array(
				'customer_id' => $customer_id,
				'phone'		  => $order->get_billing_phone(),

			);
		}
	}

	$wooahanMessage = new wooahanMessage();
	$wooahanMessage->init();

?>