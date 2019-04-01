<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div id="set-option" class="section-wrap container-fluid">
	<?php
		include (WOOAHAN_PATH . 'includes/admin/edit-permalink.php');
	?>
	<h2>옵션/재고</h2>
	<div class="modal fade" id="optionTemplate" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-list"></i> 옵션 템플릿 리스트</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
				<div class="row">
					<div class="col py-3">
						<div class="input-group">
						  <div class="input-group-prepend">
						    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">옵션명 검색</button>
						    <div class="dropdown-menu">
						      <a class="dropdown-item" href="#">옵션명 검색</a>
						      <a class="dropdown-item" href="#">옵션설명 검색</a>
						      <a class="dropdown-item" href="#">옵션코드 검색</a>
						      <div role="separator" class="dropdown-divider"></div>
						      <a class="dropdown-item" href="#">닫기</a>
						    </div>
						  </div>
						  <input type="text" class="form-control" placeholder="<?php _e('검색하실 옵션명을 기입하세요.', 'wooahan');?>" aria-label="productName" aria-describedby="inputGroup-sizing-lg">
						  <div class="input-group-append">
						  	<button class="btn btn-info" type="button" id="button-addon2">검색</button>
						  </div>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<h6>옵션 목록</h6>
						<table id="template-table" class="option-table">
							<thead>
								<tr>
									<th><input type="checkbox" class="check-all-option" v-on:click="checkAll"></th>
									<th>옵션코드</th>
									<th>템플릿 이름</th>
									<th>옵션값</th>
									<th></th>
								</tr>
							</thead>
							<tbody id="template-tbody">
							<template v-for="(item, key, index) in templates">
								<tr>
									<td><input type="checkbox" class="each-option-checkbox" v-bind:data-key="key"></td>
									<td>{{ item.code }}</td>
									<td>{{ item.name }}</td>
									<td v-bind:style="{ padding : '10px' }">
										<table class="option-table template-table">
											<thead>
												<tr>
													<th>옵션명</th>
													<th>옵션값</th>
													<th>필수선택</th>
													<th>스타일</th>
												</tr>
											</thead>
											<tbody>
												<tr v-for="each in item.options">
													<td>{{ each.name }}</td>
													<td>
														<span class="each-item" v-for="each_item in each.options">
															{{ each_item }}
														</span>
													</td>
													<td>
					                					<span class="badge badge-secondary" v-if="each.required === 'false'"><?php _e('선택', 'wooahan');?></span>
					                					<span class="badge badge-danger" v-else-if="each.required === 'true'"><?php _e('필수', 'wooahan');?></span>																
													</td>
													<td>
														<span v-if="each.style === 'selectbox' || each.style === '셀렉트박스'"><?php _e('셀렉트박스', 'wooahan');?></span>
														<span v-else-if="each.style === 'color' || each.style === '색상선택'"><?php _e('색상선택', 'wooahan');?></span>
														<span v-else-if="each.style === 'thumbnail' || each.style === '썸네일이미지'"><?php _e('썸네일이미지', 'wooahan');?></span>
														<span v-else-if="each.style === 'text' || each.style === '텍스트'"><?php _e('텍스트', 'wooahan');?></span>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
									<td><button type="button" class="button" v-on:click="select(key)"><?php _e('선택', 'wooahan');?></td>
								</tr>
							</template>
							</tbody>
						</table>
					</div>
				</div>
				<div class="row">
					<div class="col">

					</div>
					<div class="col">

					</div>
				</div>
	      </div>
	      <div class="modal-footer">

	      		<div class="col text-left">
			      	<button type="button" class="btn btn-secondary button-checked-remove">선택 삭제</button>				      			
	      		</div>
	      		<div class="col text-right">
			        <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>			      			
	      		</div>

	      </div>
	    </div>
	  </div>
	</div>
	<div class="modal fade" id="optionDetails" tabindex="-1" role="dialog" aria-labelledby="optionDetailsLabel" aria-hidden="true">
	  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel">옵션값 세부 수정</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
				<div class="row">
					<div class="col py-3">
						<small class="text-muted">옵션값 세부 수정 후 반드시 "품목 추가" 를 해 주셔야 정상적으로 반영 됩니다.<br>조합 분리선택형에서만 가능한 옵션형태는 이외의 구성방식에서 일반적인 셀렉트박스 형태로 노출됩니다.</small>
						<div class="form-group">
							<label>옵션형태 선택 {{ detail.style }}</label>
							<ul class="option-style-ul">
								<li option-style="selectbox" option-title="<?php _e('셀렉트박스', 'wooahan');?>" v-bind:class="{'active' : detail.style === '셀렉트박스'}">
									<img src="<?php echo plugins_url('/assets/images/option-select.svg', WOOAHAN__FILE__);?>">
									<label>셀렉트박스</label>
									<span class="description">일반적인 형태의 옵션 표현 방법입니다.</span>
								</li>
								<li option-style="color"  option-title="<?php _e('색상선택', 'wooahan');?>" v-bind:class="{'active' : detail.style === '색상선택'}">
									<img src="<?php echo plugins_url('/assets/images/option-color.svg', WOOAHAN__FILE__);?>">
									<label>색상선택</label>
									<span class="description">직접 등록한 색상으로 표현하는 방법 입니다. (조합 일체선택형 표현불가)</span>
								</li>
								<li option-style="thumbnail"  option-title="<?php _e('썸네일이미지', 'wooahan');?>" v-bind:class="{'active' : detail.style === '썸네일이미지'}">
									<img src="<?php echo plugins_url('/assets/images/option-thumb.svg', WOOAHAN__FILE__);?>">
									<label>썸네일 이미지</label>
									<span class="description">직접 등록한 이미지로 표현하는 방법 입니다. (조합 일체선택형 표현불가)</span>
								</li>
								<li option-style="text"  option-title="<?php _e('텍스트', 'wooahan');?>" v-bind:class="{'active' : detail.style === '텍스트'}">
									<img src="<?php echo plugins_url('/assets/images/option-txt.svg', WOOAHAN__FILE__);?>">
									<label>텍스트</label>
									<span class="description">옵션값 이름 그대로 박스형태로 노출됩니다. (조합 일체선택형 표현불가)</span>
								</li>
							</ul>
						</div>
						<table class="table" style="margin-top:20px;">
							<thead>
								<tr>
									<th scope="col">옵션명</th>
									<th scope="col">옵션값</th>
									<th scope="col">색상</th>
									<th scope="col">썸네일</th>
								</tr>
							</thead>
							<tbody>
								<tr v-bind:class="'option option-'+key" v-for="(item, key) in detail.options">
									<td>{{ detail.name }}</td>
									<td>{{ item.name }}</td>
									<td>
										<color-picker placeholder="#000000" v-bind:data-name="item.name" class="selected-color" v-bind:name="'wooahan[attributes]['+key+'][color]['+item.name+']'" v-bind:value="item.color"></color-picker>
									</td>
									<td><div class="added-thumbnail"><img v-bind:src="item.thumbnails"><input type="hidden" class="added-thumbnail-input" v-bind:name="'wooahan[attributes]['+key+'][thumbnails]['+item.name+']'" v-bind:value="item.thumbnails" v-bind:data-name="item.name"></div><button type="button" class="button thumbnail-upload button-option-image-upload" v-on:click="thumbnailUpload(key)">이미지 업로드</button><button v-show="item.thumbnails != ''" type="button" class="button button-remove-thumbnail">이미지 삭제</button></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소</button>
	        <button type="button" class="btn btn-primary button-option-detail-save">확인</button>
	      </div>
	    </div>
	  </div>
	</div>
	<div class="row">
		<table class="product-setting-table">
			<tr>
				<th>옵션사용</th>
				<td>
					<div class="form-check form-check-inline">
					  <input class="form-check-input" type="radio" name="wooahan[option_use]" id="inlineRadio1" value="yes" <?php if($product->is_type('variable')) : echo 'checked'; endif; ?>>
					  <label class="form-check-label" for="inlineRadio1">사용함</label>
					</div>
					<div class="form-check form-check-inline">
					  <input class="form-check-input" type="radio" name="wooahan[option_use]" id="inlineRadio2" value="no" <?php if($product->is_type('simple')) : echo 'checked'; endif; ?>>
					  <label class="form-check-label" for="inlineRadio2">사용안함</label>
					</div>						
				</td>
			</tr>
			<tr>
				<th>옵션 구성방식</th>
				<td>
					<div class="form-check form-check-inline">
					  <input class="form-check-input option-type-input" type="radio" name="wooahan[merge_type]" id="mergeType1" value="merge_one" <?php if($merge_type == 'merge_one') : echo 'checked'; endif; ?>>
					  <label class="form-check-label" for="mergeType1">조합 일체선택형</label>
					</div>
					<div class="form-check form-check-inline">
					  <input class="form-check-input option-type-input" type="radio" name="wooahan[merge_type]" id="mergeType2" value="merge_sep" <?php if($merge_type == 'merge_sep') : echo 'checked'; endif; ?>>
					  <label class="form-check-label" for="mergeType2">조합 분리선택형</label>
					</div>	
					<div class="form-check form-check-inline">
					  <input class="form-check-input option-type-input" type="radio" name="wooahan[merge_type]" id="mergeType3" value="indivisual" <?php if($merge_type == 'indivisual') : echo 'checked'; endif; ?> data-toggle="modal" data-target="#indivisual-notice">
					  <label class="form-check-label" for="mergeType3">독립 선택형</label>
					</div>
					<div class="form-check form-check-inline">
						<button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#optionGuide"><i class="fas fa-question"></i> 선택 가이드</button>
					</div>					
				</td>
			</tr>
			<tr>
				<th>옵션 설정</th>
				<td>
					<div class="form-check form-check-inline">
					  <input class="form-check-input input-option-radio" type="radio" data-id="direct_write" name="wooahan[option_template]" id="direct" value="direct" checked>
					  <label class="form-check-label" for="direct">직접 입력하기</label>
					</div>	
					<div class="form-check form-check-inline">
					  <input class="form-check-input input-option-radio" type="radio" data-id="load_template" name="wooahan[option_template]" id="loadTemplate" value="load_template">
					  <label class="form-check-label" for="loadTemplate">옵션 템플릿 불러오기</label>
					</div>						
				</td>
			</tr>
			<tr>
				<th>옵션 템플릿</th>
				<td><button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#optionTemplate"><i class="fas fa-list"></i> 옵션 템플릿</button></td>
			</tr>
			<tr>
				<th>옵션 직접 입력하기</th>
				<td>
					<div class="row">
						<div class="col-4">
						  <div class="form-group">
						    <label for="exampleInputEmail1">옵션명</label>
						    <input type="text" class="form-control input-option-name" id="optionName" placeholder="옵션명을 입력하세요.">
						    <small id="optionName" class="form-text text-muted">옵션값을 대표하는 명칭 입니다. (ex. 색상)</small>
						  </div>					
						</div>
						<div class="col-8">
							<div class="form-group">
							<label for="exampleInputEmail1">옵션값</label>
								<div class="input-group">
								  <input type="text" class="form-control input-option-value" id="optionValue" placeholder="옵션값을 입력하세요.">
								  <div class="input-group-append">
								    <button class="btn btn-info btn-option-regist" type="button" v-on:click="create">추가</button>
								  </div>
								</div>
							<small id="optionValue" class="form-text text-muted">옵션값은 콤마(,)로 분리하여 입력하시기 바랍니다. (ex. 블랙,화이트,모카,핑크)</small>
							</div>						
						</div>
					</div>					
				</td>
			</tr>
			<tr class="row collapse-option" id="load_template" style="display:none">
				<th>옵션 템플릿 불러오기</th>
				<td><button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#optionTemplate"><i class="fas fa-list"></i> 옵션 템플릿</button></td>
			</tr>
			<tr>
				<th>사용된 옵션</th>
				<td>
					<small class="text-muted">
					- 옵션 품목 추가 후 삭제 또는 수정하실 경우 기존에 추가 된 옵션 품목들이 초기화 되며, 품목추가 버튼을 통해 다시 등록하셔야 합니다.<br>
					- 품목 추가 후 수정시 기존 등록된 품목들이 초기화 되오니 수정에 유념하시기 바랍니다.<br>
					</small>
<?php
	$attributes 	= get_post_meta($post->ID, '_product_attributes', true);
	$not_required 	= get_post_meta($post->ID, '_product_not_required_attributes', true);
	if($attributes){
		foreach($attributes as $key => $attribute){
			if(isset($attribute['value'])){
				$optionArray = explode("|", $attribute['value']);
				$attributes[$key]['optionArray'] = $optionArray;
				$attributes[$key]['is_required'] = 'true';
				foreach($optionArray as $optionArr){
					$optionArr = trim($optionArr);
					if(!isset($attributes[$key]['color'][$optionArr])){
						$attributes[$key]['color'][$optionArr] = '';
					}
					if(!isset($attributes[$key]['thumbnails'][$optionArr])){
						$attributes[$key]['thumbnails'][$optionArr] = '';
					}
				}
				if(!isset($attributes[$key]['style'])){
					$attributes[$key]['style'] = __('셀렉트박스', 'wooahan');
				} else {
					switch($attributes[$key]['style']){
						case 'selectbox' :
							$style_text = __('셀렉트박스', 'wooahan');
						break;

						case 'color' :
							$style_text = __('색상선택', 'wooahan');
						break;

						case 'thumbnail' :
							$style_text = __('썸네일이미지', 'wooahan');
						break;

						case 'text' :
							$style_text = __('텍스트', 'wooahan');
						break;

						default :
							$style_text = $attributes[$key]['style'];
						break;
					}
					$attributes[$key]['style'] = $style_text;
				}
			}
		}
	}
	if($not_required){
		foreach($not_required as $key => $attribute){
			if(isset($attribute['value'])){
				$optionArray = explode("|", $attribute['value']);
				$not_required[$key]['optionArray'] = $optionArray;
				$not_required[$key]['is_required'] = 'false';
				foreach($optionArray as $optionArr){
					$optionArr = trim($optionArr);
					if(!isset($not_required[$key]['color'][$optionArr])){
						$not_required[$key]['color'][$optionArr] = '';
					}
					if(!isset($not_required[$key]['thumbnails'][$optionArr])){
						$not_required[$key]['thumbnails'][$optionArr] = '';
					}
				}
				if(!isset($not_required[$key]['style'])){
					$not_required[$key]['style'] = __('셀렉트박스', 'wooahan');
				} else {
					switch($not_required[$key]['style']){
						case 'selectbox' :
							$style_text = __('셀렉트박스', 'wooahan');
						break;

						case 'color' :
							$style_text = __('색상선택', 'wooahan');
						break;

						case 'thumbnail' :
							$style_text = __('썸네일이미지', 'wooahan');
						break;

						case 'text' :
							$style_text = __('텍스트', 'wooahan');
						break;

						default :
							$style_text = $not_required[$key]['style'];
						break;
					}
					$not_required[$key]['style'] = $style_text;
				}

				$attributes[$key] = $not_required[$key];
			}
		}
	}
	//print_r($attributes);


?>
					<table class="option-table attributes-table" data-attributes='<?php echo json_encode($attributes);?>'>
						<thead>
							<tr>
								<th>순서이동</th>
								<th><input type="checkbox" class="check-all-attr"></th>			
								<th>옵션명</th>
								<th>옵션값</th>
								<th>옵션스타일</th>
								<th>필수옵션</th>
								<th>옵션값 관리</th>
							</tr>
						</thead>
						<tbody id="attributes-tbody" class="option-value-tbody" v-sortable="{ onUpdate : onUpdate, swapThreshold : 1, animation:150, handle: '.button-move' }">
							<template v-for="(item, key, index) in attributes">
								<tr>
									<td>
										<button type="button" class="button button-move"><i class="fas fa-ellipsis-v"></i></button> 
									</td>
									<td><input type="checkbox" class="attribute-checkbox"></td>
									<td>
										{{ item.name }}
										<input type="hidden" v-bind:name="'wooahan[attributes]['+index+'][name]'" class="added-name" v-bind:value="item.name">
									</td>
									<td class="option-list">
										<div class="options">
											<span class="option-row" v-for="option in item.optionArray">
												<input type="checkbox" v-bind:name="'wooahan[attributes]['+index+'][value][]'" class="added-value" v-bind:value="option" checked> {{ option }}
											</span>
										</div>
										<div class="option-details">
											<input type="hidden" v-bind:name="'wooahan[attributes]['+index+'][color]['+key+']'" v-for="(option, key) in item.color" class="added-color-input" v-bind:data-name="key" v-bind:value="option">
											<input type="hidden" v-bind:name="'wooahan[attributes]['+index+'][thumbnails]['+key+']'" v-for="(option, key) in item.thumbnails" class="added-thumbnail-input" v-bind:data-name="key" v-bind:value="option">
										</div>
									</td>
									<td>{{ item.style }}<input type="hidden" v-bind:name="'wooahan[attributes]['+index+'][style]'" class="added-option-style" v-bind:value="item.style"></td>
									<td>
										<input :checked="item.is_required === 'true'" type="checkbox" v-bind:name="'wooahan[attributes]['+index+'][required]'" class="option-required" value="true">
									</td>
									<td>
										<button type="button" class="btn btn-info btn-sm btn-edit-details" v-on:click="optionDetail(key, item.name, 'attribute')">세부 수정</button>										
									</td>
								</tr>
							</template>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="7">
									<div class="row" style="padding:0px 20px;">
										<div class="col text-left"><button type="button" class="button btn-checked-attribute-remove"><i class="fas fa-trash-alt"></i>&nbsp;&nbsp;선택삭제</button> <button type="button" class="button" data-toggle="modal" data-target="#optionTemplateRegist">템플릿 등록</button></div>
										<div class="col text-right"><button type="button" class="button button-primary btn-option-add-all" data-toggle="modal" data-target="#optionCreate"><i class="fas fa-angle-double-down"></i>&nbsp;&nbsp;모든 옵션 품목추가</button></div>
									</div>
								</td>
							</tr>
						</tfoot>
					</table>
				</td>
			</tr>
		</table>
	</div>
	<div class="row">
		<div class="col expand-col" style="position:relative">
			<div class="option-progress">
				<div class="content"><img src="<?php echo plugins_url('/assets/images/ajax-loader-v3.svg', WOOAHAN__FILE__);?>"></div>
				<div class="background"></div>
			</div>
			<table class="option-table variation-table" data-variations='<?php echo json_encode(wooahan_get_products($post->ID));?>'>
				<thead>
					<tr>
						<th colspan="10" style="text-align:left; padding:10px 20px;">
							<button type="button" class="button" v-on:click="variationHandlers().remove()"><i class="fas fa-trash-alt"></i>&nbsp;&nbsp;선택삭제</button> <button type="button" class="button">일괄설정</button> <button type="button" class="button" v-on:click="variationHandlers().selectedSave()"><i class="fas fa-save"></i>&nbsp;&nbsp;선택저장</button>	<button type="button" class="button-primary" v-on:click="variationHandlers().allSave()"><i class="fas fa-save"></i>&nbsp;&nbsp;전체저장</button> <button type="button" class="button btn-option-refresh" v-on:click="variationHandlers().getVariations()"><i class="fas fa-sync-alt"></i>&nbsp;&nbsp;새로고침</button>
							<button type="button" class="button btn-variation-expand" style="float:right" expand-before="전체화면" expand-after="돌아가기"><i class="fas fa-expand"></i>&nbsp;&nbsp;전체화면</button>	
						</th>
					</tr>
					<tr>
						<th style="width:40px"><input type="checkbox" class="option-check-toggle"></th>
						<th>필수/선택</th>
						<th style="width:250px">품목번호/품목명</th>
						<th>자체 품목코드</th>
						<th>재고관리</th>
						<th>품절 후 주문</th>
						<th>수량체크 기준</th>
						<th>재고수량</th>
						<th>안전재고</th>
						<th>품절표시</th>
					</tr>
				</thead>
				<tbody class="variation-tbody">
					<template v-for="(item, key) in variations">
						<tr>
							<td rowspan="2"><input type="checkbox" class="option-check" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][variation_id]'" v-bind:value="item.variation_id"></td>
							<td rowspan="2">
								<span v-show="item.not_req !== 'yes'" class="badge badge-danger"><?php _e('필수', 'wooahan'); ?></span>
								<span v-show="item.not_req === 'yes'" class="badge badge-secondary"><?php _e('선택', 'wooahan'); ?></span>
							</td>
							<td rowspan="2">
								<small>#{{item.variation_id}}</small><br>
								{{item.variation_title}}<br>
							</td>
							<td><input type="text" style="width:100px" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][sku]'" v-bind:value="item.sku"></td>
							<td><input :checked="item.manage_stock === 'checked'" type="checkbox" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][manage_stock]'" value="yes"></td>
							<td>
								<select v-bind:name="'wooahan[manage_options]['+item.variation_id+'][backorders]'">
									<option :selected="item.backorders === 'no'" v-bind:value="'no'" >허용안함</option>
									<option :selected="item.backorders === 'notify'" v-bind:value="'notify'">허용하고 고객알림</option>
									<option :selected="item.backorders === 'yes'" v-bind:value="'yes'">허용함</option>
								</select>
							</td>
							<td>
								<select v-bind:name="'wooahan[manage_options]['+item.variation_id+'][stock_count]'">
									<option :selected="item.stock_count === 'paid'" v-bind:value="'paid'">결제기준</option>
									<option :selected="item.stock_count === 'cart'" v-bind:value="'cart'">장바구니기준</option>
									<option :selected="item.stock_count === 'shipping'" v-bind:value="'shipping'">배송완료기준</option>
								</select>
							</td>
							<td><input type="text" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][variable_stock]'" v-bind:value="item.stock" style="width:50px"></td>
							<td><input type="text" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][safe_stock]'" v-bind:value="item.safe_stock" style="width:50px"></td>
							<td><input :checked="item.stock_status === 'outofstock'" type="checkbox" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][soldout_display]'" value="outofstock"></td>
						</tr>
						<tr>
							<td colspan="7" class="text-left" style="padding-left:20px; padding-right:20px;">
								
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
								  <label class="btn btn-sm btn-outline-secondary" v-bind:class="{ 'active' : item.enabled == true }">
								    <input type="radio" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][enabled]'" id="option1" value="yes" :checked="item.enabled == true"> 판매함
								  </label>
								  <label class="btn btn-sm btn-outline-secondary" v-bind:class="{ 'active' : item.enabled == false }">
								    <input type="radio" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][enabled]'" id="option2" value="no" :checked="item.enabled == false"> 판매안함
								  </label>
								</div>
								<span style="position:relative; top:2px; margin-right:5px; margin-left:15px">추가금액</span>
								<div class="btn-group btn-group-toggle" data-toggle="buttons">
								  <label class="btn btn-cal btn-sm btn-outline-secondary">
								    <input type="radio" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][add_price]'" class="btn-cal cal-plus" id="option1" autocomplete="off" value="plus"> <i class="fas fa-plus"></i>
								  </label>
								  <label class="btn btn-cal btn-sm btn-outline-secondary">
								    <input type="radio" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][add_price]'" class="btn-cal cal-minus" id="option2" autocomplete="off" value="minus"> <i class="fas fa-minus"></i>
								  </label>
								</div>
								<input type="text" v-bind:name="'wooahan[manage_options]['+item.variation_id+'][added_price]'" style="position:relative; top:2px; width:80px" class="input-calculate">
								<span style="position:relative; top:2px; margin-right:5px; margin-left:15px">실판매금액</span>
								<input type="text" style="position:relative; top:2px; width:120px" class="real-price" v-bind:data-price="item.price" v-bind:value="item.price" readonly="readonly" v-bind:placeholder="item.price">
								<button type="button" class="button-primary" v-bind:data-id="item.variation_id" v-on:click="variationHandlers().update(item.variation_id)">변경사항 저장</button>
							</td>
						</tr>
					</template>
				</tbody>
			</table>
		</div>
	</div>
</div>