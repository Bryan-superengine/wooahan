<?php	
	class wooahan_initialize {
		function __construct(){
			register_activation_hook( WOOAHAN__FILE__, array($this, 'create_wooahan_data'));
			add_action( 'admin_init', array($this, 'activation_error'), 99 );
			add_action( 'save_post_product', array($this, 'wooahan_ajax_save') );
			add_filter( 'woocommerce_locate_template', array($this, 'wooahan_woocommerce_locate_template'), 10, 3 );
			add_action( 'wp_enqueue_scripts', array($this, 'wooahan_scripts') );
			add_shortcode('wooahan_address_fields', array($this, 'wooahan_address_fields') );
			add_action( 'woocommerce_after_add_to_cart_quantity', array($this, 'wooahan_single_add_div_start') );
			//add_action( 'woocommerce_after_add_to_cart_button', array($this, 'wooahan_single_add_div_end') );
			add_filter( 'woocommerce_cart_item_name', array( $this, 'wooahan_cart_item_name' ), 10, 3 );
			add_filter( 'woocommerce_order_item_name', array( $this, 'wooahan_cart_item_name' ), 10, 3 );
			add_action( 'woocommerce_update_order_item', array( $this, 'wooahan_update_order_item'), 10, 3 );
			add_filter( 'woocommerce_package_rates', array($this, 'wooahan_hide_shipping_when_free_available'), 100);

			add_action( 'admin_menu', array($this, 'wooahan_shop_order_menu'), 99);

			add_action( 'admin_init', array($this, 'wooahan_setup_wizard') );

			add_action( 'tgmpa_register', array($this, 'wooahan_register_required_plugins') );

			add_filter( 'woocommerce_my_account_my_orders_actions', array($this, 'wooahan_my_account_my_orders_actions'), 10, 2);

			add_filter( 'woocommerce_display_item_meta', array($this, 'wooahan_remove_display_item_meta'), 10, 3);

			add_action( 'wp_print_scripts', array($this, 'wooahan_dequeue_script'), 100 );

			$badge_use 		= get_option('wc_settings_tab_wooahan_badge_use', true);
			$badge_position = get_option('wc_settings_tab_wooahan_badge_position', true);

			if(!$badge_position){
				$badge_position = 'title_below';
			}

			if($badge_use != 'none'){
				$single_action_title = 'woocommerce_single_product_summary';
				switch($badge_position){
					case 'title_below' :
						$list_action_title 	= 'woocommerce_shop_loop_item_title';
						$single_position 	= 6;
						$list_position   	= 11;
					break;

					case 'title_above' :
						$list_action_title	= 'woocommerce_shop_loop_item_title';
						$single_position 	= 4;
						$list_position   	= 9;
					break;

					case 'price_above' :
						$list_action_title 	= 'woocommerce_after_shop_loop_item_title';
						$single_position 	= 9;
						$list_position   	= 9;
					break;

					case 'price_below' :
						$list_action_title 	= 'woocommerce_after_shop_loop_item_title';
						$single_position 	= 11;
						$list_position   	= 11;
					break;

					case 'excerpt_above' :
						$list_action_title 	= 'woocommerce_after_shop_loop_item_title';
						$single_position 	= 9;
						$list_position   	= 9;
					break;

					case 'excerpt_below' :
						$list_action_title 	= 'woocommerce_after_shop_loop_item_title';
						$single_position 	= 11;
						$list_position   	= 11;
					break;
				}
				$actionArray  = array();
				switch($badge_use){
					case 'single' :
						$actionArray[] = array(
							'action' 	=> $single_action_title,
							'position' 	=> $single_position
						);
					break;

					case 'list' :
						$actionArray[] = array(
							'action' 	=> $list_action_title,
							'position' 	=> $list_position
						);
					break;

					case 'both' :

						// single
						$actionArray[] = array(
							'action' 	=> $single_action_title,
							'position' 	=> $single_position
						);
						// list
						$actionArray[] = array(
							'action' 	=> $list_action_title,
							'position' 	=> $list_position
						);
					break;
				}

				foreach($actionArray as $arr){
					add_action( $arr['action'], array($this, 'wooahan_badge_display'), $arr['position'] );
				}
			}

			add_filter( 'woocommerce_get_settings_pages', array( $this, 'wooahan_get_settings') );

			add_filter( 'woocommerce_get_availability', array($this, 'wooahan_remove_available_product_message'), 1, 2);
		}

		/**
		 * 아임포트에서는 네이버페이 상품페이지 버튼 클릭시 variation 을 하나만 등록시킨다. 따라서 기존 JS 스크립트를 dequeue 하고 새롭게 집어넣는다.
		 * @return void
		 */
		public function wooahan_dequeue_script(){
			//wp_deregister_script('iamport_naverpay_for_woocommerce');
			//wp_register_script( 'iamport_naverpay_for_woocommerce', plugins_url( '/assets/js/iamport.naverpay.js', WOOAHAN__FILE__ ), array('jquery', 'woocommerce_iamport_script', 'naverpay_script'), date('His'));
			//wp_enqueue_script( 'iamport_naverpay_for_woocommerce' );
			//wp_register_script( 'wooahan_naverpay_for_woocommerce', plugins_url( '/assets/js/iamport.naverpay.js', WOOAHAN__FILE__ ), array('jquery', 'woocommerce_iamport_script', 'naverpay_script'), date('His'));
			//wp_enqueue_script( 'wooahan_naverpay_for_woocommerce' );
		}

		/**
		 * [주문 상세정보에서 wooahan_shipping 메타 키와 내용 표시를 삭제한다.]
		 * @param  [text] 	$html [뿌려주는 전체 아이템 메타 html]
		 * @param  [object] $item [아이템 오브젝트]
		 * @param  [array] 	$args
		 * @return void
		 */
		public function wooahan_remove_display_item_meta( $html, $item, $args ){
			foreach($item->get_formatted_meta_data() as $meta_id => $meta){
				if($meta->key != 'wooahan_shipping'){
 					$value     = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
      				$strings[] = $args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value;
      			}
			}
		    if ( $strings ) {
		      $html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
		    }
			return $html;
		}

		/**
		 * @brief  My-account 에 배송중/부분배송중/배송완료 물건에 한하여 배송조회 버튼을 노출시킨다.
		 *
		 * @param  array  	$actions 	액션배열
		 * @param  object 	$order 		주문 오브젝트
		 * @return array    $actions
		 */
		public function wooahan_my_account_my_orders_actions($actions, $order){
			if($order->get_status() == 'shipping-partial' || $order->get_status() == 'shipping-gone' || $order->get_status() == 'completed'){
				$order_id = $order->get_id();
				$shipping_numbers = get_post_meta($order_id, 'wooahan_shipping_number', true);

				if($shipping_numbers){
					$corp 	= $shipping_numbers[count($shipping_numbers)-1]['corp'];
					$code 	= $shipping_numbers[count($shipping_numbers)-1]['code'];
					$number = $shipping_numbers[count($shipping_numbers)-1]['number'];
					$actions['wooahan-shipping-tracker'] = array(
						'url' => '/shipping-tracking/'.$code.'/'.$number,
						'name' => __('배송조회', 'wooahan')
					);
				}
			}
			return $actions;
		}

		public function wooahan_register_required_plugins(){
			$plugins = array(
				array(
					'name'      => '우커머스',
					'slug'      => 'woocommerce',
					'required'  => true,
				),
				array(
					'name' 		=> '아임포트 결제플러그인 for 우커머스',
					'slug'		=> 'iamport-for-woocommerce',
					'required'	=> true
				)
			);
			$config = array(
				'id'           => 'wooahan',                 // Unique ID for hashing notices for multiple instances of TGMPA.
				'default_path' => '',                      // Default absolute path to bundled plugins.
				'menu'         => 'tgmpa-install-plugins', // Menu slug.
				'parent_slug'  => 'plugins.php',            // Parent menu slug.
				'capability'   => 'manage_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
				'has_notices'  => true,                    // Show admin notices or not.
				'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
				'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
				'is_automatic' => false,                   // Automatically activate plugins after installation or not.
				'message'      => '',                      // Message to output right before the plugins table.
			);
			tgmpa( $plugins, $config );
		}

		/**
		* 우아한 플러그인 첫 활성화시 setup wizard 노출
		*/
		public function wooahan_setup_wizard(){
		    if ( get_option( 'wooahan_setup_wizard_notice') ) {
		        delete_option( 'wooahan_setup_wizard_notice' );
		        add_action( "admin_notices", array($this, "wooahan_setup_wizard_notice" ));
		    }
		}

		public function get_required_plugins_count(){
			$required = $this->get_required_plugins();
			$count = 0;
			foreach($required as $req){
				if($req['status'] == false){
					$count++;
				}
			}
			return $count;
		}

		/**
		* 우아한 플러그인 동작을 위해 필요한 선행 설치 플러그인 리스트
		*/
		public function get_required_plugins(){

			$required = array();

			$required['woocommerce'] = array(
				'title' => '우커머스가 반드시 설치되어 있어야 합니다.',
				'description' => '우아한은 우커머스를 기반으로 동작하는 플러그인 입니다. 반드시 사전에 먼저 설치 되어 있어야 합니다.',
				'status' => false
			);

			$required['iamport'] = array(
				'title' => '아임포트 결제플러그인이 설치되어 있어야 합니다.',
				'description' => '우아한은 원할한 결제를 위한 아임포트 결제플러그인을 사용합니다.',
				'status' => false
			);

			if( class_exists( 'woocommerce') ){
				$required['woocommerce']['status'] = true;
			}

			if( class_exists( 'Base_Gateway_Iamport') ){
				$required['iamport']['status'] = true;
			}

			return $required;

		}

		public function wooahan_setup_wizard_notice(){

			$required_list = $this->get_required_plugins();
			
			if($this->get_required_plugins_count() > 0){
				$ptext = __('정상적인 사용을 위해 아래의 사항들이 반드시 필수적으로 선행되어야 합니다.<br>
						필수 요소들을 먼저 설치하시고 우아한 플러그인을 다시 활성화 해주시기 바랍니다.', 'wooahan');
			} else {
				$ptext = __('좋습니다! 이제 다음단계로 진행할 수 있습니다!<br>하단의 기본설정 바로가기 버튼을 눌러 기본정보를 기입하시기 바랍니다.', 'wooahan');
			}
		?>
			<div id="wooahan_setup_wizard">
				<div class="content">
					<img src="<?php echo plugins_url('/assets/images/logo-color-svg.svg', WOOAHAN__FILE__);?>" class="logo">
					<h3>우아한 플러그인 사용에 감사드립니다.</h3>
					<p class="h3-description">
						<?php echo $ptext; ?>
					</p>
					<div class="required-list">
						<ul>
							<?php
								foreach($required_list as $required){
							?>
								<li><?php echo $required['title'];?><span class="checked <?php if($required['status'] == true) : echo 'activated'; endif;?>"><span class="dashicons dashicons-marker"></span></span></li>
							<?php
								}
							?>
						</ul>
					</div>
					<?php 
						if($this->get_required_plugins_count() == 0){
					?>
						<a href="/wp-admin/admin.php?page=wc-settings&tab=wooahan" class="button button-primary">기본설정 바로가기</a> 
					<?php 
						} else {
					?>
						<a href="/plugins.php?page=tgmpa-install-plugins" class="button button-primary">필수 플러그인 설치</a>
					<?php
						}
					?>
					<a href="#" class="button" onclick="location.reload()">나중에 하기</a>				
				</div>
				<div class="background"></div>
			</div>
			<style>
				div#wooahan_setup_wizard {
					position:fixed;
					top:0;
					left:0;
					width:100%;
					height:100%;
					z-index:999;
				}
				div#wooahan_setup_wizard div.content {
					position:absolute;
					top:50%;
					left:50%;
					width:500px;
					height:500px;
					margin-top:-250px;
					margin-left:-250px;
					background:#fff;
					z-index:2;
					border:10px solid #cfe8f4;
					box-sizing:border-box;
					padding:20px;
					text-align:center;
				}

				div#wooahan_setup_wizard div.content div.required-list {
					display:block;
					border-top:1px solid #e0e0e0;
					border-bottom:1px solid #e0e0e0;
					margin-bottom:20px;
				}

				div#wooahan_setup_wizard div.content div.required-list ul {
					display:block;
					padding:0 20px;
					list-style:decimal;
				}

				div#wooahan_setup_wizard div.content div.required-list ul li {
					display:block;
					text-align:left;
				}

				div#wooahan_setup_wizard div.content div.required-list ul li span.checked {
					float:right;
					color:#d0d0d0;
				}

				div#wooahan_setup_wizard div.content div.required-list ul li span.checked.activated {
					color:#39c92f;
				}

				div#wooahan_setup_wizard div.content img.logo {
					display:inline-block;
					width:200px;
					height:auto;
					margin-top:20px;
				}
				div#wooahan_setup_wizard div.background {
					position:absolute;
					top:0;
					left:0;
					width:100%;
					height:100%;
					background:#000;
					opacity:0.6;
					z-index:1;
				}
			</style>
		<?php
		}


		public function wooahan_shop_order_menu(){

			$order_controll 	= get_option('wc_settings_tab_wooahan_order_conroll', true);
			$product_controll 	= get_option('wc_settings_tab_wooahan_product_controll', true);

			$default_callback   = array($this, 'wooahan_shop_order');
			$default_slug 		= 'wooahan_shop_order';
			$default_title 		= '주문정보';

			if($order_controll != 'yes'){
				$default_callback 	= '/edit.php?post_type=product';
				$default_slug 		= 'wooahan_product_mangage';
				$default_title 		= '상품관리';

				if($product_controll != 'yes'){
					$default_callback 	= array($this, 'wooahan_store_management');
					$default_slug 		= 'wooahan_store_manage';
					$default_title 		= '재고관리';
				}
			}



			add_menu_page('우아한', '우아한', 'manage_options', $default_slug, $default_callback, plugins_url('/assets/images/wooahan-20px.svg', WOOAHAN__FILE__), 39);
			add_submenu_page( $default_slug, $default_title, $default_title, 'manage_options', $default_slug );
			if($default_slug != 'wooahan_store_manage'){
				add_submenu_page( $default_slug, '재고관리', '재고관리', 'manage_options', 'wooahan_store_manage', array($this, 'wooahan_store_management') );
			}
			if($default_slug != 'wooahan_product_manage'){
				add_submenu_page( $default_slug, '상품관리', '상품관리', 'manage_options', '/edit.php?post_type=product' );
			}
			add_submenu_page( $default_slug, '설정', '설정', 'manage_options', '/admin.php?page=wc-settings&tab=wooahan' );
		}

		public function wooahan_store_management(){
			echo '다음 버전에서...';
		}

		public function wooahan_shop_order(){
			echo '<div class="wrap">';

				ob_start();

					include_once(WOOAHAN_PATH . '/includes/admin/shop.order.php');

				$contents = ob_get_contents();

				ob_get_clean();

				echo $contents;

			echo '</div>';
		}


		/**
		* 무료배송이 package rates 에 있을경우 다른 배송 클래스 숨기기
		*/
		public function wooahan_hide_shipping_when_free_available( $rates ){
			//print_r($rates);
			$free = array();
			foreach( $rates as $rate_id => $rate ){
				if( 'free_shipping' === $rate->method_id ){
					$free[$rate_id] = $rate;
					break;
				}
			}
			return !empty($free) ? $free : $rates;
		}

		/**
		* (옵션 상품중 Default 상품이 품절이라면) 우커머스 기본 available product 메세지를 삭제한다.
		*/
		public function wooahan_remove_available_product_message( $availability, $product ){
			$availability['availability'] = '';
			return $availability;
		}

		public function wooahan_rich_edit($c){
			global $post_type;
			if( 'product' == $post_type ){
				return false;
			}
			return $c;
		}

		public static function wooahan_get_settings( $settings ){
			$settings[] = include(WOOAHAN_PATH . 'class/wc-wooahan-settings.php' );
			return $settings;
		}

		public function wooahan_badge_display(){
			global $post;
			$wooahanBadge = new wooahanBadge();
			$badges 	  = $wooahanBadge->addedBadges($post->ID);

			if($badges){
		?>

				<div id="wooahanBadges">
					<ul>
		<?php
						foreach($badges as $badge){
							($badge['margin']['top']) 		? $margin_top 		= $badge['margin']['top'] 		: $margin_top = 0;
							($badge['margin']['right']) 	? $margin_right 	= $badge['margin']['right'] 	: $margin_right = 0;
							($badge['margin']['bottom']) 	? $margin_bottom 	= $badge['margin']['bottom'] 	: $margin_bottom = 0;
							($badge['margin']['left']) 		? $margin_left 		= $badge['margin']['left'] 		: $margin_left = 0;
							($badge['width'])				? $badge_width 		= $badge['width']				: $badge_width = '';
		?>
							<li class="badge" style="width:<?php echo $badge_width;?>px; margin:<?php echo $margin_top;?>px <?php echo $margin_right;?>px <?php echo $margin_bottom;?>px <?php echo $margin_left;?>px;"><img src="<?php echo $badge['url'];?>"></li>
		<?php
						}
		?>
					</ul>
				</div>
		<?php
			}
		}

		public function wooahan_update_order_item( $item_id, $item, $order_id ){
			if(isset($item['variation_id']) && $item['variation_id']){
				$variation_id = $item['variation_id'];
				$not_req      = get_post_meta($variation_id, 'not_req', true);
				if($not_req == 'yes'){
					global $wpdb;

					$wpdb->update(
						$wpdb->prefix . 'woocommerce_order_items',
						array(
							'order_item_name' => get_the_title($item['product_id']).' - '.get_post_meta($variation_id, 'not_req_title', true)
						),
						array(
							'order_item_id' => $item_id
						)
					);

					$item->save_meta_data();
					$item->apply_changes();
				}
			}
		}

		public function wooahan_cart_item_name($item_name, $cart_item, $cart_item_key){

			if($cart_item['variation_id']){
				$product_id 		= $cart_item['product_id'];
				$single_variation   = new WC_Product_Variation($cart_item['variation_id']);
        		$variation_id       = $single_variation->get_id();

        		$not_req            = get_post_meta($variation_id, 'not_req', true);

        		if($not_req != 'yes'){
        			$variation_title    = implode("-", $single_variation->get_variation_attributes());
        			$item_name 			= '<span class="product-title">'.get_the_title($product_id).'</span><span class="variation-title">'.$variation_title.'</span>';
        		} else {
        			$not_req_title 		= get_post_meta($variation_id, 'not_req_title', true);
        			$item_name 	 		= $not_req_title;
        		}
        	}

        	return $item_name;

		}

		public function wooahan_single_add_div_start(){
			if($this->can_direct_buy_controll() == true){
				global $product;

					if($product->is_type('variable')){
						$product_type = 'true';
					} else {
						$product_type = 'false';
					}

				echo '<input type="hidden" name="is_variable" value="'.$product_type.'">';
				//echo '<input type="hidden" name="product_id" value="'.$product->get_id().'">';
				if($product_type == 'true'){
					echo '<button type="button" class="button direct-buy button-direct-buy" v-on:click="direct_buy">바로구매</button>';
				} else {
					echo '<button type="button" class="button direct-buy button-single-direct-buy" v-on:click="direct_buy">바로구매</button>';
				}
			}
		}

		public function wooahan_address_fields($attr){
			ob_start();
			include_once(WOOAHAN_PATH . '/shortcodes/form.fields.php');
			$contents = ob_get_contents();
			ob_get_clean();
			return $contents;
		}

		public function create_wooahan_data(){
			global $wpdb, $user_ID;

			update_option( 'wooahan_setup_wizard_notice', 1 );

			$form_field_post = array(
				'post_title' => '배송정보 입력',
				'post_content' => '[wooahan_address_fields]',
				'post_status' => 'publish',
				'post_author' => $user_ID,
				'post_type' => 'page'
			);

			$old_form_field_id = get_option('wooahan_form_fields_id', true);
			if($old_form_field_id){
				$old_post = get_post_status($old_form_field_id);
				if(!$old_post){
					$post_id = wp_insert_post($form_field_post);
					update_option('wooahan_form_fields_id', $post_id);
				} else {
					$update_post = array(
						'ID' => $old_form_field_id,
						'post_title' => __('배송정보 입력', 'wooahan'),
						'post_content' => '[wooahan_address_fields]'
					);
					wp_update_post($update_post);
				}
			} else {
				$post_id = wp_insert_post($form_field_post);
				update_option('wooahan_form_fields_id', $post_id);
			}


			/**
			* 초기 우커머스 우아한 탭 설정
			*/
			if(!get_option('wc_settings_tab_wooahan_product_controll', true)){
				update_option( 'wc_settings_tab_wooahan_product_controll', 'yes' );			// 우아한 상품등록 활성화
			}
			if(!get_option('wc_settings_tab_wooahan_order_controll', true)){
				update_option( 'wc_settings_tab_wooahan_order_controll', 'yes' ); 			// 우아한 주문관리 활성화
			}
			if(!get_option('wc_settings_tab_wooahan_direct_buy', true)){
				update_option( 'wc_settings_tab_wooahan_direct_buy', 'yes' );				// 우아한 바로구매 활성화
			}
			if(!get_option('wc_settings_tab_wooahan_badge_user', true)){
				update_option( 'wc_settings_tab_wooahan_badge_user', 'both' );				// 우아한 뱃지 사용
			}
			if(!get_option('wc_settings_tab_wooahan_badge_position', true)){
				update_option( 'wc_settings_tab_wooahan_badge_position', 'price_above' );	// 기본 뱃지 위치
			}			
			if(!get_option('wc_settings_tab_wooahan_sweettracker_delay', true)){
				update_option( 'wc_settings_tab_wooahan_sweettracker_delay', 3 );			// API Connection delay hour
			}			

			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix.'wooahan_shipping_data';
			$sql = "CREATE TABLE ".$table_name." (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				tcorp varchar(10) DEFAULT '' NOT NULL,
				tnum varchar(55) DEFAULT '' NOT NULL,
				date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				strtotime varchar(55) DEFAULT '' NOT NULL,
				value LONGTEXT,
				PRIMARY KEY (id) 
			) ".$charset_collate.";";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}


		public function wooahan_scripts(){
			if( is_woocommerce() ){
				wp_enqueue_style( 'wooahan', plugins_url('/assets/css/wooahan.css', WOOAHAN__FILE__), '', date('His') );
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'wooahan', plugins_url('/assets/js/wooahan.js', WOOAHAN__FILE__), array('jquery'), date('His') );
			}
			if( is_product() ){
				wp_enqueue_script( 'vueJS', 'https://cdn.jsdelivr.net/npm/vue' );
				wp_localize_script( 'vueJS', 'wooahanAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) , 'security' => wp_create_nonce('wooahan') ) );
				wp_enqueue_script( 'wooahan-vue', plugins_url('/assets/js/wooahan.vue.js', WOOAHAN__FILE__) . '', array('jquery'), date('His') );
				wp_localize_script( 'wooahan-vue', 'wooahanAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));
			}
			wp_enqueue_style( 'wooahan-formfields', plugins_url('/assets/css/wooahan.form.fields.css', WOOAHAN__FILE__), '', date('His') );
			wp_enqueue_script( 'sumoselect', plugins_url('/assets/js/jquery.sumoselect.min.js', WOOAHAN__FILE__), array('jquery') );
			wp_enqueue_style( 'sumoselect', plugins_url('/assets/css/sumoselect.css', WOOAHAN__FILE__ ));
			wp_enqueue_script( 'wooahan-formfields', plugins_url('/assets/js/wooahan.form.fields.js', WOOAHAN__FILE__), array('jquery'), date('His') );
				wp_localize_script( 'wooahan-formfields', 'wooahanAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ));
			wp_enqueue_style( 'wooahan-common', plugins_url('/assets/css/wooahan.common.css', WOOAHAN__FILE__), '', date('His') );
			wp_enqueue_style ( 'dashicons' );

			if(is_account_page()){
				wp_enqueue_script( 'shipping-tracker', plugins_url('/assets/js/shipping.tracker.js', WOOAHAN__FILE__), array('jquery'), date('His') );
			}
		}

		/**
		* 우커머스 템플릿 우아한 플러그인으로 override
		*/
		public function wooahan_woocommerce_locate_template( $template, $template_name, $template_path ){
			global $woocommerce;

			$_template = $template;

			if( ! $template_path ) $template_path = $woocommerce->template_url;

			$plugin_path = WOOAHAN_PATH . 'woocommerce/';

			$template = locate_template(
				array(
					trailingslashit( $template_path ) . $template_name,
					$template_name
				)
			);

			if( ! $template && file_exists( $plugin_path . $template_name ) ){
				$template = $plugin_path . $template_name;
			}

			if( ! $template ){
				$template = $_template;
			}
				
			return $template;
		}

		public function activation_error(){
			if( !class_exists( 'Woocommerce') ){
				echo '<div id="message" class="error">';
				echo '<p>'.__('Wooahan 플러그인을 활성화 하기 위해서는 우커머스가 설치 되어 있어야 합니다.', 'wooahan').'</p>';
				echo '</div>';
				echo '<style>div.updated.notice {display:none}</style>';
				deactivate_plugins( plugin_basename( WOOAHAN__FILE__ ) );
			} else {
				if($this->can_product_controll() == true){
					if(isset($_GET['mode']) && $_GET['mode'] == 'classic'){

					} else {
						if( (isset($_GET['mode']) && $_GET['mode'] == 'wooahan') || !isset($_GET['mode']) ){
							remove_post_type_support('product', 'title');
							remove_post_type_support('product', 'editor');
							add_action( 'edit_form_after_title', array($this, 'wooahan_builder_init') );
							add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
							add_action( 'admin_footer', array($this, 'admin_footer_function') );
							add_action( 'admin_head-post.php', array($this, 'wooahan_xhr') );
							add_action( 'admin_head-post-new.php', array($this, 'wooahan_xhr' ) );
							add_filter( 'user_can_richedit' , array($this, 'wooahan_rich_edit'), 50 );
						}	
					}
				}
			}
		}

		public function wooahan_xhr(){
			global $post;
			if('product' == $post->post_type){

				wp_register_script( 'wooahan_save_product', plugins_url('/assets/admin/product.save.js', WOOAHAN__FILE__), '', date('His') );
				wp_enqueue_script( 'wooahan_save_product' );

				wp_localize_script( 'wooahan_save_product', 'ajax_object', array( 
					'post_id' => $post->ID,
					'post_url' => admin_url('post.php')
				));
			}
		}

		// 보안을 위한 sanitize

		public function sanitize( $input ){

			// Initialize the new array will hold the sanitize values
			$new_input = array();

			foreach( $input as $key => $val ){

				switch($key){
					case 'badge_position' :
					case 'badges' :
					case 'badges_keys' :
					case 'attributes' :
					case 'cat' :
						$attributes = $val;
						if(is_array($attributes)){
							foreach($attributes as $akey => $aval){

								if(is_array($aval)){
									foreach($aval as $secondkey => $secondval){

										if(is_array($secondval)){
											foreach($secondval as $thirdkey => $thirdval){
												if(is_array($thirdval)){

												} else {
													$new_input[$key][$akey][$secondkey][$thirdkey] = (isset($input[$key][$akey][$secondkey][$thirdkey])) ? sanitize_text_field( $thirdval ) : '';
												}

											}
										} else {
											$new_input[$key][$akey][$secondkey] = (isset($input[$key][$akey][$secondkey])) ? sanitize_text_field( $secondval ) : '';
										}

									}
								} else {
									$new_input[$key][$akey] = (isset($input[$key][$akey])) ? sanitize_text_field( $aval ) : '';
								}

							}
						} else {
							$new_input[$key] = (isset($input[$key])) ? sanitize_text_field( $val ) : '';
						}

					break;

					case 'option_use' :
					case 'badge_use'  :
						$new_input[$key] = (isset($input[$key])) ? sanitize_text_field( $val ) : 'no';
					break;

					case 'post_status' :
						$new_input[$key] = (isset($input[$key])) ? sanitize_text_field( $val ) : 'publish';
					break;

					case 'merge_type' :
						$new_input[$key] = (isset($input[$key])) ? sanitize_text_field( $val ) : 'merge_sep';
					break;

					default :
						$new_input[$key] = (isset($input[$key])) ? sanitize_text_field( $val ) : '';
					break;
				}
				
			}
			return $new_input;
		}

		public function wooahan_ajax_save( $post_id ){
			if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

			if(isset($_POST['post_type']) && 'product' == $_POST['post_type']){

				global $wpdb;

				$wooahan = $this->sanitize($_POST['wooahan']);
				//$wooahan = array_map( 'esc_attr', $_POST['wooahan'] );
				/* data */
				
				isset($wooahan['regular_price']) 	? $regular_price 	= $wooahan['regular_price'] 		: $regular_price 	= '';
				isset($wooahan['sale_price']) 		? $sale_price 		= $wooahan['sale_price'] 			: $sale_price 		= '';
				isset($wooahan['sale_start']) 		? $sale_start 		= $wooahan['sale_start']			: $sale_start 		= '';
				isset($wooahan['sale_end']) 		? $sale_end 		= $wooahan['sale_end']				: $sale_end 		= '';
				isset($wooahan['sale_unlimited']) 	? $sale_unlimited  	= $wooahan['sale_unlimited']		: $sale_unlimited 	= '';
				isset($wooahan['post_excerpt']) 	? $post_excerpt  	= $wooahan['post_excerpt']			: $post_excerpt 	= '';
				isset($wooahan['cat']) 				? $product_cat  	= $wooahan['cat']					: $product_cat  	= '';
				isset($wooahan['tags'])				? $product_tags 	= $wooahan['tags']					: $product_tags		= '';
				isset($wooahan['thumbnail']) 		? $thumbnail 		= $wooahan['thumbnail']				: $thumbnail 		= '';
				isset($wooahan['gallery'])			? $galleries 		= $wooahan['gallery']				: $galleries 		= '';
				isset($_POST['wooahan_editor'])		? $content 			= sanitize_text_field($_POST['wooahan_editor'])			: $content  		= '';
				isset($wooahan['option_use']) 		? $option_use 		= $wooahan['option_use']			: $option_use 		= 'no';
				isset($wooahan['badge_use'])		? $badge_use 		= $wooahan['badge_use']				: $badge_use 		= 'no';
				isset($wooahan['attributes']) 		? $attributes 		= $wooahan['attributes']			: $attributes 		= '';
				isset($wooahan['merge_type'])		? $merge_type 		= $wooahan['merge_type']			: $merge_type 		= 'merge_sep';
				isset($wooahan['attributes']) 		? $attributes 		= $wooahan['attributes']			: $attributes 		= '';
				isset($wooahan['badges']) 			? $badges 			= $wooahan['badges']				: $badges 			= array();
				isset($wooahan['badges_keys'])		? $badges_keys 		= $wooahan['badges_keys']			: $badges_keys 		= array();
				isset($wooahan['post_status'])		? $post_status 		= $wooahan['post_status']			: $post_status 		= 'publish';
				isset($wooahan['badge_position'])	? $badge_position   = $wooahan['badge_position']		: $badge_position 	= array();
				$post = get_post($post_id);

				$wpdb->update( $wpdb->posts, array( 'post_status' => $post_status ), array( 'ID' => $post_id ) );

				$old_status 	 	= $post->post_status;
				$post->post_status 	= $post_status;

				wp_transition_post_status( $post_status, $old_status, $post );				

				if(is_array($attributes)){
					// 먼저 attributes 를 메타에 집어 넣는다.
					update_post_meta( $post_id, '_test_attributes', $attributes );
					wooahan_attribute_save($post_id, $attributes);
				} else {
					delete_post_meta( $post_id, '_product_attributes' );
					delete_post_meta( $post_id, '_product_not_required_attributes' );
				}

				update_post_meta($post_id, '_regular_price', $regular_price);
				update_post_meta($post_id, '_sale_price', $sale_price);
				update_post_meta($post_id, '_badge_position', $badge_position);
				update_post_meta($post_id, '_variation_merge_type', $merge_type);
				update_post_meta($post_id, '_is_badge_use', $badge_use);

				if(is_array($product_cat)){
					$cats = array();
					foreach($product_cat as $pcat){
						foreach($pcat as $cat){
							$cats[] = $cat;
						}
					}
					$ucats = array_unique($cats);
					wp_set_post_terms( $post_id, $ucats, 'product_cat' );
					update_post_meta( $post_id, 'wooahan_product_cats', $product_cat);
				}

				if(is_array($product_tags)){
					wp_set_post_terms( $post_id, $product_tags, 'product_tag' );
				}

				if($badges){
					update_post_meta($post_id, '_wooahan_badges', $badges);
				} else {
					delete_post_meta($post_id, '_wooahan_badges');

				}
				if($badges_keys){
					$badges_keys = explode(",", $badges_keys);
					$badges_keys_int = array();
					foreach($badges_keys as $key){
						$badges_keys_int[] = (int)$key;
					}
					update_post_meta($post_id, '_wooahan_badges_keys', $badges_keys_int);
				} else {
					delete_post_meta($post_id, '_wooahan_badges_keys');
				}

				if($thumbnail){
					set_post_thumbnail( $post_id, $thumbnail );
				}

				if($option_use == 'yes'){
					wp_set_object_terms( $post_id, 'variable', 'product_type', false );
				}

				if($option_use == 'no'){
					wp_set_object_terms( $post_id, 'simple', 'product_type', false );
				}

				if(is_array($galleries)){
					$gallery_ids = join(",", $galleries);
					update_post_meta($post_id, '_product_image_gallery', $gallery_ids);
				}

				if($sale_unlimited != "true"){
					update_post_meta($post_id, '_sale_price_dates_from', strtotime($sale_start));
					update_post_meta($post_id, '_sale_price_dates_to', strtotime($sale_end));
					delete_post_meta($post_id, '_sale_unlimited');
				} else {
					delete_post_meta($post_id, '_sale_price_dates_from');
					delete_post_meta($post_id, '_sale_price_dates_to');
					update_post_meta($post_id, '_sale_unlimited', "true");
				}

				$product 		= wc_get_product( $post_id );
				if($product->is_type('variable')){
					update_post_meta($post_id, '_wooahan_regular_price', $regular_price);
					update_post_meta($post_id, '_wooahan_sale_price', $sale_price);
					$regular_price = get_post_meta($post_id, '_wooahan_regular_price', true);
					$sale_price    = get_post_meta($post_id, '_wooahan_sale_price', true);
				}

				$handle = new WC_Product_Variable($post_id);
				$variations = $handle->get_children();
				foreach($variations as $value){
					$single_variation = new WC_Product_Variation($value);
					$price = $single_variation->get_price();
					$variation_id = $single_variation->get_id();
					update_post_meta($variation_id, '_regular_price', $regular_price);
					update_post_meta($variation_id, '_sale_price', $sale_price);
					update_post_meta($variation_id, '_sale_price_dates_from', strtotime($sale_start));
					update_post_meta($variation_id, '_sale_price_dates_to', strtotime($sale_end));				
				}



                # Send JSON response
                # NOTE: We use ==, not ===, because the value may be String("true")
                if (isset($_POST['wooahan_doing_ajax']) && $_POST['wooahan_doing_ajax'] == "true")
                {
                        header('Content-type: application/json');
                        echo json_encode(array('success' => true));

                        # Don't return full wp-admin
                        exit;
                }
			}
		}

		public function admin_footer_function(){
			global $typenow, $pagenow;
			if(in_array($typenow, array('product')) && in_array($pagenow, array('post.php', 'post-new.php'))){
				if($this->can_product_controll() == true){
?>

			<div id="wooahan-toast" aria-live="polite" aria-atomic="true" style="position:fixed; min-height:84px; top:40px; right:20px; z-index:99999; background:transparent; width:266px; display:none">
				  <!-- Position it -->
				  <div style="position: absolute; top: 0; right: 0; width:100%">
				  	<!-- input toast by javascript -->
				  </div>
			</div>

			<!-- Modal -->

			<div class="modal fade" id="addcatModal" tabindex="-1" role="dialog" aria-labelledby="addcatModalLabel" aria-hidden="true">
			  <div class="modal-dialog modal-dialog-centered" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-list-alt"></i> 분류 신규등록</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
						<div class="row">
							<div class="col">
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
								    <span class="input-group-text" id="inputGroup-sizing-default">분류명</span>
								  </div>
								  <input type="text" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col">
								<div class="input-group mb-3">
								  <div class="input-group-prepend">
								    <label class="input-group-text" for="inputGroupSelect01">상위 분류</label>
								  </div>
								  <select class="custom-select" id="inputGroupSelect01">
								    <option selected>Choose...</option>
						<?php
							$terms = get_terms('product_cat', array( 'hide_empty' => false, 'fields' => 'all'));
							foreach($terms as $term){
									//echo '<option>id:'.$term->term_id.'-'.$term->parent.'-'.get_term($term->parent, 'product_cat')->parent.'</option>';
								
								if($term->name != '미분류' && ($term->parent == 0 || get_term($term->parent, 'product_cat')->parent == 0)){
						?>
									<option value="<?php echo $term->term_id;?>">
										<?php
											if($term->parent != 0){
												echo '-';
											} 
											echo $term->name;
										?>
									</option>
						<?php
								}
								
							}
						?>

								  </select>
								</div>
							</div>
						</div>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
			        <button type="button" class="btn btn-primary">등록하기</button>
			      </div>
			    </div>
			  </div>
			</div>
			<div class="modal fade" id="optionCreate" tabindex="-1" role="dialog" aria-labelledby="optionCreateLabel" aria-hidden="true">
			  <div class="modal-dialog modal-dialog-centered" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="exampleModalLabel">옵션 품목추가</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
						<div class="row">
							<div class="col py-3">
								기존 등록된 옵션 품목들은 모두 초기화 됩니다.<br> 
								해당 품목으로 옵션을 생성 하시겠습니까?	
							</div>
						</div>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
			        <button type="button" class="btn btn-primary btn-option-create">옵션생성</button>
			      </div>
			    </div>
			  </div>
			</div>
			<div class="modal fade" id="indivisual-notice" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog modal-lg" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="exampleModalLabel">독립 선택형 주의사항</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
						<div class="row">
							<div class="col text-danger">
								<strong>독립 선택형을 선택하실 경우 기존 등록된 옵션 품목들은 초기화되며 다시 생성됩니다.</strong><br><br>
								또한 필수옵션의 사용이 불가능하며 등록된 품목은 전체 선택옵션으로 등록됩니다.<br>
								이에 동의하십니까? (재조합하기 버튼 클릭시 자동으로 옵션품목이 리셋 됩니다.)
							</div>
						</div>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary button-indi-cancle" data-dismiss="modal">취소</button>
			        <button type="button" class="btn btn-primary button-indi-reset">재조합하기</button>
			      </div>
			    </div>
			  </div>
			</div>
			<div class="modal fade" id="optionGuide" tabindex="-1" role="dialog" aria-labelledby="optionGuideLabel" aria-hidden="true">
			  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="optionGuideLabel">옵션 구성방식 선택 가이드</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
						<div class="row">
							<div class="col py-3">

							</div>
						</div>

			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">확인</button>
			      </div>
			    </div>
			  </div>
			</div>
			<div class="modal fade" id="optionTemplateRegist" tabindex="-1" role="dialog" aria-labelledby="optionTemplateRegistLabel" aria-hidden="true">
			  <div class="modal-dialog modal-dialog-centered" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="optionTemplateRegistLabel">옵션 템플릿 등록</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
						<div class="row">
							<div class="col py-3">
								<div class="form-group">
									<label for="template-optionCode">옵션코드</label>
									<input type="text" class="form-control option-template-code" id="template-optionCode" placeholder="빈칸으로 둘시 임의 생성 됩니다.">
								</div>
								<div class="form-group">
									<label for="template-optionName">템플릿 이름</label>
									<input type="text" class="form-control option-template-name" id="template-optionName" placeholder="등록 할 템플릿 이름을 기입하세요.">
								</div>
								<div class="form-group">
									<label for="template-optionDesc">템플릿 설명</label>
									<input type="text" class="form-control option-template-desc" id="template-optionDesc" placeholder="등록 할 템플릿 설명을 기입하세요.">
								</div>								
							</div>
						</div>

			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
			        <button type="button" class="btn btn-primary button-template-regist">저장하기</button>
			      </div>
			    </div>
			  </div>
			</div>
			<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			  <div class="modal-dialog modal-lg" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title" id="exampleModalLabel">옵션상세 등록</h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
						<div class="row">
							<div class="col py-3">
								<div class="input-group">
								  <div class="input-group-prepend">
								    <span class="input-group-text" id="inputGroup-sizing-lg"><?php _e('옵션명', 'wooahan');?></span>
								  </div>
								  <input type="text" class="form-control" placeholder="<?php _e('콤마(,)단위로 구분됩니다.', 'wooahan');?>" aria-label="productName" aria-describedby="inputGroup-sizing-lg">
								  <div class="input-group-append">
								  	<button class="btn btn-danger" type="button" id="button-addon2">등록</button>
								  </div>
								</div>
							</div>
						</div>

			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
			        <button type="button" class="btn btn-primary">저장하기</button>
			      </div>
			    </div>
			  </div>
			</div>
<?php
				}
			}		
		}

		public function admin_scripts(){
			global $typenow, $pagenow;
			if(in_array($typenow, array('product')) && in_array($pagenow, array('post.php', 'post-new.php'))){
				if($this->can_product_controll() == true){
					wp_enqueue_script('jquery-ui');
					wp_enqueue_style('jquery-ui');
					wp_enqueue_script( 'popper', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js' );
					wp_enqueue_style( 'bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css' );
					wp_enqueue_script('bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js');
					wp_enqueue_style( 'fontawesome', 'https://use.fontawesome.com/releases/v5.6.3/css/all.css' );
					wp_enqueue_script( 'ckeditor', plugins_url('/ckeditor/ckeditor.js', WOOAHAN__FILE__) );
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'wooahan', plugins_url('/assets/admin/wooahan.js', WOOAHAN__FILE__), array('jquery', 'wp-color-picker'), date('His'), true  );
					wp_enqueue_style( 'wooahan', plugins_url('/assets/admin/wooahan.css', WOOAHAN__FILE__), '', date('His'));
					//wp_enqueue_style( 'wooahan-css-toggle', plugins_url('wooahan') . '/assets/css/css.toggle.css' );
					wp_enqueue_script( 'vueJS', 'https://cdn.jsdelivr.net/npm/vue' );
					wp_enqueue_script( 'Sortable', plugins_url('/assets/admin/Sortable.min.js', WOOAHAN__FILE__) );
					wp_enqueue_script( 'vue-sortable', plugins_url('/assets/admin/vue-sortable.js', WOOAHAN__FILE__), array('jquery') );
				}
			}
			
		}

		/**
		* 우아한 바로구매 활성화 상태 리턴
		*/
		public function can_direct_buy_controll(){
			$direct_buy_controll = get_option('wc_settings_tab_wooahan_direct_buy', true);
			if($direct_buy_controll == 'yes'){
				return true;
			} else {
				return false;
			}
		}

		/**
		* 우아한 상품관리 활성화 상태 리턴
		*/
		public function can_product_controll(){
			$product_controll 	= get_option('wc_settings_tab_wooahan_product_controll', true);
			if($product_controll == 'yes'){
				return true;
			} else {
				return false;
			}
		}


		public function wooahan_builder_init(){
			global $typenow, $post;
			if(in_array($typenow, array('product'))){
				$product 			= wc_get_product( $post->ID );
				$regular_price  	= $product->get_regular_price();
				$sale_price 		= $product->get_sale_price();

				if($product->is_type('variable')){
					$regular_price = get_post_meta($post->ID, '_wooahan_regular_price', true);
					$sale_price    = get_post_meta($post->ID, '_wooahan_sale_price', true);
				}
				include_once( WOOAHAN_PATH . 'class/class.badge.php' );
				include_once( WOOAHAN_PATH . 'includes/admin/edit-product.php');
				//include_once( WOOAHAN_PATH . 'includes/admin/js-templates/variation.php');

			}
		}
	}

	new wooahan_initialize();

?>