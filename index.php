<?php   
error_reporting(E_ALL ^ E_NOTICE);   
// ���� HTTP_ACCEPT_LANGUAGE ������   
// ����ֻȡ��һ�������� �������ɸ�����Ҫ��ǿ���ܣ�����ֻ���򵥵ķ�����ʾ��   
  
preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);   
$lang = $matches[1];   
echo $lang;

if (isset($_COOKIE["lang"])){

  $lang = $_COOKIE["lang"];


  switch ($lang) {   
	
			case 'zh-CN' :   
					 header('Location: http://localhost/iworld/cn/');   
					break;   
			case 'zh-tw' :   
					 header('Location: http://localhost/iworld/tw/');   
					break;   
			case 'ko' :   
					 header('Location: http://localhost/iworld/ko/');   
					break;   

			case 'ko-KR' :   
					 header('Location: http://localhost/iworld/ko/');   
					break;  
					

			case 'ja' :   
					 header('Location: http://localhost/iworld/ja/');   
					break;

			case 'ja-JP' :   
					 header('Location: http://localhost/iworld/ja/');   
					break;

			case 'de' :   
					 header('Location: http://localhost/iworld/de/');   
					break;

			case 'ba-RU' :   
					 header('Location: http://localhost/iworld/ru/');   
					break;
			
			case 'en' :   
					 header('Location: http://localhost/iworld/en/');   
					break;


			default:   
					 header('Location: http://localhost/iworld/en/');   
					break;   
					
	} 

}else{


	switch ($lang) {   
			case 'zh-CN' :   
					 header('Location: http://localhost/iworld/cn/');   
					break;   
			case 'zh-tw' :   
					 header('Location: http://localhost/iworld/tw/');   
					break;   
			case 'ko' :   
					 header('Location: http://localhost/iworld/ko/');   
					break;   

			case 'ko-KR' :   
					 header('Location: http://localhost/iworld/ko/');   
					break;  
					

			case 'ja' :   
					 header('Location: http://localhost/iworld/ja/');   
					break;

			case 'ja-JP' :   
					 header('Location: http://localhost/iworld/ja/');   
					break;

			case 'de' :   
					 header('Location: http://localhost/iworld/de/');   
					break;

			case 'ba-RU' :   
					 header('Location: http://localhost/iworld/ru/');   
					break;
			
			case 'en' :   
					 header('Location: http://localhost/iworld/en/');   
					break;


			default:   
					 header('Location: http://localhost/iworld/en/');   
					break;   
	} 


}
?>  