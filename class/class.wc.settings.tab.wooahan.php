<?php
	class WC_Settings_Tab_Wooahan {

		function __construct(){
			add_filter( 'woocommerce_get_settings_wooahan', array( $this, 'wooahan_get_settings') );			
		}



	}

	new WC_Settings_Tab_Wooahan();
?>