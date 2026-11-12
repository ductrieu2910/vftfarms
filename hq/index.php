<?php
	require_once('config.php' );

	$uri = rtrim( dirname($_SERVER["SCRIPT_NAME"]), '/' );
	$uri = trim( str_replace( $uri, '', $_SERVER['REQUEST_URI'] ), '/' );
	$file_name = ABSPATH.$uri;
	$uri = urldecode($uri );
	$index = strpos($uri, '?');
	
	if($index !== false)
	{
		$PARAMS = substr($uri, $index + 1);
		$uri = substr($uri, 0,$index);
	}
	
	if(strrpos($uri, ".php") !== false)
	{
		// Gọi thẳng .../index.php: chuyển về thư mục, vì include lại chính file này sẽ đệ quy vô hạn (lỗi 500)
		if(realpath(ABSPATH.$uri) === realpath(__FILE__))
		{
			header('Location: '.URL.(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '' ? '?'.$_SERVER['QUERY_STRING'] : ''));
			exit();
		}
		
		include(ABSPATH .$uri);
		exit();
	}
	if(strrpos($uri, ".css") !== false){
		$file_name = ABSPATH.$uri;
		if(file_exists($file_name) == true){
			header('Content-Type: text/css');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			exit();
		}
		

	}else if(strrpos($uri, ".woff2") !== false){
		
		$file_name = ABSPATH.$uri;
		
		if(file_exists($file_name) == true){
			header('Content-Type: application/font-woff2');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			exit();
		}
		

	}else if(strrpos($uri, ".woff") !== false){
		
		$file_name = ABSPATH.$uri;
		

		if(file_exists($file_name) == true){
			header('Content-Type: application/font-woff');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			//$txt = $file_name;
 			//$myfile = file_put_contents(ABSPATH.'log/logs.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
			exit();
		}
		

	}else if(strrpos($uri, ".eot") !== false){
		
		$file_name = ABSPATH.$uri;
		
		if(file_exists($file_name) == true){
			header('Content-Type: application/vnd.ms-fontobject');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			exit();
		}
		

	}else if(strrpos($uri, ".ttf") !== false){
		
		$file_name = ABSPATH.$uri;
		
		if(file_exists($file_name) == true){
			header('Content-Type: application/font-sfnt');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			exit();
		}
		

	}else if(strrpos($uri, ".svg") !== false){
		
		$file_name = ABSPATH.$uri;
		
		if(file_exists($file_name) == true){
			header('Content-Type: image/svg+xml');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			exit();
		}
		

	}else if(strrpos($uri, ".js") !== false){
		$file_name = ABSPATH.$uri;
		if(file_exists($file_name) == true){
			header('Content-Type: application/javascript');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			exit();
		}
		

	}
	else if(strrpos($uri, ".") !== false){
		$file_name = ABSPATH.$uri;
		if(file_exists($file_name) == true){
			header('Content-Type: application/octet-stream');
			header('Content-Length: ' . filesize($file_name));
    			readfile($file_name);
			exit();
		}
		

	}
	function validUser($appSession)
	{
		if($appSession->getUserInfo()->getId() == '')
		{
			
			header('Location: '.URL.'account?continue='.$appSession->getTool()->urlEncode(URL."admin"));
			exit;
		}
	}
	
	
	$uri = rtrim( dirname($_SERVER["SCRIPT_NAME"]), '/' );
	
	$uri = trim( str_replace( $uri, '', $_SERVER['REQUEST_URI'] ), '/' );
	
	$uri = urldecode($uri );
	$index = strpos($uri, '?');
	
	if($index !== false)
	{
		$uri = substr($uri, 0,$index);
	}
	
	if(strrpos($uri, ".php") !== false)
	{
		
		include(ABSPATH .$uri);
		exit();
	}
	
	$base = $uri;
		
	$index = $appSession->getTool()->lastIndexOf($base, '/');
	if($index != -1)
	{
		$base = $appSession->getTool()->substring($base, 0, $index)."/".$routing->findKey($appSession->getTool()->substring($base, $index +1 ), $lang_id);
		
	}
	
	
	$base = $routing->findKey($base, $lang_id);
	if(is_dir(ABSPATH.$base) == false)
	{
		$base = "";
	}
	if($base == "")
	{
		$base = addons;
	}
	
	if(file_exists(ABSPATH .$base.'/index.php') == true)
	{
		include( ABSPATH .$base.'/index.php' );
		exit();
	}
	include( ABSPATH .addons.'/index.php' );
	exit();
?>