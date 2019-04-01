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
	            /*
	            'msg_template'	=> __( '메세지 템플릿', 'wooahan' ),
	            'sms'         	=> __( 'SMS 설정', 'wooahan' ),
	            'kakao'         => __( '카카오 알림톡 설정', 'wooahan' ),
	            'faq'			=> __( 'FAQ', 'wooahan')
				*/
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
	            case 'kakao':
	                $settings = array(
	                    'section_title' => array(
	                        'name'     => __( 'Section Two Title', 'woocommerce-settings-tab-demo' ),
	                        'type'     => 'title',
	                        'desc'     => '',
	                        'id'       => 'wc_settings_tab_demo_section_title'
	                    ),
	                    'title' => array(
	                        'name' => __( 'Section Two Title', 'woocommerce-settings-tab-demo' ),
	                        'type' => 'text',
	                        'desc' => __( 'This is some helper text', 'woocommerce-settings-tab-demo' ),
	                        'id'   => 'wc_settings_tab_demo_title'
	                    ),
	                    'description' => array(
	                        'name' => __( 'Section Two Description', 'woocommerce-settings-tab-demo' ),
	                        'type' => 'textarea',
	                        'desc' => __( 'This is a paragraph describing the setting. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda. Lorem ipsum yadda yadda yadda.', 'woocommerce-settings-tab-demo' ),
	                        'id'   => 'wc_settings_tab_demo_description'
	                    ),
	                    'section_end' => array(
	                         'type' => 'sectionend',
	                         'id' => 'wc_settings_tab_demo_section_end'
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
	        $settings = $this->get_settings( $current_section );
	        WC_Admin_Settings::output_fields( $settings );
	    }


	    /**
	     * Save settings
	     */
	    public function save() {
	        global $current_section;
	        $settings = $this->get_settings( $current_section );
	        WC_Admin_Settings::save_fields( $settings );
	    }

	}

	return new WC_Settings_Wooahan();
?>