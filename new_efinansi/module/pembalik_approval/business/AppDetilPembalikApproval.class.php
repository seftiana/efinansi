<?php

class AppDetilPembalikApproval extends Database {

   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;
   protected $mUserId   = NULL;

   function __construct($connectionNumber=0) {
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      $this->mSqlFile   = 'module/pembalik_approval/business/appdetilpembalikapproval.sql.php';
      parent::__construct($connectionNumber);
   }

   private function setUserId()
   {
      if(class_exists('Security')){
         if(method_exists(Security::Instance(), 'GetUserId')){
            $this->mUserId    = Security::Instance()->GetUserId();
         }else{
            $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
         }
      }
   }

   public function getUserId()
   {
      $this->setUserId();
      return (int)$this->mUserId;
   }

   function GetInformasi($kegiatan_detil_id) {
      $result     = $this->Open($this->mSqlQueries['get_informasi'], array($kegiatan_detil_id));
      return $result[0];
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());
      if($return){
         return (int)$return[0]['count'];
      }else{
         return 0;
      }
   }

   function GetData($offset, $limit, $kegiatan_detil_id) {
      $result = $this->Open($this->mSqlQueries['get_data'], array(
         $kegiatan_detil_id,
         $kegiatan_detil_id,
         $kegiatan_detil_id,
         $offset, 
         $limit));
      return $result;
   }

   function GetCountData($kegiatan_detil_id) {
      $result = $this->Open($this->mSqlQueries['get_count_data'], array($kegiatan_detil_id));
      if (!$result) {
         return 0;
      } else {
         return $result[0]['total'];
      }
   }

   function GetStatusPembalikApproval($id) {
      $result = $this->Open($this->mSqlQueries['get_status_approval'], array($id));
      return $result;
   }

   function DoUpdateDetilPembalikApproval($id) {
      for($i=0;$i<sizeof($id);$i++) {
         $result = $this->Execute($this->mSqlQueries['do_update_detil_approval'], array($id[$i]));
      }
      return $result;
   }

   function DoUpdateStatusPembalikApprovalKegiatanDetil($id) {
      $result = $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array($id));
      return $result;
   }

   function getLastLogId($id){
      $result = $this->Open($this->mSqlQueries['get_last_log_kegiatan_detail'], array($id));
      $return = array();
      foreach ($result as $value)
         $return = $value['kodeaksi'];

      return $return;
   }

   public function DoInsertKegdetStatus($id = array(), $keg_id){

      $result     = true;
      $this->StartTrans();
      if(!is_array($id)){
         $result  &= false;
      }

      $userId    = $this->getUserId();
      $last_log  = $this->getLastLogId($keg_id->mrVariable);

      $param = array();
      
      for($i=0;$i<sizeof($id);$i++){
         $param[$i]['id']        = $id[$i];
         $param[$i]['kegdet_id'] = $keg_id->mrVariable;
         $param[$i]['last_log']  = $last_log;
      } 

      for($i=0;$i<sizeof($id);$i++){
         $result     &= $this->Execute($this->mSqlQueries['do_insert_kegdet_status'], array(
            $param[$i]['kegdet_id'],
            $param[$i]['id'],
            $userId,
            date('Y-m-d H:i:s'),
            'Balik Persetujuan',
            $param[$i]['last_log'] == NULL ? 1 : $param[$i]['last_log']+1
         ));
      }
      
      $return['result']    = $this->EndTrans($result);
      return $return;
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

   /**
    * @param string  path_info url to be parsed, default null
    * @return string parsed_url based on Dispatcher::Instance()->getQueryString();
    */
   public function _getQueryString($pathInfo = null)
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
}
?>
