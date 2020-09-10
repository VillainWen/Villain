<?php
/*------------------------------------------------------------------------
 * Offiaccount.php 
 * 	
 * 公众号
 *
 * Created on 2020/9/7
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */


namespace Villain\Wechat;

use Villain\Wechat\ApiUrl;
use Villain\Cache;
use Villain\Logs;

class Offiaccount {
	public $appid;
	public $secret;
	public $token;
	public $runtime_path;
	public $data;
	public $tips_info;
	public $media_path;

	public function __construct($appid, $secret, $token, $runtime_path, $media_path) {
		$this->appid        = $appid;
		$this->secret       = $secret;
		$this->token        = $token;

		$this->runtime_path = $runtime_path . 'runtime/villain/wechat/';
		$this->media_path   = $media_path;

		Cache::init($this->runtime_path . 'simplecache/');
	}

	public function init () {
	}

	public function request () {
		$xml = file_get_contents("php://input");
		$this->data = $this->xml2arr($xml);
		$this->logs('微信平台POST：' . print_r($this->data, true));
		return $this->data;
	}

	/**
	 * [getAccessToken 获取AccessToken]
	 * @return [type] [description]
	 */
	public function getAccessToken () {
		$appid  = $this->appid;
		$secret = $this->secret;

		if(!$appid || !$secret){
			$this->logs('未设置AppId和Secret！');
			return false;
		}

		$token = Cache::get($appid . '_token');

		if($token){
			return $token;
		}

		$url = ApiUrl::API['offiaccount']['access_token'] . '?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;

		$get_return = file_get_contents($url);
		$get_data = json_decode($get_return, true);
		$this->logs('获取Accesstoken请求数据: ' . print_r($get_data, true));

		if (isset($get_data['errcode']) && $get_data['errcode']) {
			$this->logs('获取Accesstoken失败: ' . print_r($get_data, true));
			return false;
		}

		Cache::set($appid.'_token', $get_data['access_token'], $get_data['expires_in'] - 200);
		return $get_data['access_token'];
	}

	/**
	 * 获取二维码URL地址
	 * @param  integer $scene_id       场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
	 * @param  array   $params         二维码携带参数
	 * @param  string  $action_name    二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
	 * @param  integer $expire_seconds 该二维码有效时间，以秒为单位。 最大不超过2592000（即30天），此字段如果不填，则默认有效期为30秒。
	 */
	public function get_qrcode_url($scene_id = 1, $action_name = 'QR_SCENE', $expire_seconds = 2592000){
		$access_token = $this->getAccessToken();
		$url = ApiUrl::API['offiaccount']['create_qrcode'] . '?access_token=' . $access_token;
		$post_data['expire_seconds'] = $expire_seconds;
		$post_data['action_name']    = $action_name;
		$post_data['action_info']['scene']['scene_id'] = $scene_id;
		$post_josn = json_encode($post_data);
		$return_json = http($url, $post_josn, 'POST');
		$return_data = json_decode($return_json, true);
		return $return_data;
	}

	/**
	 * [show_qrcode_img 展示二维码]
	 * @param  string $ticket [description]
	 * @return [type]         [description]
	 */
	public function show_qrcode_img ($ticket = '') {
		return ApiUrl::API['offiaccount']['show_qrcode'] . "?ticket=" . $ticket;
	}

	/**
	 * [replyTextMessage 被动回复文本消息]
	 * @param  [type] $to      	[接收方OPENID]
	 * @param  [type] $from    	[发送方微信号]
	 * @param  [type] $content 	[内容]
	 * @param  [type] $FuncFlag [是否新标刚接受到的信息]
	 * @return [type]          	[description]
	 */
	public function replyTextMessage ($to, $from, $content, $FuncFlag = 0) {
		$data['ToUserName']   = $to;
		$data['FromUserName'] = $from;
		$data['CreateTime']   = NOW_TIME;
		$data['MsgType']      = 'text';
		$data['FuncFlag']     = $FuncFlag;
		$data['Content'] 	  = $content;

		return $this->xml($data);
	}

	/**
	 * [replyVoiceMessage description]
	 * @param  [type]  $to       [description]
	 * @param  [type]  $from     [description]
	 * @param  array   $file_id  [description]
	 * @param  integer $FuncFlag [description]
	 * @return [type]            [description]
	 */
	public function replyVoiceMessage ($to, $from, $voice, $FuncFlag = 0) {
		$data['ToUserName']   = $to;
		$data['FromUserName'] = $from;
		$data['CreateTime']   = time();
		$data['MsgType']      = 'voice';
		$data['FuncFlag']     = $FuncFlag;

		// 上传图片到素材 errcode
		$media = $this->uploadMedia($voice, 'voice');

		if ($media['errcode']) {
			$this->logs("临时素材上传失败：" . $media['errmsg']);
			return false;
		}
		$media_id = $media['media_id'];

		$data['Voice']['MediaId'] = $media_id;
		return $this->xml($data);
	}

	/**
	 * [replyVideoMessage 视频消息]
	 * @param  [type]  $to       [description]
	 * @param  [type]  $from     [description]
	 * @param  [type]  $video    [description]
	 * @param  integer $FuncFlag [description]
	 * @return [type]            [description]
	 */
	public function replyVideoMessage ($to, $from, $video, $FuncFlag = 0) {
		$data['ToUserName']   = $to;
		$data['FromUserName'] = $from;
		$data['CreateTime']   = time();
		$data['MsgType']      = 'video';
		$data['FuncFlag']     = $FuncFlag;

		// 上传图片到素材 errcode
		$media = $this->uploadMedia($video['file'], 'video');

		if ($media['errcode']) {
			$this->logs("临时素材上传失败：" . $media['errmsg']);
			return false;
		}
		$media_id = $media['media_id'];

		$data['video']['MediaId']     = $media_id;
		$data['video']['Title']       = $video['title'];
		$data['video']['Description'] = $video['description'];
		return $this->xml($data);
	}

	/**
	 * [replyMusicMessage 音乐]
	 * @param  [type]  $to       [description]
	 * @param  [type]  $from     [description]
	 * @param  [type]  $music    [description]
	 * @param  integer $FuncFlag [description]
	 * @return [type]            [description]
	 */
	public function replyMusicMessage ($to, $from, $music, $FuncFlag = 0) {
		$data['ToUserName']   = $to;
		$data['FromUserName'] = $from;
		$data['CreateTime']   = time();
		$data['MsgType']      = 'music';
		$data['FuncFlag']     = $FuncFlag;

		// 上传图片到素材 errcode
		$media = $this->uploadMedia($music['file'], 'thumb');

		if ($media['errcode']) {
			$this->logs("临时素材上传失败：" . $media['errmsg']);
			return false;
		}
		$media_id = $media['media_id'];

		$data['Music']['ThumbMediaId'] = $media_id;
		$data['Music']['Title']        = $music['title'];
		$data['Music']['Description']  = $music['description'];
		$data['Music']['HQMusicUrl']   = $music['hq_url'];
		$data['Music']['MusicUrl']     = $music['url'];

		return $this->xml($data);
	}

	/**
	 * [replyNewsMessage 图文消息]
	 * @param  [type]  $to       [description]
	 * @param  [type]  $from     [description]
	 * @param  [type]  $news     [图片链接，支持JPG、PNG格式，较好的效果为大图360*200，小图200*200]
	 *  // $news = [
	 *  //     ['Title' => '1', 'Description' => 'a', 'PicUrl' => 'http://wa.jsm', 'Url' => 'http://aaaa.com'],
	 *  //     ['Title' => '1', 'Description' => 'a', 'PicUrl' => 'http://wa.jsm', 'Url' => 'http://aaaa.com'],
	 *  //     ['Title' => '1', 'Description' => 'a', 'PicUrl' => 'http://wa.jsm', 'Url' => 'http://aaaa.com'],
	 *  //     ['Title' => '1', 'Description' => 'a', 'PicUrl' => 'http://wa.jsm', 'Url' => 'http://aaaa.com'],
	 *  // ];
	 * </Articles>
	 * @param  integer $FuncFlag [description]
	 * @return [type]            [description]
	 */
	public function replyNewsMessage ($to, $from, $news, $FuncFlag = 0) {
		$data['ToUserName']   = $to;
		$data['FromUserName'] = $from;
		$data['CreateTime']   = time();
		$data['MsgType']      = 'news';
		$data['FuncFlag']     = $FuncFlag;

		$data['ArticleCount'] = count($news);
		$data['Articles'] = $news;

		return $this->xml($data);
	}

	/**
	 * [replyVoiceMessage description]
	 * @param  [type]  $to       [description]
	 * @param  [type]  $from     [description]
	 * @param  array   $image 	 [description]
	 * @param  integer $FuncFlag [description]
	 * @return [type]            [description]
	 */
	public function replyImageMessage ($to, $from, $image, $FuncFlag = 0) {
		$data['ToUserName']   = $to;
		$data['FromUserName'] = $from;
		$data['CreateTime']   = time();
		$data['MsgType']      = 'image';
		$data['FuncFlag']     = $FuncFlag;

		// 上传图片到素材 errcode
		$media = $this->uploadMedia($image, 'image');

		if ($media['errcode']) {
			$this->logs("临时素材上传失败：" . $media['errmsg']);
			return false;
		}
		$media_id = $media['media_id'];

		$data['Image']['MediaId'] = $media_id;
		return $this->xml($data);
	}

	/**
	 * [getKfTextMessage 客服文本消息]
	 * @param  [type] $to      [description]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public function getKfTextMessage ($to, $content) {
		$data = [];
		$data['touser'] = $to;
		$data['msgtype'] = 'text';
		$data['text'] = [];
		$data['text']['content'] = $content;

		return json_encode($data, 320);
	}

	/**
	 * [getKfNewsMessage 客服图文消息]
	 * @param  string $to   [description]
	 * @param  array  $news [title,description,url,picurl]
	 * @return [type]       [description]
	 */
	public function getKfNewsMessage ($to, $news) {
		$data = [];
		$data['touser'] = $to;
		$data['msgtype'] = 'news';
		$data['news'] = [];
		$data['news']['articles'] = $news;

		return json_encode($data, 320);
	}

	/**
	 * [getKfImageMessage 客服图片消息]
	 * @param  [type] $to       [description]
	 * @param  [type] $media_id [description]
	 * @return [type]           [description]
	 */
	public function getKfImageMessage ($to, $media_id) {
		$data = [];
		$data['touser'] = $to;
		$data['msgtype'] = 'image';
		$data['image'] = [];
		$data['image']['media_id'] = $media_id;

		return json_encode($data, 320);
	}

	/**
	 * 客服小程序卡片消息
	 * @param $to
	 * @param $title
	 * @param $appid
	 * @param $pagepath
	 * @param $thumb_media_id
	 * @return false|string
	 */
	public function getKMiniprogrampageMessage($to,$title,$appid,$pagepath,$thumb_media_id) {
		$data = [];
		$data['touser'] = $to;
		$data['msgtype'] = 'miniprogrampage';
		$data['miniprogrampage'] = [];
		$data['miniprogrampage']['title'] = $title;
		$data['miniprogrampage']['appid'] = $appid;
		$data['miniprogrampage']['pagepath'] = $pagepath;
		$data['miniprogrampage']['thumb_media_id'] = $thumb_media_id;

		return json_encode($data, 320);
	}

	/**
	 * [uploadMedia 上传]
	 * @param  [type] $file [description]
	 * @param  string $type [description]
	 * @return [type]       [description]
	 */
	public function uploadMedia ($file, $type = 'image') {
		$access_token = $this->getAccessToken();
		$url = ApiUrl::API['offiaccount']['upload_media'] . '?access_token=' . $access_token . '&type=' . $type;
		$this->logs('上传文件到微信素材库：' . $file);
		$param['media'] = new \CURLFile(realpath($file));
		$media = http($url, $param, "POST");
		return json_decode($media, true);
	}

	/**
	 * [getUserInfoByOpenId 通过openid获取用户信息]
	 * @param  string $openid [description]
	 * @return [type]         [description]
	 */
	public function getUserInfoByOpenId ($openid = '') {
		$access_token = $this->getAccessToken();
		$url = ApiUrl::API['offiaccount']['userinfo'] . '?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
		$get_return = file_get_contents($url);
		if($get_return){
			$get_data = json_decode($get_return, true);
			$this->logs('通过openid获取用户信息：' . print_r($get_data, true));
		}
		return $get_data;
	}

	/**
	 * [sendKfMessage 发送客服消息]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public function sendKfMessage ($content) {
		$access_token = $this->getAccessToken();
		$url = ApiUrl::API['offiaccount']['customer_send'] . '?access_token='.$access_token;
		$this->logs("开始发送客服消息：" . $content);
		$response = http($url, $content, "POST");
		$this->logs("发送客服消息结果：" . print_r($response, true));
	}

	/**
	 * [logs 日志]
	 * @param  string $content [description]
	 * @return [type]          [description]
	 */
	public function logs ($content = '') {
		$Logs = new Logs();
		$Logs->logs($content, $this->runtime_path);
	}

	/**
	 * 发送模板消息
	 * @param string $to
	 * @param string $template_id
	 * @param array  $router
	 * @param string $data
	 */
	public function sendTemplateMessage($to='', $template_id = '', $router = [], $data = []) {
		$access_token = $this->getAccessToken();

		$url = ApiUrl::API['offiaccount']['template_send'] . '?access_token='.$access_token;
		$send['touser'] = $to;
		$send['template_id'] = $template_id;
		$send['url'] = $router['url'];
		if (isset($router['miniprogram'])) {
			$send['miniprogram']['pagepath'] = $router['miniprogram']['pagepath'];
			$send['miniprogram']['appid'] = $router['miniprogram']['appid'];
		}
		$send['data'] = $data;
		$content = json_encode($send, 320);
		$this->logs("开始发送模板消息：" . $content);
		$response = http($url, $content, "POST");
		$this->logs("发送客服模板结果：" . print_r($response, true));
	}

	/**
	 * [getWebCode 获取权限code]
	 * @param  [type] $redirect_url [description]
	 * @param  string $scope        [description]
	 * @param  string $state        [description]
	 * @return [type]               [description]
	 */
	public function getWebCode ($redirect_url, $scope='snsapi_base', $state='200') {
		$redirect_url = str_replace(':80', '', $redirect_url);
		$redirect_url = urlencode($redirect_url);

		$url = ApiUrl::API['offiaccount']['authorize'] . '?appid='.$this->appid.'&redirect_uri='.$redirect_url.'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';;
		header('Location: ' . $url);
	}

	/**
	 * [getWebCode 获取权限code]
	 * @param  [type] $redirect_url [description]
	 * @param  string $scope        [description]
	 * @param  string $state        [description]
	 * @return [type]               [description]
	 */
	public function getCodeOpenId ($code='') {
		$url = ApiUrl::API['offiaccount']['codeopenid'] . '?appid='.$this->appid.'&secret='.$this->secret.'&code='.$code.'&grant_type=authorization_code';
		$get_return = file_get_contents($url);
		if($get_return){
			$get_data = json_decode($get_return,true);
			$openid = $get_data['openid'];
			if(!$openid){
				$this->logs('微站：通过code获取用户openid['.$code.']！');
				$this->logs('微站：返回结果['.print_r($get_data, true).']！');
			}
		}
		return $openid;
	}

	/**
	 * 对数据进行签名认证，确保是微信发送的数据
	 * @param  string $token 微信开放平台设置的TOKEN
	 * @return boolean       true-签名正确，false-签名错误
	 */
	private function auth($token){
		/* 获取数据 */
		$timestamp = $_GET['timestamp'];
		$nonce     = $_GET['nonce'];
		$signature = $_GET['signature'];
		$sign      = $_GET['signature'];

		// $data = array($_GET['timestamp'], $_GET['nonce'], $token);


		$data = array($token, $timestamp, $nonce);

		/* 对数据进行字典排序 */
		sort($data,SORT_STRING);

		/* 生成签名 */
		$tmpdata = implode($data);
		$signature = sha1($tmpdata);

		if($signature == $sign){
			$this->logs("签名：成功");
		}else{
			$this->logs('获取GET参数：' . print_r($_GET, true));
			$this->logs('签名：失败->本地[' . $signature . ']，微信[' . $sign . ']');
		}

		return $signature === $sign;
	}

	/**
	 * 数据XML编码
	 * @param  object $xml  XML对象
	 * @param  mixed  $data 数据
	 * @param  string $item 数字索引时的节点名称
	 * @return string
	 */
	private function data2xml($xml, $data, $item = 'item') {
		foreach ($data as $key => $value) {
			/* 指定默认的数字key */
			is_numeric($key) && $key = $item;

			/* 添加子元素 */
			if(is_array($value) || is_object($value)){
				$child = $xml->addChild($key);

				$this->data2xml($child, $value, $item);
			} else {
				if(is_numeric($value)){
					$child = $xml->addChild($key, $value);
				} else {
					$child = $xml->addChild($key);
					$node  = dom_import_simplexml($child);
					$node->appendChild($node->ownerDocument->createCDATASection($value));
				}
			}
		}
	}

	/**
	 * [xml description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function xml ($data) {
		return $this->data_to_xml($data);;
	}

	/**
	 * [data_to_xml 数组转xml]
	 * @param  [type] $data       [description]
	 * @param  [type] $parentNode [description]
	 * @param  string $item       [description]
	 * @return [type]             [description]
	 */
	public function data_to_xml ($data, $parentNode = null, $item = 'item') {
		if ($parentNode === null) {
			$simxml = new \SimpleXMLElement('<xml></xml>');
		} else {
			$simxml = $parentNode;
		}

		foreach ($data as $key => $value) {
			if (is_numeric($key)) {
				$key = $item;
			}

			if (is_array($value)) {
				$this->data_to_xml($value, $simxml->addChild($key));
			} else {
				$simxml->addChild($key, $value);
			}
		}

		header('Content-type:text/xml;charse=utf-8');
		return $simxml->saveXML();
	}

	/**
	 * [xml2arr xml转数组]
	 * @param  [type] $xml [description]
	 * @return [type]      [description]
	 */
	public function xml2arr ($xml) {
		$xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		// $xml = $xml->login;
		$jsonStr = json_encode($xml);
		$jsonArray = json_decode($jsonStr,true);
		return $jsonArray;
	}

	/**
	 * 数据XML编码
	 * @param mixed  $data 数据
	 * @param string $item 数字索引时的节点名称
	 * @param string $id   数字索引key转换为的属性名
	 * @return string
	 */
	public function data_to_xml2($data, $item='item', $id='id') {
		$xml = $attr = '';
		foreach ($data as $key => $val) {
			if(is_numeric($key)){
				$id && $attr = " {$id}=\"{$key}\"";
				$key  = $item;
			}
			$xml    .=  "<{$key}{$attr}>";
			$xml    .=  (is_array($val) || is_object($val)) ? $this->data_to_xml($val, $item, $id) : $val;
			$xml    .=  "</{$key}>";
		}
		return $xml;
	}

	public function createMenu ($json_menu = '') {
		$access_token = $this->getAccessToken();
		$url = ApiUrl::API['offiaccount']['menucreate'] . '?access_token='.$access_token;
		$res = http($url, $json_menu, "POST");

		$this->logs('微信助手POST：修改菜单！' . print_r(json_decode($json_menu, true), true));
		if(!$res){
			$this->logs('微信助手POST：请求修改菜单接口通讯失败！！');
			exit( '请求修改菜单接口通讯失败！' );
		}

		$array_data = json_decode($res, true);
		if($array_data['errcode'] == 0){
			$status = true;
			$this->logs('微信助手POST：修改菜单成功！' . $res);
		}else{
			$status = false;
			$this->tips_info = $array_data['errcode'] . ' ' . $array_data['errmsg'];
			$this->logs('微信助手POST：修改菜单失败[' . $array_data['errcode'] . ']！');
		}

		return $status;
	}
}