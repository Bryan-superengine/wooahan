<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div id="set-basic" class="section-wrap container-fluid">
	<?php
		include (WOOAHAN_PATH . 'includes/admin/edit-permalink.php');
	?>
	<h2>일반설정</h2>
	<div class="row">
		<table class="product-setting-table">
			<tr>
				<th>상품명</th>
				<td colspan="3"><input type="text" name="wooahan[product_title]" class="form-control product-title" placeholder="<?php _e('상품명을 기입하세요.', 'wooahan');?>" aria-label="productName" aria-describedby="inputGroup-sizing-lg" value="<?php echo get_the_title();?>"></td>
			</tr>
			<tr>
				<th>상품요약</th>
				<td colspan="3"><textarea class="form-control product-excerpt" name="wooahan[post_excerpt]" aria-label="요약설명"><?php echo get_the_excerpt();?></textarea></td>
			</tr>
			<tr>
				<th>상품금액</th>
				<td><input type="text" name="wooahan[regular_price]" class="form-control regular-price" aria-label="Amount (to the nearest dollar)" value="<?php echo $regular_price;?>"></td>
				<th>할인 판매가</th>
				<td><input type="text" name="wooahan[sale_price]" class="form-control sale-price" aria-label="Amount (to the nearest dollar)" value="<?php echo $sale_price;?>"></td>
			</tr>
			<tr>
				<th>상품종류</th>
				<td>
			  		<div class="product-type-group">
			  			<span>
			  			<?php
			  				if($product->is_type('variable')){
			  					echo __('옵션상품', 'wooahan');
			  				}
			  				if($product->is_type('simple')){
			  					echo __('단일상품', 'wooahan');
			  				}
			  			?>
			  			</span>
			  			<p class="description" style="display:block">옵션사용 상품은 옵션/재고 탭에서 변경하실 수 있습니다.</p>
					</div>							
				</td>
				<th>할인일정</th>
				<td>
				  	<div class="col-half">
				  		<input type="text" name="wooahan[sale_start]"" class="form-control datepicker" aria-label="Amount (to the nearest dollar)" placeholder="시작일" value="<?php if($sale_start != "") : echo date("Y-m-d", $sale_start); endif;?>">
				  	</div>
				  	<div class="col-half">
				  		<input type="text" name="wooahan[sale_end]" class="form-control datepicker" aria-label="Amount (to the nearest dollar)" placeholder="종료일" value="<?php if($sale_end != "") : echo date("Y-m-d", $sale_end); endif;?>">
				  	</div>
				  	<small class="text-muted">
				  		<br>
				  		<?php _e('시작일과 종료일을 비워두시면 할인된 판매가로 기한없이 판매됩니다.', 'wooahan'); ?>
				  	</small>
				</td>
			</tr>
			<tr>
				<th>상품태그</th>
				<td colspan="3">
					<small class="text-muted">
					- 태그를 추가하시려면 복수개 입력시 콤마(,)를 사용하세요.
					</small>
				    <div class="input-group">
					  <input type="text" class="form-control input-tags" placeholder="태그를 추가하세요. (콤마 단위로 구분됩니다.)" aria-label="Recipient's username" aria-describedby="button-addon2">
					  <div class="input-group-append">
					    <button class="btn btn-secondary btn-tags-add" type="button">추가</button>
					  </div>
					</div>
					<div class="col col-added-tags">
		<?php
			$tags = wp_get_post_terms($post->ID, 'product_tag', array('orderby' => 'term_id', 'order' => 'ASC'));
			if($tags){
				foreach($tags as $tag){
		?>
						<span class="tag"><?php echo $tag->name;?> <input type="hidden" name="wooahan[tags][]" value="<?php echo $tag->name;?>"><i class="fas fa-times"></i></span>
		<?php
				}
			}
		?>				
					</div>					
				</td>
			</tr>
			<tr>
				<th>상품분류 등록</th>
				<td colspan="3">
					
					<small class="text-muted">
	<?php
	$terms = get_the_terms( $post->ID, 'product_cat' );
	foreach( $terms as $term ){
		$category_url = get_term_link($term, 'product_cat');
		break;
	}
	?>
					- 상품분류란, 쇼핑몰 방문자가 원하는 상품을 쉽게 찾을 수 있도록 구분해주는 값을 뜻합니다.<br>
					- 등록하는 상품을 진열할 상품분류를 선택하시면 해당 분류로 상품이 진열됩니다.<br>
					- 상품분류가 먼저 등록되어 있어야 분류 선택이 가능합니다. <a href="/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product">[상품분류 등록하기]</a>
					</small>
					<table id="category-table" class="option-table">
						<thead>
							<tr>
								<th>대분류</th>
								<th>중분류</th>
								<th>소분류</th>
							</tr>
						</thead>
	<?php
		$terms 		= get_the_terms($post->ID, 'product_cat');
		$all_terms 	= get_terms( 'product_cat', array( 'hide_empty' => false, 'fields' => 'all' ) );
		$added_cats = get_post_meta($post->ID, 'wooahan_product_cats', true);
	?>	
						<tbody class="cat-tbody" data-cat='<?php echo json_encode($all_terms);?>' added-terms='<?php echo json_encode($added_cats);?>'>

							<tr>
								<td class="cat-first-td">						
									<ul>
										<template v-for="(item, key) in items.first">
											<li v-bind:class="'each-cat-'+item.term_id" v-bind:data-title="item.name" v-bind:data-id="item.term_id" v-on:click="checkNext(1, item.term_id)">{{ item.name }} <i class="fas fa-angle-right"></i></li>
										</template>							
									</ul>
								</td>
								<td class="cat-second-td">
									<ul>
										<template v-for="(item, key) in items.second">
											<li v-bind:class="'each-cat-'+item.term_id" v-bind:data-title="item.name" v-bind:data-id="item.term_id" v-on:click="checkNext(2, item.term_id)">{{ item.name }} <i class="fas fa-angle-right"></i></li>
										</template>
									</ul>									
								</td>
								<td class="cat-third-td">
									<ul>
										<template v-for="(item, key) in items.third">
											<li v-bind:class="'each-cat-'+item.term_id" v-bind:data-title="item.name" v-bind:data-id="item.term_id" v-on:click="checkNext(3, item.term_id)">{{ item.name }} <i class="fas fa-angle-right"></i></li>
										</template>
									</ul>									
								</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="3">
									<span style="display:inline-block; vertical-align:middle">
										<i class="fas fa-check-circle"></i>  선택된 상품분류
									</span> 
									<span class="selected-categories">
										<ul>
										<template v-for="(item, key) in added">
											<li v-bind:class="'cat-key-'+key">
												<span class="selected" v-for="(item, k) in item.cats">{{ item.name }} <input type="hidden" v-bind:name="'wooahan[cat]['+key+'][]'" v-bind:value="item.term_id"></span> <button type="button" class="button" v-on:click="remove(key)"><? _e('삭제', 'wooahan');?></button>
											</li>
										</template>
										</ul>
									</span>
								</td>
							</tr>
						</tfoot>
					</table>							

				</td>
			</tr>
			<tr>
				<th>썸네일 이미지</th>
				<td class="sep2">
				  	<div class="uploaded-thumbnail">			  		
				  		<div class="col">
<?php
	if(has_post_thumbnail($post->ID)){
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array(500,500) );
		echo '<img src="'.$image[0].'"><input type="hidden" name="wooahan[thumbnail]" value="'.get_post_thumbnail_id($post->ID).'">';
	}
?>
				  		</div>
				  	</div>
				    <div class="row empty-row" style="<?php if(has_post_thumbnail($post->ID)) : echo 'display:none'; endif; ?>">
				    	<div class="col">
						  <div class="form-group">
						    <a class="btn btn-danger btn-upload-thumbnail" href="#" role="button">대표 썸네일 업로드</a>
						    <small id="emailHelp" class="form-text text-muted">대표 사진으로 상품 리스트에 노출되는 사진 입니다.</small>
						  </div>							    		
				    	</div>
				    </div>
				    <div class="row button-row" style="<?php if(has_post_thumbnail($post->ID)) : echo 'display:block'; endif; ?>">
				    	<div class="col" style="text-align:center; margin-top:10px;">
				    		<div>
				    			<button type="button" class="btn btn-secondary btn-sm btn-thumbnail-remove"><i class="fas fa-times"></i> 삭제</button>
				    			<button type="button" class="btn btn-info btn-sm btn-upload-thumbnail"><i class="fas fa-sync-alt"></i> 변경</button>
				    		</div>
				    	</div>
				    </div>						
				</td>
				<th>갤러리 이미지</th>
				<td class="sep2">
		  	<div class="uploaded-image uploaded-gallery">
				<?php
					$galleries = get_post_meta($post->ID, '_product_image_gallery', true);
					if($galleries){
						$galleries = explode(",", $galleries);
						$gallery_html = '';
						$count = 0;
						foreach($galleries as $gallery){
							$url = wp_get_attachment_image_src( $gallery, 'thumbnail' );
							$gallery_html .= '<span class="gallery"><span class="gallery-wrapper"><img src="'.$url[0].'" class="gallery"><input type="hidden" name="wooahan[gallery][]" value="'.$gallery.'"><span class="remove" data-none-image="'.plugins_url('/assets/images/gallery-svg.svg', WOOAHAN__FILE__).'"><img src="'.plugins_url('/assets/images/exit-svg.svg', WOOAHAN__FILE__).'"></span></span></span>';
							$count++;
						}
					}

					for ($i=$count; $i < 10; $i++) { 
						$gallery_html .= '<span class="gallery"><span class="gallery-wrapper"><span class="gallery-none"><img src="'.plugins_url('/assets/images/gallery-svg.svg', WOOAHAN__FILE__).'"></span><span class="remove"></span></span></span>';
					}

				?>
		  		<div class="col">
		  			<?php echo $gallery_html; ?>
				    <a class="btn btn-info btn-sm btn-upload-gallery" href="#" role="button" data-none-image="<?php echo plugins_url('/assets/images/gallery-svg.svg', WOOAHAN__FILE__);?>" data-remove-image="<?php echo plugins_url('/assets/images/exit-svg.svg', WOOAHAN__FILE__);?>"><?php _e('갤러리 이미지 업로드', 'wooahan');?></a>
				    <small id="emailHelp" class="form-text text-muted">상품 상세페이지 썸네일 하단에 노출될 갤러리 이미지이며 복수로 선택할 수 있습니다.</small>
		  		</div>
		  	</div>							
				</td>
			</tr>
			<tr>
				<td colspan="4" style="border-left:0; padding:15px 0">
					<textarea id="wooahan_editor" name="wooahan_editor"><?php echo get_post_field('post_content', $post->ID); ?></textarea>
					<script>
							CKEDITOR.replace( 'wooahan_editor', {
								extraPlugins : 'uploadimage'
							});
					</script>
				</td>
			</tr>
		</table>
	</div>

</div>