jQuery(document).ready(function(){
	jQuery("td").find("a.wooahan-shipping-tracker").click(function(){
		var url = jQuery(this).attr("href");
			url = url.split('/');

		var method 			= url[url.length - 3];
		var corp 			= url[url.length - 2];
		var shipping_number = url[url.length - 1];
		
		if(method == 'shipping-tracking'){
			var trackingUrl = wooahan_get_shipping_tracking_url(corp);

			if(trackingUrl != false){
				trackingUrl = trackingUrl+shipping_number;
			} else {
				alert('죄송합니다. 해당 택배사는 배송조회를 제공하지 않습니다.');
				return false;
			}
			window.open(trackingUrl, '_blank');
		}
		return false;
	});
});

/*

			$tcorp[] = array( 'code' => '26', 'name' => 'USPS' );
 */
function wooahan_get_shipping_tracking_url(corp){
	var url = '';
	switch(corp){
		case '01' : 									// 우체국택배
			url = 'http://service.epost.go.kr/trace.RetrieveRegiPrclDeliv.postal?sid1=';
		break;

		case '18' : 									// 건영택배
			url = 'http://www.kunyoung.com/goods/goods_01.php?mulno=';
		break;

		case '23' : 									// 경동택배
			url = 'http://kdexp.com/basicNewDelivery.kd?barcode=';
		break;

		case '22' : 									// 대신택배
			url = 'http://home.daesinlogistics.co.kr/daesin/jsp/d_freight_chase/d_general_process2.jsp?billno1=';
		break; 

		case '06' : 									// 로젠택배
			url = 'http://d2d.ilogen.com/d2d/delivery/invoice_tracesearch_quick.jsp?slipno=';
		break;

		case '08' : 									// 롯데택배
			url = 'https://www.lotteglogis.com/open/tracking?invno=';
		break;

		case '11' : 									// 일양로지스
			url = 'http://www.ilyanglogis.com/functionality/card_form_waybill.asp?hawb_no=';
		break;

		case '17' : 									// 천일택배
			url = 'http://www.cyber1001.co.kr/kor/taekbae/HTrace.jsp?transNo=';
		break;

		case '05' : 									// 한진택배
			url = 'http://www.hanjin.co.kr/Delivery_html/inquiry/result_waybill.jsp?wbl_num=';
		break;

		case '32' : 									// 합동택배
			url = 'http://www.hdexp.co.kr/parcel/order_result_t.asp?stype=1&p_item=';
		break;

		case '04' : 									// CJ대한통운
			url = 'https://www.doortodoor.co.kr/parcel/doortodoor.do?fsp_action=PARC_ACT_002&fsp_cmd=retrieveInvNoACT&invc_no=';
		break;

		case '46' : 									// CU편의점택배
			url = 'https://www.cupost.co.kr/postbox/delivery/localResult.cupost?invoice_no=';
		break;

		case '56' : 									// KGB택배
			url = 'http://www.kgbls.co.kr//sub5/trace.asp?f_slipno=';
		break;

		case '13' : 									// DHL
			url = 'http://www.dhl.co.kr/ko/express/tracking.html?brand=DHL&AWB=';
		break;

		case '12' : 									// EMS
			url = 'http://service.epost.go.kr/trace.RetrieveEmsTrace.postal?ems_gubun=E&POST_CODE=';
		break;

		case '21' : 									// Fedex
			url = 'http://www.fedex.com/Tracking?cntry_code=kr&language=korean&action=track&tracknumbers=';
		break;

		case '25' : 									// TNT Express
			url = 'http://www.tnt.com/webtracker/tracking.do?respCountry=kr&respLang=ko&searchType=CON&cons=';
		break;

		case '14' : 									// UPS
			url = 'http://www.ups.com/WebTracking/track?loc=ko_KR&InquiryNumber1=';
		break;

		case '26' : 									// USPS
			url = 'https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=';
		break;

	}

	if(!url){
		return false;
	}

	return url;
}