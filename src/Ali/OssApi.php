<?php
/*------------------------------------------------------------------------
 * OssApi.php 
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

use OSS\OssClient;
use OSS\Core\OssException;
use AlibabaCloud\SDK\ViapiUtils\ViapiUtils;

class OssApi {

	/**
	 *  阿里云主账号AccessKey拥有所有API的访问权限，风险很高。
	 *  强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录RAM控制台创建RAM账号。
	 * @var
	 */
	protected $accessKeyId;
	protected $accessKeySecret;

	/**
	 * 其它Region请按实际情况填写。以杭州为例http://oss-cn-hangzhou.aliyuncs.com
	 * @var
	 */
	protected $endpoint;

	/**
	 * 设置存储空间名称。
	 * @var
	 */
	protected $bucket;

	/**
	 * 客户端
	 * @var
	 */
	protected $ossClient;

	/**
	 * 错误消息
	 * @var string
	 */
	protected $tips_info;

	/**
	 * OssApi 构造函数.
	 * @param $accessKeyId      账号ID
	 * @param $accessKeySecret  账号密钥
	 * @param $endpoint         节点
	 * @param $bucket           空间名
	 * @throws OssException
	 */
	public function __construct ($accessKeyId, $accessKeySecret, $endpoint, $bucket, $securityToken = null) {
		$this->accessKeyId = $accessKeyId;
		$this->accessKeySecret = $accessKeySecret;
		$this->endpoint = $endpoint;
		$this->bucket = $bucket;
		try {
			$this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint, false, $securityToken);
		} catch(OssException $e) {
			$this->tips_info = $e->getMessage();
			return false;
		}
	}

	/**
	 * 文件上传
	 * 由本地文件路径加文件名包括后缀组成，例如/users/local/test.txt
	 * @param $object
	 * @param $filePath
	 * @return bool
	 */
	public function uploadFile ($object, $filePath) {
		try{
			$this->ossClient->uploadFile($this->bucket, $object, $filePath);
			return true;
		} catch(OssException $e) {
			$this->tips_info = $e->getMessage();
			return false;
		}
	}

	/**
	 * 错误消息
	 * @return string
	 */
	public function getMessage() {
		return $this->tips_info;
	}

	public function getFileLoadAddress ($filepath = '') {
		return  ViapiUtils::upload($this->accessKeyId, $this->accessKeySecret, $filepath);
	}
}