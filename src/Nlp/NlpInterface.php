<?php
/*------------------------------------------------------------------------
 * NplInterface.php 
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


interface NlpInterface {
	/**
	 * 获取摘要
	 * @return mixed
	 */
	public function getSummary ($title = '', $content = '', $max_summary_len = 300);

	/**
	 * 获取标签
	 * @return mixed
	 */
	public function getTopic ($title = '', $content = '');

	/**
	 * 获取关键字
	 * @param string $title
	 * @param string $content
	 * @return mixed
	 */
	public function getKeyword($title = '', $content = '');
}