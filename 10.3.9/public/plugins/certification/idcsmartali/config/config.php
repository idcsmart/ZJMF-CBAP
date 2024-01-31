<?php
/*
 * 智简魔方实名认证
 */
return [
    # 认证完成后用户的跳转链接
    'return_url'            => "/login.php",
    # 认证场景码。入参支持的认证场景码和商户签约的认证场景相关，取值如下:
    #   FACE：多因子人脸认证
    #   CERT_PHOTO：多因子证照认证
    #   CERT_PHOTO_FACE ：多因子证照和人脸认证
    #   SMART_FACE：多因子快捷认证
    'biz_code'              => "FACE",
	# 证件类型
	# 	IDENTITY_CARD：身份证
	# 	HOME_VISIT_PERMIT_HK_MC：港澳通行证
	# 	HOME_VISIT_PERMIT_TAIWAN：台湾通行证
	# 	RESIDENCE_PERMIT_HK_MC：港澳居住证
	# 	RESIDENCE_PERMIT_TAIWAN：台湾居住证
    'cert_type'              => "IDENTITY_CARD",
];