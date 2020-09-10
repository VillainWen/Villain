<?php
/*------------------------------------------------------------------------
 * Aes.php 
 * 	
 * Aes加密
 *
 * Created on 2020/9/10
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */


namespace Villain;


class Aes {
	/**
	 * 默认秘钥
	 */
	public static $KEY = "hm2KzN8k32UyVAEm";//16位

	/**向量
	 * @var string
	 */
	public static $IV = '';//16位

	public function __construct($key = '', $iv = '') {
		self::$KEY = $key ? $key :  'hm2KzN8k32UyVAEm';
		self::$IV = $iv ? $iv : substr(md5(md5(self::$KEY) . md5("villain") . 'villain') , 8, 16);
	}

	/**
	 * 解密字符串
	 * @param string $data 字符串
	 * @param string $key 加密key
	 * @return string
	 */
	public static function decryptWithOpenssl($data){
		$key = self::$KEY;
		$iv  = self::$IV;
		return openssl_decrypt(base64_decode($data), "AES-128-CBC", $key, OPENSSL_RAW_DATA, $iv);
	}

	/**
	 * 加密字符串
	 * 参考网站： https://segmentfault.com/q/1010000009624263
	 * @param string $data 字符串
	 * @param string $key 加密key
	 * @return string
	 */
	public static function encryptWithOpenssl($data){
		$key = self::$KEY;
		$iv  = self::$IV;
		return base64_encode(openssl_encrypt($data, "AES-128-CBC", $key, OPENSSL_RAW_DATA, $iv));
	}

	function pkcs7_pad($str) {
		$len = mb_strlen($str, '8bit');
		$c = 16 - ($len % 16);
		$str .= str_repeat(chr($c), $c);
		return $str;
	}
}