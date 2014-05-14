<?php
	###### EXAMPLE FILE #####
	/*
		$data = http_request(
							'POST',
							$ip,
							80,
							'/file.php',
							array(),
							array('lol' => 'lol1'),
							array(),
							array(
								0 => 
									array(
										'key' => 'test', 
										'filename' => 'test.txt', 
										'content-type' => 'test/test', 
										'value' => 'teststring'
									)
								),
							array(),
							50000,
							false,
							false);
	*/


	function http_request( 
		$verb = 'GET',             /* HTTP Request Method (GET and POST supported) */ 
		$ip,                       /* Target IP/proxy */ 
		$host,					   /* Target IP/site */
		$port = 80,                /* Target TCP port */ 
		$uri = '/',                /* Target URI */ 
		$getdata = array(),        /* HTTP GET Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
		$postdata = array(),       /* HTTP POST Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
		$cookie = array(),         /* HTTP Cookie Data ie. array('var1' => 'val1', 'var2' => 'val2') */ 
		$files = array(),
		$custom_headers = array(), /* Custom HTTP headers ie. array('Referer: http://localhost/ */ 
		$timeout = 20000,           /* Socket timeout in milliseconds */ 
		$req_hdr = false,          /* Include HTTP request headers */ 
		$res_hdr = false          /* Include HTTP response headers */ 
		) 
	{ 
		global $settings;
	
		$ret = ''; 
		$verb = strtoupper($verb); 
		$cookie_str = ''; 
		$getdata_str = count($getdata) ? '?' : ''; 
		$postdata_str = '';
		$boundary = (string)abs(rand());
		$crlf = "\r\n"; 

		foreach ($getdata as $k => $v) 
			$getdata_str .= urlencode($k) .'='. $v .'&'; 

		if(count($files) == 0) {
			foreach ($postdata as $k => $v) 
				$postdata_str .= urlencode($k) .'='. $v .'&'; 
		} else {
			foreach ($postdata as $k => $v) {
				$postdata_str .= '--' . $boundary . $crlf;
				$postdata_str .= 'Content-Disposition: form-data; name="'.urlencode($k).'"' . $crlf;
				$postdata_str .= $crlf;
				$postdata_str .= $v . $crlf;
			}
			foreach ($files as $v) {
				$postdata_str .= '--' . $boundary . $crlf;
				$postdata_str .= 'Content-Disposition: form-data; name="'.urlencode($v['key']).'"; filename="'.$v['filename'].'"' . $crlf;
				$postdata_str .= 'Content-Type: ' . $v['content-type'] . $crlf;
				$postdata_str .= $crlf;
				$postdata_str .= $v['value'] . $crlf;
			}
			$postdata_str .= '--' . $boundary . '--' . $crlf;
		}
		
		foreach ($cookie as $k => $v) 
			$cookie_str .= urlencode($k) .'='. $v .'; '; 

		$req = $verb .' '. $uri . $getdata_str .' HTTP/1.1' . $crlf; 
		$req .= 'Host: '. $host . $crlf; 
		$req .= 'User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.205 Safari/534.16' . $crlf; 
		$req .= 'Accept: text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1' . $crlf; 
		$req .= 'Accept-Language: ru-RU,ru;q=0.9,en;q=0.8' . $crlf; 
		$req .= 'Accept-Encoding: deflate' . $crlf; 
		$req .= 'Accept-Charset: iso-8859-1, utf-8, utf-16, *;q=0.1' . $crlf; 
		foreach ($custom_headers as $k => $v) 
			$req .= $k .': '. $v . $crlf; 
			
		if (!empty($cookie_str)) 
			$req .= 'Cookie: '. substr($cookie_str, 0, -2) . $crlf;
			
		$req .= 'Cookie2: $Version=1' . $crlf; 
		$req .= 'Connection: Close' . $crlf; 	
		
			
		if ($verb == 'POST' && !empty($postdata_str)) 
		{ 
			if(count($files) == 0) {
				$postdata_str = substr($postdata_str, 0, -1); 
				$req .= 'Content-Type: application/x-www-form-urlencoded' . $crlf; 
				$req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf; 
				$req .= $postdata_str; 
			} else {
				$postdata_str = substr($postdata_str, 0, -1); 
				$req .= 'Content-Type: multipart/form-data; boundary="'.$boundary.'"' . $crlf; 
				$req .= 'Content-Length: '. strlen($postdata_str) . $crlf . $crlf; 
				$req .= $postdata_str; 
			}
		} 
		else $req .= $crlf.$crlf; 
		
		if ($req_hdr) 
			$ret .= $req; 
		
		while(!($fp = @fsockopen($ip, $port, $errno, $errstr,7))) {
			echo "Error in connection, sleep ".$settings['sleepPerError'].PHP_EOL;
			sleep($settings['sleepPerError']);
		}
		
		stream_set_timeout($fp, 0, $timeout * 1000); 
		@fwrite($fp, $req); 
		
		while ($line = fgets($fp)) $ret .= $line; 
		fclose($fp);
		if (!$res_hdr and strpos($ret, "\r\n\r\n") !== FALSE) 
			$ret = substr($ret, strpos($ret, "\r\n\r\n") + 4); 
			
		if($settings['sleepPerQuery'] !== 0)
			sleep($settings['sleepPerQuery']);

		return $ret; 
	} 
	
?>