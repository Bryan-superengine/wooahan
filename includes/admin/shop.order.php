<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
			$limit[0] = 1;
			$limit[1] = 20;	
	$orderList = new wooahanOrderList();
	$trackingCompanies = $orderList->get_tracking_companies();
	$orderCount 	   = $orderList->get_order_count();
	$defaultCorp 	   = $orderList->get_default_corp();
?>
<div id="wooahanShopOrder" order-count='<?php echo json_encode($orderCount);?>' v-cloak>
	<!-- Modal -->
	<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
	    <div class="modal-content">
	    	<div class="modal-loading-progress">
				<div class="spinner-border text-success" role="status">
				  <span class="sr-only">Loading...</span>
				</div>	    		
	    		<div class="background"></div>
	    	</div>
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel"><?php _e('주문 상세', 'wooahan');?></h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
			<h6>상품 주문내역</h6>
			<table class="order-detail-table">
				<thead>
					<tr>
						<th class="detail-title">상품명</th>
						<th class="detail-checker"><input type="checkbox" class="checker" checked v-on:click="itemChecker"></th>
						<th class="detail-item">구매옵션</th>
						<th class="detail-method">결제수단</th>
						<th class="detail-qty">구매수량</th>
						<th class="detail-price">금액</th>
					</tr>
				</thead>
				<tbody v-for="(item, key) in details.order_items">
					<tr v-for="(eitem, index) in item.items">
						<td v-show="index == 0" v-bind:rowspan="item.items.length"><img v-bind:src="item.product_thumbnail" class="product-thumbnail"> {{ item.product_title }}</td>
						<td class="detail-checker"><input v-show="eitem.is_shipping == false || (eitem.is_shipping == true && orderStatus == 'wc-exchange-request')" type="checkbox" class="each-check" v-bind:value="eitem.item_id" checked></td>
						<td class="detail-item">{{ eitem.item_title }}</td>
						<td class="detail-method" v-show="index == 0" v-bind:rowspan="item.items.length">{{ details.payment_method }}</td>
						<td class="detail-qty">{{ eitem.item_qty }}</td>
						<td class="detail-price">{{ eitem.item_formatted_price }}</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6">결제된 상품금액 : {{ details.item_total }} 원 <span v-show="details.shipping_cost > 0"> + 배송비 : {{ details.shipping_cost }}원</span> = 총 결제된 금액 : {{ details.total }} 원</td>
					</tr>
				</tfoot>
			</table>
			<h6>배송지 정보</h6>
			<div class="shipping-details">
				<table class="shipping-detail-table">
					<tr>
						<th>수취인</th>
						<td>{{ details.customer.name }}</td>
					</tr>
					<tr>
						<th>연락처</th>
						<td>{{ details.customer.phone }} <button class="button button-phone-send"><i class="fas fa-mobile-alt"></i> 문자전송</button></td>
					</tr>
					<tr>
						<th>배송 주소</th>
						<td>{{ details.customer.address }}</td>
					</tr>
				</table>
			</div>
			<h6 v-show="orderStatus == 'wc-shipping-standby' || orderStatus == 'wc-shipping-partial' || orderStatus == 'wc-exchange-request'">운송장 정보</h6>
			<div v-show="orderStatus == 'wc-shipping-standby' || orderStatus == 'wc-shipping-partial' || orderStatus == 'wc-exchange-request'" class="shipping-number">
				<table class="shipping-number-table">
					<thead>
						<tr>
							<th>등록된 운송장 번호</th>
							<th>
								<span v-show="orderStatus == 'wc-shipping-standby' || orderStatus == 'wc-shipping-partial'">신규 운송장 등록</span>
								<span v-show="orderStatus == 'wc-exchange-request'">교환 운송장 등록</span>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<div class="shipping-added" v-show="details.shipping_number != ''">
									<ul class="shipping-number-ul">
										<li v-for="item in details.shipping_number">
											<label>{{ item.corp }}</label>
											<span class="number">{{ item.number }} <button class="button"><i class="fas fa-truck"></i> 배송조회</button></span>											
										</li>
									</ul>
								</div>
								<div class="shipping-none" v-show="details.shipping_number == ''">
									<i class="fas fa-exclamation-circle"></i> <?php _e('등록된 운송장 번호가 없습니다.', 'wooahan'); ?>
								</div>							
							</td>
							<td>
								<p v-show="orderStatus == 'wc-exchange-request' && details.return_received != 'yes'" class="description"><?php _e('고객으로부터 상품을 수령하신 후 교환상품 배송처리가 가능합니다. 교환상품을 수령하셨다면 교환수령 버튼을 먼저 클릭 하여 주시기 바랍니다.', 'wooahan');?></p>
								<div v-show="(orderStatus == 'wc-shipping-standby' || orderStatus == 'wc-shipping-partial') || (orderStatus == 'wc-exchange-request' &&  details.return_received == 'yes')" class="shipping-number-add-box">
									<select class="select-corp">
									<?php

										foreach($trackingCompanies as $company){
									?>
										<option value="<?php echo $company['code'];?>" <?php if($defaultCorp == $company['code']) : echo 'selected'; endif; ?>><?php echo $company['name'];?></option>
									<?php
										}
									?>
									</select>
									<div class="shipping-number-box">
										<input type="text" class="input-shipping-number" placeholder="숫자만 기입하시기 바랍니다.">
										<button class="button button-primary" v-on:click="shippingNumberRegist(details.id)">송장등록 및 배송처리</button>
									</div>
								</div>								
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<h6>주문 메모</h6>
			<div class="input-memo-box">
				<textarea class="memo-textarea" placeholder="<?php _e("남기실 메모를 기입하신 후 하단에서 개인용 이나 고객에게 보낼 메모 인지를 선택하여 주세요.\n고객에게 보낼 메모를 선택시 메모등록시 이메일이 고객에게 발송되오니 유념하시기 바랍니다.", 'wooahan');?>"></textarea>
				<div class="submit-controll">
					<select class="memo-type">
						<option value="private"><?php _e('개인용 메모', 'wooahan');?></option>
						<option value="customer"><?php _e('고객에게 보낼메모', 'wooahan');?></option>
					</select>
					<button class="button" v-on:click="submitNote(details.id)"><?php _e('메모입력', 'wooahan');?></button>
				</div>
			</div>
			<ul class="memo-ul">
				<li v-for="(memo, key) in details.memo" class="memo" v-bind:class="{ 'customer' : memo.is_customer_note == true }" ><label>{{ memo.label }}</label>{{ memo.note_content }} <span class="date">{{ memo.note_date }}</span> <span class="remove" v-on:click="removeNote(details.id, memo.note_id)"><i class="fas fa-times"></i></span></li>
			</ul>
	      </div>
	      <div class="modal-footer">
	      	<button type="button" class="button"><i class="fas fa-print"></i> <?php _e('인쇄하기', 'wooahan');?></button>
	        <button type="button" class="button button-primary" data-dismiss="modal"><i class="fas fa-times"></i> <?php _e('닫기', 'wooahan');?></button>
	      </div>
	    </div>
	  </div>
	</div>
	<div class="modal fade" id="trackingModal" tabindex="-1" role="dialog" aria-labelledby="trackingModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
	    <div class="modal-content">
	    	<div class="modal-loading-progress">
				<div class="spinner-border text-success" role="status">
				  <span class="sr-only">Loading...</span>
				</div>	    		
	    		<div class="background"></div>
	    	</div>
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-truck"></i> <?php _e('배송조회', 'wooahan');?></h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	      		<table class="tracking-table">
					<thead>
						<tr>
							<th><?php _e('처리일시', 'wooahan');?></th>
							<th><?php _e('현재위치', 'wooahan');?></th>
							<th><?php _e('상태', 'wooahan');?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, index) in trackingList">
							<td>{{ item.timeString }}</td>
							<td>
								{{ item.where }}
								<span v-show="item.manName != '' && item.telno != ''" class="manager">담당자 : {{ item.manName }} ( {{ item.telno }} )</span>
								<span v-show="item.manName == '' && item.telno != ''" class="manager">연락처 : {{ item.telno }}</span>
							</td>
							<td><span class="status" v-bind:class="{ 'arrived' : index == 0 && (item.kind == '인수자등록' || item.kind == '배달완료' || item.kind == '배송완료') }">{{ item.kind }}</span></td>
						</tr>
						<tr v-show="trackingList.length == 0">
							<td colspan="3"><i class="fas fa-exclamation-circle"></i> 송장 데이터를 처리중에 있거나 운송장 정보가 잘못되었습니다.</td>
						</tr>
					</tbody>
				</table>
				<p class="tracking-alert"><i class="fas fa-exclamation-circle"></i> 본 정보는 스윗트래커에서 제공받는 정보로, 실제 배송상황과 다를 수 있습니다.</p>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="button button-primary" data-dismiss="modal"><?php _e('확인', 'wooahan');?></button>
	      </div>
	    </div>
	  </div>
	</div>
	<div class="modal fade" id="csvModal" tabindex="-1" role="dialog" aria-labelledby="csvModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
	    <div class="modal-content">
	    	<div class="modal-loading-progress">
	    		<label class="shipping-progress-title"><?php _e('송장정보 처리중...', 'wooahan');?></label>
				<div class="spinner-border text-success" role="status">
				  <span class="sr-only">Loading...</span>
				</div>	    		
	    		<div class="background"></div>
	    	</div>
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-truck"></i> <?php _e('일괄 운송장 등록', 'wooahan');?></h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
	        <p>하단의 양식에 맞춰 CSV 파일을 구성하신뒤 업로드 하시기 바랍니다.<br>CSV 이외의 다른 파일들은 업로드 되지 않습니다.<br>등록이 완료되면 자동으로 배송중(부분배송)으로 주문상태가 변경되오며, 해당 고객에게 알림이 가게 되오니 이점 유념하시기 바랍니다.</p>
	        <p class="text-danger"><strong>(주의) 상단 해더영역을 반드시 삭제하시고 보내실 송장 리스트가 첫번째 열(row)에 위치해야 합니다.</strong></p> 
	        <ul class="example-csv">
	        	<li class="text-danger">
	        		{ 주문번호 }
	        		<p class="description">
	        			필수요소로 반드시 첫번째 컬럼에 위치해야 합니다.
	        		</p>
	        	</li>
	        	<li class="text-danger">
	        		{ 택배사코드 }
	        		<p class="description">
	        			택배사 코드가 반드시 두번째 컬럼에 위치해야 합니다.
	        		</p>
	        	</li>
	        	<li class="text-danger">
	        		{ 운송장번호 }
	        		<p class="description">
	        			해당 물건에 대한 보내실 운송장 번호로 반드시 두번째 컬럼에 위치해야 합니다.
	        		</p>
	        	</li>
	        	<li>
	        		{ 미배송 옵션 아이디 }
	        		<p class="description">
	        			전체 상품중 미배송 상품이 있다면 미배송 옵션 아이디를 콤마(,)단위로 공백없이 기입하셔야 합니다.
	        		</p>
	        	</li>
	        </ul>
	        <br>
	        <h6>기입 예제</h6>
	        <p class="description">주문번호가 1280 인 주문을 택배사코드 06 : 로젠택배, 운송장번호 12345678910 으로 발송 처리하되, 구매한 옵션 127번과 128번은 미배송 하여 부분배송으로 처리 됩니다.</p>
	        <ul class="example-csv">
	        	<li class="text-danger">1280</li>
	        	<li class="text-danger">06</li>
	        	<li class="text-danger">12345678910</li>
	        	<li>127,128</li>
	        </ul>
	        <form id="csvUpload" method="post" enctype="multipart/form-data">
	        	<input type="file" class="input-file-csv" placeholder="운송장 csv 파일을 업로드">
	    	</form>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="button" data-dismiss="modal"><?php _e('취소', 'wooahan');?></button>
	        <button type="button" class="button button-primary" v-on:click="csvUpload"><?php _e('송장 등록 및 배송처리', 'wooahan');?></button>
	      </div>
	    </div>
	  </div>
	</div>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	  <a class="navbar-brand" href="#" v-on:click="list('wc-processing', '<?php _e('상품준비중', 'wooahan');?>')"><img src="<?php echo plugins_url('/assets/images/w-logo.svg', WOOAHAN__FILE__);?>"> 주문정보</a>
	  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
	    <span class="navbar-toggler-icon"></span>
	  </button>
	  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
	    <div class="navbar-nav">
	      <a class="nav-item nav-link active" href="#" v-on:click="list('wc-processing', '<?php _e('상품준비중', 'wooahan');?>')" data-title="<?php _e('상품준비중', 'wooahan');?>">상품준비중<span class="sr-only">(current)</span><span class="count">{{ count.processing }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-shipping-standby', '<?php _e('배송준비중', 'wooahan');?>')" data-title="<?php _e('배송준비중', 'wooahan');?>">배송준비중<span class="count">{{ count.standby }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-shipping-partial', '<?php _e('부분배송 조회', 'wooahan');?>')" data-title="<?php _e('부분배송 조회', 'wooahan');?>">부분배송<span class="count">{{ count.partial }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-shipping-gone', '<?php _e('배송중 조회', 'wooahan');?>')" data-title="<?php _e('배송중 조회', 'wooahan');?>">배송중<span class="count">{{ count.gone }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-completed', '<?php _e('배송완료 조회', 'wooahan');?>')" data-title="<?php _e('배송완료 조회', 'wooahan');?>">배송완료<span class="count">{{ count.completed }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-shipping-pending', '<?php _e('배송보류 조회', 'wooahan');?>')" data-title="<?php _e('배송완료 조회', 'wooahan');?>" data-title="<?php _e('배송보류 조회', 'wooahan');?>">배송보류<span class="count">{{ count.pending }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-refund-request', '<?php _e('반품요청', 'wooahan');?>')" data-title="<?php _e('반품요청', 'wooahan');?>">반품요청<span class="count">{{ count.refrequest }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-exchange-request', '<?php _e('교환요청', 'wooahan');?>')" data-title="<?php _e('교환요청', 'wooahan');?>">교환요청<span class="count">{{ count.excrequest }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-refunded', '<?php _e('환불건 관리', 'wooahan');?>')" data-title="<?php _e('환불건 관리', 'wooahan');?>">환불건<span class="count">{{ count.refunded }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('wc-pending', '<?php _e('결제 대기중', 'wooahan');?>')" data-title="<?php _e('결제 대기중', 'wooahan');?>">결제 대기중<span class="count">{{ count.awaiting }}</span></a>
	      <a class="nav-item nav-link" href="#" v-on:click="list('', '<?php _e('전체주문 조회', 'wooahan');?>')" data-title="<?php _e('전체주문 조회', 'wooahan');?>">전체주문</a>
	    </div>
	  </div>
	</nav>
	<div class="order-search-box">
		<table class="search-table">
			<tr class="tr-search">
				<th>검색어</th>
				<td>
					<select class="select-search-type">
						<option value="id">주문번호</option>
						<option value="customer">주문자명</option>
					</select>
					<input type="text" class="input-search-keyword">
				</td>
			</tr>
			<tr class="tr-date">
				<th>기간</th>
				<td>
					<select class="select-search-date">
						<option value="order">주문일</option>
						<option value="shipping">배송일</option>
					</select>
					<div class="date-search">
						<ul>
							<li v-on:click="setDate('today')">오늘</li>
							<li v-on:click="setDate('yesterday')">어제</li>
							<li v-on:click="setDate('3days')">3일</li>
							<li v-on:click="setDate('7days')">7일</li>
							<li v-on:click="setDate('15days')">15일</li>
							<li v-on:click="setDate('1month')">1개월</li>
							<li v-on:click="setDate('3month')">3개월</li>
							<li v-on:click="setDate('6month')">6개월</li>
						</ul>
						<input type="hidden" class="date-search-button">
					</div>
					<input type="text" class="wooahan-date-picker order-date-start"> <i class="far fa-calendar-alt"></i> ~ <input type="text" class="wooahan-date-picker order-date-end"> <i class="far fa-calendar-alt"></i>
				</td>
			</tr>
		</table>
		<button type="button" class="button button-lg button-primary" v-on:click="search">검색</button>
		<button type="button" class="button button-lg" v-on:click="reset">초기화</button>
	</div>
	<h1>{{ title }}</h1>
	<div class="order-controller">
		<button type="button" v-show="orderStatus === 'wc-processing'" class="button button-primary" v-on:click="shippingStandby"><i class="fas fa-truck"></i> 배송준비중 처리</button>
		<button type="button" v-show="orderStatus === 'wc-processing'" class="button" v-on:click="shippingPending"><i class="fas fa-ban"></i> 배송보류 처리</button>
		<button type="button" v-show="orderStatus === 'wc-processing' || orderStatus === 'wc-shipping-standby' || orderStatus === 'wc-shipping-pending'" class="button" v-on:click="statusChange('cancelled')"><i class="fas fa-undo-alt"></i> 환불 처리</button>
		<button type="button" v-show="orderStatus === 'wc-shipping-standby'" class="button" v-on:click="exportCSV"><i class="fas fa-download"></i> 엑셀(CSV) 다운로드</button>
		<button type="button" v-show="orderStatus === 'wc-shipping-standby'" class="button" data-toggle="modal" data-target="#csvModal"><i class="fas fa-truck"></i> 일괄 송장등록</button>
		<button type="button" v-show="orderStatus === 'wc-pending'" class="button" v-on:click="paid"><i class="fas fa-download"></i> 입금확인</button>
		<button type="button" v-show="orderStatus === 'wc-shipping-pending'" class="button" v-on:click="statusChange('processing')"><i class="fas fa-play"></i> 상품준비중 처리</button>
		<button type="button" v-show="orderStatus === ''" class="button" v-on:click="removeOrders"><i class="far fa-trash-alt"></i> 주문삭제</button>
		<button type="button" v-show="orderStatus === 'wc-shipping-gone'" class="button" v-on:click="statusChange('completed')"><i class="far fa-check-circle"></i> 배송완료 처리</button>
		<button type="button" v-show="orderStatus === 'wc-shipping-partial'" class="button" v-on:click="statusChange('shipping-gone');"><i class="far fa-check-circle"></i> 배송중 처리</button>
		<button type="button" class="button" v-on:click="refresh"><i class="fas fa-sync-alt"></i> 새로고침</button>
		<span class="list-count" v-show="listCount != ''">총 {{ listCount }} 건</span>
	</div>
	<div class="list-wrapper">
		<div class="loading-progress">
			<div class="spinner-border text-success" role="status">
			  <span class="sr-only">Loading...</span>
			</div>
			<div class="loading-bg"></div>
		</div>
		<ul class="header">
			<li class="data-order-checker"><input type="checkbox" class="check-all" v-on:click="checkToggle"></li>
			<li class="data-order-date"><?php _e('주문일시', 'wooahan');?></li>
			<li class="data-order-number"><?php _e('주문번호', 'wooahan');?></li>
			<li class="data-order-id"><?php _e('주문자', 'wooahan');?></li>
			<li class="data-order"><?php _e('상품명/옵션', 'wooahan');?></li>
			<li class="data-order-price"><?php _e('총 상품구매금액', 'wooahan');?></li>
			<li class="data-order-paid"><?php _e('총 실결제금액', 'wooahan');?></li>
			<li class="data-order-method"><?php _e('결제수단', 'wooahan');?></li>
			<li class="data-order-status"><?php _e('주문상태', 'wooahan');?></li>
			<li v-show="orderStatus != 'wc-shipping-partial'" class="data-order-quantity"><?php _e('상품건수', 'wooahan');?></li>
			<li v-show="orderStatus != 'wc-pending' && orderStatus != 'wc-processing' && orderStatus != 'wc-exchange-request' && orderStatus != 'wc-refund-request' && orderStatus != 'wc-refunded' && orderStatus != 'wc-shipping-gone' && orderStatus != 'wc-completed' && orderStatus != ''" class="data-order-notd"><?php _e('미배송', 'wooahan');?></li>
			<li v-show="orderStatus == 'wc-refund-request' || orderStatus == 'wc-exchange-request'" class="data-order-received">
				<span v-show="orderStatus == 'wc-refund-request'"><?php _e('반품수령', 'wooahan');?></span>
				<span v-show="orderStatus == 'wc-exchange-request'"><?php _e('교환수령', 'wooahan');?></span>
			</li>
			<li v-show="orderStatus != 'wc-pending' && orderStatus != 'wc-refunded' && orderStatus != 'wc-processing' && orderStatus != 'wc-exchange-request' && orderStatus != 'wc-refund-request' && orderStatus != 'wc-shipping-gone' && orderStatus != 'wc-completed' && orderStatus != ''" class="data-order-deliveried"><?php _e('배송완료', 'wooahan');?></li>
			<li v-show="orderStatus == 'wc-shipping-partial' || orderStatus == 'wc-shipping-gone' || orderStatus == 'wc-completed'" class="data-order-shipping"><?php _e('운송장 번호', 'wooahan');?></li>
		</ul>
		<ul class="body">
			<li class="item" v-bind:class="'item-li-'+key" v-for="(item, key) in items">
				<ul class="item-ul">
					<li class="data-order-checker"><input type="checkbox" class="check-each" v-bind:class="'check-each-'+key" v-bind:data-key="key" v-on:click="check(key)" v-bind:value="item.ID"></li>
					<li class="data-order-date">{{ item.date_created }}</li>
					<li class="data-order-number"><span data-toggle="modal" data-target="#detailModal" v-bind:data-id="item.ID" v-on:click="idChange(item.ID)">#{{ item.ID }}</span></li>
					<li class="data-order-id">{{ item.customer_name }} <span v-show="item.user_id == 0" class="guest"><?php _e('[비회원]', 'wooahan');?></span></li>
					<li class="data-order">
						<span class="item" v-for="oitem in item.order_items">
							<img v-bind:src="oitem.product_thumbnail" class="product-thumbnail">
							<label class="product-title"><a v-bind:href="oitem.product_url" target="_blank">{{ oitem.product_title }}</a></label>
							<span v-show="oitem.is_type === 'variable'" class="option-badge"><?php _e('옵션', 'wooahan');?></span>
							<ul v-show="oitem.is_type === 'variable'" class="items">
								<li v-for="eitem in oitem.items">{{ eitem.item_title }} X {{ eitem.item_qty }} 개</li>
							</ul>
						</span>
					</li>
					<li class="data-order-price">{{ item.item_total }}</li>
					<li class="data-order-paid">{{ item.total }}</li>
					<li class="data-order-method"><span v-bind:class="'badge badge-secondary badge-'+item.origin_method">{{ item.payment_method }}</span></li>
					<li class="data-order-status">{{ item.order_status }}</li>
					<li v-show="orderStatus != 'wc-shipping-partial'" class="data-order-quantity">{{ item.quantity }}</li>
					<li v-show="orderStatus != 'wc-pending' && orderStatus != 'wc-processing' && orderStatus != 'wc-exchange-request' && orderStatus != 'wc-refund-request' && orderStatus != 'wc-refunded' && orderStatus != 'wc-shipping-gone' && orderStatus != 'wc-completed' && orderStatus != ''" class="data-order-notd" v-bind:class="{ 'text-danger' : item.not_shipping > 0}">{{ item.not_shipping }}</li>
					<li v-show="orderStatus == 'wc-refund-request' || orderStatus == 'wc-exchange-request'" class="data-order-received">
						<button v-show="orderStatus == 'wc-refund-request'" class="button button-primary" v-on:click="refund_received(item.ID)"><?php _e('반품수령', 'wooahan');?></button>
						<button v-show="orderStatus == 'wc-exchange-request' && item.return_received != 'yes'" class="button button-primary" v-on:click="exchange_received(item.ID)"><?php _e('교환수령', 'wooahan');?></button>
						<span v-show="item.return_received === 'yes'">수령완료</span>
					</li>
					<li v-show="orderStatus != 'wc-pending' && orderStatus != 'wc-processing' && orderStatus != 'wc-exchange-request' && orderStatus != 'wc-refund-request' && orderStatus != 'wc-refunded' && orderStatus != 'wc-shipping-gone' && orderStatus != 'wc-completed' && orderStatus != ''" class="data-order-deliveried">{{ item.shipping_gone }}</li>
					<li v-show="orderStatus == 'wc-shipping-partial' || orderStatus == 'wc-shipping-gone' || orderStatus == 'wc-completed'" class="data-order-shipping">
						<ul class="shipping-numbers">
							<li v-for="snum in item.shipping_number">
								<label>{{ snum.corp }}</label>
								<span class="number">{{ snum.number }} <span class="search-shipping" data-toggle="modal" data-target="#trackingModal" v-bind:data-id="item.ID" v-bind:data-corp="snum.code" v-bind:data-number="snum.number"><i class="fas fa-truck"></i></span>
							</li>
						</ul>
					</li>
				</ul>
			</li>
			<li v-show="totalCount == 0" class="item item-none"><i class="fas fa-exclamation-circle"></i> <?php _e('해당 주문이 없습니다.', 'wooahan');?></li>
		</ul>
		<nav v-show="totalCount > posts_per_page" aria-label="Page navigation">
		  <ul class="pagination justify-content-center">
		    <li class="page-item" v-bind:class="{ 
		      'disabled' : page == 1}">
		      <span class="page-link" v-on:click="goPage('minus')" tabindex="-1" v-bind:aria-disabled="true"><span aria-hidden="true"><i class="fas fa-angle-left"></i></span></span>
		    </li>
		    <li class="page-item" v-bind:class="{'active' : page == n}" v-for="n in navCount"><span class="page-link" v-on:click="goPage(n)">{{ n }}</span></li>
		    <li class="page-item" v-bind:class="{ 
		      'disabled' : page == navCount}">
		      <span class="page-link" v-on:click="goPage('plus')"><span aria-hidden="true"><i class="fas fa-angle-right"></i></span></span>
		    </li>
		  </ul>
		</nav>
	</div>
	<?php
		include_once(WOOAHAN_PATH . 'includes/admin/footer.php');
	?>
</div>