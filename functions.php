<?php

function wooahan_attribute_save($order_id, $attributes){

    $not_required_arr = array();
    $attribute_arr    = array();
    $count = 0;
    foreach($attributes as $attribute){
        if(isset($attribute['name']) && isset($attribute['value'])){
            $name   = $attribute['name'];
            $values = $attribute['value'];
            $style  = $attribute['style'];
            //$color  = $attribute['color'];
            if(isset($attribute['color'])){
                $color = $attribute['color'];
            } else {
                $color = array();
            }
            if(isset($attribute['thumbnails'])){
                $thumbnails = $attribute['thumbnails'];
            } else {
                $thumbnails = array();
            }

            if(isset($attribute['required']) && $attribute['required'] == "true"){
                $vcount = 0;
                $attribute_arr[sanitize_title($name)] = array(
                    'name' => $name,
                    'value' => join("|", $values),
                    'position' => $count,
                    'is_visible' => 1,
                    'is_variation' => 1,
                    'is_taxonomy' => 0,
                    'style' => $style,
                    'color' => $color,
                    'thumbnails' => $thumbnails
                );
            } else {
                $not_required_arr[sanitize_title($name)] = array(
                    'name' => $name,
                    'value' => join("|", $values),
                    'position' => $count,
                    'is_visible' => 1,
                    'is_variation' => 1,
                    'is_taxonomy' => 0,
                    'style' => $style,
                    'color' => $color,
                    'thumbnails' => $thumbnails
                );
            }
            $count++;
        }

        update_post_meta( $order_id, '_product_attributes', $attribute_arr );
        update_post_meta( $order_id, '_product_not_required_attributes', $not_required_arr ); 
    }
}

function wooahan_order_items($order){
    $new_order_items = array();
    if ( count( $order->get_items() ) > 0 ) {
        foreach($order->get_items() as $item_id => $item ){
            if($item->get_product_id() > 0){
                $product_id = $item->get_product_id();
                $new_order_items[$product_id]['items'][] = array(
                    'variation_id' => $item->get_variation_id(),
                    'quantity'  => $item->get_quantity()
                );
                if(!isset($new_order_items[$product_id]['quantity'])){
                    $new_order_items[$product_id]['quantity'] = 0;
                }
                if(!isset($new_order_items[$product_id]['subtotal'])){
                    $new_order_items[$product_id]['subtotal'] = 0;
                }
                $new_order_items[$product_id]['quantity'] += $item->get_quantity();
                $new_order_items[$product_id]['subtotal'] += $item->get_subtotal();
            }
        }
    }
    return $new_order_items;
}

add_action( 'wp_ajax_ckeditor_image_upload', 'ckeditor_image_upload' );

function ckeditor_image_upload(){

    // 이미지가 업로드될 폴더의 전체 경로입니다. 
    // 여기서는 구현을 간단히 하기 위해서 웹 루트 안에 업로드합니다. 
    $upload_dir = wp_upload_dir();
    $uploadfullPath = $upload_dir['path']."/";

    // 이미지가 웹에서 보여질때 사용되어질 기본 URL입니다. 
    // 웹루트 부터의 절대 URL을 입력합니다. 
    $imageBaseUrl = $upload_dir['url'];

    // 에디터가 만들어진 textarea의 id 값이 넘어옵니다. 
    $CKEditor = $_GET['CKEditor'] ; 

    // 이미지 업로드 후 에디터 내에 이미지를 표시하는데 사용되는 값입니다. 
    // CKEditor의 addFunction으로 추가된 함수를 호출하기 위한 키값입니다. 
    $funcNum = $_GET['CKEditorFuncNum'] ; 

    // 브라우저의 언어코드가 넘어옵니다. (ko) 
    // 필요하다면 파일명 엔코딩 등에 사용되어질 수 있습니다. 
    $langCode = $_GET['langCode'] ; 

    // 업로드후 이미지를보여줄 이미지 url 
    $url = '';

    // 에러가 발생하면 메세지를 보여줍니다. 
    $message = ''; 

    // CKEditor에서 이미지 업로드는 파일 키값으로 upload를 사용합니다. 
    if (isset($_FILES['upload'])) { 
        $name = $_FILES['upload']['name']; 

        // 파일 이름 중복 체크는 없습니다.(실제 구현에는 직접 작성해야 할 것입니다.) 
        move_uploaded_file($_FILES["upload"]["tmp_name"], $uploadfullPath . $name); 

        // 업로드후 이미지를 보여줄 URL 을 만듭니다. 
        $url = $imageBaseUrl ."/". $name ; 
    } else { 
        $message = '업로드된 파일이 없습니다.'; 
    } 

    // 이미지 업로드는 iframe을 사용해서 처리되므로 parent 와 통신하기 위해서 
    // 자바스크립트를 사용합니다. 
    echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$_GET['CKEditorFuncNum']}, '$url', '$message');</script>"; 

    die();
}

add_action( 'wp_ajax_wooahan_create_variations', 'wooahan_create_variations' );

function wooahan_create_variations(){

    $result['status'] = 'failed';

    parse_str($_POST["values"], $output);

    $wooahan = $output['wooahan'];

    $regular_price  = sanitize_text_field($_POST['regular_price']);
    $sale_price     = sanitize_text_field($_POST['sale_price']);

    /* data */
    isset($wooahan['attributes'])       ? $attributes       = sanitize_text_field($wooahan['attributes'])            : $attributes       = '';
    isset($wooahan['merge_type'])       ? $merge_type       = sanitize_text_field($wooahan['merge_type'])            : $merge_type       = 'merge_sep';

    if(isset($output['post_ID'])){
        $post_id = $output['post_ID'];

        update_post_meta($post_id, '_variation_merge_type', $merge_type);

        $handle = new WC_Product_Variable($post_id);
        $variations = $handle->get_children();
        $data   = '';
        foreach($variations as $value){
            $single_variation   = new WC_Product_Variation($value);
            $variation_id       = $single_variation->get_id();
            wp_delete_post($variation_id);
        }

        delete_post_meta($post_id, 'not_req_variations');   


        $attribute_arr = '';


        if(is_array($attributes)){

            // 먼저 attributes 를 메타에 집어 넣는다.
            wooahan_attribute_save($post_id, $attributes);

            /**
            * link all variations
            * 조합 일체선택형 과 조합 분리선택형에서 사용, 나머지는 따로 분기
            */

            if ( ! current_user_can( 'edit_products' ) ) {
                wp_die( -1 );
            }

            wc_maybe_define_constant( 'WC_MAX_LINKED_VARIATIONS', 49 );
            wc_set_time_limit( 0 );

            if ( ! $post_id ) {
                wp_die();
            }

            $product    = wc_get_product( $post_id );
            $attributes = wc_list_pluck( array_filter( $product->get_attributes(), 'wc_attributes_array_filter_variation' ), 'get_slugs' );

            if ( ! empty( $attributes ) ) {
                $test = 'attributes_ok';
                // Get existing variations so we don't create duplicates.
                $existing_variations = array_map( 'wc_get_product', $product->get_children() );
                $existing_attributes = array();

                foreach ( $existing_variations as $existing_variation ) {
                    $test = 'exisiting_variations_ok';
                    $existing_attributes[] = $existing_variation->get_attributes();
                }

                $added               = 0;
                $possible_attributes = array_reverse( wc_array_cartesian( $attributes ) );

                foreach ( $possible_attributes as $possible_attribute ) {
                    if ( in_array( $possible_attribute, $existing_attributes ) ) {
                        continue;
                    }
                    $test = 'possible_attributes_ok';
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id( $post_id );
                    $variation->set_attributes( $possible_attribute );

                    $variation_id = $variation->get_id();

                    do_action( 'product_variation_linked', $variation->save() );

                    if ( ( $added ++ ) > WC_MAX_LINKED_VARIATIONS ) {
                        break;
                    }
                }
            }

            $data_store = $product->get_data_store();
            $data_store->sort_all_product_variations( $product->get_id() );


            /**
            * 추가옵션 (_product_not_required_attributes) 처리
            */

            $not_requred_attributes = get_post_meta( $post_id, '_product_not_required_attributes', true );

            if($not_requred_attributes){
                $attributes     = array();
                $test = 'not_required_ok';
                foreach($not_requred_attributes as $value){

                    $meta_key   = sanitize_title($value['name']);
                    $meta_key   = strtolower($meta_key);
                    $values_arr = explode("|", $value['value']);
                    $variation_ids  = array();
                    foreach($values_arr as $option_value){

                        $attribute = array(
                            $meta_key => trim($option_value)
                        );

                        $variation = new WC_Product_Variation();
                        $variation->set_parent_id( $post_id );
                        $variation->set_attributes( $attribute );
                        $variation_id = $variation->save();
                        $variation_ids[] = array(
                            'variation_id' => $variation_id,
                            'option_title' => trim($option_value),
                            'is_active'    => true,
                            'is_in_stock'  => true
                        );
                        update_post_meta($variation_id, 'not_req', 'yes');
                        update_post_meta($variation_id, 'not_req_title', $value['name'].'-'.trim($option_value));
                    }
                    $attributes[$meta_key]                          = $variation_ids;
                    $attributes[$meta_key]['details']['style']      = $value['style'];
                    $attributes[$meta_key]['details']['color']      = $value['color'];
                    $attributes[$meta_key]['details']['thumbnails'] = $value['thumbnails'];
                }

                update_post_meta($post_id, 'not_req_variations', $attributes);

            }


            $handle = new WC_Product_Variable($post_id);
            $variations = $handle->get_children();
            $data   = '';
            foreach($variations as $value){
                $single_variation = new WC_Product_Variation($value);
                $variation_id = $single_variation->get_id();
                update_post_meta($variation_id, '_regular_price', $regular_price);
                update_post_meta($variation_id, '_sale_price', $sale_price);
            }

            update_post_meta($post_id, '_wooahan_regular_price', $regular_price);
            update_post_meta($post_id, '_wooahan_sale_price', $sale_price);

            $result['status'] = 'success';
            $result['data'] = wooahan_get_products($post_id);
            $result['post_id'] = $test;

        }

    }

    echo json_encode($result);
    die();  

}


add_action( 'wp_ajax_wooahan_add_category', 'wooahan_add_category' );

function wooahan_add_category(){

    $result             = array();
    $result['status']   = 'failed';
    $value              = sanitize_text_field($_POST['val']);
    $post_id            = sanitize_text_field($_POST['id']);
    $parent             = 0;

    if(!term_exists($value, 'product_cat')){
        wp_insert_term(
            $value,
            'product_cat',
            array(
                'parent' => 0
            )
        );
        $term_id = term_exists($value, 'product_cat');
        if($term_id){
            $result['status'] = 'success';
            $terms = get_terms('product_cat', array( 'hide_empty' => false, 'fields' => 'all', 'parent' => 0));
            $data  = '';

            foreach($terms as $list){
                if($list->name != '미분류'){
                    $data .= '<div class="form-check">';
                    $data .= '<input class="form-check-input" type="checkbox" value="" id="defaultCheck1">';
                    $data .= '<label class="form-check-label" for="defaultCheck1">';
                    $data .= $list->name;
                    $data .= '</label>';
                    $data .= '</div>';
                }
            }
            
            $result['data'] = $data;
        } else {
            $result['message'] = __('문제가 발생하였습니다.', 'wooahan');
        }
    } else {
        $result['message'] = __('이미 존재하는 분류 입니다.', 'wooahan');
    }

    echo json_encode($result);

    die();    
}

add_action( 'wp_ajax_wooahan_update_variation', 'wooahan_update_variation' );

function wooahan_update_variation(){

    global $wpdb;

    $result['status'] = 'failed';

    parse_str($_POST["values"], $output);

    $wooahan = $output['wooahan'];
    $variation_id = $_POST['variation_id'];

    if(!is_array($variation_id)){
        $variations[] = $variation_id;
    } else {
        $variations = $variation_id;
    }
    /* data */
    isset($wooahan['manage_options'])       ? $options      = $wooahan['manage_options']            : $options      = '';

    $post_id = $output['post_ID'];

    if($options){
        foreach($options as $key => $value){

            $post = get_post( $key );

            $old_status = $post->post_status;

            isset($value['soldout_display']) ? $stock_status = 'outofstock' : $stock_status = 'instock';

            if(in_array($key, $variations)){
                update_post_meta($key, '_sku', $value['sku']);
                update_post_meta($key, '_manage_stock', $value['manage_stock']);
                update_post_meta($key, '_backorders', $value['backorders']);
                update_post_meta($key, '_stock_count', $value['stock_count']);
                update_post_meta($key, '_stock', $value['variable_stock']);
                update_post_meta($key, '_safe_stock', $value['safe_stock']);
                update_post_meta($key, '_stock_status', $stock_status);
                update_post_meta($key, '_add_price', $value['add_price']);
                update_post_meta($key, '_added_price', $value['added_price']);

                if(isset($value['enabled']) && $value['enabled'] == 'yes'){
                    $wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => $key ) );
                } else {
                    $wpdb->update( $wpdb->posts, array( 'post_status' => 'private' ), array( 'ID' => $key ) );
                }

                $real_price = get_post_meta($post_id, '_wooahan_sale_price', true);
                $variation_price = get_post_meta($key, '_sale_price', true);
                $notreq_variations = get_post_meta($post_id, 'not_req_variations', true);

                if($notreq_variations){
                    foreach($notreq_variations as $k => $notreq_variation){
                        foreach($notreq_variation as $vk => $variation){
                            if($variation['variation_id'] == $key){
                                $stock_status = get_post_meta($key, '_stock_status', true);
                                if($stock_status == 'outofstock'){
                                    $stock_status = 'false';
                                } else {
                                    $stock_status = 'true';
                                }
                                $notreq_variations[$k][$vk]['is_in_stock'] = $stock_status;
                            }
                        }
                    }
                    update_post_meta($post_id, 'not_req_variations', $notreq_variations);
                }

                if($real_price != $variation_price){
                    $real_price = $variation_price;
                }

                switch($value['add_price']){
                    case 'plus' :
                        $reset_price = $real_price + $value['added_price'];
                    break;
                    case 'minus' :
                        $reset_price = $real_price - $value['added_price'];
                    break;
                }
                if($value['added_price'] > 0){
                    update_post_meta($key, '_sale_price', $reset_price);
                }
            }
        }
    }

    $result['data'] = wooahan_get_products($post_id);

    $result['status'] = 'success';


    echo json_encode($result);
    die();    
}

add_action( 'wp_ajax_wooahan_delete_variation', 'wooahan_delete_variation' );

function wooahan_delete_variation(){
    $result['status']   = 'succss';
    $variation_ids      = sanitize_text_field($_POST['variation_id']);
    $product_id         = sanitize_text_field($_POST['product_id']);

    if(!is_array($variation_id)){
        $variations[] = $variation_id;
    } else {
        $variations = $variation_id;
    }

    foreach($variation_ids as $variation_id){
        wp_delete_post($variation_id);
    }

    /**
    * 추가옵션 (_product_not_required_attributes) 처리
    */

    $not_requred_variations = get_post_meta( $product_id, 'not_req_variations', true );
    $new_variations = array();
    foreach($not_requred_variations as $key => $variations){
        foreach($variations as $k => $variation){
            if(get_post($variation['variation_id'])){
                $new_variations[$key][] = array(
                    'variation_id' => $variation['variation_id'],
                    'option_title' => $variation['option_title'],
                    'is_active'    => $variation['is_active'],
                    'is_in_stock'  => $variation['is_in_stock']
                );
            }
        }
    }

    update_post_meta($product_id, 'not_req_variations', $new_variations);

    $result['data'] = wooahan_get_products($product_id);
    $result['status'] = 'success';
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_remove_custom_badge', 'wooahan_remove_custom_badge' );

function wooahan_remove_custom_badge(){
    $result['status'] = 'failed';
    $badge_url        = sanitize_text_field($_POST['badge_url']);

    $wooahanBadge = new wooahanBadge();
    $wooahanBadge->removeCustomBadge($badge_url);

    $result['status'] = 'success';
    $result['data']   = $wooahanBadge->allBadges();

    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_custom_badge_upload', 'wooahan_custom_badge_upload' );

function wooahan_custom_badge_upload(){
    $result['status'] = 'failed';
    if(isset($_FILES['badge_file'])){

        $title      = sanitize_text_field($_POST['badge_title']);
        $size       = sanitize_text_field($_POST['badge_size']);

        $upload     = wp_upload_dir();
        $upload_dir = $upload['basedir'] . '/wooahan';

        if( !is_dir($upload_dir) ){
            mkdir( $upload_dir, 0700 );
        }

        $upload_dir = $upload['basedir'] . '/wooahan/custom-badges';
        $link_url   = $upload['baseurl'] . '/wooahan/custom-badges';
        if( !is_dir($upload_dir) ){
            mkdir( $upload_dir, 0700 );
        }

        $allowed_ext = array( 'jpg', 'jpeg', 'png', 'gif' );
        $error       = $_FILES['badge_file']['error'];
        $name        = sanitize_file_name($_FILES['badge_file']['name']);
        $nameArr     = explode(".", $name);
        
        // 이름 중복을 막기 위해 유니크한 숫자로 변경한다.
        $realName    = date('YmdHis');

        $ext         = array_pop(explode(".", $name));

        if( $error != UPLOAD_ERR_OK ){
            switch( $error ){
                case UPLOAD_ERR_INI_SIZE :
                case UPLOAD_ERR_FORM_SIZE :
                    $result['message'] = __('파일이 너무 큽니다.', 'wooahan');
                break;

                case UPLOAD_ERR_NO_FILE :
                    $result['message'] = __('파일이 제대로 첨부되지 않았습니다.', 'wooahan');
                break;

                default :
                    $result['message'] = __('파일이 제대로 업로드되지 않았습니다.', 'wooahan');
                break;
            }
        } else {

            if(!in_array($ext, $allowed_ext)){
                $result['message'] = __('허용되지 않는 확장자 입니다.', 'wooahan');
            } else {

                $status = move_uploaded_file( $_FILES['badge_file']['tmp_name'], $upload_dir.'/'.$realName.".".$ext );

                if($status == true){
                    $badge_url = $link_url.'/'.$realName.".".$ext;
                    $wooahanBadge = new wooahanBadge();
                    $addStatus = $wooahanBadge->addCustomBadge($title, $badge_url, $size);

                    if($addStatus == false){
                        $result['message'] = __('이미 등록된 뱃지 입니다. (이미지 경로가 같은 경우에도 등록된 뱃지로 처리 됩니다.)', 'wooahan');
                    } else {
                        $result['status']   = 'success';
                        $result['data']     = $wooahanBadge->allBadges();
                    }
                }
            }
            
        }
    }
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_get_variations', 'wooahan_get_variations' );

function wooahan_get_variations(){
    $result['status']   = 'succss';
    $product_id         = sanitize_text_field($_POST['product_id']);

    $result['data'] = wooahan_get_products($product_id);
    $result['status'] = 'success';
    echo json_encode($result); 
    die();   
}

add_action( 'wp_ajax_wooahan_return_product_received', 'wooahan_return_product_received' );

function wooahan_return_product_received(){
    $result['status'] = 'failed';
    $order_id         = sanitize_text_field($_POST['order_id']);
    $status           = update_post_meta($order_id, 'wooahan_return_product_received', 'yes');
    if($status){
        $result['status'] = 'success';
    } else {
        $result['message'] = __('반품/교환 물품 수령 처리에 문제가 발생하였습니다.', 'wooahan');
    }
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_get_tracking_list', 'wooahan_get_tracking_list' );

function wooahan_get_tracking_list(){
    $result['status']   = 'failed';

    $corp               = sanitize_text_field($_POST['corp']);
    $number             = sanitize_text_field($_POST['number']);

    $tracking           = new wooahanOrderTracking();
    $tracking->corp     = $corp;
    $data               = $tracking->getCURL($number);
    $data               = json_decode($data);
    if($data->status == 'success'){
        $result['status'] = 'success';
        $result['data']   = array_reverse($data->data->trackingDetails);
        $result['corp']   = $corp;
        $result['complete'] = $data->data->complete;
    } else {
        $result['message'] = $data->message;
    }
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_csv_shipping_number_regist', 'wooahan_csv_shipping_number_regist' );

function wooahan_csv_shipping_number_regist(){
    $result['status'] = 'failed';
    if(isset($_FILES['file'])){
        if($_FILES['file']['error'] > 0){
            $result['message'] = __('CSV 업로드 에러 : ', 'wooahan').$_FILES['file']['error'];
        } else {
            $shipping   = new wooahanShipping();
            $tmpName    = $_FILES['file']['tmp_name'];
            $csvAsArray = array_map('str_getcsv', file($tmpName));
            $testArray = array();
            if(is_array($csvAsArray)){
                $csvCount     = 0;
                $successCount = 0;
                $errors       = array();
                foreach($csvAsArray as $row){
                    $order_id = $row[0];    // 주문번호
                    $code     = $row[1];    // 택배사코드
                    $number   = $row[2];    // 운송장번호
                    $exclude  = $row[3];    // 미배송 옵션 아이디
                    $exclude  = explode(",", $exclude); // 콤마단위로 구분해서 배열로 전환
                    $data     = array(
                        'code'      => $code,
                        'number'    => $number,
                        'exclude'   => $exclude
                    );
                    
                    $return   = $shipping->Regist($order_id, $data);
                    if($return->status == true){
                        $successCount++;
                    } else {
                        $errors[] = $return->message;
                    }
                    
                    $csvCount++;
                }
            }
            $result['result_msg'] = '총 '.$csvCount.'건의 주문 중 '.$successCount.'건의 주문이 처리되었습니다.';
            if($successCount == $csvCount){
                $result['status'] = 'success';  
            } else {
                $result['result_msg'] .= "\n\n\n-- 오류내용 --\n\n";
                $result['result_msg'] .= join("\n", $errors);
            }
        }
    }
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_shipping_number_regist', 'wooahan_shipping_number_regist' );

function wooahan_shipping_number_regist(){

    $result['status'] = 'failed';

    $shipping        = new wooahanShipping();
    $order_id        = sanitize_text_field($_POST['order_id']);
    $data_args       = array(
        'code'      => sanitize_text_field($_POST['corp']),
        'number'    => sanitize_text_field($_POST['number']),
        'item_ids'  => sanitize_text_field($_POST['item_ids'])
    );
    $return = $shipping->Regist($order_id, $data_args);

    if($return->status == true){
        $result['status']           = 'success';
        $result['shipping_status']  = $return->shipping_status;
        $result['shipping_number']  = get_post_meta($order_id, 'wooahan_shipping_number', true);
    } else {
        $result['message']          = $return->message;
    }

    echo json_encode($result);

    die();
}

add_action( 'wp_ajax_wooahan_insert_note', 'wooahan_insert_note' );

function wooahan_insert_note(){

    $current_user = wp_get_current_user();

    $result['status'] = 'failed';
    $order_id   = sanitize_text_field($_POST['order_id']);
    $memo       = sanitize_text_field($_POST['memo']);
    $memo_type  = sanitize_text_field($_POST['memo_type']);

    if($memo_type == 'customer'){
        $is_customer_note = 1;
    } else {
        $is_customer_note = 0;
    }

    $_order     = new WC_Order( $order_id );
    $comment_id = $_order->add_order_note( $memo, $is_customer_note, $current_user->user_login );

    if($comment_id){
        $result['status'] = 'success';

    } else {
        $result['message'] = __('메모 등록에 문제가 발생하였습니다.', 'wooahan');
    }
    $orderList = new wooahanOrderList();
    $memo      = $orderList->get_private_order_notes( $order_id );
    $result['memo'] = $memo;

    echo json_encode($result);
    die();    
}

add_action( 'wp_ajax_wooahan_remove_note', 'wooahan_remove_note' );

function wooahan_remove_note(){
    $result['status'] = 'failed';

    $comment_id = sanitize_text_field($_POST['note_id']);
    $order_id   = sanitize_text_field($_POST['order_id']);

    $delete = wp_delete_comment( $comment_id, true);

    if($delete){
        $result['status'] = 'success';
    } else {
        $result['message'] = __('메모 삭제에 문제가 발생하였습니다.', 'wooahan');
    }

    $orderList = new wooahanOrderList();

    $memo      = $orderList->get_private_order_notes( $order_id );
    $result['memo'] = $memo;

    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_order_remove', 'wooahan_order_remove' );

function wooahan_order_remove(){
    $order_ids = $_POST['order_ids'];
    $result['status'] = 'failed';
    foreach($order_ids as $key => $order_id){
        $oid = sanitize_text_field($order_ids[$key]);
        wp_delete_post( $oid, true );
    }
    $result['status'] = 'success';
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_order_status_change', 'wooahan_order_status_change' );

function wooahan_order_status_change(){
    $result['status'] = 'failed';
    $orders         = $_POST['orders'];
    $order_status   = sanitize_text_field($_POST['order_status']);

    if(is_array($orders)){

        foreach($orders as $order){
            $_order = new WC_Order($order);
            $payment_method = $_order->get_payment_method();
            if($payment_method == ''){
                update_post_meta( $order, '_payment_method', 'programmatically' );
            }
            if(!empty($_order)){
                $_order->update_status( $order_status );
            }
        }

        $result['status'] = 'success';
    } else {
        $result['message'] = __('주문이 선택되지 않았습니다.', 'wooahan');
    }

    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_get_wooahan_orders', 'get_wooahan_orders' );

function get_wooahan_orders(){
    $result['status'] = 'failed';
    $Orders = new wooahanOrderList();
    if($Orders){
        $page               = sanitize_text_field($_POST['page']);
        $posts_per_page     = sanitize_text_field($_POST['posts_per_page']);
        $search             = sanitize_text_field($_POST['search']);
        $order_status       = sanitize_text_field($_POST['order_status']);

        $result['status']   = 'success';
        $result['data']     = $Orders->getList($order_status, array($page, $posts_per_page), $search);
        $result['count']    = $Orders->getListCount($search);
        $result['orderCount'] = $Orders->get_order_count();
    }
    echo json_encode($result);
    die();
}

add_action( 'wp_ajax_wooahan_change_permalink', 'wooahan_change_permalink' );

function wooahan_change_permalink(){

    $return['status'] = 'failed';

    $post_name  = sanitize_text_field($_POST['post_name']);
    $post_id    = sanitize_text_field($_POST['post_id']);

    $update_post = array(
        'ID'        => $post_id,
        'post_name' => $post_name
    );

    $result = wp_update_post( $update_post );

    if($result){
        $return['status'] = 'success';
        $return['permalink'] = str_replace( $post_name."/", "", urldecode(get_the_permalink($post_id)));
        $return['post_name'] = $post_name;
    }

    echo json_encode($return);

    die();
}

add_action( 'wp_ajax_wooahan_get_option_templates', 'wooahan_get_option_templates' );

function wooahan_get_option_templates(){
    $templates      = get_option( 'wooahan_option_templates' );
    echo json_encode($templates);
    die();
}

add_action( 'wp_ajax_wooahan_option_template_remove', 'wooahan_option_template_remove' );

function wooahan_option_template_remove(){
    $templates      = get_option( 'wooahan_option_templates' );
    $checked_key    = $_POST['checked'];
    $new_templates  = array();
    foreach($templates as $key => $template){
        if(in_array($key, $checked_key) != true){
            $new_templates[] = $template;
        }
    }
    update_option( 'wooahan_option_templates', $new_templates );
    echo json_encode($new_templates);
    die();
}

add_action( 'wp_ajax_wooahan_option_template_regist', 'wooahan_option_template_regist' );
function wooahan_option_template_regist(){
    $template_code      = sanitize_text_field($_POST['code']);
    $template_name      = sanitize_text_field($_POST['name']);
    $template_desc      = sanitize_text_field($_POST['desc']);

    if($template_code == ''){
        $template_code = '00'+date('His');
    }


    $template_options   = $_POST['options'];

    $templates      = get_option( 'wooahan_option_templates' );

    $new_templates = array(
        'code'      => $template_code,
        'name'      => $template_name,
        'desc'      => $template_desc,
        'options'   => $template_options,
        'date'      => date('Y-m-d')
    );

    if(!$templates){
        $templates   = array();
        $templates[] = $new_templates;
    } else {
        $templates[] = $new_templates;
    }

    update_option( 'wooahan_option_templates', $templates );
    echo json_encode($templates);
    die();
}

add_action( 'wp_ajax_wooahan_variation_add_to_cart', 'wooahan_variation_add_to_cart' );
add_action( 'wp_ajax_nopriv_wooahan_variation_add_to_cart', 'wooahan_variation_add_to_cart' );

function wooahan_variation_add_to_cart(){
    global $woocommerce;
    $result['status'] = 'failed';
    parse_str($_POST["data"], $data);
    $variations = $data['wooahan']['variations'];
    $product_id = $data['product_id'];
    if($variations){
        foreach($variations as $variation){
            $woocommerce->cart->add_to_cart( $product_id, $variation['quantity'], $variation['variation_id'] );
        }
    } else {
        $woocommerce->cart->add_to_cart( $product_id, $data['quantity'] );
    }
    $result['status'] = 'success';
    $result['message'] = __("장바구니에 상품을 담았습니다.\n장바구니로 이동하시겠습니까?", "wooahan");
    $result['callback'] = $woocommerce->cart->get_cart_url();
    $result['quantity'] = $data;
    echo json_encode($result);

    die();
}

add_action( 'wp_ajax_wooahan_save_shipping_info', 'wooahan_save_shipping_info' );
add_action( 'wp_ajax_nopriv_wooahan_save_shipping_info', 'wooahan_save_shipping_info' );

function wooahan_save_shipping_info(){

    global $user_ID;

    $user = wp_get_current_user();

    if($_POST['agree'] == true){

        $payment_method = sanitize_text_field($_POST['payment_method']);

        $post_receiver  = sanitize_text_field($_POST['receiver']);
        $post_phone1    = sanitize_text_field($_POST['phone1']);
        $post_address_1 = sanitize_text_field($_POST['address1']);
        $post_address_2 = sanitize_text_field($_POST['address2']);
        $post_postcode  = sanitize_text_field($_POST['postcode']);

        $address = array(
            'first_name' => $post_receiver,
            'phone'      => $post_phone1,
            'address_1'  => $post_address_1,
            'address_2'  => $post_address_2,
            'postcode'   => $post_postcode
        );

        if(is_user_logged_in()){
            

            $post_phone2    = sanitize_text_field($_POST['phone2']);
            $post_location  = sanitize_text_field($_POST['location']);
            $post_memo      = sanitize_text_field($_POST['memo']);

            if($_POST['locationAdd'] == true){
                $shipping_locations = get_user_meta($user_ID, 'wooahan_shipping_locations', true);

                if($_POST['defaultChk'] == true){
                    foreach($shipping_locations as $key => $location){
                        $shipping_locations[$key]['default'] = false;
                    }
                }

                $default_check = $_POST['defaultChk'];

                $duplicated_key = null;

                foreach($shipping_locations as $key => $location){
                    if($location['shipping_title'] == $_POST['location']){
                        $duplicated_key = $key;
                    }
                }

                if(!empty($duplicated_key)){
                    $shipping_locations[$duplicated_key] = array(
                        'default'        => $default_check,
                        'shipping_title' => $post_location,
                        'receiver'       => $post_receiver,
                        'phone1'         => $post_phone1,
                        'phone2'         => $post_phone2,
                        'postcode'       => $post_postcode,
                        'address1'       => $post_address_1,
                        'address2'       => $post_address_2,
                        'memo'           => $post_memo
                    );
                } else {
                    $shipping_locations[] = array(
                        'default'        => $default_check,
                        'shipping_title' => $post_location,
                        'receiver'       => $post_receiver,
                        'phone1'         => $post_phone1,
                        'phone2'         => $post_phone2,
                        'postcode'       => $post_postcode,
                        'address1'       => $post_address_1,
                        'address2'       => $post_address_2,
                        'memo'           => $post_memo
                    );
                }


                update_user_meta($user_ID, 'wooahan_shipping_locations', $shipping_locations);
            }
        }

        $order_id = sanitize_text_field($_POST['order_id']);
        $order = new WC_Order( $order_id );

        if(is_user_logged_in()){
            $order->set_customer_id( $user_ID );
        }

        $order->set_address( $address, 'billing' );
        $order->set_address( $address, 'shipping' );

        if($_POST['memo']){
            $order->add_order_note( $post_memo );
        }


        // payment method 를 업데이트 해줘야함 (iamport 오류)
        update_post_meta( $order_id, '_payment_method', $payment_method );
        if(is_user_logged_in()){
            $user_email = $user->user_email;
        } else {
            $user_email = '';
        }
        update_post_meta( $order_id, '_customer_email', $user_email);
        update_post_meta( $order_id, '_shipping_email', $user_email);


        $order->save();

    } else {

    }

    die();
}



add_action( 'wp_ajax_wooahan_direct_buy', 'wooahan_direct_buy' );
add_action( 'wp_ajax_nopriv_wooahan_direct_buy', 'wooahan_direct_buy' );

function wooahan_direct_buy(){

    //check_ajax_referer( 'wooahan', 'security' );

    global $woocommerce;

    parse_str($_POST["data"], $output);

    $is_variable      = $output['is_variable'];
    $product_id       = $output['product_id'];
    $result           = array();
    $result['status'] = 'failed';
    $result['callback']    = '';

    if($is_variable == 'true'){

        $variations = $output['wooahan']['variations'];

        $order = wc_create_order();


        $delivery_zones = WC_Shipping_Zones::get_zones();
        $min_amount     = 0;
        $shipping_cost  = 0;
        $flat_rate      = array();
        $free_rate      = array();
        foreach ((array) $delivery_zones as $key => $the_zone ) {
          //echo $the_zone['zone_name'];
          //echo "<br/>";
          foreach ($the_zone['shipping_methods'] as $value) {
            if($value->id == 'flat_rate'){
                $shipping_cost  = $value->cost;
                $flat_rate      = $value;
            }
            if($value->id == 'free_shipping'){
                $min_amount = $value->min_amount;
            }
          }
          break;
        }


        foreach($variations as $variation){
            $_product = get_product($variation['variation_id']);
            $args = array(
                'name' => $_product->get_name(),
                'tax_class' => $_product->get_tax_class(),
                'product_id' => $product_id,
                'variation_id' => $variation['variation_id'],
                'variation' => $_product->get_attributes(),
                'subtotal' => wc_get_price_excluding_tax( $_product, array( 'qty' => $variation['quantity'] ) ),
                'total' => wc_get_price_excluding_tax( $_product, array( 'qty' => $variation['quantity'] ) ),
                'quantity' => $variation['quantity']
            );
            $order->add_product( get_product( $product_id ), 1, $args );
        }

        $order->calculate_totals();

        if( ($min_amount != 0 && $order->get_total() < $min_amount) || ( $min_amount == 0) ){
            $item = new WC_Order_Item_Shipping();
            $item->set_props(array('method_id' => $rate->id, 'total' => wc_format_decimal($flat_rate->cost) ) );
            $order->add_item($item);
            $order->calculate_totals();
            $order->save();
        }

        if($order){
            $result['status']      = 'success';
            // 개인정보, 배송, 청구 주소등 변경하는 페이지를 먼저 불러온 후 콜백을 넘겨준다.
            // 상품 타입에 맞게 배송/청구 폼은 나타나지 않을수도 있다.
            // 커스텀 페이지 등록을 위해 플러그인 activation 할때 미리 포스트를 생성한다.
            $result['step'] = 1;
            $result['url'] = get_the_permalink(get_option('wooahan_form_fields_id', true)).'?callback='.base64_encode($order->get_checkout_payment_url().'&order_id='.$order->get_id());
            $result['callback']    = $order->get_checkout_payment_url().'&order_id='.$order->get_id();
        }

    } else {
        // 단일상품
        $order      = wc_create_order();
        $quantity   = $output['quantity'];

        $delivery_zones = WC_Shipping_Zones::get_zones();
        $min_amount     = 0;
        $shipping_cost  = 0;
        $flat_rate      = array();
        $free_rate      = array();
        foreach ((array) $delivery_zones as $key => $the_zone ) {
          //echo $the_zone['zone_name'];
          //echo "<br/>";
          foreach ($the_zone['shipping_methods'] as $value) {
            if($value->id == 'flat_rate'){
                $shipping_cost  = $value->cost;
                $flat_rate      = $value;
            }
            if($value->id == 'free_shipping'){
                $min_amount = $value->min_amount;
            }
          }
          break;
        }

        $order->add_product( get_product( $product_id ), $quantity );
        $order->calculate_totals();

        if( ($min_amount != 0 && $order->get_total() < $min_amount) || ( $min_amount == 0) ){
            $item = new WC_Order_Item_Shipping();
            $item->set_props(array('method_id' => $rate->id, 'total' => wc_format_decimal($flat_rate->cost) ) );
            $order->add_item($item);
            $order->calculate_totals();
            $order->save();
        }

        if($order){
            $result['status']      = 'success';
            $result['step'] = 1;
            $result['url'] = get_the_permalink(get_option('wooahan_form_fields_id', true)).'?callback='.base64_encode($order->get_checkout_payment_url().'&order_id='.$order->get_id());
            $result['callback']    = $order->get_checkout_payment_url().'&order_id='.$order->get_id();
        }

    }

    echo json_encode($result);

    die();
}

function create_variation_json($product_id, $type, $attributes){

    $json           = array();

    $handle         = new WC_Product_Variable($product_id);
    $variations     = $handle->get_children();

    foreach($variations as $value){
        $single_variation   = new WC_Product_Variation($value);
        $variation_id       = $single_variation->get_id();
        if(get_post_meta($variation_id, 'not_req', true) != 'yes'){
            $variation_title    = implode("-", $single_variation->get_variation_attributes());
            $json[] = array(
                'type'  => $type,
                'title' => $variation_title,
                'variation_id' => $variation_id
            );
        }
    }

    return $json;
}

function wooahan_variation_html($product_id, $type, $attributes){

    $handle         = new WC_Product_Variable($product_id);
    $variations     = $handle->get_children();

    $html           = '';
    $option_html    = '';
    $not_req_attributes = get_post_meta($product_id, '_product_not_required_attributes', true);
    $not_req_variations = get_post_meta($product_id, 'not_req_variations', true);
    //print_r($not_req_variations);
    switch($type){
        case 1 :
            foreach($variations as $value){
                $single_variation   = new WC_Product_Variation($value);
                $variation_id       = $single_variation->get_id();
                $variation_title    = implode("-", $single_variation->get_variation_attributes());
                $option_html       .= '<li class="option" data-option="'.$variation_title.'" variation-id="'.$variation_id.'">';
                $option_html       .= '<input type="radio" name="radio" id="variation-'.$variation_id.'" class="option-check">';
                $option_html       .= '<label for="variation-'.$variation_id.'">';
                $option_html       .= $variation_title;
                $option_html       .= '</label>';
                $option_html       .= '</li>';
            }
            $html = '<div class="select-wrapper"><div class="select-text" data-title="옵션 선택">옵션 선택<span class="dashicons dashicons-arrow-down-alt2"></span></div><ul class="variation-ul">'.$option_html.'</ul></div>';
        break;

        case 2 :
            $cnt         = 0;
            $radio_count = 0;
            foreach( $attributes as $attribute_name => $options ){
                $option_html = '';
                foreach($options as $option){
                    $option_html .= '<li class="option" data-option="'.$option.'">';
                    $option_html .= '<input type="radio" name="radio'.$radio_count.'" id="variation-'.$cnt.'" class="option-check">';
                    $option_html .= '<label for="variation-'.$cnt.'">';
                    $option_html .= $option;
                    $option_html .= '</label>';
                    $option_html .= '</li>';
                    $cnt++;
                }

                $html .= '<div class="select-wrapper"><div class="select-text" data-title="'.$attribute_name.'">'.$attribute_name.'<span class="dashicons dashicons-arrow-down-alt2"></span></div><ul class="variation-ul">'.$option_html.'</ul></div>';

                $radio_count++; 
            }

            if(is_array($not_req_variations)){
                $radio_count = 0;
                $option_html = '';
                foreach( $not_req_variations as $attribute_name => $notreq_options ){
                    foreach($notreq_options as $option){
                        $variation_id       = $option['variation_id'];
                        $variation_title    = $option['option_title'];
                        $option_html .= '<li class="option" data-option="'.$variation_title.'" variation-id="'.$variation_id.'">';
                        $option_html .= '<input type="radio" name="radio'.$radio_count.'" id="variation-'.$variation_id.'" class="option-check">';
                        $option_html .= '<label for="variation-'.$variation_id.'">';
                        $option_html .= $variation_title;
                        $option_html .= '</label>';
                        $option_html .= '</li>';
                        $cnt++;
                    }
                }
                $html .= '<div class="select-notreq-wrapper"><div class="select-text" data-title="'.urldecode($attribute_name).'">'.urldecode($attribute_name).'<span class="dashicons dashicons-arrow-down-alt2"></span></div><ul class="variation-ul">'.$option_html.'</ul></div>';                
            }
        break;
    }

    return $html;
}


/**
* Get Product Variations by JSON
*/
function wooahan_get_products($product_id){
    if(!$product_id){
        return false;
    }

    $handle = new WC_Product_Variable($product_id);
    $variations = $handle->get_children();
    $data   = '';
    foreach($variations as $value){
        $single_variation           = new WC_Product_Variation($value);
        $variation_id               = $single_variation->get_id();
        $sku                        = get_post_meta($variation_id, '_sku', true);
        $manage_stock               = get_post_meta($variation_id, '_manage_stock', true);
        $backorders                 = get_post_meta($variation_id, '_backorders', true);
        $stock_count                = get_post_meta($variation_id, '_stock_count', true);
        $stock                      = get_post_meta($variation_id, '_stock', true);
        $safe_stock                 = get_post_meta($variation_id, '_safe_stock', true);
        $stock_status               = get_post_meta($variation_id, '_stock_status', true);
        $add_price                  = get_post_meta($variation_id, '_add_price', true);
        $added_price                = get_post_meta($variation_id, '_added_price', true);
        $origin_sale_price          = get_post_meta($product_id, '_sale_price', true);
        $origin_regular_price       = get_post_meta($product_id, '_regular_price', true);
        $variation_sale_price       = get_post_meta($variation_id, '_sale_price', true);
        $variation_regular_price    = get_post_meta($variation_id, '_regular_price', true);
        $wooahan_sale_price         = get_post_meta($product_id, '_wooahan_sale_price', true);
        $wooahan_regular_price      = get_post_meta($product_id, '_wooahan_regular_price', true);
        $not_req                    = get_post_meta($variation_id, 'not_req', true);
        $not_req_title              = get_post_meta($variation_id, 'not_req_title', true);
        $color                      = get_post_meta($variation_id, '_option_color', true);
        $variation_post             = get_post($variation_id);
        $post_status                = $variation_post->post_status;

        if($post_status == 'publish'){
            $enabled =  true;
        } else {
            $enabled = false;
        }

        if($not_req == 'yes'){
            $variation_title   = $not_req_title;
        } else {
            $variation_title   = implode("/", $single_variation->get_variation_attributes());
        }

        if($manage_stock == 'yes'){
            $manage_stock      = 'checked';
        } else {
            $manage_stock      = '';
        }

        if(!$origin_sale_price){
            $origin_sale_price  = $wooahan_sale_price;
        }
        if(!$origin_regular_price){
            $origin_regular_price   = $wooahan_regular_price;
        }

        if(!$origin_sale_price){
            $origin_price = $origin_regular_price;
        } else {
            $origin_price = $origin_sale_price;
        }

        if(!$variation_sale_price){
            $variation_price = $variation_regular_price;
        } else {
            $variation_price = $variation_sale_price;
        }

        if($origin_price != $variation_price){
            $price = $variation_price;
        } else {
            $price = $origin_price;
        }

        
        $data[$variation_id] = array(
            'variation_id'          => $single_variation->get_id(),
            'sku'                   => $sku,
            'manage_stock'          => $manage_stock,
            'backorders'            => $backorders,
            'stock_count'           => $stock_count,
            'stock_status'          => $stock_status,
            'stock'                 => $stock,
            'safe_stock'            => $safe_stock,
            'add_price'             => $add_price,
            'added_price'           => $added_price,
            'variation_title'       => $variation_title,
            'price'                 => $price,
            'not_req'               => $not_req,
            'enabled'               => $enabled,
            'color'                 => $color
        );
    }
    return $data;
}


/**
 * Create a product variation for a defined variable product ID.
 *
 * @since 3.0.0
 * @param int   $product_id | Post ID of the product parent variable product.
 * @param array $variation_data | The data to insert in the product.
 */

function create_product_variation( $product_id, $variation_data ){
    // Get the Variable product object (parent)
    $product = wc_get_product($product_id);

    $variation_post = array(
        'post_title'  => $product->get_title(),
        'post_name'   => 'product-'.$product_id.'-variation',
        'post_status' => 'publish',
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => $product->get_permalink()
    );

    // Creating the product variation
    $variation_id = wp_insert_post( $variation_post );

    // Get an instance of the WC_Product_Variation object
    $variation = new WC_Product_Variation( $variation_id );

    // Iterating through the variations attributes
    foreach ($variation_data['attributes'] as $attribute => $term_name )
    {
        $taxonomy = 'pa_'.$attribute; // The attribute taxonomy

        // If taxonomy doesn't exists we create it (Thanks to Carl F. Corneil)
        if( ! taxonomy_exists( $taxonomy ) ){
            register_taxonomy(
                $taxonomy,
               'product_variation',
                array(
                    'hierarchical' => false,
                    'label' => ucfirst( $taxonomy ),
                    'query_var' => true,
                    'rewrite' => array( 'slug' => '$taxonomy'), // The base slug
                )
            );
        }

        // Check if the Term name exist and if not we create it.
        if( ! term_exists( $term_name, $taxonomy ) )
            wp_insert_term( $term_name, $taxonomy ); // Create the term

        $term_slug = get_term_by('name', $term_name, $taxonomy )->slug; // Get the term slug

        // Get the post Terms names from the parent variable product.
        $post_term_names =  wp_get_post_terms( $product_id, $taxonomy, array('fields' => 'names') );

        // Check if the post term exist and if not we set it in the parent variable product.
        if( ! in_array( $term_name, $post_term_names ) )
            wp_set_post_terms( $product_id, $term_name, $taxonomy, true );

        // Set/save the attribute data in the product variation
        update_post_meta( $variation_id, 'attribute_'.$taxonomy, $term_slug );
    }

    ## Set/save all other data

    // SKU
    if( ! empty( $variation_data['sku'] ) )
        $variation->set_sku( $variation_data['sku'] );

    // Prices
    if( empty( $variation_data['sale_price'] ) ){
        $variation->set_price( $variation_data['regular_price'] );
    } else {
        $variation->set_price( $variation_data['sale_price'] );
        $variation->set_sale_price( $variation_data['sale_price'] );
    }
    $variation->set_regular_price( $variation_data['regular_price'] );

    // Stock
    if( ! empty($variation_data['stock_qty']) ){
        $variation->set_stock_quantity( $variation_data['stock_qty'] );
        $variation->set_manage_stock(true);
        $variation->set_stock_status('');
    } else {
        $variation->set_manage_stock(false);
    }

    $variation->set_weight(''); // weight (reseting)

    $variation->save(); // Save the data
}
?>