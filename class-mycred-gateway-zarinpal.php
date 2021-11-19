<?php

add_action( 'plugins_loaded', 'mycred_zarinpal_plugins_loaded' );

function mycred_zarinpal_plugins_loaded() {
    add_filter( 'mycred_setup_gateways', 'Add_zarinpal_to_Gateways' );
    function Add_zarinpal_to_Gateways( $installed ) {
        $installed['zarinpal'] = [
            'title'    => get_option( 'zarinpal_display_name' ) ? get_option( 'zarinpal_display_name' ) : __( 'zarinpal payment gateway', 'zarinpal-mycred' ),
            'callback' => [ 'myCred_zarinpal' ],
        ];
        return $installed;
    }

    add_filter( 'mycred_buycred_refs', 'Add_zarinpal_to_Buycred_Refs' );
    function Add_zarinpal_to_Buycred_Refs( $addons ) {
        $addons['buy_creds_with_zarinpal'] = __( 'zarinpal Gateway', 'zarinpal-mycred' );

        return $addons;
    }

    add_filter( 'mycred_buycred_log_refs', 'Add_zarinpal_to_Buycred_Log_Refs' );
    function Add_zarinpal_to_Buycred_Log_Refs( $refs ) {
        $zarinpal = [ 'buy_creds_with_zarinpal' ];

        return $refs = array_merge( $refs, $zarinpal );
    }

    add_filter( 'wp_body_open', 'zarinpal_success_message_handler' );
    function zarinpal_success_message_handler( $template ){
        if( !empty( $_GET['mycred_zarinpal_nok'] ) )
            echo '<div class="mycred_zarinpal_message error">'. $_GET['mycred_zarinpal_nok'] .'</div>';

        if( !empty( $_GET['mycred_zarinpal_ok'] ) )
            echo '<div class="mycred_zarinpal_message success">'. $_GET['mycred_zarinpal_ok'] .'</div>';

        if( !empty( $_GET['mycred_zarinpal_nok'] ) || !empty( $_GET['mycred_zarinpal_ok'] ))
            echo '<style>
                .mycred_zarinpal_message {
                    position: absolute;
                    z-index: 9;
                    top: 40px;
                    right: 15px;
                    color: #fff;
                    padding: 15px;
                }
                .mycred_zarinpal_message.error {
                    background: #F44336;
                }
                .mycred_zarinpal_message.success {
                    background: #4CAF50;
                }
            </style>';
    }
}

spl_autoload_register( 'mycred_zarinpal_plugin' );

function mycred_zarinpal_plugin() {
    if ( ! class_exists( 'myCRED_Payment_Gateway' ) ) {
        return;
    }

    if ( ! class_exists( 'myCred_zarinpal' ) ) {
        class myCred_zarinpal extends myCRED_Payment_Gateway {

            function __construct( $gateway_prefs ) {
                $types            = mycred_get_types();
                $default_exchange = [];

                foreach ( $types as $type => $label ) {
                    $default_exchange[ $type ] = 1000;
                }

                parent::__construct( [
                    'id'                => 'zarinpal',
                    'label'             => get_option( 'zarinpal_display_name' ) ? get_option( 'zarinpal_display_name' ) : __( 'zarinpal payment gateway', 'zarinpal-mycred' ),
                    'documentation'     => 'https://zarinpal.com',
                    'gateway_logo_url'  => plugins_url( '/assets/zarinpal.png', __FILE__ ),
                    'defaults'          => [
                        'merchant'            => NULL,
                        'zarinpal_display_name' => __( 'zarinpal payment gateway', 'zarinpal-mycred' ),
                        'currency'           => 'rial',
                        'exchange'           => $default_exchange,
                        'item_name'          => __( 'Purchase of myCRED %plural%', 'mycred' ),
                    ],
                ], $gateway_prefs );
            }

            public function zarinpal_Iranian_currencies( $currencies ) {
                unset( $currencies );

                $currencies['rial']  = __( 'Rial', 'zarinpal-mycred' );
                $currencies['toman'] = __( 'Toman', 'zarinpal-mycred' );

                return $currencies;
            }

            function preferences() {
                add_filter( 'mycred_dropdown_currencies', [
                    $this,
                    'zarinpal_Iranian_currencies',
                ] );

                $prefs = $this->prefs;
                ?>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'merchant' ); ?>"><?php _e( 'کد درگاه - مرچنت', 'zarinpal-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'merchant' ); ?>"
                                   name="<?php echo $this->field_name( 'merchant' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['merchant']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>



                <label class="subheader"
                       for="<?php echo $this->field_id( 'zarinpal_display_name' ); ?>"><?php _e( 'Title', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'zarinpal_display_name' ); ?>"
                                   name="<?php echo $this->field_name( 'zarinpal_display_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['zarinpal_display_name'] ? $prefs['zarinpal_display_name'] : __( 'zarinpal payment gateway', 'zarinpal-mycred' ); ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'currency' ); ?>"><?php _e( 'Currency', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->currencies_dropdown( 'currency', 'mycred-gateway-zarinpal-currency' ); ?>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'item_name' ); ?>"><?php _e( 'Item Name', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'item_name' ); ?>"
                                   name="<?php echo $this->field_name( 'item_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['item_name']; ?>"
                                   class="long"/>
                        </div>
                        <span class="description"><?php _e( 'Description of the item being purchased by the user.', 'mycred' ); ?></span>
                    </li>
                </ol>

                <label class="subheader"><?php _e( 'Exchange Rates', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <?php $this->exchange_rate_setup(); ?>
                    </li>
                </ol>
                <?php
            }

            public function sanitise_preferences( $data ) {
                $new_data['merchant']            = sanitize_text_field( $data['merchant'] );
                $new_data['zarinpal_display_name'] = sanitize_text_field( $data['zarinpal_display_name'] );
                $new_data['currency']           = sanitize_text_field( $data['currency'] );
                $new_data['item_name']          = sanitize_text_field( $data['item_name'] );
                // $new_data['sandbox']            = sanitize_text_field( $data['sandbox'] );

                if ( isset( $data['exchange'] ) ) {
                    foreach ( (array) $data['exchange'] as $type => $rate ) {
                        if ( $rate != 1 && in_array( substr( $rate, 0, 1 ), ['.', ',',] ) ) {
                            $data['exchange'][ $type ] = (float) '0' . $rate;
                        }
                    }
                }

                $new_data['exchange'] = $data['exchange'];
                update_option( 'zarinpal_display_name', $new_data['zarinpal_display_name'] );
                return $data;
            }

            public function process() {

                // $pending_post_id = sanitize_text_field( $_REQUEST['payment_id'] );
                $pending_post_id = sanitize_text_field( $_REQUEST['orderId'] );
                $org_pending_payment = $pending_payment = $this->get_pending_payment( $pending_post_id );




                $mycred = mycred( $org_pending_payment->point_type );

                $status    = !empty($_POST['status'])  ? sanitize_text_field($_POST['status'])   : (!empty($_GET['status'])  ? sanitize_text_field($_GET['status'])   : NULL);
                $Authority  = !empty($_POST['Authority'])? sanitize_text_field($_POST['Authority']) : (!empty($_GET['Authority'])? sanitize_text_field($_GET['Authority']) : NULL);
                $success    = !empty($_POST['success'])  ? sanitize_text_field($_POST['success'])   : (!empty($_GET['success'])  ? sanitize_text_field($_GET['success'])   : NULL);
                // $id        = !empty($_POST['id'])      ? sanitize_text_field($_POST['id'])       : (!empty($_GET['id'])      ? sanitize_text_field($_GET['id'])       : NULL);
                $orderId  = !empty($_POST['orderId'])? sanitize_text_field($_POST['orderId']) : (!empty($_GET['orderId'])? sanitize_text_field($_GET['orderId']) : NULL);
                $params    = !empty($_POST['id']) ? $_POST : $_GET;

                $amount = $_GET['amount'];
                $status =  $_GET['Status'];
                $Authority = $_GET['Authority'];
                if ( $status == "OK" ) {
                    $merchant = $merchant = $this->prefs['merchant'];

                    $data = [
                        "merchant_id" => $merchant,
                        "authority" => $Authority,
                        "amount" => $amount

                    ];

                    $result = $this->post_to_zarinpal('verify', json_encode($data));

                    if ( is_wp_error( $result ) ) {

                        $log = $result->get_error_message();
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_zarinpal',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id,
                            $params
                        );

                        $return = add_query_arg( 'mycred_zarinpal_nok', $log, $this->get_cancelled() );
                        //  var_dump($result . " <br> ".$return );
                        wp_redirect( $return );
                        exit;
                    }




                    if ( $result['data'] == null ) {

                        $log = sprintf( __( 'An error occurred while verifying the transaction. status: %s, code: %s, message: %s', 'zarinpal-mycred' ), $_GET['status'], $result['errors']['code'],  $result['errors']['message'] );
                        $this->log_call( $pending_post_id, $log );
                        $mycred->add_to_log(
                            'buy_creds_with_zarinpal',
                            $pending_payment->buyer_id,
                            $pending_payment->amount,
                            $log,
                            $pending_payment->buyer_id,
                            $params
                        );

                        $return = add_query_arg( 'mycred_zarinpal_nok', $log, $this->get_cancelled() );
                        //var_dump($result . " <br> ".$return );
                        wp_redirect( $return );
                        // exit;
                    }

                    if ( $result['data']['code'] == 100  ) {

                        $message = sprintf( __( 'Payment succeeded. Status: %s, Track id: %s, Order no: %s', 'zarinpal-mycred' ), $result['data']['code'] , $result['data']['ref_id'], $orderId );
                        $log = $message;

                        add_filter( 'mycred_run_this', function( $filter_args ) use ( $log ) {
                            return $this->mycred_zarinpal_success_log( $filter_args, $log );
                        } );

                        // if ( $this->complete_payment( $org_pending_payment, $id ) ) {
                        if ( $this->complete_payment( $org_pending_payment, $result['data']['ref_id'] ) ) {

                            $this->log_call( $pending_post_id, $message );
                            $this->trash_pending_payment( $pending_post_id );

                            $return = add_query_arg( 'mycred_zarinpal_ok', $message, $this->get_thankyou() );
                            // var_dump($result . " <br> ".$return );
                            wp_redirect( $return );
                            exit;



                        }


                        else {

                            $log = sprintf( __( 'transaction success. Track id is: %s', 'zarinpal-mycred', $result['data']['ref_id'] ) );
                            $this->log_call( $pending_post_id, $log );
                            $mycred->add_to_log(
                                'buy_creds_with_zarinpal',
                                $pending_payment->buyer_id,
                                $pending_payment->amount,
                                $log,
                                $pending_payment->buyer_id,
                                $result
                            );

                            // $return = add_query_arg( 'mycred_zarinpal_nok', $log, $this->get_cancelled() );
                            $return = add_query_arg( 'mycred_zarinpal_ok', $message, $this->get_thankyou() );

                            // var_dump($return  );
                            wp_redirect( $return );
                            exit;
                        }
                    }

                    $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s', 'zarinpal-mycred' ), $result['errors'], $Authority );
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_zarinpal',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id,
                        $result
                    );

                    $return = add_query_arg( 'mycred_zarinpal_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                    //var_dump($result);
                    //exit;

                }

                if ($status == "NOK"){
                    $merchant = $merchant = $this->prefs['merchant'];

                    $data = [
                        "merchant_id" => $merchant,
                        "authority" => $Authority,
                        "amount" => $amount

                    ];

                    $result = $this->post_to_zarinpal('verify', json_encode($data));

                    $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s', 'zarinpal-mycred' ), $result['errors']['code'], $Authority );
                    $this->log_call( $pending_post_id, $log );
                    $mycred->add_to_log(
                        'buy_creds_with_zarinpal',
                        $pending_payment->buyer_id,
                        $pending_payment->amount,
                        $log,
                        $pending_payment->buyer_id,
                        $result
                    );

                    $return = add_query_arg( 'mycred_zarinpal_nok', $log, $this->get_cancelled() );
                    wp_redirect( $return );
                }
            }

            public function returning() {}

            public function mycred_zarinpal_success_log( $request, $log ){
                if( $request['ref'] == 'buy_creds_with_zarinpal' )
                    $request['entry'] = $log;

                return $request;
            }
            /**
             * Prep Sale
             *
             * @since   1.8
             * @version 1.0
             */
            public function prep_sale( $new_transaction = FALSE ) {

                // Point type
                $type   = $this->get_point_type();
                $mycred = mycred( $type );

                // Amount of points
                $amount = $mycred->number( $_REQUEST['amount'] );

                // Get cost of that points
                $cost = $this->get_cost( $amount, $type );
                $cost = abs( $cost );

                $to   = $this->get_to();
                $from = $this->current_user_id;

                // Revisiting pending payment
                if ( isset( $_REQUEST['revisit'] ) ) {
                    $this->transaction_id = strtoupper( $_REQUEST['revisit'] );
                } else {
                    $post_id = $this->add_pending_payment( [
                        $to,
                        $from,
                        $amount,
                        $cost,
                        $this->prefs['currency'],
                        $type,
                    ] );
                    $this->transaction_id = get_the_title( $post_id );
                }

                $is_ajax    = ( isset( $_REQUEST['ajax'] ) && $_REQUEST['ajax'] == 1 ) ? true : false;
                $callback = add_query_arg( 'payment_id', $this->transaction_id, $this->callback_url() );
                $callback = add_query_arg( 'amount', (( $this->prefs['currency'] == 'toman' ) ? ( $cost * 10 ) : $cost), $this->callback_url());
                $merchant  = $this->prefs['merchant'];

                $data = array(
                    "merchant_id" => $merchant,
                    "description" => $this->transaction_id,
                    "amount"   => ( $this->prefs['currency'] == 'toman' ) ? ( $cost * 10 ) : $cost,
                    "callback_url" => $callback,
                );

                $result = $this->post_to_zarinpal( 'request', json_encode($data) );

                if ( is_wp_error( $result ) ) {
                    $error = $result->get_error_message();
                    $mycred->add_to_log(
                        'buy_creds_with_zarinpal',
                        $from,
                        $amount,
                        $error,
                        $from,
                        $data,
                        'point_type_key'
                    );

                    if($is_ajax){
                        $this->errors[] = $error;
                    }
                    else if( empty( $_GET['zarinpal_error'] ) ){
                        wp_redirect( $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&zarinpal_error='. $error );
                        exit;
                    }
                }

                //$result = (object)$response;


                if (empty($result['errors']) ) {

                    if ( ! empty( $result['errors']['code'] ) ) {
                        $error = $result['errors']['code'];

                        $mycred->add_to_log(
                            'buy_creds_with_zarinpal',
                            $from,
                            $amount,
                            $error,
                            $from,
                            $data,
                            'point_type_key'
                        );

                        if($is_ajax){
                            $this->errors[] = $error;
                        }
                        else if( empty( $_GET['zarinpal_error'] ) ){
                            wp_redirect( $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'] . '&zarinpal_error='. $error );
                            exit;
                        }
                    }
                }

                $item_name = str_replace( '%number%', $this->amount, $this->prefs['item_name'] );
                $item_name = $this->core->template_tags_general( $item_name );

                $redirect_fields = [
                    //'pay_to_email'        => $this->prefs['account'],
                    'transaction_id'      => $this->transaction_id,
                    'return_url'          => $this->get_thankyou(),
                    'cancel_url'          => $this->get_cancelled( $this->transaction_id ),
                    'status_url'          => $this->callback_url(),
                    'return_url_text'     => get_bloginfo( 'name' ),
                    'hide_login'          => 1,
                    'merchant_fields'     => 'sales_data',
                    'sales_data'          => $this->post_id,
                    'amount'              => $this->cost,
                    'currency'            => $this->prefs['currency'],
                    'detail1_description' => __( 'Item Name', 'mycred' ),
                    'detail1_text'        => $item_name,
                ];

                // Customize Checkout Page
                if ( isset( $this->prefs['account_title'] ) && ! empty( $this->prefs['account_title'] ) ) {
                    $redirect_fields['recipient_description'] = $this->core->template_tags_general( $this->prefs['account_title'] );
                }

                if ( isset( $this->prefs['account_logo'] ) && ! empty( $this->prefs['account_logo'] ) ) {
                    $redirect_fields['logo_url'] = $this->prefs['account_logo'];
                }

                if ( isset( $this->prefs['confirmation_note'] ) && ! empty( $this->prefs['confirmation_note'] ) ) {
                    $redirect_fields['confirmation_note'] = $this->core->template_tags_general( $this->prefs['confirmation_note'] );
                }

                // If we want an email receipt for purchases
                if ( isset( $this->prefs['email_receipt'] ) && ! empty( $this->prefs['email_receipt'] ) ) {
                    $redirect_fields['status_url2'] = $this->prefs['account'];
                }

                // Gifting
                if ( $this->gifting ) {
                    $user                                   = get_userdata( $this->recipient_id );
                    $redirect_fields['detail2_description'] = __( 'Recipient', 'mycred' );
                    $redirect_fields['detail2_text']        = $user->display_name;
                }

                $this->redirect_fields = $redirect_fields;
                //echo $result;
                //  var_dump($result);
                wp_redirect('https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);
                exit;
            }

            /**
             * AJAX Buy Handler
             *
             * @since   1.8
             * @version 1.0
             */
            public function ajax_buy() {
                // Construct the checkout box content
                $content = $this->checkout_header();
                $content .= $this->checkout_logo();
                $content .= $this->checkout_order();
                $content .= $this->checkout_cancel();
                $content .= $this->checkout_footer();

                // Return a JSON response
                $this->send_json( $content );
            }

            /**
             * Checkout Page Body
             * This gateway only uses the checkout body.
             *
             * @since   1.8
             * @version 1.0
             */
            public function checkout_page_body() {
                echo $this->checkout_header();
                echo $this->checkout_logo( FALSE );
                echo $this->checkout_order();
                echo $this->checkout_cancel();
                if( !empty( $_GET['zarinpal_error'] ) ){
                    echo '<div class="alert alert-error zarinpal-error">'. $_GET['zarinpal_error'] .'</div>';
                    echo '<style>
                        .checkout-footer, .zarinpal-logo, .checkout-body > img {display: none;}
                        .zarinpal-error {
                            background: #F44336;
                            color: #fff;
                            padding: 15px;
                            margin: 10px 0;
                        }
                    </style>';
                }
                else {
                    echo '<style>.checkout-body > img {display: none;}</style>';
                }
                echo $this->checkout_footer();
                echo sprintf(
                    '<span class="zarinpal-logo" style="font-size: 12px;padding: 5px 0;"><img src="%1$s" style="display: inline-block;vertical-align: middle;width: 70px;">%2$s</span>',
                    plugins_url( '/assets/zarinpal.png', __FILE__ ), __( 'Pay with zarinpal', 'zarinpal-mycred' )
                );

            }

            /**
             * @param $action (PaymentRequest, )
             * @param $params string
             *
             * @return mixed
             */
            function post_to_zarinpal($action, $params)
            {
                try {

                    $number_of_connection_tries = 10;
                    $response = null;
                    while ( $number_of_connection_tries>0 ) {
                        $response = wp_safe_remote_post('https://api.zarinpal.com/pg/v4/payment/' . $action . '.json',array(
                            'body'=> $params,
                            'headers'=>array(
                                'Content-Type'=>'application/json',
                                'Content-Length' =>   strlen($params) ,
                                'User-Agent' => 'ZarinPal Rest Api v4'
                            )
                        ));

                        if ( is_wp_error( $response ) ) {
                            $number_of_connection_tries --;
                            continue;
                        } else {
                            break;
                        }
                    }

                    $body = wp_remote_retrieve_body($response);
                    return json_decode($body, true);
                } catch (Exception $ex) {
                    return false;
                }



            }




        }
    }
}
