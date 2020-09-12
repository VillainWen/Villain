<?php
/*------------------------------------------------------------------------
 * StsApi.php 
 * 	
 * 文档地址 https://help.aliyun.com/document_detail/28763.html?spm=a2c4g.11186623.6.798.2c924ee9X6CV4X
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
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Sts\Sts;
use AlibabaCloud\Sts\V20150401\AssumeRole;

class StsApi {

	/**
	 *  阿里云主账号AccessKey拥有所有API的访问权限，风险很高。
	 *  强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录RAM控制台创建RAM账号。
	 * @var
	 */
	protected $accessKeyId;
	protected $accessKeySecret;

	protected $regionId = 'cn-hangzhou';

	/**
	 * 指定角色的ARN。格式：acs:ram::$accountID:role/$roleName 。
	 * @var
	 */
	protected $arn;

	/**
	 * 权限策略。
	 * 生成STS Token时可以指定一个额外的权限策略，以进一步限制STS Token的权限。
	 * 若不指定则返回的Token拥有指定角色的所有权限。
	 * 长度为1~1024个字符。
	 * {"Statement": [{"Action": ["*"],"Effect": "Allow","Resource": ["*"]}],"Version":"1"}
	 * @var
	 */
	protected $policy;

	/**
	 * 用户自定义参数。此参数用来区分不同的令牌，可用于用户级别的访问审计。
	 * 长度为2~32个字符，可包含英文字母、数字、英文句点（.）、at（@）、短划线（-）和下划线（_）。
	 * @var
	 */
	protected $roleSessionName;

	/**
	 * 过期时间，单位为秒。
	 * 过期时间最小值为900秒，最大值为MaxSessionDuration设置的时间。默认值为3600秒。
	 * @var
	 */
	protected $durationSeconds = 3600;

	protected $tips_info;

	public function __construct ($accessKeyId, $accessKeySecret, $regionId) {
		AlibabaCloud::accessKeyClient($accessKeyId, $accessKeySecret)->regionId($regionId)->asDefaultClient();
	}

	/**
	 * @param        $arn
	 * @param string $policy
	 * @param string $roleSessionName
	 * @param int    $durationSeconds
	 * @return array|bool
	 * @throws ClientException
	 */
	public function getSts ($arn, $policy, $roleSessionName = 'client', $durationSeconds = 3600) {
		try {
			$response = Sts::v20150401()->assumeRole()
							->withRoleArn($arn)
							->withRoleSessionName($roleSessionName)
							->withPolicy($policy)
							->connectTimeout(60)
							->withDurationSeconds($durationSeconds)
							->timeout(65)
							->request();
			return $response->toArray();
		} catch (ServerException $exception) {
			$this->tips_info = $exception->getErrorMessage();
			return false;
		}
	}
}