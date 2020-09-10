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

	public $tips_info;

	public static $defaultTask = [
		// 音频编码格式 PCM/WAV/MP3
		"format" => "MP3",
		// 音频采样率 6000Hz/8000Hz，默认值：16000Hz。
		"sample_rate" => 16000,
		// voice 发音人，可选，默认是aiqi。
		"voice" => "aiqi",
		// volume 音量，范围是0~100，可选，默认50。
		"volume" => 50,
		// speech_rate 语速，范围是-500~500，可选，默认是0。
		"speech_rate" => 0,
		// pitch_rate 语调，范围是-500~500，可选，默认是0。
		"pitch_rate" => 0
	];


	/**
	 * 支持设置不同类型的声音。
	 * label        => 名称
	 * value        => voice参数值
	 * type         => 类型
	 * language     => 支持语言
	 * sampling     => 支持采样率(Hz)
	 * word_level   => 支持字级别音素边界接口
	 * is_boutique  => 是否精品
	 * @var array
	 */
	public static $voiceList = [
		"normal" => [
			"label" => "通用场景",
			"children" => [
				["label" => "艾琪", "value" => "Aiqi", "type" => "温柔女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾城", "value" => "Aicheng", "type" => "标准男声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾佳", "value" => "Aijia", "type" => "标准女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "思琪", "value" => "Siqi", "type" => "温柔女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => true, "is_boutique" => true],
				["label" => "思佳", "value" => "Sijia", "type" => "标准女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => false],
				["label" => "思城", "value" => "Sicheng", "type" => "标准男声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => true, "is_boutique" => true],
				["label" => "若兮", "value" => "Ruoxi", "type" => "温柔女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => false],
				["label" => "艾达", "value" => "Aida", "type" => "标准男声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "宁儿", "value" => "Ninger", "type" => "标准女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => false],
				["label" => "小云", "value" => "Xiaoyun", "type" => "标准女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => false, "is_boutique" => false],
				["label" => "小刚", "value" => "Xiaogang", "type" => "标准男声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => false, "is_boutique" => false],
				["label" => "瑞琳", "value" => "Ruilin", "type" => "标准女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => false],
			]
		],
		"customer" => [
			"label" => "客服场景",
			"children" => [
				["label" => "思悦", "value" => "Siyue", "type" => "温柔女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => true],
				["label" => "艾雅", "value" => "Aiya", "type" => "严厉女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾夏", "value" => "Aixia", "type" => "亲和女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾美", "value" => "Aimei", "type" => "甜美女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾雨", "value" => "Aiyu", "type" => "自然女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾悦", "value" => "Aiyue", "type" => "温柔女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾婧", "value" => "Aijing", "type" => "严厉女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "小美", "value" => "Xiaomei", "type" => "甜美女声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => false],
				["label" => "艾娜", "value" => "Aina", "type" => "浙普女声", "language" => "纯中文场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "伊娜", "value" => "Yina", "type" => "浙普女声", "language" => "纯中文场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => false],
				["label" => "思婧", "value" => "Sijing", "type" => "严厉女声", "language" => "纯中文场景", "sampling" => "8K/16K/24K", "word_level" => true, "is_boutique" => false],
				["label" => "艾硕", "value" => "Aishuo", "type" => "自然男声", "language" => "中文及中英文混合场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
			]
		],
		"child" => [
			"label" => "童声场景",
			"children" => [
				["label" => "思彤", "value" => "Sitong", "type" => "儿童音", "language" => "纯中文场景", "sampling" => "8K/16K/24K", "word_level" => false, "is_boutique" => false],
				["label" => "小北", "value" => "Xiaobei", "type" => "萝莉女声", "language" => "纯中文场景", "sampling" => "8K/16K/24K", "word_level" => true, "is_boutique" => false],
				["label" => "艾彤", "value" => "Aitong", "type" => "儿童音", "language" => "纯中文场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾薇", "value" => "Aiwei", "type" => "萝莉女声", "language" => "纯中文场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
				["label" => "艾宝", "value" => "Aibao", "type" => "萝莉女声", "language" => "纯中文场景", "sampling" => "8K/16K", "word_level" => true, "is_boutique" => true],
			]
		]
	];

	function __construct (Array $config, $runtime_path) {
		$this->runtime_path = $runtime_path . 'runtime/villain/ali/';
		Cache::init($this->runtime_path . 'simplecache/');
	}

	/**
	 * 语音合成 非长文
	 * @param        $appkey
	 * @param        $token
	 * @param        $text
	 * @param string $method
	 * @param array  $config
	 * @param        $audioSaveFile
	 * @return bool
	 */
	public function processTTSRequest($appkey, $token, $text, $method = "post", $config = [], $audioSaveFile) {
		$url = "https://nls-gateway.cn-shanghai.aliyuncs.com/stream/v1/tts";

		$request = array_merge(self::$defaultTask, $config);

		$request['appkey'] = $appkey;
		$request['token']  = $token;
		$request['text']   = $text;

		$method = strtolower($method);
		if ($method == "get") {
			$temp = [];
			foreach ($request as $key => $value) {
				$temp[] = $key . "=" . $value;
			}
			$data = join("&", $temp);
			$url = $url . "?" . $data;
		} else {
			$data = json_encode($request);
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, TRUE);

		if ($method == "get") {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		} else {
			curl_setopt($curl, CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}

		$response = curl_exec($curl);

		if ($response == FALSE) {
			$this->tips_info = "curl_exec failed!";
			$this->logs("curl_exec failed!");
			curl_close($curl);
			return false;
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
			return true;
		}
		else {
			$this->tips_info = "The GET request failed: " . $bodyContent;
			$this->logs("The GET request failed: " . $bodyContent);
			return false;
		}
	}

	/**
	 * [logs 日志]
	 * @param  string $content [description]
	 * @return [type]          [description]
	 */
	private function logs ($content = '') {
		$Logs = new Logs();
		$Logs->logs($content, $this->runtime_path);
	}
}