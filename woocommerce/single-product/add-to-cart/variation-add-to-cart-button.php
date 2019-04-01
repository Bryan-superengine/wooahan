<?php
/**
 * Single variation cart button
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

?>
<div id="wooahan-add-to-cart" class="woocommerce-variation-add-to-cart variations_button">
	<div class="added-variation-list" v-bind:class="{active: isActive}">
		<ul>
			<template v-for="(item, key) in items">
				<li>
					<p>{{ item.title }}</p>
					<div class="option-summary">
						<div class="quantity-box">
							<button type="button" class="minus" v-bind:min-qty="item.min_qty" v-on:click="qty_minus(item.variation_id)"></button>
							<input type="number" v-bind:value="item.qty">
							<button type="button" class="plus" v-bind:max-qty="item.max_qty" v-on:click="qty_plus(item.variation_id)">+</button>
						</div>
						<div class="option-price">
							<span class="price">
								<strong>{{ item.formatted_price }}</strong>
								원
							</span>
							<div class="remove-variation" v-bind:variation-id="item.variation_id" v-on:click="remove_variation(item.variation_id)">
								<div class="close-wrapper">
									<span class="close-css"></span>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" v-bind:name="'wooahan[variations]['+item.variation_id+'][variation_id]'" v-bind:value="item.variation_id">
					<input type="hidden" v-bind:name="'wooahan[variations]['+item.variation_id+'][quantity]'" v-bind:value="item.qty">
				</li>
			</template>
		</ul>
	</div>
	<div class="total-box" v-cloak>
		<div class="content-inner">
			<span class="total-span"><label><?php _e('총 상품금액', 'wooahan');?></label></span>
			<div class="total-result">
				<span class="qty-span"><label><?php _e('총 수량', 'wooahan'); ?> {{ totalQuantity }}<?php _e('개', 'wooahan');?></label></span>
				<span class="price-span"><span class="total-price">{{ totalPrice }}</span></span>
			</div>
		</div>
	</div>
	<style>
		[v-cloak] {
			display:none !important;
		}
	</style>
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<?php
	do_action( 'woocommerce_before_add_to_cart_quantity' );
	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<button type="button" class="button wooahan-variation-add-to-cart"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
	

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
	<input type="hidden" name="variation_id" class="variation_id" value="0" />

</div>
