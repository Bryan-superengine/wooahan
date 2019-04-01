<?php
	class wooahanOrder {

		function __construct(){
			add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
			add_action( 'init', array($this, 'wooahan_register_custom_order_status') );
			add_filter( 'wc_order_statuses', array($this, 'wooahan_custom_order_status') );
			add_action( 'woocommerce_order_status_changed', array($this, 'wooahan_order_status_notification'), 10, 4);
			add_filter( 'woocommerce_email_classes', array($this, 'edit_woocommerce_email_classes' ) );
			add_action( 'woocommerce_order_status_shipping-gone_to_completed_notification', array( 'WC_Email_Customer_Completed_Order', 'trigger' ), 10, 2);
			//아임포트 환불 프로세스 (반품요청/배송준비중 상태일때)
			add_action( 'woocommerce_order_status_refund-request_to_cancelled', 'iamport_refund_payment', 10, 1 );
			add_action( 'woocommerce_order_status_shipping-standby_to_cancelled', 'iamport_refund_payment', 10, 1 );

			// external 은 submit reject 대상이여서 변경
			add_action( 'admin_init', array($this, 'wooahan_download_csv') );

		}

		/**
		* 주문 CSV 다운로드 기능을 internal 로 설정
		*/
		public function wooahan_download_csv(){
			if( isset($_POST['wooahan_download_csv']) ){
			    if(!isset($_POST['orders']) || !isset($_POST['title'])){
			        return false;
			    }
			    global $woocommerce, $product;

			    $order_ids = sanitize_text_field($_POST['orders']);
			    $title     = sanitize_text_field($_POST['title']);
			    $order_ids = explode(",", $order_ids);

			    $orderList = new wooahanOrderList();

			    // output headers so that the file is downloaded rather than displayed
			    header('Content-Type: text/csv; charset=euc-kr');
			    header('Content-Disposition: attachment; filename='.$title.'-'.date('YmdHis').'.csv');
			    header("Cache-Control: no-cache, no-store, must-revalidate");
			    header("Pragma: no-cache");
			    # Disable caching - Proxies
			    header("Expires: 0");

			    $output = fopen( 'php://output', 'w' );

			    fputcsv( $output, apply_filters('wooahan_csv_header_columns', array( iconv('UTF-8', 'EUC-KR','주문번호'), iconv('UTF-8', 'EUC-KR', '주문자'), iconv('UTF-8', 'EUC-KR','주문상품'), iconv('UTF-8', 'EUC-KR','옵션 아이디'), iconv('UTF-8', 'EUC-KR','옵션명'), iconv('UTF-8', 'EUC-KR','구매수량'), iconv('UTF-8', 'EUC-KR','결제된금액'), iconv('UTF-8', 'EUC-KR','상품구매금액'), iconv('UTF-8', 'EUC-KR','배송비'), iconv('UTF-8', 'EUC-KR','결제수단'), iconv('UTF-8', 'EUC-KR','주문상태'), iconv('UTF-8', 'EUC-KR','연락처'), iconv('UTF-8', 'EUC-KR','배송지 주소'), iconv('UTF-8', 'EUC-KR','송장번호') ) ) ); 
			    if($order_ids){
			        foreach($order_ids as $order_id){
			            $order = wc_get_order($order_id);
			            $order_items = $orderList->getOrderItem($order, 'title');
			            $customer = new WC_Customer( $order_id );
			            $customer_name = $order->get_billing_first_name().' '.$order->get_billing_last_name();
			            $customer_name = trim($customer_name);
			            $shipping_address = $order->get_billing_address_1().' '.$order->get_billing_address_2();
			            if($customer_name == ''){
			                $customer_name = __('비회원', 'wooahan');
			            }
			            $item_title = '';
			            if($order_items){
			                foreach($order_items as $product_id => $item){
			                    foreach($item['items'] as $title){
			                        $data = array(
			                                '주문번호'      => $order_id, 
			                                '주문자'       => iconv('UTF-8', 'EUC-KR', $customer_name),
			                                '주문상품'      => iconv('UTF-8', 'EUC-KR', get_the_title($product_id)),
			                                '옵션 아이디'   => iconv('UTF-8', 'EUC-KR', $title['item_id']),
			                                '옵션명'       => iconv('UTF-8', 'EUC-KR', str_replace( get_the_title($product_id)." - ", "", $title['item_title'])),
			                                '구매수량'     => $title['item_qty'],
			                                '결제된금액'    => number_format($order->get_total()),
			                                '상품구매금액'  => number_format($order->get_total()),
			                                '배송비'       => number_format($order->get_shipping_total()),
			                                '결제수단'      => iconv('UTF-8', 'EUC-KR', $orderList->paymentText($order->get_payment_method())),
			                                '주문상태'      => iconv('UTF-8', 'EUC-KR', $orderList->orderStatusText($order->get_status())),
			                                '연락처'       => $order->get_billing_phone(),
			                                '배송지 주소'   => iconv('UTF-8', 'EUC-KR', $shipping_address),
			                                '송장번호'      => ''
			                        );
			                        $filtered_data = apply_filters( 'wooahan_export_csv_data', $data);
			                        fputcsv( $output, $filtered_data );
			                    }
			                }
			            }
			        }
			    }
			    fclose($output);
			    echo '<meta http-equiv=\"Content-Type\" content=\"text/csv; charset=euc-kr\">';
			    echo $output;

			    die();
			}
		}

		/**
		* 우아한 order status email 클래스를 우커머스에 추가
		*/
		public function edit_woocommerce_email_classes( $email_classes ){

			require_once( WOOAHAN_PATH . 'woocommerce/emails/classes/class.shipping-standby.php' );
			require_once( WOOAHAN_PATH . 'woocommerce/emails/classes/class.shipping-partial.php' );
			require_once( WOOAHAN_PATH . 'woocommerce/emails/classes/class.shipping-gone.php' );

			$email_classes['WC_Email_Customer_Shipping_Standby'] = new WC_Email_Customer_Shipping_Standby();
			$email_classes['WC_Email_Customer_Shipping_Partial'] = new WC_Email_Customer_Shipping_Partial();
			$email_classes['WC_Email_Customer_Shipping_Gone']	 = new WC_Email_Customer_Shipping_Gone();
			return $email_classes;
		}

		/**
		* 오더 상태 변경에 따른 이메일 전송
		*/
		public function wooahan_order_status_notification( $order_id, $from_status, $to_status, $order ){

			$mailer  = WC()->mailer()->get_emails();

			if($from_status == 'processing' && $to_status == 'shipping-standby'){				
				$mailer['WC_Email_Customer_Shipping_Standby']->trigger( $order_id );
			}
			if($from_status == 'shipping-standby' && $to_status == 'shipping-partial'){
				$mailer['WC_Email_Customer_Shipping_Partial']->trigger( $order_id );
			}
			if( ($from_status == 'shipping-partial' || $from_status == 'shipping-standby' || $from_status == 'exchange-request') && $to_status == 'shipping-gone' ){
				$mailer['WC_Email_Customer_Shipping_Gone']->trigger( $order_id );
			}
			if( $from_status == 'shipping-gone' && $to_status == 'completed') {
				$mailer['WC_Email_Customer_Completed_Order']->trigger( $order_id );
			}
		}

		/**
		* 우커머스에 커스텀 order status 등록
		*/
		public function wooahan_custom_order_status( $order_statuses ){
			$order_statuses['wc-shipping-partial']  = _x('부분배송', 'Order status', 'woocommerce');
			$order_statuses['wc-shipping-gone'] 	= _x('배송중', 'Order status', 'woocommerce');
			$order_statuses['wc-shipping-standby'] 	= _x('배송준비중', 'Order status', 'woocommerce');
			$order_statuses['wc-shipping-pending'] 	= _x('배송보류', 'Order status', 'woocommerce');
			$order_statuses['wc-change'] 			= _x('교환', 'Order status', 'woocommerce');
			return $order_statuses;
		}

		/**
		* 워드프레스에 커스텀 post status 등록
		*/
		public function wooahan_register_custom_order_status( $order_statuses ){

			// WC()->mailer initiate

			if( isset($_POST['order_status']) ){
				WC()->mailer();
			}

			register_post_status( 'wc-shipping-partial' , array(
				'label' 						=> __( '부분배송', 'wooahan'),
				'public'						=> false,
				'exclude_from_search'			=> false,
				'show_in_admin_all_list'		=> true,
				'show_in_admin_status_list'		=> true,
				'label_count'					=> _n_noop( '부분 배송중 <span class="count">(%s)</span>', '부분 배송중 <span class="count">(%s)</span>' )
			));

			register_post_status( 'wc-shipping-gone' , array(
				'label' 						=> __( '배송중', 'wooahan'),
				'public'						=> false,
				'exclude_from_search'			=> false,
				'show_in_admin_all_list'		=> true,
				'show_in_admin_status_list'		=> true,
				'label_count'					=> _n_noop( '배송중 <span class="count">(%s)</span>', '배송중 <span class="count">(%s)</span>' )
			));

			register_post_status( 'wc-shipping-standby' , array(
				'label' 						=> __( '배송준비중', 'wooahan'),
				'public'						=> false,
				'exclude_from_search'			=> false,
				'show_in_admin_all_list'		=> true,
				'show_in_admin_status_list'		=> true,
				'label_count'					=> _n_noop( '배송 준비중 <span class="count">(%s)</span>', '배송 준비중 <span class="count">(%s)</span>' )
			));

			register_post_status( 'wc-shipping-pending', array(
				'label'							=> __( '배송보류', 'wooahan'),
				'public'						=> false,
				'exclude_from_search'			=> false,
				'show_in_admin_all_list'		=> true,
				'show_in_admin_status_list'		=> true,
				'label_count'					=> _n_noop( '배송보류 <span class="count">(%s)</span>', '배송보류 <span class="count">(%s)</span>')
			));

			register_post_status( 'wc-change', array(
				'label'							=> __( '교환', 'wooahan'),
				'public'						=> false,
				'exclude_from_search'			=> false,
				'show_in_admin_all_list'		=> true,
				'show_in_admin_status_list'		=> true,
				'label_count'					=> _n_noop( '교환 <span class="count">(%s)</span>', '교환 <span class="count">(%s)</span>')
			));

			return $order_statuses;
		}

		public function admin_scripts(){
			global $pagenow;
			if( in_array($pagenow, array('admin.php')) && isset($_GET['page']) && $_GET['page'] == 'wooahan_shop_order'){
				wp_enqueue_script('jquery-ui-datepicker');
				wp_register_style( 'jquery-ui', 'http://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css' );
				wp_enqueue_style('jquery-ui');
				wp_enqueue_style( 'wooahan-order', plugins_url('/assets/admin/shop.order.css', WOOAHAN__FILE__), '', date('His'));
				wp_enqueue_script( 'vueJS', 'https://cdn.jsdelivr.net/npm/vue' );
				wp_enqueue_script( 'wooahan-order', plugins_url('/assets/admin/shop.order.js', WOOAHAN__FILE__), '', date('His'));
				wp_enqueue_style( 'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css' );
				wp_enqueue_script('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js');
				wp_enqueue_style( 'fontawesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css' );
			}
		}



	}



	new wooahanOrder();
?>
