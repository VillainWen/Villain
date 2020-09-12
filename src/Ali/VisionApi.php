<?php
/*------------------------------------------------------------------------
 * VisionApi.php 
 * 	
 * Description
 *
 * Created on 2020/9/11
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */


namespace Villain\Ali;


use AlibabaCloud\Client\AlibabaCloud;
use \AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class VisionApi {
	/**
	 *  阿里云主账号AccessKey拥有所有API的访问权限，风险很高。
	 *  强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录RAM控制台创建RAM账号。
	 * @var
	 */
	protected $accessKeyId;
	protected $accessKeySecret;

	/**
	 * 地域
	 * @var string
	 */
	protected $regionId = 'cn-shanghai';

	protected $host = 'videoenhan.cn-shanghai.aliyuncs.com';

	/**
	 * 错误消息
	 * @var string
	 */
	protected $tips_info;

	/**
	 * VisionApi 构造函数.
	 * @param $accessKeyId
	 * @param $accessKeySecret
	 * @throws \AlibabaCloud\Client\Exception\ClientException
	 */
	public function __construct ($accessKeyId, $accessKeySecret) {
		$this->accessKeyId = $accessKeyId;
		$this->accessKeySecret = $accessKeySecret;

		AlibabaCloud::accessKeyClient($this->accessKeyId, $this->accessKeySecret)
			->regionId($this->regionId)
			->asDefaultClient();
	}

	/**
	 * @param int    $with
	 * @param int    $height
	 * @param bool   $mute
	 * @param array  $fileList
	 * @param string $Scene
	 * @param string $style
	 * @param int    $Duration
	 * @param bool   $DurationAdaption
	 * @param null   $TransitionStyle
	 * @param bool   $SmartEffect
	 * @param bool   $PuzzleEffect
	 */
	public function generateVideo ($with = 640, $height = 640, $mute = false, $fileList = [], $Scene = 'costume', $style = 'normal', $Duration= 20, $DurationAdaption = true, $TransitionStyle = null, $SmartEffect=true,$PuzzleEffect=false) {
		try {
			$query = [];
			foreach ($fileList as $key => $value) {
				$str = '';
				$str = $key + 1;
				unset($pathinfo);
				$pathinfo = pathinfo($value['FileUrl']);
				$query['FileList.' . $str . ".FileName"] = $pathinfo['basename'];
				$query['FileList.' . $str . ".FileUrl"] = $value['FileUrl'];
				$query['FileList.' . $str . ".Type"]    = $value['Type'];
			}
			$query['RegionId'] = $this->regionId;
			$query['Width'] = $with;
			$query['Height'] = $height;
			$query['Mute'] = $mute;
			if ($Scene) {
				$query['Scene'] = $Scene;
			}
			if ($style) {
				$query['Style'] = $style;
			}
			if ($Duration) {
				$query['Duration'] = $Duration;
			}
			if ($DurationAdaption) {
				$query['DurationAdaption'] = $DurationAdaption;
			}
			if (!is_null($TransitionStyle) ) {
				$query['TransitionStyle'] = $TransitionStyle;
			}
			if ($SmartEffect) {
				$query['SmartEffect'] = $SmartEffect;
			}
			if (!is_null($PuzzleEffect)) {
				$query['PuzzleEffect'] = $PuzzleEffect;
			}
			$result = AlibabaCloud::rpc()
				->product('videoenhan')
				// ->scheme('https') // https | http
				->version('2020-03-20')
				->action('GenerateVideo')
				->method('POST')
				->host($this->host)
				->options([
					'query' => $query,
				])
				->request();
			print_r($result->toArray());
		} catch (ClientException $e) {
			echo $e->getErrorMessage() . PHP_EOL;
		} catch (ServerException $e) {
			echo $e->getErrorMessage() . PHP_EOL;
		}
	}
}