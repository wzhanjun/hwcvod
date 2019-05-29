<?php
namespace hwcvod\obs\s3\signature;

use hwcvod\obs\common\Constants;
use hwcvod\obs\common\Model;

class V2Signature extends AbstractSignature
{
	const INTEREST_HEADER_KEY_LIST = array('content-type', 'content-md5', 'date');
	
	public function __construct($ak, $sk, $pathStyle, $endpoint, $methodName, $securityToken=false)
	{
	    parent::__construct($ak, $sk, $pathStyle, $endpoint, $methodName, $securityToken);
	}
	
	public function doAuth(array &$requestConfig, array &$params, Model $model, array &$pathArg)
	{
		$result = $this -> prepareAuth($requestConfig, $params, $model, $pathArg);
		
		/*$result['headers']['Date'] = gmdate('D, d M Y H:i:s \G\M\T');
		
		$canonicalstring = $this-> makeCanonicalstring($result['method'], $result['headers'], $result['pathArgs'], $result['dnsParam'], $result['uriParam']);
		
		$result['cannonicalRequest'] = $canonicalstring;
		
		$signature = base64_encode(hash_hmac('sha1', $canonicalstring, $this->sk, true));
		
		$authorization = 'AWS ' . $this->ak . ':' . $signature;
		
		$result['headers']['Authorization'] = $authorization;*/
		
		return $result;
	}	
	
	public function makeCanonicalstring($method, $headers, $pathArgs, $bucketName, $objectKey, $expires = null)
	{
		$buffer = [];
		
		$buffer[] = $method;
		$buffer[] = "\n";
		
		$interestHeaders = [];
		
		foreach ($headers as $key => $value){
			$key = strtolower($key);
			if(in_array($key, self::INTEREST_HEADER_KEY_LIST) || strpos($key, Constants::AMAZON_HEADER_PREFIX) === 0){
				$interestHeaders[$key] = $value;
			}
		}
		
		if(array_key_exists(Constants::ALTERNATIVE_DATE_HEADER, $interestHeaders)){
			$interestHeaders['date'] = '';
		} 
		
		if($expires !== null){
			$interestHeaders['date'] = strval($expires);
		}
		
		if(!array_key_exists('content-type', $interestHeaders)){
			$interestHeaders['content-type'] = '';
		}
		
		if(!array_key_exists('content-md5', $interestHeaders)){
			$interestHeaders['content-md5'] = '';
		}
		
		ksort($interestHeaders);
		
		foreach ($interestHeaders as $key => $value){
			if(strpos($key, Constants::AMAZON_HEADER_PREFIX) === 0){
				$buffer[] = $key . ':' . $value;
			}else{
				$buffer[] = $value;
			}
			$buffer[] = "\n";
		}
		
		$uri = '';
		
		if($bucketName){
			$uri .= '/';
			$uri .= $bucketName;
			if(!$this->pathStyle){
				$uri .= '/';
			}
		}
		
		if($objectKey){
			if(!($pos=strripos($uri, '/')) || strlen($uri)-1 !== $pos){
				$uri .= '/';
			}
			$uri .= $objectKey;
		}
		
		$buffer[] = $uri === ''? '/' : $uri;
		
		if(!empty($pathArgs)){
			ksort($pathArgs);
			$_pathArgs = [];
			foreach ($pathArgs as $key => $value){
				if(in_array(strtolower($key), Constants::ALLOWED_RESOURCE_PARAMTER_NAMES)){
					$_pathArgs[] = $value === null || $value === '' ? $key : $key . '=' . urldecode($value);
				}
			}
			if(!empty($_pathArgs)){
				$buffer[] = '?';
				$buffer[] = implode('&', $_pathArgs);
			}
		}
		
		return implode('', $buffer);
	}

}