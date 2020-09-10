<?php
/*------------------------------------------------------------------------
 * Cache.php 
 * 	
 * 缓存
 *
 * Created on 2020/9/10
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */


namespace Villain;

use Villain\Aes;
class Cache {
	private static $cacheDir;
	private static $cacheFile;
	private static $cacheTime;

	private static $aes;
	/**
	 * [init 初始化]
	 * @param  [type]  $cache_dir  [description]
	 * @param  integer $cache_time [description]
	 * @return [type]              [description]
	 */
	public static function init($cache_dir, $cache_time=600){
		self::$cacheDir = $cache_dir;
		self::$cacheTime = $cache_time;
		self::$cacheFile = $cache_dir.'/simplecache.scache';

		self::$aes = new Aes();
	}

	/**
	 * [get 获取缓存数据]
	 * @param  [type] $key     [description]
	 * @param  string $default [description]
	 * @return [type]          [description]
	 */
	public static function get($key, $default=''){

		$data = self::readAndRender();
		self::checkTimeoutAndSave($data);

		if(isset($data[$key])){
			return $data[$key]['value'];
		}else{
			return $default;
		}
	}

	/**
	 * [set 设置缓存数据]
	 * @param [type]  $key   [description]
	 * @param [type]  $value [description]
	 * @param boolean $time  [description]
	 */
	public static function set($key, $value, $time = false){
		if(!$time) $time = self::$cacheTime;

		$data = self::readAndRender();
		$data[$key] = ['value'=>$value,'time'=>time()+$time];

		return self::checkTimeoutAndSave($data);
	}

	/**
	 * [readAndRender 读取]
	 * @return [type] [description]
	 */
	private static function readAndRender(){
		if(!file_exists(self::$cacheDir)){
			mkdir(self::$cacheDir, 0755, true);
		}

		if(file_exists(self::$cacheFile)){
			$json = file_get_contents(self::$cacheFile);
			$json = self::$aes->decryptWithOpenssl($json);
			$data = json_decode($json, true);
			if(!is_array($data)){
				$data = [];
			}
		}else{
			$data = [];
		}

		return $data;
	}

	/**
	 * [checkTimeoutAndSave 检查]
	 * @param  [type] &$data [description]
	 * @return [type]        [description]
	 */
	private static function checkTimeoutAndSave(&$data){
		$cur_time = time();
		foreach($data as $k=>$v){
			if($cur_time>$data[$k]['time']){
				unset($data[$k]);
			}
		}
		$content = json_encode($data);
		$content = self::$aes->encryptWithOpenssl($content);
		if(file_put_contents(self::$cacheFile, $content)){
			return true;
		}else{
			return false;
		}
	}
}