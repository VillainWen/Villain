<?php
/*------------------------------------------------------------------------
 * BaiduApi.php 
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

use Villain\Cache;
use Villain\Logs;
use Villain\Nlp\NlpInterface;
class BaiduApi implements NlpInterface{

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

	/**
	 * 获取AccessToken
	 * @return bool|mixed|string
	 */
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
	public function getKeyword ($title = '', $content = '') {
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
	public function getTopic ($title = '', $content = '') {
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
	public function getSummary ($title = '', $content = '', $max_summary_len = 300) {
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
	 * n => 普通名词	f => 方位名词	s	处所名词	t => 时间名词
	 * nr => 人名	ns => 地名 nt => 机构团体名	nw => 作品名
	 * nz => 其他专名	v => 普通动词	vd	动副词	vn => 名动词
	 * a => 形容词	ad => 副形词	an	名形词	d => 副词
	 * m => 数量词	q => 量词	r => 代词	p => 介词
	 * c => 连词	u => 助词	xc => 其他虚词	w => 标点符号
	 * PER => 人名	LOC => 地名	ORG => 机构名	TIME => 时间
	 * @param string $text  待分析文本，长度不超过20000字节
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
	 * 依存句法分析
	 * @param string $content   待分析文本，长度不超过256字节
	 * @param int    $mode      模型选择。默认值为0，可选值mode=0（对应web模型）；mode=1（对应query模型）
	 * @return bool|mixed
	 */
	public function depparser( $content = '', $mode = 0) {
		$access_token = $this->getAccessToken();
		$data['mode'] = $mode;
		$data['text'] = $content;
		$url = "https://aip.baidubce.com/rpc/2.0/nlp/v1/depparser?charset=UTF-8&access_token=" . $access_token;
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