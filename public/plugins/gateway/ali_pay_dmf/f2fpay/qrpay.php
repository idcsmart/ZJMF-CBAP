<?php 
header("Content-type: text/html; charset=utf-8");
require_once 'model/builder/AlipayTradePrecreateContentBuilder.php';
require_once 'service/AlipayTradeService.php';

if (!empty($_POST['out_trade_no'])&& trim($_POST['out_trade_no'])!=""){
    $aop = new AopClient ();
    $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
    $aop->appId = $config['appid'];
    $aop->rsaPrivateKey = '请填写开发者私钥去头去尾去回车，一行字符串';
    $aop->alipayrsaPublicKey='请填写支付宝公钥，一行字符串';
    $aop->apiVersion = '1.0';
    $aop->signType = 'RSA2';
    $aop->postCharset='GBK';
    $aop->format='json';
    $request = new AlipayTradePrecreateRequest ();
    $request->setBizContent("{" .
        "\"out_trade_no\":\"20150320010101001\"," .
        "\"seller_id\":\"2088102146225135\"," .
        "\"total_amount\":88.88," .
        "\"discountable_amount\":8.88," .
        "\"undiscountable_amount\":80," .
        "\"buyer_logon_id\":\"15901825620\"," .
        "\"subject\":\"Iphone6 16G\"," .
        "      \"goods_detail\":[{" .
        "        \"goods_id\":\"apple-01\"," .
        "\"alipay_goods_id\":\"20010001\"," .
        "\"goods_name\":\"ipad\"," .
        "\"quantity\":1," .
        "\"price\":2000," .
        "\"goods_category\":\"34543238\"," .
        "\"categories_tree\":\"124868003|126232002|126252004\"," .
        "\"body\":\"特价手机\"," .
        "\"show_url\":\"http://www.alipay.com/xxx.jpg\"" .
        "        }]," .
        "\"body\":\"Iphone6 16G\"," .
        "\"product_code\":\"FACE_TO_FACE_PAYMENT\"," .
        "\"operator_id\":\"yx_001\"," .
        "\"store_id\":\"NJ_001\"," .
        "\"disable_pay_channels\":\"pcredit,moneyFund,debitCardExpress\"," .
        "\"enable_pay_channels\":\"pcredit,moneyFund,debitCardExpress\"," .
        "\"terminal_id\":\"NJ_T_001\"," .
        "\"extend_params\":{" .
        "\"sys_service_provider_id\":\"2088511833207846\"," .
        "\"hb_fq_num\":\"3\"," .
        "\"hb_fq_seller_percent\":\"100\"," .
        "\"industry_reflux_info\":\"{\\\\\\\"scene_code\\\\\\\":\\\\\\\"metro_tradeorder\\\\\\\",\\\\\\\"channel\\\\\\\":\\\\\\\"xxxx\\\\\\\",\\\\\\\"scene_data\\\\\\\":{\\\\\\\"asset_name\\\\\\\":\\\\\\\"ALIPAY\\\\\\\"}}\"," .
        "\"card_type\":\"S0JP0000\"" .
        "    }," .
        "\"timeout_express\":\"90m\"," .
        "\"royalty_info\":{" .
        "\"royalty_type\":\"ROYALTY\"," .
        "        \"royalty_detail_infos\":[{" .
        "          \"serial_no\":1," .
        "\"trans_in_type\":\"userId\"," .
        "\"batch_no\":\"123\"," .
        "\"out_relation_id\":\"20131124001\"," .
        "\"trans_out_type\":\"userId\"," .
        "\"trans_out\":\"2088101126765726\"," .
        "\"trans_in\":\"2088101126708402\"," .
        "\"amount\":0.1," .
        "\"desc\":\"分账测试1\"," .
        "\"amount_percentage\":\"100\"" .
        "          }]" .
        "    }," .
        "\"settle_info\":{" .
        "        \"settle_detail_infos\":[{" .
        "          \"trans_in_type\":\"cardAliasNo\"," .
        "\"trans_in\":\"A0001\"," .
        "\"summary_dimension\":\"A0001\"," .
        "\"settle_entity_id\":\"2088xxxxx;ST_0001\"," .
        "\"settle_entity_type\":\"SecondMerchant、Store\"," .
        "\"amount\":0.1" .
        "          }]," .
        "\"settle_period_time\":\"7d\"" .
        "    }," .
        "\"sub_merchant\":{" .
        "\"merchant_id\":\"2088000603999128\"," .
        "\"merchant_type\":\"alipay: 支付宝分配的间连商户编号, merchant: 商户端的间连商户编号\"" .
        "    }," .
        "\"alipay_store_id\":\"2016052600077000000015640104\"," .
        "\"merchant_order_no\":\"20161008001\"," .
        "\"ext_user_info\":{" .
        "\"name\":\"李明\"," .
        "\"mobile\":\"16587658765\"," .
        "\"cert_type\":\"IDENTITY_CARD\"," .
        "\"cert_no\":\"362334768769238881\"," .
        "\"min_age\":\"18\"," .
        "\"fix_buyer\":\"F\"," .
        "\"need_check_info\":\"F\"" .
        "    }," .
        "\"business_params\":{" .
        "\"campus_card\":\"0000306634\"," .
        "\"card_type\":\"T0HK0000\"," .
        "\"actual_order_time\":\"2019-05-14 09:18:55\"" .
        "    }," .
        "\"qr_code_timeout_express\":\"90m\"" .
        "  }");
    $result = $aop->execute ( $request);

    $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
    $resultCode = $result->$responseNode->code;
    if(!empty($resultCode)&&$resultCode == 10000){
        echo "成功";
    } else {
        echo "失败";
    }
}

?>