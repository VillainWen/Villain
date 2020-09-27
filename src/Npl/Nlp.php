<?php
/*------------------------------------------------------------------------
 * Npl.php 
 * 	
 * Description
 *
 * Created on 2020/9/26
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */


namespace Villain\Nlp;

class Nlp {

	public $tips_info;

	public function __construct () {
	}

	/**
	 * 创建一个Npl
	 * @param       $container
	 * @param array $configuration
	 * @return BaiduApi|null
	 */
	public static function create($container, array $configuration = []) {
		if ($container === "baidu") {
			return new BaiduApi($configuration['appid'], $configuration['api_key'], $configuration['api_secret'], $configuration['runtime_path']);
		}
		return null;
	}
}