<?php

require_once __DIR__ . '/JsonMapper.php';
require_once __DIR__ . '/wiki_cate.php'; 
require_once __DIR__ . '/wiki_page.php';  
require_once __DIR__ . '/wiki_user.php';  
require_once __DIR__ . '/wiki_request.php'; 
    
/*
 * Created on 2014-8-5
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 class WikiApi{
     private $wikiRequest ;
     private $mapper ;
     
     public function __construct(){
        $mapper = new JsonMapper();
        $wikiRequest= new WikiApiRequest();
     }
     
     /**
      * login();
      * $user
      * User Login;
      * return true|false;
      *  <login
      * 	result="NeedToken"
      *     token="b5780b6e2f27e20b450921d9461010b4"
      * 	cookieprefix="enwiki"
      *     sessionid="17ab96bd8ffbe8ca58a78657a918558e"
      *	 />
      **/  
      
     public function login($user,$psw){
         $action = "login";
         $login_vars['lgname'] = $user.getUserName();
         
         if($psw){
             $login_vars['lgpassword'] = $psw;
         }else{
             $login_vars['lgtoken'] = $user.getUserToken();
         }
         $request = $wikiRequest->post($action,$login_vars);
         
         //解析JSON
         $result = Array();
         $results = json_decode($request,true);
         
         if($results['login']['result']=="success"){
              //解析JSON
         	$arr['lguserid'] = $results['login']['lguserid'];
         	$arr['lgusername'] = $results['login']['lgusername'];
         	$arr['lgtoken'] = $results['login']['lgtoken'];
         	$arr['sessionid'] = $results['login']['sessionid'];
         	
         	$user->setFromArray($arr); 
            return $user;
         }else{
             return false;
         }
          
     }
     
     /**
      * unlogin()
      * login out
      * $user
      * return true|false;
      * 
      */
     public function unlogin($user){
         $action = "logout";
         $login_vars['lgname'] = $user.getUserName();  
         $request= $wikiRequest->execute($action,$login_vars);
         
         return $request;  
     }
     
     public function checkToken($user){
         $action = "login";
         
         if($user.getUserToken()){
         	
         }
         $login_vars['lgname'] = $user.getUserName();
         
         if($psw){
             $login_vars['lgpassword'] = $psw;
         }else{
             $login_vars['lgtoken'] = $user.getUserToken();
         }     
         $request = $wikiRequest->execute($action,$login_vars);        
         
         $results = json_decode($request,true);
         if($results['login']['result']=="success"){
              //解析JSON
         	$arr['lguserid'] = $results['login']['lguserid'];
         	$arr['lgusername'] = $results['login']['lgusername'];
         	$arr['lgtoken'] = $results['login']['lgtoken'];
         	$arr['sessionid'] = $results['login']['sessionid'];
         	
         	$user->setFromArray($arr); 
            return true;
         }else{
             return false;
         }
         
     }
     
     public function getUserList($limit){
         $action = 'query';
         
         if($limit){
            $request  = $wikiRequest->execute($action,$limit);
         }else{
            $request = $wikiRequest->execute($action);
         }
         $result = json_decode($request,true);
         
         //map to array object
         $users=$mapper->mapArray($result,new ArrayObject(),'WikiUser');
         
         return $users;
         
     }
     
     /**
      * 创建用户
      */
     public function createAccount($user) {
         $action = "createaccount";
         
         $login_vars['lgname'] = $user.getUserName();
         $login_vars['email'] = $user.getUserEmail();
         $login_vars['realname'] = $user.getUserRealName();
         
         $request  = $wikiRequest->post($action,$login_vars); 
		 $result = json_decode($request,true);
		 
		 if($result['token']&&$result['result']='ndtoken'){
		 	$login_vars['token'] = $result['token'];
		 	$request  = $wikiRequest->post($action,$login_vars);
		 	$result = json_decode($request,true);
		  	if($result['result']=='success'){
             	$user->setUserToken($result['token']);
             	$user->setUserId($result['userid']);
             	return $user;
         	}
         	//if network never work,please request try.
         	
		 }
		 
		 return false;     
     }
     //======================================================================
     
     public function getPageContent($titles){
     	$action = "query";
     	$prop = "revisions";
        $rvprop = "content";
        
        if($titles){
        	$request = $wikiRequest->execute($action,$titles,$prop,$rvprop);
        	$result = json_decode($request);
        	
        	echo $result;
        }
     }
     
     /**
      * Page
      **/
     public function getPageSummary($titles){
         $action = "query";
         $prop = "info";
       
         
         if($titles){
             $request = $wikiRequest->execute($action,$titles);
             if($prop){
                $prop ="info";
                $request = $wikiRequest->execute($action,$titles,$prop);     
             }
         }
         
         $result = json_decode($request);
         
         if($request){
             if(is_array($result)){
                 return  $pages = $mapper->map($result,new ArrayObject(),'WikiPage');
               
             }else{
                 return $page = $mapper->map($result, new WikiPage());
             }
         }else{
         	return false;
         }
     }
     
     /**
      * PageList
      **/
     public function getPages($limit){
         $action = "query";
         $pages;
                
         $request = $wikiRequest->execute($action,$limit);
         $result = json_decode($request,true);
         
         if($result){
                $pages=$mapper->mapArray($result,new ArrayObject(),'WikiPage');
         }
         return $pages;
     }
     
    /**
     * getPagesExtracts()
     * $exchars:截取字数
     * return: pages extracts数组
     */
                
     public function getPagesExtracts($exchars,$titles){
        $action = "query";
        $prop = "extracts";
        
        $request = $wikiRequest->execute($action,$exchars,$titles,$prop); 
        $result = json_decode($request); 
        
         
        
        if($result){
            $extracts=$mapper->mapArray($result,new ArrayObject(),'WikiPage');
        }
        
        return $extracts;
     }
        
    //=================================================================================
                
     /**
      * Images
      **/
     public function setImages($pages,$titles){
         $action = "query";
         $prop = "imageinfo";
         $pages;
         
         $request = $wikiRequest->execute($action,$titles,$prop);
         $result = json_decode($request);
         
         //遍历
         while ($key = key($result)) {
         	$json_imageinfo = json_decode($result[page][imageinfo]);
			$image = $mapper->mapArray($json_imageinfo,new ArrayObject(),'WikiImage');
			$pages[$key]->setImages($image);
			next($result);
		 }
         return $pages;
     }
    
     
    /**
     * 
     */
    public function setThumbnail($pages){
        $thumbnail;
        
        $images ;
        
        $action = "query";
        $prop = "pageimages";
        
        for ($i=0; $i < count($pages); $i+=1) {
        	$titles .= $pages[i]->getTitle();
        	$images[i] = $pages[i]->getPageImage();
        	if(!$images[i]){
        		$pages[i]->setImage();
        	}
        }
        
        $request = $wikiRequest->execute($action,$titles,$prop);
        $result = json_decode($request);
        
        for ($i=0; $i < count($result); $i+=1) {
        	$thumbnailData = $result[page][thumbnail];
        
        	if($thumbnailData){
            	$thumbnail=$mapper->mapArray($thumbnailData,new ArrayObject(),'Thumbnail');
        	}

        	$image->setThumbnail();  
        }
       
    }
     /**
      * Upload File
      */
     public function uploadFile($file){
        return false;
     }
 	
}
?>
