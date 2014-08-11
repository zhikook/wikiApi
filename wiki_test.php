<?php
require_once __DIR__ . '/wiki_api.php';     
    
/*
 * Created on 2014-8-5
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
    
    //加载相关对象
    global $wikiApi;
    global $output;
    
    $wikiApi=new WikiApi();
    
    //=================================================================
    function get_page($title){
        $page = $wikiApi->getPages($title);  
        return $page;
    }
    
    function get_recent_pages($limit){
        $page = $wikiApi->getPages($limit);      
        return $pages;
    }
    
    function get_recent_pages($cates,$limit){
        $pages = $wikiApi->getPages($cates,$limit);      
        return $pages;
    }
    
    function get_page_image($title){
       	$pages = $wikiApi-> getPages($title);
    	$pageimages= $wikiApi->setImages($pages);
    	return $pageimages; 
    }
    
    function get_page_thumb($title){
    	$pages = $wikiApi-> getPages($title);
    	$pagethumb = $wikiApi->setThumbnail($pages);
    	return $pagethumb;
    }
    
    //=================================================================
    
    function get_user_list(){
        $userlist = $wikiApi->getUserList();
    }

 ?>
