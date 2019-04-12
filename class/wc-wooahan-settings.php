<?php
	class WC_Settings_Wooahan extends WC_Settings_Page {

	    /**
	     * Constructor
	     */
	    public function __construct() {

	        $this->id    = 'wooahan';

	        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
	        add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
	        add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
	        add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

	    }

	    /**
	     * Add plugin options tab
	     *
	     * @return array
	     */
	    public function add_settings_tab( $settings_tabs ) {
	        $settings_tabs[$this->id] = __( '우아한', 'wooahan' );
	        return $settings_tabs;
	    }

	    /**
	     * Get sections
	     *
	     * @return array
	     */
	    public function get_sections() {

	        $sections = array(
	            'basic'         => __( '기본설정', 'wooahan' ),
	            'kakao'         => __( '카카오 알림톡 설정', 'wooahan' ),
	            'templates'		=> __( '알림톡 템플릿 리스트', 'wooahan' ),
	            'reserve_text'  => __( '알림톡 예약어 리스트', 'wooahan' )
	        );

	        return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	    }


	    /**
	     * Get sections
	     *
	     * @return array
	     */
	    public function get_settings( $section = 'basic' ) {

	    	$orderList 		= new wooahanOrderList();
	    	$tcorps    		= $orderList->get_tracking_companies();
	    	$option_corps 	= array();
	    	$option_corps['0'] = __('선택하세요', 'wooahan');
	    	if($tcorps){
	    		foreach($tcorps as $tcorp){
	    			$option_corps[$tcorp['code']] = $tcorp['name'];
	    		}
	    	}
	    	$settings = array();

	        switch( $section ){
	        	default :
	            case 'basic' :
	                $settings = array(
	                    'section_title' => array(
	                        'name'     => __( '우아한(Wooahan) 기본설정', 'wooahan' ),
	                        'type'     => 'title',
	                        'desc'     => '',
	                        'id'       => 'wc_settings_tab_wooahan_basic_title'
	                    ),
	                    'product' => array(
	                        'name' => __( '우아한 상품등록 활성화', 'wooahan' ),
	                        'type' => 'checkbox',
	                        'desc' => __( '우아한 상품등록을 사용 합니다.', 'wooahan' ),
	                        'default' => 'yes',
	                        'id'   => 'wc_settings_tab_wooahan_product_controll'
	                    ),
	                    'order' => array(
	                        'name' => __( '우아한 주문관리 활성화', 'wooahan' ),
	                        'type' => 'checkbox',
	                        'desc' => __( '우아한 주문관리를 사용 합니다.', 'wooahan' ),
	                        'default' => 'yes',
	                        'id'   => 'wc_settings_tab_wooahan_order_conroll'
	                    ),
	                    'direct_buy' => array(
	                        'name' => __( '바로구매 활성화', 'wooahan' ),
	                        'type' => 'checkbox',
	                        'desc' => __( '우아한 바로구매 기능을 사용합니다.', 'wooahan' ),
	                        'default' => 'yes',
	                        'id'   => 'wc_settings_tab_wooahan_direct_buy'
	                    ),
	                    'shipping' => array(
	                        'name' => __( '기본 택배사 선택', 'wooahan' ),
	                        'type' => 'select',
	                        'options' => $option_corps,
	                        'desc' => '',
	                        'id'   => 'wc_settings_tab_wooahan_tcorp'
	                    ),
	                    'badge_use' => array(
	                        'name' => __( '뱃지 사용', 'wooahan' ),
	                        'type' => 'select',
	                        'options' => array(
	                        	'single' 	=> __('상세 페이지 에서 사용', 'wooahan'),
	                        	'list' 		=> __('리스트(loop) 페이지에서 사용', 'wooahan'),
	                        	'both' 		=> __('상세/리스트 페이지 모두 사용', 'wooahan'),
	                        	'none' 		=> __('사용안함', 'wooahan')
	                        ),
	                        'default' => 'both',
	                        'desc' => '',
	                        'id'   => 'wc_settings_tab_wooahan_badge_use'
	                    ),		                    
	                    'badge_position' => array(
	                        'name' => __( '기본 뱃지 위치', 'wooahan' ),
	                        'type' => 'select',
	                        'options' => array(
	                        	'title_above' => __('타이틀 상단', 'wooahan'),
	                        	'title_below' => __('타이틀 하단', 'wooahan'),
	                        	'price_above' => __('금액 상단', 'wooahan'),
	                        	'price_below' => __('금액 하단', 'wooahan'),
	                        	'excerpt_above' => __('요약 상단', 'wooahan'),
	                        	'excerpt_below' => __('요약 하단', 'wooahan')
	                        ),
	                        'default' => 'title_below',
	                        'desc' => '',
	                        'id'   => 'wc_settings_tab_wooahan_badge_position'
	                    ),
	                    /*
	                    'wooahan_id' => array(
	                        'name' => __( '우아한 인증 아이디', 'wooahan' ),
	                        'type' => 'text',
	                        'desc' => '<br><a href="http://wooahan.shop" target="_blank">woooahan.shop</a> 에서 인증 아이디를 발급받으세요~!<br>SMS/카카오알림/기타 부가서비스를 사용하시려면 반드시 등록하셔야 합니다.',
	                        'id'   => 'wc_settings_tab_wooahan_wooahan_id'
	                    ),
	                    'wooahan_key' => array(
	                        'name' => __( '우아한 API KEY', 'wooahan' ),
	                        'type' => 'text',
	                        'desc' => '<br><a href="http://wooahan.shop" target="_blank">woooahan.shop</a> 에 로그인 하신 후 발급받은 API KEY 를 기입하시기 바랍니다.<br>(우아한 인증 아이디와 API KEY 는 필수요소 입니다.)',
	                        'id'   => 'wc_settings_tab_wooahan_wooahan_id'
	                    ),
	                    */
	                    'sweetr_key' => array(
	                        'name' => __( '스윗트래커 API KEY', 'wooahan' ),
	                        'type' => 'text',
	                        'desc' => '<br>스윗트래커는 택배 배송추적 API 를 제공하는 회사 입니다.<br>우아한은 기본적으로 스윗트래커의 택배추적 API 를 사용합니다. (<a href="https://tracking.sweettracker.co.kr" target="_blank">https://tracking.sweettracker.co.kr/</a>)<br>배송중인 상품을 자동으로 추적하여 배송완료로 변경하여 주는 기능을 위해 반드시 필요합니다.',
	                        'id'   => 'wc_settings_tab_wooahan_sweettracker_key'
	                    ),
	                    'sweetr_delay' => array(
	                        'name' => __( 'API Connection Delay Hour', 'wooahan' ),
	                        'type' => 'number',
	                        'desc' => '<br>배송완료건은 데이터베이스에 저장된 배송추적정보를 이용합니다. 그외 배송건은 상단 기입하신 시간 이상 경과하였을 경우 다시 API 를 통해 배송위치를 전달받게 됩니다.<br>기입하신 시간만큼 경과하지 않았다면 배송완료와 마찬가지로 데이터베이스에 저장된 정보로 보여지게 됩니다.<br>(일일 동일 운송장번호에 대한 요청건수가 20회로 제한되어 있어 그로인한 조치 입니다.)',
	                        'placeholder' => '시간 단위로 기입하세요.',
	                        'default' 	  => '3',
	                        'id'   => 'wc_settings_tab_wooahan_sweettracker_delay'
	                    ),	                    
	                    'section_end' => array(
	                         'type' => 'sectionend',
	                         'id' => 'wc_settings_tab_wooahan_basic'
	                    )
	                );

	            break;
	            case 'sms':
	                $settings = array(
	                    'section_title' => array(
	                        'name'     => __( 'Section One Title', 'woocommerce-settings-tab-demo' ),
	                        'type'     => 'title',
	                        'desc'     => '',
	                        'id'       => 'wc_settings_tab_demo_section_title_section-2'
	                    ),
	                    'title' => array(
	                        'name' => __( 'Section One Title', 'woocommerce-settings-tab-demo' ),
	                        'type' => 'text',
	                        'desc' => __( 'This is some helper text', 'woocommerce-settings-tab-demo' ),
	                        'id'   => 'wc_settings_tab_demo_title_section-2'
	                    ),
	                    'description' => array(
	                        'name' => __( 'Section One Description', 'woocommerce-settings-tab-demo' ),
	                        'type' => 'textarea',
	                        'desc' => __( 'This is a paragraph describing the setting. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda.', 'woocommerce-settings-tab-demo' ),
	                        'id'   => 'wc_settings_tab_demo_description_section-2'
	                    ),
	                    'section_end' => array(
	                         'type' => 'sectionend',
	                         'id' => 'wc_settings_tab_demo_section_end_section-2'
	                    )
	                );
	            break;

	        }

	        return apply_filters( 'wc_settings_tab_demo_settings', $settings, $section );

	    }

	    /**
	     * Output the settings
	     */
	    public function output() {
	        global $current_section;
	        if($current_section == 'kakao'){

?>
			<script>
				jQuery(document).ready(function(){

					jQuery("select.select-template-code").change(function(){
						var self 	     = jQuery(this);
						var templateCode = self.val();
						var templates    = jQuery.parseJSON(jQuery("table.talk-enable-table").attr("data-templates"));
						var message 	 = '';
						jQuery.each(templates, function(k,v){
							if(this.template_code == templateCode){
								message  = this.template_msg;
								self.parent().parent().find("td.message-content").html(message);
								return false;
							}
						});
						if(message == ''){
							self.parent().parent().find("td.message-content").html('<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>');
						}
					});

					jQuery("button.button-verification").click(function(){
						var sendNumber = jQuery("input.send-number").val();
						var pinNumber  = jQuery("input.pin-number").val();
						if(!sendNumber){
							alert('발신번호를 기입하시기 바랍니다.');
							return false;
						}
						if(!pinNumber){
							alert('핸드폰으로 전송된 6자리 인증번호를 기입하시기 바랍니다.');
							return false;
						}
						jQuery.ajax({
							url : ajaxurl,
							type : 'post',
							dataType : "json",
							data : {
								action  	: 'wooahan_pin_number_verification',
								sendnumber 	: sendNumber,
								pinnumber   : pinNumber
							},
							success : function( response ){
								if(response.status == 'success'){
									console.log(response);
									if(response.result_code != '200'){
										alert(response.message);
									}
									location.reload();
								} else {
									alert(response.message);
								}
							},
							complete : function(){

							}
						});							
					});

					jQuery("button.button-get-pin-number").click(function(){
						var number = jQuery("input.send-number").val();
						if(!number){
							alert('발신자 연락처를 기입하시기 바랍니다.');
							return false;
						}
						jQuery.ajax({
							url : ajaxurl,
							type : 'post',
							dataType : "json",
							data : {
								action  : 'wooahan_get_kakao_pin_number',
								number : number
							},
							success : function( response ){
								if(response.status == 'success'){
									if(response.result_code != '200'){
										alert(response.message);
										return false;
									}
									jQuery("tr.pinnumber-regist-tr").show();
								} else {
									alert(response.message);
								}
							},
							complete : function(){

							}
						});						
					});
				});
			</script>
			<h2>카카오 알림톡 설정</h2>
<?php
	$kakao_arr 		= get_option('wooahan_kakao_account', true);
	$store_id 		= (isset($kakao_arr['store_id'])) ? $kakao_arr['store_id'] 	: '';
	$plus_id 		= (isset($kakao_arr['plus_id']))  ? $kakao_arr['plus_id'] 	: '';
	$key 			= (isset($kakao_arr['key'])) 	  ? $kakao_arr['key']		: '';
	$sendnumber 	= (isset($kakao_arr['sendnumber'])) ? $kakao_arr['sendnumber'] : '';
	$send_use 		= $kakao_arr['send_use'];
	$bizMessage 	= new wooahanMessage();
	$senderList 	= json_decode($bizMessage->senderList());
	$templateList 	= json_decode($bizMessage->templateList());
	$codes 			= array();
	$option_codes   = '';
	$json_templates = array();
	$templateMSG    = array();
	//print_r($templateList);
	if($templateList){
	if($templateList->result_code == '200'){
		$json_templates = json_encode($templateList->templateList);
		foreach($templateList->templateList as $template){
			if($template->status == '승인'){
				$codes[] = $template->template_code;
				$templateMSG[$template->template_code] = $template->template_msg;
			}
		}
	}
	}
	if(!empty($codes)){

		$standby_options  = '';
		$partial_options  = '';
		$gone_options     = '';
		$exchange_options = '';
		$refund_options   = '';
		$account_options  = '';
		$paid_options  	  = '';
		foreach($codes as $tcode){
			if($send_use['standby']['code'] == $tcode){
				$standby_options .= '<option value="'.$tcode.'" selected>'.$tcode.'</option>';
			} else {
				$standby_options .= '<option value="'.$tcode.'">'.$tcode.'</option>';
			}
			if($send_use['partial']['code'] == $tcode){
				$partial_options .= '<option value="'.$tcode.'" selected>'.$tcode.'</option>';
			} else {
				$partial_options .= '<option value="'.$tcode.'">'.$tcode.'</option>';
			}
			if($send_use['gone']['code'] == $tcode){
				$gone_options .= '<option value="'.$tcode.'" selected>'.$tcode.'</option>';
			} else {
				$gone_options .= '<option value="'.$tcode.'">'.$tcode.'</option>';
			}
			if($send_use['exchange']['code'] == $tcode){
				$exchange_options .= '<option value="'.$tcode.'" selected>'.$tcode.'</option>';
			} else {
				$exchange_options .= '<option value="'.$tcode.'">'.$tcode.'</option>';
			}

			if($send_use['refund']['code'] == $tcode){
				$refund_options .= '<option value="'.$tcode.'" selected>'.$tcode.'</option>';
			} else {
				$refund_options .= '<option value="'.$tcode.'">'.$tcode.'</option>';
			}

			if($send_use['account']['code'] == $tcode){
				$account_options .= '<option value="'.$tcode.'" selected>'.$tcode.'</option>';
			} else {
				$account_options .= '<option value="'.$tcode.'">'.$tcode.'</option>';
			}

			if($send_use['paid']['code'] == $tcode){
				$paid_options .= '<option value="'.$tcode.'" selected>'.$tcode.'</option>';
			} else {
				$paid_options .= '<option value="'.$tcode.'">'.$tcode.'</option>';
			}

			$option_codes .= '<option value="'.$tcode.'">'.$tcode.'</option>';
		}
	}
?>
			<p class="description">카카오 알림톡은 <a href="https://www.apistore.co.kr/" target="_blank">API스토어</a> 를 사용합니다. 카카오 알림톡 사용을 위해서는 사전에 가입 과정이 필요합니다.<br>템플릿 등록 및 수정은 별도로 API 를 제공하고 있지 않기 때문에 <a href="https://www.apistore.co.kr/" target="_blank">API스토어</a> 에 접속하셔서 직접 해주셔야 합니다.<br><a href="https://www.apistore.co.kr/" target="_blank">API스토어</a> 에 직접 등록하신 템플릿 리스트로 검수상태가 승인 상태인 템플릿만 전송 가능 합니다.</p>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">API스토어 아이디</th>
						<td><input type="text" name="apistore_id" value="<?php echo $store_id;?>"></td>
					</tr>
					<tr valign="top">
						<th scope="row">카카오 플러스친구 아이디</th>
						<td><input type="text" name="kakaoplus_id" value="<?php echo $plus_id;?>"></td>
					</tr>
					<tr valign="top">
						<th scope="row">API스토어 KEY</th>
						<td><input type="text" name="apistore_key" value="<?php echo $key;?>"></td>
					</tr>
					<tr valign="top">
						<th scope="row">
							알림톡 전송
							<p class="description" style="font-weight:normal">전송하실 주문상태에 체크하시기 바랍니다.</p>
						</th>
						<td>
							<table class="talk-enable-table" data-templates='<?php echo $json_templates;?>'>
								<tr>
									<th style="width:10%;"><input type="checkbox"> 사용여부</th>
									<th style="width:15%;">주문 상태</th>
									<th style="width:20%;">템플릿 코드</th>
									<th style="width:55%">메세지 내용</th>
								</tr>
								<tr>
									<td><input type="checkbox" name="kakao_talk[standby][enable]" value="yes" <?php if($send_use['standby']['enable'] == 'yes') : echo 'checked'; endif; ?>></td>
									<td>
										배송 준비중
									</td>
									<td>
										<select class="select-template-code" name="kakao_talk[standby][code]">
											<option>선택하세요.</option>
											<?php echo $standby_options; ?>
										</select>
									</td>
									<td class="message-content">
										<?php 
											if(isset($templateMSG[$send_use['standby']['code']])){
												echo $templateMSG[$send_use['standby']['code']];
											} else {
										?>
											<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>
										<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td><input type="checkbox" name="kakao_talk[partial][enable]" value="yes" <?php if($send_use['partial']['enable'] == 'yes') : echo 'checked'; endif; ?>></td>
									<td>
										부분 배송중
									</td>
									<td>
										<select class="select-template-code" name="kakao_talk[partial][code]">
											<option>선택하세요.</option>
											<?php echo $partial_options; ?>
										</select>
									</td>
									<td class="message-content">
										<?php 
											if(isset($templateMSG[$send_use['partial']['code']])){
												echo $templateMSG[$send_use['partial']['code']];
											} else {
										?>
											<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>
										<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td><input type="checkbox" name="kakao_talk[gone][enable]" value="yes" <?php if($send_use['gone']['enable'] == 'yes') : echo 'checked'; endif; ?>></td>
									<td>
										배송중
									</td>
									<td>
										<select class="select-template-code" name="kakao_talk[gone][code]">
											<option>선택하세요.</option>
											<?php echo $gone_options; ?>
										</select>
									</td>
									<td class="message-content">
										<?php 
											if(isset($templateMSG[$send_use['gone']['code']])){
												echo $templateMSG[$send_use['gone']['code']];
											} else {
										?>
											<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>
										<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td><input type="checkbox" name="kakao_talk[exchange][enable]" value="yes" <?php if($send_use['exchange']['enable'] == 'yes') : echo 'checked'; endif; ?>></td>
									<td>
										교환처리
									</td>
									<td>
										<select class="select-template-code" name="kakao_talk[exchange][code]">
											<option>선택하세요.</option>
											<?php echo $exchange_options; ?>
										</select>
									</td>
									<td class="message-content">
										<?php 
											if(isset($templateMSG[$send_use['exchange']['code']])){
												echo $templateMSG[$send_use['exchange']['code']];
											} else {
										?>
											<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>
										<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td><input type="checkbox" name="kakao_talk[refund][enable]" value="yes" <?php if($send_use['refund']['enable'] == 'yes') : echo 'checked'; endif; ?>></td>
									<td>
										환불처리
									</td>
									<td>
										<select class="select-template-code" name="kakao_talk[refund][code]">
											<option>선택하세요.</option>
											<?php echo $refund_options; ?>
										</select>
									</td>
									<td class="message-content">
										<?php 
											if(isset($templateMSG[$send_use['refund']['code']])){
												echo $templateMSG[$send_use['refund']['code']];
											} else {
										?>
											<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>
										<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td><input type="checkbox" name="kakao_talk[account][enable]" value="yes" <?php if($send_use['account']['enable'] == 'yes') : echo 'checked'; endif; ?>></td>
									<td>
										가상계좌/무통장 입금안내
									</td>
									<td>
										<select class="select-template-code" name="kakao_talk[account][code]">
											<option>선택하세요.</option>
											<?php echo $account_options; ?>
										</select>
									</td>
									<td class="message-content">
										<?php 
											if(isset($templateMSG[$send_use['account']['code']])){
												echo $templateMSG[$send_use['account']['code']];
											} else {
										?>
											<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>
										<?php
											}
										?>
									</td>
								</tr>
								<tr>
									<td><input type="checkbox" name="kakao_talk[paid][enable]" value="yes" <?php if($send_use['paid']['enable'] == 'yes') : echo 'checked'; endif; ?>></td>
									<td>
										입금확인 (processing)
									</td>
									<td>
										<select class="select-template-code" name="kakao_talk[paid][code]">
											<option>선택하세요.</option>
											<?php echo $paid_options; ?>
										</select>
									</td>
									<td class="message-content">
										<?php 
											if(isset($templateMSG[$send_use['paid']['code']])){
												echo $templateMSG[$send_use['paid']['code']];
											} else {
										?>
											<p class="description">템플릿 코드를 선택하셔야 사용 가능합니다.</p>
										<?php
											}
										?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">사용할 발신번호</th>
						<td>
							<select name="use_number">
								<option value="">===== 선택하세요 =====</option>
							<?php
								$count = 0;
								foreach($senderList->numberList as $number){
									if($number->use_yn == 'Y'){
							?>
								<option value="<?php echo $number->sendnumber;?>" <?php if($sendnumber == $number->sendnumber) : echo 'selected'; endif; ?>><?php echo $number->sendnumber;?></option>
							<?php
										$count++;
									}
								}
							?>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">신규 발신번호 등록</th>
						<td>
							<input name="wooahan_setup_kakao_sendnumber" class="send-number" type="text"> <button type="button" class="button button-get-pin-number"><?php _e('인증번호 전송', 'wooahan');?></button>
							<span class="description">
								<br>하이픈(-)없이 번호만 기입하시기 바랍니다.
							</span>
						</td>
					</tr>
					<tr valign="top" class="pinnumber-regist-tr" style="display:none">
						<th scope="row">인증번호</th>
						<td>
							<input name="wooahan_setup_kakao_pinnumber" class="pin-number" type="text"> <button type="button" class="button button-primary button-verification">인증완료</button>
							<span class="description">
								<br>SMS 로 전송된 인증번호 6자리를 기입하시기 바랍니다.
							</span>
						</td>
					</tr>
				</tbody>
			</table>
			<style>
				table.talk-enable-table { width:100%; border-collapse:collapse; border:1px; }
				table.talk-enable-table th { padding:10px !important; text-align:center; border:1px solid #e0e0e0; }
				table.talk-enable-table td { padding:10px; text-align:center; border:1px solid #e0e0e0; background:#fff; }
				table.talk-enable-table td.message-content { text-align:left; line-height:1.5; }
			</style>
			<h3>등록된 발신번호 리스트</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>발신번호</th>
						<th>인증상태</th>
					</tr>
				</thead>
				<tbody>
<?php
				if($senderList){
					if($senderList->result_code == '200'){
						foreach( $senderList->numberList as $number ){
?>
						<tr>
							<td><?php echo $number->sendnumber;?></td>
							<td>
								<?php
									if($number->use_yn == 'Y'){
										echo '인증완료';
									} else {
										echo '미인증';
									}
								?>
							</td>
						</tr>
<?php
						}
					}
				}
?>
				</tbody>
			</table>
<?php
	        } elseif( $current_section == 'templates' ){

				$bizMessage = new wooahanMessage();
				$response   = json_decode($bizMessage->templateList());
?>
				<h2>알림톡 템플릿 리스트</h2>
				<p class="description">템플릿 등록 및 수정은 별도로 API 를 제공하고 있지 않기 때문에 <a href="https://www.apistore.co.kr/" target="_blank">API스토어</a> 에 접속하셔서 직접 해주셔야 합니다.<br><a href="https://www.apistore.co.kr/" target="_blank">API스토어</a> 에 직접 등록하신 템플릿 리스트로 검수상태가 승인 상태인 템플릿만 전송 가능 합니다.<br>보다 자세한 템플릿 가이드라인은 <a href="https://www.apistore.co.kr/template_guide.do?type=1" target="_blank">여기를</a> 참조하시기 바랍니다.</p>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><input type="checkbox"></th>
							<th>템플릿 코드</th>
							<th>템플릿 명</th>
							<th>메세지 내용</th>
							<th>버튼정보</th>
							<th>검수상태</th>
						</tr>
					</thead>
					<tbody>
<?php
					if($response){
						if($response->result_code == '200'){
							$templateList = $response->templateList;
							foreach($templateList as $template){
?>
							<tr>
								<td><input type="checkbox"></td>
								<td><?php echo $template->template_code;?></td>
								<td><?php echo $template->template_name;?></td>
								<td><?php echo $template->template_msg;?></td>
								<td></td>
								<td><?php echo $template->status;?></td>
							</tr>
<?php
							}
						} else {
?>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
<?php
						}
					} else {
?>
							<tr>
								<td colspan="5">카카오 알림톡 설정을 완료하셔야 템플릿 리스트가 노출 됩니다.</td>
							</tr>
<?php
					}
?>
					</tbody>
				</table>
<?php

	        	} elseif( $current_section == 'reserve_text' ){
?>
					<h2>알림톡 예약어 리스트</h2>
					<p class="description">
						알림톡 예약어는 고객명이나 주문번호를 메세지 내용에 넣을 때 필요한 약속어 입니다.<br>
						템플릿 등록시에 하단의 정보를 넣을때 반드시 동일하게 넣어주셔야 합니다.
					</p>
					<table>
						<tr>
							<th style="text-align:left">상점명 :</th>
							<td>#{shop_name}</td>
						</tr>
						<tr>
							<th style="text-align:left">고객명 :</th>
							<td>#{customer_name}</td>
						</tr>
						<tr>
							<th style="text-align:left">주문번호 :</th>
							<td>#{order_number}</td>
						</tr>
						<tr>
							<th style="text-align:left">고객 연락처 :</th>
							<td>#{customer_phone}</td>
						</tr>
						<tr>
							<th style="text-align:left">배송상태 :</th>
							<td>#{order_status}</td>
						</tr>
						<tr>
							<th style="text-align:left">발송 운송장번호 :</th>
							<td>#{tracking_number}</td>
						</tr>
						<tr>
							<th style="text-align:left">발송 택배사 :</th>
							<td>#{tracking_company}</td>
						</tr>
					</table>
<?php
	        	} else {
		        	$settings = $this->get_settings( $current_section );
		        	WC_Admin_Settings::output_fields( $settings );	        	
	        	}
	    }


	    /**
	     * Save settings
	     */
	    public function save() {
	        global $current_section;
	        if($current_section == 'kakao'){
	        	$apistore_id 	= sanitize_text_field($_POST['apistore_id']);
	        	$kakaoplus_id 	= sanitize_text_field($_POST['kakaoplus_id']);
	        	$apistore_key 	= sanitize_text_field($_POST['apistore_key']);
	        	$sendnumber 	= sanitize_text_field($_POST['use_number']);
	        	$kakao_talk     = $_POST['kakao_talk'];
	        	
	        	if(!isset($kakao_talk['standby']['enable'])){
	        		$kakao_talk['standby']['enable'] = 'no';
	        	}

	        	if(!isset($kakao_talk['partial']['enable'])){
	        		$kakao_talk['partial']['enable'] = 'no';
	        	}

	        	if(!isset($kakao_talk['gone']['enable'])){
	        		$kakao_talk['gone']['enable'] = 'no';
	        	}

	        	if(!isset($kakao_talk['exchange']['enable'])){
	        		$kakao_talk['exchange']['enable'] = 'no';
	        	}

	        	if(!isset($kakao_talk['refund']['enable'])){
	        		$kakao_talk['refund']['enable'] = 'no';
	        	}

	        	if(!isset($kakao_talk['account']['enable'])){
	        		$kakao_talk['account']['enable'] = 'no';
	        	}

	        	if(!isset($kakao_talk['paid']['enable'])){
	        		$kakao_talk['paid']['enable'] = 'no';
	        	}

	        	$kakao_arr 		= array(
	        		'store_id' 	 => $apistore_id,
	        		'plus_id'	 => $kakaoplus_id,
	        		'key'		 => $apistore_key,
	        		'sendnumber' => $sendnumber,
	        		'send_use'	 => $kakao_talk
	        	);
	        	update_option('wooahan_kakao_account', $kakao_arr);
	        } else {
	        	$settings = $this->get_settings( $current_section );
	        	WC_Admin_Settings::save_fields( $settings );
	        }
	    }

	}

	return new WC_Settings_Wooahan();
?>