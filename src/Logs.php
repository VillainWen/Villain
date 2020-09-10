<?php
/*------------------------------------------------------------------------
 * Logs.php 
 * 	
 * 日志
 *
 * Created on 2020/9/10
 *
 * Author: 蚊子 <1423782121@qq.com>
 * 
 * Copyright (c) 2020 All rights reserved.
 * ------------------------------------------------------------------------
 */

namespace Villain;


class Logs {
	/**
	 * [logs 日志]
	 * @param  string $content [description]
	 * @return [type]          [description]
	 */
	public function logs ($content = '', $base_path) {
		$path = 'log/' . date("Y-m") . '/';
		$full_path = $base_path . $path;

		if (!is_dir($full_path)) {
			mkdir($full_path, 0755, true);
		}
		$filename = date("d") . '.log';
		file_put_contents($full_path . $filename, $content . PHP_EOL, FILE_APPEND);
	}
}