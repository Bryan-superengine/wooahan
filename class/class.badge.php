<?php

	
	class wooahanBadge {

		public $badges_url;

		function __construct(){
			$this->badges_url = plugins_url('/assets/images/badges/styles', WOOAHAN__FILE__);
		}

		public function is_badge_use($product_id){
			$badge_use = get_post_meta($product_id, '_is_badge_use', true);
			if($badge_use == 'yes'){
				return true;
			} else {
				return false;
			}
		}

		public function allBadges(){
			$badges = $this->defaultBadges();

			$customBadges = $this->getCustomBadges();

			if(is_array($customBadges)){
				foreach($customBadges as $customBadge){
					$badges[] = $customBadge;
				}
			}

			return apply_filters( 'wooahan_all_badges', $badges, $this->badges_url );
		}

		public function addedBadges($product_id){
			$badges = get_post_meta($product_id, '_wooahan_badges', true);
			return $badges;
		}

		public function getCustomBadges(){
			$customBadges = get_option('_wooahan_custom_badges', true);
			return $customBadges;
		}

		public function removeCustomBadge($badge_url){
			$customBadges = $this->getCustomBadges();
			$newBadges = array();
			if(is_array($customBadges)){
				foreach($customBadges as $customBadge){
					if($customBadge['url'] != $badge_url){
						$newBadges[] = $customBadge;
					} else {
						$upload 	= wp_upload_dir();
						$link_dir   = $upload['basedir'] . '/wooahan/custom-badges';
						$url 		= $customBadge['url'];
						$url 		= explode("/", $url);
						$file_name  = $url[count($url)-1];
						$file_path  = $link_dir."/".$file_name;
						unlink($file_path);
					}
				}
				update_option( '_wooahan_custom_badges', $newBadges );
			}
			return true;
		}

		public function getCustomBadge($badge_url){
			$customBadges = $this->getCustomBadges();
			$returnBadge  = array();
			if(is_array($customBadges)){
				foreach($customBadges as $customBadge){
					if($customBadge['url'] == $badge_url){
						$returnBadge = $customBadge;
					}
				}
			}
			return $returnBadge;
		}

		public function isDuplicated($badge_url){
			$customBadges = $this->getCustomBadges();
			if(is_array($customBadges)){
				$duplicated = 0;
				foreach($customBadges as $cutomBadge){
					if($customBadge['url'] == $badge_url){
						$duplicated++;
					}
				}
				if($duplicated > 0){
					return true;
				}
			}
			return false;
		}

		public function addCustomBadge($title, $badge_url, $size){
			if(!$title || !$badge_url || !$size){
				return false;
			}

			if($this->isDuplicated($badge_url) == false){
				$customBadges 	= $this->getCustomBadges();
				if(!is_array($customBadges)){
					$customBadges = array();
				}
				$customBadges[] = array(
					'title' => $title,
					'type'	=> 'custom',
					'url'	=> $badge_url,
					'width' => $size,
					'margin' => array(
						'top'		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left'		=> ''
					)
				);
				update_option('_wooahan_custom_badges', $customBadges);
				return true;
			} else {
				return false;
			}
		}

		public function getBadgesKeys($product_id){
			$badges_keys = get_post_meta($product_id, '_wooahan_badges_keys', true);
			return $badges_keys;
		}

		private function defaultBadges(){
			$badges = array(
				array(
					'title' 	 => '업데이트',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-update.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '일시품절',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-temporarilyout.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '품절',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-soldout.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '인기상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-recommend.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '신상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-newone.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '히트상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-hit.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '자체제작',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-handmade.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '주문폭주',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-floodorder.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '당일배송',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-deliveryontheday.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '베스트 상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-best.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '예약발송',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/icon-afterbooking.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '할인상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type01/discount.gif',
					'width' 	 => 100,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '업데이트',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-update.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '일시품절',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-temporarilyout.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '품절',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-soldout.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '추천상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-recommend.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '히트상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-hit.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '자체제작',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-handmade.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '주문폭주',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-floodorder.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '할인상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-discount.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '당일배송',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-deliveryontheday.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '베스트상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-best.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '예약발송',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/ljh/type02/icon-afterbooking.gif',
					'width' 	 => 86,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '타임특가',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-time.gif',
					'width' 	 => 61,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '품절',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-sold-out.gif',
					'width' 	 => 49,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '할인상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-sale.gif',
					'width' 	 => 60,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '강력추천',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-recommend.gif',
					'width' 	 => 61,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '투쁠원',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-plus-two.gif',
					'width' 	 => 57,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '원쁠원',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-plus-one.gif',
					'width' 	 => 57,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '신상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-new.gif',
					'width' 	 => 57,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '한정상품',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-limit.gif',
					'width' 	 => 61,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '반값할인',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-half.gif',
					'width' 	 => 57,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '이벤트',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-event.gif',
					'width' 	 => 55,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '쿠폰가능',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-coupon.gif',
					'width' 	 => 61,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '재입고',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-come.gif',
					'width' 	 => 55,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
				array(
					'title' 	 => '베스트',
					'type'		 => 'default',
					'url'   	 => $this->badges_url.'/smw/type01/v13-best.gif',
					'width' 	 => 57,
					'margin' => array(
						'top' 		=> '',
						'right' 	=> '',
						'bottom' 	=> '',
						'left' 		=> ''
					)
				),
			);
			return $badges;			
		}
	}


?>