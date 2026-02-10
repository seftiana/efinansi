<?php

/**
 * GetCookieSppu
 * untuk manajemen cookie pada pilihan sppu
 * 
 * @author noorhadi <no3r_hadi@yahoo.com>
 * 
 * 2021
 */


class GetCookieSppu {

   private $_getCookie = array();

   public function __construct(){

      /**
       * koleksi coockie ke array
       */
      $_COOKIE = is_object($_COOKIE) ? $_COOKIE->AsArray() : $_COOKIE;
      $getCookie = array();
      if (is_array($_COOKIE) && !empty($_COOKIE)) {
         foreach($_COOKIE as $key => $item){
            if( substr($key,0,6) === "myCBId") {         
               array_push($getCookie, $item); 
            }
         }
      }
      $this->_getCookie = $getCookie;
   }

   /**
    * get
    * get isi coockie (value)
    */
   public function get() {
      return $this->_getCookie;
   }

   /**
    * remove
    * untuk hapus
    */
   public function remove(){   
      $getCookie = $this->_getCookie;
      if (is_array($getCookie) && !empty($getCookie)) {
         foreach($getCookie as  $value){ 
            unset($_COOKIE["myCBId_" .$value]); 
         }
      }  
   }

}