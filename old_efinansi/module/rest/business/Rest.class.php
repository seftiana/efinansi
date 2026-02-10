<?php
/**
* ================= doc ====================
* FILENAME     : Rest.class.php
* @package     : Rest
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2014-01-09
* @Modified    : 2014-01-09
* @Analysts    : Dyah Fajar
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class Rest extends Database
{
   # internal variables
   private static $mrInstance;
   protected $mSqlFile;
   protected $mServiceClient;
   protected $mToken       = '';
   private $mModule        = '';
   private $mSubModule     = '';
   private $mAction        = 'rest';
   private $mType          = 'rest';
   private $applicationId  = '';
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile         = 'module/rest/business/rest.sql.php';
      parent::__construct($connectionNumber);
   }

   public function setApplication($id)
   {
      $this->applicationId   = $id;
   }

   public function setModule($module)
   {
      $this->mModule          = $module;
   }

   public function setSubModule($subModule)
   {
      $this->mSubModule       = $subModule;
   }

   public function setAction($action)
   {
      $this->mAction          = $action;
   }

   public function setType($type)
   {
      $this->mType            = $type;
   }

   public function setToken($token = null)
   {
      $this->mToken           = $token;
   }

   public function getServiceAddress()
   {
      $getToken   = $this->Open($this->mSqlQueries['get_token'], array(
         $this->applicationId
      ));
      $token      = is_null($this->mToken) ? $getToken[0]['token'] : $this->mToken;
      $url        = Dispatcher::Instance()->GetUrl(
         $this->mModule,
         $this->mSubModule,
         $this->mAction,
         $this->mType
      ).'&token='.$token;

      return parse_url($url);
   }

   public function setDebugOn() {
      #code here
      $this->mServiceClient->setDebugOn();
   }

   public function setDebugOff() {
      #code here
      $this->mServiceClient->setDebugOff();
   }

   public function Send($queries = array(), $method = 'post')
   {
      $query               = $this->Open($this->mSqlQueries['rest_service'], array(
         $this->applicationId
      ));

      $result              = self::ChangeKeyName($query[0]);

      if(empty($query)){
         return array(
            'status' => '403', 
            'data' => null, 
            'message' => GTFWConfiguration::GetValue('message', 'msg403')
         );
      }elseif($result['status'] == 'N'){
         return array(
            'status' => '403', 
            'data' => null, 
            'message' => GTFWConfiguration::GetValue('message', 'msg403')
         );
      }else{
         $serviceAddress   = self::getServiceAddress();
         $servicePath      = $result['service_path'].'?'.$serviceAddress['query'];
         $mRestClientObj      = Dispatcher::Instance()->restClient($servicePath);
         $mRestClientObj->SetPath($servicePath);

         if(empty($queries) OR $queries === NULL OR !is_array($queries)){
            return array(
               'status' => '403', 
               'data' => null, 
               'message' => GTFWConfiguration::GetValue('message', 'msg403')
            );
         }else{
            $queryString         = '';
            if(method_exists(Dispatcher::Instance(), 'getQueryString')){
               $queryString      = Dispatcher::Instance()->getQueryString($queries);
            }else{
               foreach($queries as $key=>$value){
                  $param[$key]   = Dispatcher::Instance()->Encrypt($value);
               }

               $queryString      = urldecode(http_build_query($param));
            }

            switch (strtolower($method)) {
               case 'post':
                  $mRestClientObj->SetPost($queryString);
                  $response      = $mRestClientObj->Send($queryString);
                  return $response['gtfwResult'];
                  break;
               case 'get':
                  $mRestClientObj->SetGet($queryString);
                  $response      = $mRestClientObj->Send($queryString);
                  return $response['gtfwResult'];
                  break;
               default:
                  $mRestClientObj->SetPost($queryString);
                  $response      = $mRestClientObj->Send($queryString);
                  return $response['gtfwResult'];
                  break;
            }
         }

         return array(
            'status' => '403', 
            'data' => null, 
            'message' => GTFWConfiguration::GetValue('message', 'msg403')
         );
      }

      return array(
         'status' => '403', 
         'data' => null, 
         'message' => GTFWConfiguration::GetValue('message', 'msg403')
      );
   }

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function __getQueryString($pathInfo = null)
   {
      $parseUrl            = is_null($pathInfo) ? parse_url($_SERVER['QUERY_STRING']) : parse_url($pathInfo);
      $explodedUrl         = explode('&', $parseUrl['path']);
      $requestData         = '';
      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/^ascomponent=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
         if(preg_match('/uniqid=[a-zA-Z0-9_-]+/', $path)){
            continue;
         }
   
         list($key, $value)   = explode('=', $path);
         $requestData[$key]   = Dispatcher::Instance()->Decrypt($value);
      }
      if(method_exists(Dispatcher::Instance(), 'getQueryString') === true){
         $queryString         = Dispatcher::Instance()->getQueryString($requestData);
      }else{
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
      }
      return $queryString;
   }

   /*
    * @param string $camelCasedWord Camel-cased word to be "underscorized"
    * @param string $case case type, uppercase, lowercase
    * @return string Underscore-syntaxed version of the $camelCasedWord
    */
   public static function humanize($camelCasedWord, $case = 'upper')
   {
      switch ($case) {
         case 'upper':
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'lower':
            $return     = strtolower(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'title':
            $return     = ucwords(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         case 'sentences':
            $return     = ucfirst(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
         default:
            $return     = strtoupper(preg_replace('/(?<=\w)([A-Z])/', '_\1', $camelCasedWord));
            break;
      }
      return $return;
   }
   
   /*
    * @desc change key name from input data
    * @param array $input
    * @param string $case based on humanize method
    * @return array
    */
   public function ChangeKeyName($input = array(), $case = 'lower')
   {
      if(!is_array($input)){
         return $input;
      }
   
      foreach ($input as $key => $value) {
         if(is_array($value)){
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else{
            $array[self::humanize($key, $case)]  = $value;
         }
      }
   
      return (array)$array;
   }

   static function Instance() {
      if (!isset(self::$mrInstance))
         self::$mrInstance = new Rest();

      return self::$mrInstance;
   }
}
?>