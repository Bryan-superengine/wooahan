<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	$callback = '';

	global $product, $user_ID;

	if(isset($_GET['callback'])){
		$callback = base64_decode($_GET['callback']);
	}
	if(isset($_GET['order_id'])){
		$order_id = $_GET['order_id'];
		$order = wc_get_order($order_id);

		//print_r($order->get_shipping_method());
	}
?>
<div id="wooahan-form-fields">
	<h3><?php _e('배송지정보', 'wooahan'); ?></h3>
<?php
	if(is_user_logged_in()){
?>
	<div class="modal-shipping-location-list">
		<div class="content">
			<h3>배송지 목록</h3>
			<button type="button" class="button button-top-close-modal button-close-modal"><span class="dashicons dashicons-no-alt"></span></button>
			<div class="location-list">
<?php
	$locations = get_user_meta($user_ID, 'wooahan_shipping_locations', true);
?>
				<div class="header">
					<span class="location-title">배송지</span>
					<span class="location-address">주소</span>
					<span class="location-phone">연락처</span>
					<span class="location-select">선택</span>
				</div>
<?php
	if($locations){
?>
				<ul>

<?php
		$count = 0;
		foreach($locations as $location){
?>
					<li>
						<span class="location-title">
							<strong><?php echo $location['shipping_title'];?></strong><br>
							<?php echo $location['receiver'];?>
							<?php if($location['default'] == true) : echo '<span class="basic">기본배송지</span>'; endif; ?>
						</span>
						<span class="location-address"><?php echo $location['postcode'].'<br>'.$location['address1'].' '.$location['address2'];?></span>
						<span class="location-phone"><?php echo $location['phone1'];?></span>
						<span class="location-select"><button type="button" class="button button-insert-location" data-location="<?php echo $location['shipping_title'];?>" data-receiver="<?php echo $location['receiver'];?>" data-postcode="<?php echo $location['postcode'];?>" data-address1="<?php echo $location['address1'];?>" data-address2="<?php echo $location['address2'];?>" data-phone1="<?php echo $location['phone1'];?>" data-phone2="<?php echo $location['phone2'];?>" data-memo="<?php echo $location['memo'];?>">선택</button></span>
					</li>
<?php
			$count++;
		}
?>
				</ul>
<?php
	} else {
?>
				<ul>
					<li><span class="location-empty"><?php _e('등록된 배송지가 없습니다.', 'wooahan');?></span></li>
				</ul>
<?php
	}
?>
			</div>

			<div class="actions">
				<button type="button" class="button button-close-modal">닫기</button>
			</div>
		</div>
		<div class="background"></div>
	</div>
<?php
	}
?>
	<div class="wrapper">
<?php
	if(is_user_logged_in()){
?>
		<div class="row location-row">
			<label class="title">배송지 선택</label>
			<div class="inner-row">
				<div class="checker-wrap">
					<input type="radio" name="location">
					<label class="checker">
						<span class="title">기본배송지</span>
					</label>
				</div>
				<div class="checker-wrap">
					<input type="radio" name="location">
					<label class="checker">
						<span class="title">신규배송지</span>
					</label>
				</div>
				<button type="button" class="button button-shipping-locations">배송지 목록</button>
			</div>
		</div>
<?php
	}
?>
		<div class="row">
			<label class="title">수령인</label>
			<input type="text" class="medium wooahan-input-receiver" placeholder="50자 이내로 입력하세요.">
		</div>
<?php
	if(is_user_logged_in()){
?>
		<div class="row">
			<label class="title">배송지명</label>
			<input type="text" class="medium wooahan-input-location" placeholder="등록하실 배송지명을 입력하세요.">
		</div>
<?php
	}
?>
		<div class="row phone-row">
			<label class="title">연락처1</label>
			<select class="sumoselect phone wooahan-input-phone1-num1">
				<option value="010">010</option>
				<option value="011">011</option>
				<option value="016">016</option>
				<option value="017">017</option>
				<option value="018">018</option>
				<option value="019">019</option>
				<option value="02">02</option>
				<option value="031">031</option>
				<option value="032">032</option>
				<option value="033">033</option>
				<option value="041">041</option>
				<option value="042">042</option>
				<option value="043">043</option>
				<option value="044">044</option>
				<option value="051">051</option>
				<option value="052">052</option>
				<option value="053">053</option>
				<option value="054">054</option>
				<option value="055">055</option>
				<option value="061">061</option>
				<option value="062">062</option>
				<option value="063">063</option>
				<option value="064">064</option>
				<option value="070">070</option>
				<option value="080">080</option>
				<option value="0130">0130</option>
				<option value="0303">0303</option>
				<option value="0502">0502</option>
				<option value="0503">0503</option>
				<option value="0504">0504</option>
				<option value="0506">0506</option>
				<option value="0507">0507</option>
				<option value="050">050</option>
				<option value="012">012</option>
				<option value="059">059</option>
			</select>
			<span class="sep"> - </span>
			<input type="text" class="phone wooahan-input-phone1-num2">
			<span class="sep"> - </span>
			<input type="text" class="phone wooahan-input-phone1-num3">
		</div>
		<div class="row phone-row">
			<label class="title">연락처2</label>
			<select class="sumoselect phone wooahan-input-phone2-num1">
				<option value="0">선택</option>
				<option value="010">010</option>
				<option value="011">011</option>
				<option value="016">016</option>
				<option value="017">017</option>
				<option value="018">018</option>
				<option value="019">019</option>
				<option value="02">02</option>
				<option value="031">031</option>
				<option value="032">032</option>
				<option value="033">033</option>
				<option value="041">041</option>
				<option value="042">042</option>
				<option value="043">043</option>
				<option value="044">044</option>
				<option value="051">051</option>
				<option value="052">052</option>
				<option value="053">053</option>
				<option value="054">054</option>
				<option value="055">055</option>
				<option value="061">061</option>
				<option value="062">062</option>
				<option value="063">063</option>
				<option value="064">064</option>
				<option value="070">070</option>
				<option value="080">080</option>
				<option value="0130">0130</option>
				<option value="0303">0303</option>
				<option value="0502">0502</option>
				<option value="0503">0503</option>
				<option value="0504">0504</option>
				<option value="0506">0506</option>
				<option value="0507">0507</option>
				<option value="050">050</option>
				<option value="012">012</option>
				<option value="059">059</option>
			</select>
			<span class="sep"> - </span>
			<input type="text" class="phone wooahan-input-phone2-num2">
			<span class="sep"> - </span>
			<input type="text" class="phone wooahan-input-phone2-num3">
		</div>
		<div class="row">
			<label class="title">배송지 주소</label>
			<input type="text" class="small post-code">
			<button type="button" class="button postcode" onclick="daum_postcode()">우편번호</button>
<?php
	if(is_user_logged_in()){
?>
			<div class="checker-wrap">
				<input type="checkbox" class="wooahan-checkbox-location-add">
				<label class="checker">
					<span class="title">배송지목록에 추가</span>
				</label>
			</div>
			<div class="checker-wrap">
				<input type="checkbox" class="wooahan-checkbox-default-location">
				<label class="checker">
					<span class="title">기본배송지로 선택</span>
				</label>
			</div>
<?php
	}
?>			
			<div class="inner-row">
				<input type="text" class="half address-1"><input type="text" class="half address-2">
			</div>
		</div>
		<div class="row memo-row">
			<label class="title">배송메모</label>
			<div class="inner-row">
				<select class="sumoselect wooahan-select-memo">
					<option>배송 전에 미리 연락 바랍니다.</option>
					<option>부재시 경비실에 맡겨 주세요.</option>
					<option>부재시 전화 주시거나 문자 남겨 주세요.</option>
				</select>
				<input type="text" class="full wooahan-input-memo" placeholder="요청사항을 직접 입력합니다.">
			</div>
		</div>
		<div class="row">
			<p class="notice">도서산간 지역의 경우 추후 수령 시 추가 배송비가 과금될 수 있습니다.</p>
		</div>
	</div>
</div>
<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>
<script>
	function daum_postcode(){
	    new daum.Postcode({
	        oncomplete: function(data) {
	            // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분입니다.
	            // 예제를 참고하여 다양한 활용법을 확인해 보세요.
	            jQuery("div#wooahan-form-fields").find("input.post-code").val(data.zonecode);
	            jQuery("div#wooahan-form-fields").find("input.address-1").val(data.roadAddress);
	            jQuery("div#wooahan-form-fields").find("input.address-2").focus();
	        }
	    }).open();
	}
</script>