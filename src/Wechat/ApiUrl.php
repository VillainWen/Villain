<?php
/*------------------------------------------------------------------------
 * ApiUrl.php
 * 	
 * 微信开发接口地址
 *
 * Created on 2020/9/7
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */

namespace Villain\Wechat;

class ApiUrl {
	const API = [
		'offiaccount' => [
			'access_token' => 'https://api.weixin.qq.com/cgi-bin/token',
			'create_qrcode' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create',
			'show_qrcode' => 'https://mp.weixin.qq.com/cgi-bin/showqrcode',
			'upload_media' => 'https://api.weixin.qq.com/cgi-bin/media/upload',
			'userinfo' => 'https://api.weixin.qq.com/cgi-bin/user/info',
			'customer_send' => 'https://api.weixin.qq.com/cgi-bin/message/custom/send',
			'authorize' => 'https://open.weixin.qq.com/connect/oauth2/authorize',
			'codeopenid' => 'https://api.weixin.qq.com/sns/oauth2/access_token',
			'menucreate' => 'https://api.weixin.qq.com/cgi-bin/menu/create',
			'template_send' => 'https://api.weixin.qq.com/cgi-bin/message/template/send',
		]
	];
}