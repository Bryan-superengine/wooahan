<?php
/**
 * Pay for order form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-pay.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

$totals = $order->get_order_item_totals();
$items  = wooahan_order_items($order);
$shipping_cost = $order->get_shipping_total();

if($shipping_cost == 0){
	$shipping_cost = __('무료', 'wooahan');
} else {
	$shipping_cost = wc_price($shipping_cost);
}

?>
<div id="wooahan-order-pay">
<form id="order_review" method="post">
	<table class="shop-table wooahan-order-pay-table">
		<thead>
			<tr>
				<th class="product-thumbnail"></th>
				<th class="product-name"><?php esc_html_e( '상품정보', 'wooahan' ); ?></th>
				<th class="product-shipping"><?php esc_html_e( '배송비', 'wooahan' ); ?></th>
				<th class="product-quantity"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
				<th class="product-total"><?php esc_html_e( '주문금액', 'wooahan' ); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
foreach($items as $product_id => $item){
	//print_R($item);
	$thumbnail_image 	= wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
	$not_req_variations = get_post_meta($product_id, 'not_req_variations', true);
	$not_req_attributes = get_post_meta($product_id, '_product_not_required_attributes', true);
?>
			<tr>
				<td class="product-thumbnail"><img src="<?php echo $thumbnail_image[0];?>"></td>
				<td class="product-name">
					<p class="product-title"><?php echo get_the_title($product_id);?></p>
<?php
	if(count($item) > 0){
?>
					<div class="options">
						<label>옵션</label>
						<ul>
<?php
		$not_req_variation_ids = array();
		$not_req_variation_detail    = array();
		foreach($item['items'] as $variation){
			$single_variation   = new WC_Product_Variation($variation['variation_id']);
			$variation_id 		= $single_variation->get_id();

			$variation_title    = implode("/", $single_variation->get_variation_attributes());
			$not_req 			= get_post_meta($variation_id, 'not_req', true);
			if($not_req != 'yes'){
?>
							<li><?php echo $variation_title;?>/<?php echo $variation['quantity'];?>개</li>					
<?php
			} else {
				$not_req_variation_ids[] = $variation['variation_id'];
				$not_req_variation_detail[$variation['variation_id']]['quantity'] = $variation['quantity'];
			}
		}
		if($not_req_variations){

			foreach($not_req_variations as $key => $not_req_variation){
				$key_title = urldecode($key);
							//print_r($not_req_variation);
				foreach($not_req_variation as $notreq_key => $variation){
					//print_r($variation);
					if($notreq_key != 'details'){
						if( in_array($variation['variation_id'], $not_req_variation_ids) == true ){
?>
							<li><?php echo $key_title; ?> - <?php echo $variation['option_title'];?>/<?php echo sprintf( __('%s개', 'wooahan'),  $not_req_variation_detail[$variation['variation_id']]['quantity']); ?></li>
<?php
						}
					}
				}
			}
		}
?>
						</ul>
					</div>
<?php
	}
?>
				</td>
				<td class="product-shipping"><?php echo $shipping_cost; ?></td>
				<td class="product-quantity"><?php echo sprintf( __('%s개', 'wooahan'), $item['quantity']); ?></td>
				<td class="product-subtotal"><?php echo wc_price($item['subtotal']);?></td>
			</tr>
<?php
}
?>

		</tbody>
	</table>

	<div class="order-total">
		<div class="total-price">
			<label><?php _e('결제금액', 'wooahan');?></label>
			<span class="price"><?php echo wc_price($order->get_total());?></span>
		</div>
		<div class="price-details">
			<ul>
				<li>
					<label>총 상품금액</label>
					<span class="price"><?php echo wc_price(($order->get_total() - $order->get_shipping_total())); ?></span>
				</li>
				<li>
					<label>배송비</label>
					<span class="price"><?php echo $shipping_cost;?></span>
				</li>
			</ul>
		</div>
	</div>

<?php

	echo do_shortcode('[wooahan_address_fields]');

?>
	<h3><?php _e('결제정보', 'wooahan');?></h3>
	<div id="wooahan-payment">

		<?php if ( $order->needs_payment() ) : ?>
			<ul class="methods">
				<?php
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
		?>
				<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
					<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />

					<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
						<?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?> <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
					</label>
				</li>
		<?php
						//wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
					}
				} else {
					//echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', __( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) . '</li>'; // @codingStandardsIgnoreLine
				}
				?>
			</ul>
		<?php endif; ?>
		<div class="form-agree">
			<input type="checkbox" class="wooahan-checkbox-pay-agree" value="yes"> 위 상품의 구매조건 확인 및 결제진행에 동의 합니다.
		</div>
		<div class="form-row">
			<input type="hidden" name="woocommerce_pay" value="1" />
			<input type="hidden" class="order-id" value="<?php echo $order->get_id();?>">
			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

			<button type="button" class="button alt button-pay" id="place_order"><?php _e('결제하기', 'wooahan');?></button>

			<?php //echo apply_filters( 'woocommerce_pay_order_button_html', '<button type="submit" class="button alt" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>' ); // @codingStandardsIgnoreLine ?>

			<?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
		</div>
	</div>
</form>
</div>