<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	
	$sale_start 	= get_post_meta($post->ID, '_sale_price_dates_from', true);
	$sale_end   	= get_post_meta($post->ID, '_sale_price_dates_to', true);
	$sale_unlimit 	= get_post_meta($post->ID, '_sale_unlimited', true);
	$merge_type 	= get_post_meta($post->ID, '_variation_merge_type', true);
	//print_r($merge_type);
	if($sale_unlimit == "true"){
		$sale_start = '';
		$sale_end   = '';
	}

	$product = wc_get_product( $post->ID );

	$post_status = $post->post_status;

?>
<div id="wooahan-wrap">
	<div class="alert alert-secondary alert-dismissible fade show" role="alert">
	  <strong>기존 상품편집으로 돌아가고 싶다면?</strong> <a href="/wp-admin/admin.php?page=wc-settings&tab=wooahan">우아한 -> 설정</a>에서 상품등록 활성화를 꺼주시기 바랍니다.
	  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    <span aria-hidden="true">&times;</span>
	  </button>
	</div>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<a class="navbar-brand" href="#"><img src="<?php echo plugins_url('/assets/images/w-logo.svg', WOOAHAN__FILE__);?>"> 상품설정</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
		</button>
	  	<div class="collapse navbar-collapse" id="navbarNav">
		    <ul class="navbar-nav">
		      <li class="nav-item active">
		        <a class="nav-link set-basic" href="#" data-menu="set-basic">일반설정 <span class="sr-only">(current)</span></a>
		      </li>
		      <li class="nav-item">
		        <a class="nav-link set-option" href="#" data-menu="set-option">옵션/재고</a>
		      </li>
		      <li class="nav-item">
		        <a class="nav-link set-badge" href="#" data-menu="set-badge">뱃지관리</a>
		      </li>
		    </ul>
	  	</div>
		<button type="button" class="btn btn-sm btn-success btn-publish">템플릿 불러오기</button>
		<span class="save-progress" style="text-align:right; display:inline-block; color:#fff; width:30px; vertical-align:middle; line-height:38px; margin-right:10px;"></span>
		<div class="btn-group btn-group-toggle btn-post-type-toggle" data-toggle="buttons">
		  <label class="btn btn-sm btn-secondary <?php if($post_status == 'publish') : echo 'active'; endif; ?>">
		    <input type="radio" name="wooahan[post_status]" class="radio-post-status" id="option1" autocomplete="off" value="publish" <?php if($post_status == 'publish') : echo 'checked'; endif; ?>> 판매상품
		  </label>
		  <label class="btn btn-sm btn-secondary <?php if($post_status == 'private') : echo 'active'; endif; ?>">
		    <input type="radio" name="wooahan[post_status]" class="radio-post-status" id="option2" autocomplete="off" value="private" <?php if($post_status == 'private') : echo 'checked'; endif; ?>> 비공개상품
		  </label>
		  <label class="btn btn-sm btn-secondary <?php if($post_status == 'draft') : echo 'active'; endif; ?>">
		    <input type="radio" name="wooahan[post_status]" class="radio-post-status" id="option3" autocomplete="off" value="draft" <?php if($post_status == 'draft') : echo 'checked'; endif; ?>> 임시상품
		  </label>
		</div>
		<button type="button" class="btn btn-sm btn-danger btn-publish" style="margin-left:10px">저장하기</button>
	</nav>
	<div class="column-wrapper">
	<?php
		require_once( WOOAHAN_PATH . 'includes/admin/edit-product-basic.php' );
		require_once( WOOAHAN_PATH . 'includes/admin/edit-product-option.php' );
		require_once( WOOAHAN_PATH . 'includes/admin/edit-product-badge.php' );
	?>
	<?php
		include_once(WOOAHAN_PATH . 'includes/admin/footer.php');
	?>
</div>