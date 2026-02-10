<?php
class AppDetilApproval extends Database
{
   protected $mSqlFile;
   protected $mUserId = NULL;
   public $_POST;
   public $_GET;
   public $method;

   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/approval/business/appdetilapproval.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
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

   public function getDataKegiatan($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_kegiatan'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }

   public function getDataKegiatanDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_kegiatan_detail'], array(
         $id, $id, $id
      ));

      return self::ChangeKeyName($return);
   }

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getLastLogId($id){
      $result  = $this->Open($this->mSqlQueries['get_last_log_kegiatan_detail'], array($id));
      return $result;
   }

   public function doApproval($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }

      $userId  = $this->getUserId();

      if(!$param['data'] OR empty($param['data'])){
         $result  &= false;
      }else{
         foreach ($param['data'] as $data) {

         if($data['status'] != 'Belum' && $data['satuan_approve'] != '0' && $data['status_awal'] == ''){
            $result  &= $this->Execute($this->mSqlQueries['do_update_detil_approval'], array(
               $data['nominal_approve'],
               $data['nominal_satuan_approve'],
               $data['satuan_approve'],
               $data['keterangan'],
               $data['status'],
               $data['id']
            ));

            $lastLog  = $this->getLastLogId($data['id']);

            if($data['status'] == 'Ya'){
               $aktifitas  = 'Setuju';
            }elseif($data['status'] == 'Tidak'){
               $aktifitas  = 'Tolak';
            }else{
               $aktifitas  = '';
            }
            
            $result  &= $this->Execute($this->mSqlQueries['do_insert_kegdet_status'], array(
               $data['kegiatan_id'],
               $data['id'],
               $userId,
               date('Y-m-d H:i:s'),
               $aktifitas,
               $lastLog[0]['kodeaksi'] == NULL ? 1 : $lastLog[0]['kodeaksi']+1
            ));
         }
      }

         $uniqueStatus  = array_unique($param['status']);
         if(count($uniqueStatus) ===  1 AND strtoupper($uniqueStatus[0]) == 'YA'){
            $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array(
               'Ya',
               $param['id']
            ));
         }elseif(count($uniqueStatus) ===  1 AND strtoupper($uniqueStatus[0]) == 'TIDAK'){
            $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array(
               'Tidak',
               $param['id']
            ));
         }elseif(count($uniqueStatus) ===  1 AND strtoupper($uniqueStatus[0]) == 'BELUM'){
            $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array(
               'Belum',
               $param['id']
            ));
         }elseif(count($uniqueStatus) > 1 AND in_array('Ya', (array)$uniqueStatus)){
            $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array(
               'Ya',
               $param['id']
            ));
         }/*elseif(count($uniqueStatus) > 1 AND !in_array('Belum', (array)$uniqueStatus)){
            $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array(
               'Ya',
               $param['id']
            ));
         }*/else{
            $result     &= $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array(
               'Belum',
               $param['id']
            ));
         }
      }
      return $this->EndTrans($result);
   }

   function GetInformasi($kegiatan_detil_id) {
      $result = $this->Open($this->mSqlQueries['get_informasi'], array($kegiatan_detil_id));

      return $result[0];
   }

   function GetData($offset, $limit, $kegiatan_detil_id) {
      $result = $this->Open($this->mSqlQueries['get_data'], array($kegiatan_detil_id,$offset, $limit));

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

   function GetStatusApproval($id) {
      $result = $this->Open($this->mSqlQueries['get_status_approval'], array($id));
      $return = array();
      foreach ($result as $value)
         $return[$value['approval']] = $value['jml'];
      return $return;
   }

   function DoUpdateDetilApproval($totalapprove, $nominal, $satuan, $keterangan, $status, $id) {
      for($i=0;$i<sizeof($id);$i++) {
         $result = $this->Execute($this->mSqlQueries['do_update_detil_approval'], array($this->replace_dec($totalapprove[$id[$i]]), $this->replace_dec($nominal[$id[$i]]), $satuan[$id[$i]], $keterangan[$id[$i]], $status, $id[$i]));
         if (!$result) break;
      }
      return $result;
   }

   function DoUpdateStatusApprovalKegiatanDetil($status, $id) {
      $result = $this->Execute($this->mSqlQueries['do_update_kegiatan_detil_status_approval'], array($status, $id));

      return $result;
   }

   function replace_dec($number)
   {
      list($numb,$dec)    = explode(',',$number);
      return (int)str_replace('.','',$numb);
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

   public function getModule($pathInfo = null)
   {
      $module              = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^mod=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $module        = $value;
         }
      }

      return $module;
   }

   public function getSubModule($pathInfo = null)
   {
      $subModule           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^sub=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $subModule     = $value;
         }
      }

      return $subModule;
   }

   public function getAction($pathInfo = null)
   {
      $action           = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^act=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $action        = $value;
         }
      }

      return $action;
   }

   public function getType($pathInfo = null)
   {
      $type                = NULL;
      if(is_null($pathInfo)){
         $parseUrl         = parse_url($_SERVER['QUERY_STRING']);
         $explodedUrl      = explode('&', $parseUrl['path']);
      }else{
         $parseUrl         = parse_url($pathInfo);
         $explodedUrl      = explode('&', $parseUrl['query']);
      }

      foreach ($explodedUrl as $path) {
         if(preg_match('/^typ=[a-zA-Z0-9_-]+/', $path, $matches)){
            list($key, $value)   = explode('=', $matches[0]);
            $type          = $value;
         }
      }

      return $type;
   }
}
?>