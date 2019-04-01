<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="row">
	<div class="col py-3 col-permalink">
		<label>상품주소 :</label>
		<?php
			if(get_option('permalink_structure', true) == ''){
		?>
		<span class="permallink"><?php echo get_the_permalink($post->ID);?></span>
		<a href="/wp-admin/options-permalink.php" target="_blank" class="button">고유주소 변경</a>				
		<?php
			} else {
		?>
		<span class="permallink"><?php echo str_replace ( urldecode($post->post_name)."/","", urldecode(get_the_permalink($post->ID)));?><span class="post-name"><?php echo urldecode($post->post_name); ?></span><input type="hidden" class="input-post-name" value="<?php echo urldecode($post->post_name);?>">/</span>
		<button type="button" class="button button-change-post-name">변경</button>
		<?php
			}
		?>
		<a href="<?php echo get_the_permalink($post->ID);?>" target="_blank" class="button button-preview">미리보기</a>
		<span class="ajax-loader"><i class="fas fa-spinner fa-spin"></i></span>
		<button type="button" class="button button-primary button-save-post-name" style="display:none" data-post-id="<?php echo $post->ID; ?>" data-old-post-name="<?php echo urldecode($post->post_name); ?>">저장</button>
		<button type="button" class="button button-cancle-post-name" style="display:none">취소</button>
		<div class="message"></div>
	</div>
</div>