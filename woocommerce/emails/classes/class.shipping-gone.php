<?php
/**
 * Class WC_Email_Customer_Shipping_Gone
 *
 * @package WooCommerce\Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Email_Customer_Shipping_Gone', false ) ) :

	/**
	 * Customer Shipping gone Order Email.
	 *
	 * An email sent to the customer when a new order is paid for.
	 *
	 * @class       WC_Email_Customer_Shipping_Gone
	 * @version     1.0.0
	 * @package     woocommerce/emails/classes/
	 * @extends     WC_Email
	 */
	class WC_Email_Customer_Shipping_Gone extends WC_Email {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id             = 'customer_shipping_gone_order';
			$this->customer_email = true;

			$this->title          = __( '고객님의 주문 상품이 배송중입니다.', 'wooahan' );
			$this->description    = __( 'This is an order notification sent to customers containing order details after payment.', 'woocommerce' );
			$this->template_html  = 'emails/customer-shipping-gone.php';
			$this->template_plain = 'emails/plain/customer-shipping-gone.php';
			$this->placeholders   = array(
				'{site_title}'   => $this->get_blogname(),
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			// Triggers for this email.
			add_action( 'woocommerce_order_status_processing_to_shipping-gone_notification', array( $this, 'trigger' ), 10, 2);
			add_action( 'woocommerce_order_status_shipping-partial_to_shipping-gone_notification', array( $this, 'trigger' ), 10, 2);
		 	add_action( 'woocommerce_order_status_exchange-request_to_shipping-gone_notification', array( $this, 'trigger' ), 10, 2);
			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( '[{site_title}] 고객님의 주문이 현재 배송중 입니다.', 'woocommerce' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( '배송중 알림', 'woocommerce' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int            $order_id The order ID.
		 * @param WC_Order|false $order Order object.
		 */
		public function trigger( $order_id, $order = false ) {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $this->object->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
				$this->placeholders['{order_number}'] = $this->object->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $this,
				)
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'         => $this->object,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => true,
					'email'         => $this,
				)
			);
		}
	}

endif;

return new WC_Email_Customer_Shipping_Gone();
