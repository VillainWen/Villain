<?php
/*------------------------------------------------------------------------
 * AliApi.php 
 * 	
 * Description
 *
 * Created on 2020/9/10
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */


namespace Villain\Ali;

use Villain\Cache;
use Villain\Logs;

class AliApi {

	function __construct (Array $config, $runtime_path) {
		$this->runtime_path = $runtime_path . 'runtime/villain/ali/';
		Cache::init($this->runtime_path . 'simplecache/');
	}


	public function processGETRequest($appkey, $token, $text, $audioSaveFile, $format, $sampleRate) {
		$url = "https://nls-gateway.cn-shanghai.aliyuncs.com/stream/v1/tts";
		$url = $url . "?appkey=" . $appkey;
		$url = $url . "&token=" . $token;
		$url = $url . "&text=" . $text;
		$url = $url . "&format=" . $format;
		$url = $url . "&sample_rate=" . strval($sampleRate);
		// voice 发音人，可选，默认是xiaoyun。
		// $url = $url . "&voice=" . "xiaoyun";
		// volume 音量，范围是0~100，可选，默认50。
		// $url = $url . "&volume=" . strval(50);
		// speech_rate 语速，范围是-500~500，可选，默认是0。
		// $url = $url . "&speech_rate=" . strval(0);
		// pitch_rate 语调，范围是-500~500，可选，默认是0。
		// $url = $url . "&pitch_rate=" . strval(0);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		/**
		 * 设置HTTPS GET URL。
		 */
		curl_setopt($curl, CURLOPT_URL, $url);
		/**
		 * 设置返回的响应包含HTTPS头部信息。
		 */
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		/**
		 * 发送HTTPS GET请求。
		 */
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		$response = curl_exec($curl);
		if ($response == FALSE) {
			print "curl_exec failed!\n";
			curl_close($curl);
			return ;
		}
		/**
		 * 处理服务端返回的响应。
		 */
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$headers = substr($response, 0, $headerSize);
		$bodyContent = substr($response, $headerSize);
		curl_close($curl);
		if (stripos($headers, "Content-Type: audio/mpeg") != FALSE || stripos($headers, "Content-Type:audio/mpeg") != FALSE) {
			file_put_contents($audioSaveFile, $bodyContent);
			print "The GET request succeed!\n";
		}
		else {
			print "The GET request failed: " . $bodyContent . "\n";
		}
	}

	function processPOSTRequest($appkey, $token, $text, $audioSaveFile, $format, $sampleRate) {
		$url = "https://nls-gateway.cn-shanghai.aliyuncs.com/stream/v1/tts";
		/**
		 * 请求参数，以JSON格式字符串填入HTTPS POST请求的Body中。
		 */
		$taskArr = array(
			"appkey" => $appkey,
			"token" => $token,
			"text" => $text,
			"format" => $format,
			"sample_rate" => $sampleRate
			// voice 发音人，可选，默认是xiaoyun。
			// "voice" => "xiaoyun",
			// volume 音量，范围是0~100，可选，默认50。
			// "volume" => 50,
			// speech_rate 语速，范围是-500~500，可选，默认是0。
			// "speech_rate" => 0,
			// pitch_rate 语调，范围是-500~500，可选，默认是0。
			// "pitch_rate" => 0
		);
		$body = json_encode($taskArr);
		print "The POST request body content: " . $body . "\n";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		/**
		 * 设置HTTPS POST URL。
		 */
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		/**
		 * 设置HTTPS POST请求头部。
		 * */
		$httpHeaders = array(
			"Content-Type: application/json"
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
		/**
		 * 设置HTTPS POST请求体。
		 */
		curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
		/**
		 * 设置返回的响应包含HTTPS头部信息。
		 */
		curl_setopt($curl, CURLOPT_HEADER, TRUE);
		/**
		 * 发送HTTPS POST请求。
		 */
		$response = curl_exec($curl);
		if ($response == FALSE) {
			print "curl_exec failed!\n";
			curl_close($curl);
			return ;
		}
		/**
		 * 处理服务端返回的响应。
		 */
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$headers = substr($response, 0, $headerSize);
		$bodyContent = substr($response, $headerSize);
		curl_close($curl);
		if (stripos($headers, "Content-Type: audio/mpeg") != FALSE || stripos($headers, "Content-Type:audio/mpeg") != FALSE) {
			file_put_contents($audioSaveFile, $bodyContent);
			print "The POST request succeed!\n";
		}
		else {
			print "The POST request failed: " . $bodyContent . "\n";
		}
	}
}