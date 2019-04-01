<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.1
 */

defined( 'ABSPATH' ) || exit;

global $product;
$attribute_keys = array_keys( $attributes );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<?php
    $type 				= get_post_meta($product->get_id(), '_variation_merge_type', true);
    $json_attributes 	= array();
    $cnt 				= 0;
	$style 		 		= 'selectbox';
	$origin_attr 		= get_post_meta( $product->get_id(),  '_product_attributes', true );
    switch($type){
    	case 'merge_one' :
		    $handle         = new WC_Product_Variable($product->get_id());
		    $variations     = $handle->get_children();
		    //print_r($variations);
		    foreach($variations as $value){
                $single_variation   = new WC_Product_Variation($value);
                $variation_id       = $single_variation->get_id();
                $variation_post 	= get_post($variation_id);
				$stock_status = get_post_meta($variation_id, '_stock_status', true);
				if($stock_status == 'outofstock'){
					$stock_status = false;
				} else {
					$stock_status = true;
				}
                $not_req 			= get_post_meta($variation_id, 'not_req', true);
                if($not_req != 'yes' && $variation_post->post_status == 'publish'){
	                $variation_title    = implode(" / ", $single_variation->get_variation_attributes());
	                //print_r($value);
	                $json_attributes[$product->get_id()]['name'] = __('옵션 선택', 'wooahan');
	                $json_attributes[$product->get_id()]['style'] = 'selectbox';
	                $json_attributes[$product->get_id()]['options'][] = array(
		    			'name' => $variation_title,
		    			'variation_id' => $variation_id,
		    			'details' => array(
		    				'is_in_stock' => $stock_status,
		    				'variation_is_active' => true
		    			)
	                );
            	}
            	$cnt++;
		    }
    	break;

    	case 'merge_sep' :
    		//print_r($origin_attr);
		    foreach($attributes as $attribute_name => $options){
		    	if(isset($origin_attr[sanitize_title($attribute_name)])){
		    		$style = $origin_attr[sanitize_title($attribute_name)]['style'];
		    		if(isset($origin_attr[sanitize_title($attribute_name)]['color'])){
		    			$color = $origin_attr[sanitize_title($attribute_name)]['color'];
		    		} else {
		    			$color = array();
		    		}
		    		if(isset($origin_attr[sanitize_title($attribute_name)]['thumbnails'])){
		    			$thumbnails = $origin_attr[sanitize_title($attribute_name)]['thumbnails'];
		    		} else {
		    			$thumbnails = array();
		    		}
		    	}
		    	if(count($attributes) > 1){
			    	foreach($options as $option){
			    		$json_attributes[$cnt]['style'] = $style;
			    		$json_attributes[$cnt]['name'] = $attribute_name;
			    		$json_attributes[$cnt]['colors'] = $color;
			    		$json_attributes[$cnt]['thumbnails'] = $thumbnails;
			    		$json_attributes[$cnt]['options'][] = array(
			    			'name' => $option,
			    			'variation_id' => null,
			    			'details' => array(
			    				'is_in_stock' => 'true'
			    			)
			    		);
			    	}
		    	}
		    	if(count($attributes) <= 1){
				    $handle         = new WC_Product_Variable($product->get_id());
				    $variations     = $handle->get_children();
				    //print_r($variations);
				    foreach($variations as $value){
		                $single_variation   = new WC_Product_Variation($value);
		                $variation_id       = $single_variation->get_id();
						$stock_status = get_post_meta($variation_id, '_stock_status', true);
						if($stock_status == 'outofstock'){
							$stock_status = false;
						} else {
							$stock_status = true;
						}
		                $not_req 			= get_post_meta($variation_id, 'not_req', true);
		                if($not_req != 'yes'){
			                $variation_title    = implode(" / ", $single_variation->get_variation_attributes());
			                $json_attributes[$product->get_id()]['style'] = $style;
			                $json_attributes[$product->get_id()]['name'] = $attribute_name;
			                $json_attributes[$product->get_id()]['colors'] = $color;
			                $json_attributes[$product->get_id()]['thumbnails'] = $thumbnails;
			                $json_attributes[$product->get_id()]['options'][] = array(
				    			'name' => $variation_title,
				    			'variation_id' => $variation_id,
				    			'details' => array(
				    				'is_in_stock' => $stock_status,
				    				'variation_is_active' => true
				    			)
			                );
		            	}
				    }


		    	}
		    	$cnt++;
		    }

    	break;
    }

    $not_req_attributes = get_post_meta($product->get_id(), '_product_not_required_attributes', true);
    $not_req_variations = get_post_meta($product->get_id(), 'not_req_variations', true);
    //print_r($json_attributes);
 ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ); // WPCS: XSS ok. ?>" data-attributes="<?php echo htmlspecialchars(wp_json_encode($json_attributes));?>" wooahan-notreq-variations="<?php echo htmlspecialchars(wp_json_encode($not_req_variations));?>" data-type="<?php echo $type;?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php esc_html_e( 'This product is currently out of stock and unavailable.', 'woocommerce' ); ?></p>
	<?php else : ?>
		<div id="wooahan-variation">
			<input type="hidden" class="variation-type" value="<?php echo $type;?>">
			<div id="optionList" class="variations">

				<template v-if="item.style === 'text' || item.style === '텍스트'" v-for="(item, key, index) in items">
					<div class="option-select-wrapper select-wrapper select-colorpicker-wrapper" v-bind:class="'select-wrapper-'+key">
						<p class="option-title">{{ item.name }}</p>
						<ul class="variation-ul">
							<li class="option" v-bind:class="{ 'soldout' : variation.details.is_in_stock === false, 'deactive' : variation.details.variation_is_active === false }" v-for="variation in item.options" v-bind:data-option="variation.name" v-on:click="variation_checker(index, key, item.name, variation.name, variation.details.is_in_stock, variation )">
                				<label>
                					{{ variation.name }}
                					<span class="error" v-if="variation.details.is_in_stock === false"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.details.variation_is_active === false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>

				<template v-if="item.style === 'thumbnail' || item.style === '썸네일이미지'" v-for="(item, key, index) in items">
					<div class="option-select-wrapper select-wrapper select-colorpicker-wrapper" v-bind:class="'select-wrapper-'+key">
						<p class="option-title">{{ item.name }}</p>
						<ul class="variation-ul">
							<li class="option" v-bind:class="{ 'soldout' : variation.details.is_in_stock === false, 'deactive' : variation.details.variation_is_active === false }" v-for="variation in item.options" v-bind:data-option="variation.name" v-on:click="variation_checker(index, key, item.name, variation.name, variation.details.is_in_stock, variation )">
                				<span class="thumbnail-box"><img v-bind:src="item.thumbnails[variation.name]"></span>
                				<label>
                					{{ variation.name }}
                					<span class="error" v-if="variation.details.is_in_stock === false"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.details.variation_is_active === false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>

				<template v-if="item.style === 'color' || item.style === '색상선택'" v-for="(item, key, index) in items">
					<div class="option-select-wrapper select-wrapper select-colorpicker-wrapper" v-bind:class="'select-wrapper-'+key">
						<p class="option-title">{{ item.name }}</p>
						<ul class="variation-ul">
							<li class="option" v-bind:class="{ 'soldout' : variation.details.is_in_stock === false, 'deactive' : variation.details.variation_is_active === false }" v-for="variation in item.options" v-bind:data-option="variation.name" v-on:click="variation_checker(index, key, item.name, variation.name, variation.details.is_in_stock, variation )">
                				<span class="color-box" v-bind:style="{ background : item.colors[variation.name] }"></span>
                				<label>
                					{{ variation.name }}
                					<span class="error" v-if="variation.details.is_in_stock === false"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.details.variation_is_active === false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>

				<template v-if="item.style === 'selectbox' || item.style === '셀렉트박스'" v-for="(item, key, index) in items">
					<div class="option-select-wrapper select-wrapper" v-bind:class="'select-wrapper-'+key">
						<div class="select-text" v-bind:data-title="item.name" v-on:click="toggle(key)">
							{{ item.name }}
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</div>
						<ul class="variation-ul">
							<li class="option" v-bind:class="{ 'soldout' : variation.details.is_in_stock === false, 'deactive' : variation.details.variation_is_active === false }" v-for="variation in item.options" v-bind:data-option="variation.name" v-on:click="variation_checker(index, key, item.name, variation.name, variation.details.is_in_stock, variation )">
                				<input type="radio" name="radio" class="option-check">
                				<label>
                					{{ variation.name }}
                					<span class="error" v-if="variation.details.is_in_stock === false"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.details.variation_is_active === false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>

				<template v-if="item.details.style === 'text' || item.details.style === '텍스트'" v-for="(item, key, index) in notreq">
					<div class="option-select-wrapper select-notreq-wrapper select-colorpicker-wrapper" v-bind:class="'select-wrapper-'+key">
						<p class="option-title">{{ decodeURIComponent(key) }}</p>
						<ul class="variation-ul">
							<li v-if="k != 'details'" class="option" v-bind:class="{ 'soldout' : variation.is_in_stock === 'false', 'deactive' : variation.is_active === false }" v-for="(variation, k) in item" v-bind:data-option="variation.option_title" v-on:click="notreq_checker(index, key, variation.option_title, variation.variation_id)">
                				<label>
                					{{ variation.option_title }}
                					<span class="error" v-if="variation.is_in_stock === false"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.variation_is_active === false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>

				<template v-if="item.details.style === 'thumbnail' || item.details.style === '썸네일이미지'" v-for="(item, key, index) in notreq">
					<div class="option-select-wrapper select-notreq-wrapper select-colorpicker-wrapper" v-bind:class="'select-wrapper-'+key">
						<p class="option-title">{{ decodeURIComponent(key) }}</p>
						<ul class="variation-ul">
							<li v-if="k != 'details'" class="option" v-bind:class="{ 'soldout' : variation.is_in_stock === 'false', 'deactive' : variation.is_active === false }" v-for="(variation, k) in item" v-bind:data-option="variation.option_title" v-on:click="notreq_checker(index, key, variation.option_title, variation.variation_id)">
                				<span class="thumbnail-box"><img v-bind:src="item.details.thumbnails[variation.option_title]"></span>
                				<label>
                					{{ variation.option_title }}
                					<span class="error" v-if="variation.is_in_stock === false"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.variation_is_active === false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>

				<template v-if="item.details.style === 'color' || item.details.style === '색상선택'" v-for="(item, key, index) in notreq">
					<div class="option-select-wrapper select-notreq-wrapper select-colorpicker-wrapper" v-bind:class="'select-wrapper-notreq-'+index">
						<p class="option-title">{{ decodeURIComponent(key) }}</p>
						<ul class="variation-ul">
							<li v-if="k != 'details'" class="option" v-bind:class="{ 'soldout' : variation.is_in_stock === 'false', 'deactive' : variation.is_active === false }" v-for="(variation, k) in item" v-bind:data-option="variation.option_title" v-on:click="notreq_checker(index, key, variation.option_title, variation.variation_id)">
                				<span class="color-box" v-bind:style="{ background : item.details.color[variation.option_title] }"></span>
                				<label>
                					{{ variation.option_title }}
                					<span class="error" v-if="variation.is_in_stock === false"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.variation_is_active === false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>

				<template v-if="item.details.style === 'selectbox' || item.details.style === '셀렉트박스'" v-for="(item, key, index) in notreq">
					<div class="option-select-wrapper select-notreq-wrapper" v-bind:class="'select-wrapper-notreq-'+index">
						<div class="select-text" v-bind:data-title="decodeURIComponent(key)" v-on:click="notreq_toggle(index)">
							{{ decodeURIComponent(key) }}
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</div>
						<ul class="variation-ul">
							<li v-if="k != 'details'" class="option" v-bind:class="{ 'soldout' : variation.is_in_stock === 'false', 'deactive' : variation.is_active === false }" v-for="(variation, k) in item" v-bind:data-option="variation.option_title" v-on:click="notreq_checker(index, key, variation.option_title, variation.variation_id)">
                				<input type="radio" name="radio" class="option-check">
                				<label>
                					{{ variation.option_title }}
                					<span class="error" v-if="variation.is_in_stock === 'false'"><?php _e('[품절]', 'wooahan');?></span>
                					<span class="error" v-else-if="variation.is_active == false"><?php _e('[등록됨]', 'wooahan');?></span>
                				</label>
                			</li>							
						</ul>
					</div>
				</template>
			</div>
		</div>
		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
