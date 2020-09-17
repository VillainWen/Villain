<?php
/*------------------------------------------------------------------------
 * BaiduApi.php 
 * 	
 * Description
 *
 * Created on 2020/9/9
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */
namespace Villain\Baidu;

use Villain\Cache;
use Villain\Logs;

class BaiduApi {
	/**
	 * 开发者ID
	 * @var
	 */
	protected $appid;

	/**
	 * 应用Key
	 * @var
	 */
	protected $api_key;

	/**
	 * 应用密钥
	 * @var
	 */
	protected $api_secret;

	/**
	 * 缓存路径
	 * @var string
	 */
	protected $runtime_path;

	function __construct ($appid, $api_key, $api_secret, $runtime_path='') {
		$this->appid      = $appid;
		$this->api_key    = $api_key;
		$this->api_secret = $api_secret;

		$this->runtime_path = $runtime_path . 'runtime/villain/baidu/';

		Cache::init($this->runtime_path . 'simplecache/');
	}

	public function getAccessToken () {
		$appid  = $this->api_key;
		$secret = $this->api_secret;

		if(!$appid || !$secret){
			$this->logs('未设置AppId和Secret！');
			return false;
		}

		$token = Cache::get($appid . '_token');
		$time  = Cache::get($appid . '_time');

		if ($time > time() + 60*60*24*29) {
			if($token){
				return $token;
			}
		}

		$url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=' . $this->api_key . '&client_secret=' . $this->api_secret;

		$json = $this->http($url, '', "POST");

		$data = json_decode($json, true);

		if (isset($data['error']) && $data['error']) {
			$this->logs($data['error_description']);
			return false;
		}

		Cache::set($appid.'_token', $data['access_token'], $data['expires_in']);
		Cache::set($appid.'_time', $data['expires_in']+time(),  $data['expires_in']);
		return $data['access_token'];
	}

	/**
	 * 文章标签接口
	 * @param string $title
	 * @param string $content
	 * @return bool|mixed
	 */
	public function getNplKeyword ($title = '', $content = '') {
		$access_token = $this->getAccessToken();

		$url = "https://aip.baidubce.com/rpc/2.0/nlp/v1/keyword?charset=UTF-8&access_token=" . $access_token;

		$data['title'] = $title;
		$data['content'] = $content;

		$json = $this->http($url, json_encode($data, 320), "POST", ["Content-type: application/json"]);

		$return = json_decode($json, true);

		if (isset($return['error_code']) && $return['error_code']) {
			$this->logs($return['error_msg']);
			return false;
		}

		return $return['items'];
	}

	/**
	 * 文章分类接口
	 * @param string $title
	 * @param string $content
	 * @return bool|mixed
	 */
	public function getNplTopic ($title = '', $content = '') {
		$access_token = $this->getAccessToken();

		$url = "https://aip.baidubce.com/rpc/2.0/nlp/v1/topic?charset=UTF-8&access_token=" . $access_token;

		$data['title'] = $title;
		$data['content'] = $content;

		$json = $this->http($url, json_encode($data, 320), "POST", ["Content-type: application/json"]);

		$return = json_decode($json, true);

		if (isset($return['error_code']) && $return['error_code']) {
			$this->logs($return['error_msg']);
			return false;
		}

		return $return['item'];
	}

	/**
	 * 文章摘要
	 * @param string $title
	 * @param string $content
	 * @param int    $max_summary_len
	 * @return bool|mixed
	 */
	public function getNplNewsSummary ($title = '', $content = '', $max_summary_len = 300) {
		$access_token = $this->getAccessToken();

		$url = "https://aip.baidubce.com/rpc/2.0/nlp/v1/news_summary?charset=UTF-8&access_token=" . $access_token;

		$data['title'] = $title;
		$data['content'] = $content;
		$data['max_summary_len'] = $max_summary_len;

		$json = $this->http($url, json_encode($data, 320), "POST", ["Content-type: application/json"]);

		$return = json_decode($json, true);

		if (isset($return['error_code']) && $return['error_code']) {
			$this->logs($return['error_msg']);
			return false;
		}

		return $return['summary'];
	}

	/**
	 * 词法分析
	 * @param string $text
	 * @return bool|mixed
	 */
	public function lexer ($text = "") {
		$access_token = $this->getAccessToken();
		$url = "https://aip.baidubce.com/rpc/2.0/nlp/v1/lexer?charset=UTF-8&access_token=" . $access_token;

		$data['text'] = $text;

		$json = $this->http($url, json_encode($data, 320), "POST", ["Content-type: application/json"]);

		$return = json_decode($json, true);

		if (isset($return['error_code']) && $return['error_code']) {
			$this->logs($return['error_msg']);
			return false;
		}

		return $return;
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

	/**
	 * 请求HTTP数据
	 * @param  [type] $url     完整URL地址
	 * @param  string $params GET、POST参数
	 * @param  string $method 提交方式GET、POST
	 * @param  array $header Header参数
	 */
	protected function http($url, $params = '', $method = 'GET', $header = array(), $agent = array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		if (strtoupper($method) == 'POST' && !empty($params)) {
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		if (strtoupper($method) == 'GET' && $params) {
			$query_str = http_build_query($params);
			$url       = $url . '?' . $query_str;
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		if (!empty($agent)) {
			curl_setopt($ch, CURLOPT_PROXY, $agent['ip']); //代理服务器地址
			curl_setopt($ch, CURLOPT_PROXYPORT, $agent['port']); //代理服务器端口
			//http代理认证帐号，username:password的格式
			if ($agent['username'] && $agent['password']) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $agent['username'] . ":" . $agent['password']);
				curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式
			}
		}
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			return curl_error($ch);
		}
		curl_close($ch);

		return $response;
	}
}