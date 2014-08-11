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
         $jsonResult = $wikiRequest->post($action,$login_vars);
         
         //解析JSON
         $result = Array();
         $results = json_decode($jsonResult,true);
         
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
         $jsonResult= $wikiRequest->execute($action,$login_vars);
         
         //解析JSON
         
         return $jsonResult;  
     }
     
     public function checkToken($user){
         $action = "login";
         $login_vars['lgname'] = $user.getUserName();
         
         if($psw){
             $login_vars['lgpassword'] = $psw;
         }else{
             $login_vars['lgtoken'] = $user.getUserToken();
         }     
         $jsonResult = $wikiRequest->execute($action,$login_vars);        
         
         $results = json_decode($jsonResult,true);
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
         $users;
         $jsonData;
         
         $action = 'query';
         
         if($limit){
            $jsonData  = $wikiRequest->execute($action,$limit);
         }else{
            $jsonData = $wikiRequest->execute($action);
         }
         
         //map to array object
         $users=$mapper->mapArray($jsonData,new ArrayObject(),'WikiUser');
         
         return $userArray;
         
     }
     
     /**
      * 创建用户
      */
     public function createAccount($user) {
         $action = "createaccount";
         $jsonData ;
         
         $login_vars['lgname'] = $user.getUserName();
         $login_vars['email'] = $user.getUserEmail();
         $login_vars['realname'] = $user.getUserRealName();
         
         $result  = $wikiRequest->execute($action);
		 $jsonData = json_decode($result,true);
		 
         if($jsonData['result']){
             $user->setUserToken($jsonData['token']);
             $user->setUserId($jsonData['userid']);
             return true;
         }else{
             return false;
         }
     
     }
     //======================================================================
     
     /**
      * Page
      **/
     public function getPages($titles,$prop){
         $action = "query";
         $jsonData;
         
         if($titles){
             $results = $wikiRequest->execute($action,$titles);
             if($prop){
                $prop ="info";
                $results = $wikiRequest->execute($action,$titles,$prop);     
             }
         }
         
         $jsonData = json_decode($results);
         
         if($results){
             if(is_array($jsonData)){
                 return  $pages = $mapper->map($jsonData,new ArrayObject(),'WikiPage');
               
             }else{
                 return $page = $mapper->map($json, new WikiPage());
                
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
                
         $jsonData = $wikiRequest->execute($action,$limit);
         if($jsonData){
                $pages=$mapper->mapArray($jsonData,new ArrayObject(),'WikiPage');
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
        $extracts;
        $jsonData = $wikiRequest->execute($action,$exchars,$titles,$prop);    
        if($jsonData){
            $extracts=$mapper->mapArray($jsonData,new ArrayObject(),'WikiPage');
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
         $images;
         $jsonData = $wikiRequest->execute($action,$titles,$prop);
         if($jsonData){
            $result=$mapper->mapArray($jsonData,new ArrayObject(),'WikiPage');
         }
                
         $result->pageid;
         $pages[]->setImages($result->$images);
         
         return $pages;
     }
    
     
    public function setThumbnail($pages){
        $thumbnail;
        
        $images = $pages->getPageImage();
        
        $action = "query";
        $prop = "pageimages";
        $titles = $pages->getTitle();
                
        $jsonData = $wikiRequest->execute($action,$titles,$prop);
        if($jsonData){
            $thumbnail=$mapper->mapArray($jsonData,new ArrayObject(),'Thumbnail');
        }

        $image->setThumbnail();  
    }
     /**
      * Upload File
      */
     public function uploadFile($file){
        return false;
     }
 	
}
?>
