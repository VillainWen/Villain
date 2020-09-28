<?php
/*------------------------------------------------------------------------
 * Nls.php 
 * 	
 * Description
 *
 * Created on 2020/9/28
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */


namespace Villain\Nls;


use Villain\Nls\AliNls\AliNls;

class Nls {
	public $tips_info;

	public function __construct () {
	}

	/**
	 * 创建一个Npl
	 * @param       $container
	 * @param array $configuration
	 * @return AliNls|null
	 */
	public static function create($container, array $configuration = []) {
		if ($container === "aliNls") {
			return new AliNls($configuration['accessKeyId'], $configuration['accessKeySecret'], $configuration['regionId'], $configuration['runtime_path']);
		}
		return null;
	}
}