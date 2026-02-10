<?php
class AppRencanaPenerimaan extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   protected $mUserId   = null;
   public $method;
   public $indonesianMonth    = array(
      0 => array(
         'id' => 0,
         'name' => 'N/A'
      ), array(
         'id' => 1,
         'name' => 'Januari'
      ), array(
         'id' => 2,
         'name' => 'Februari'
      ), array(
         'id' => 3,
         'name' => 'Maret'
      ), array(
         'id' => 4,
         'name' => 'April'
      ), array(
         'id' => 5,
         'name' => 'Mei'
      ), array(
         'id' => 6,
         'name' => 'Juni'
      ), array(
         'id' => 7,
         'name' => 'Juli'
      ), array(
         'id' => 8,
         'name' => 'Agustus'
      ), array(
         'id' => 9,
         'name' => 'September'
      ), array(
         'id' => 10,
         'name' => 'Oktober'
      ), array(
         'id' => 11,
         'name' => 'November'
      ), array(
         'id' => 12,
         'name' => 'Desember'
      )
   );

   public function __construct($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/rencana_penerimaan/business/apprencanapenerimaan.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
   }

   private function setUserId()
   {
      if(class_exists('Security')){
         $this->mUserId       = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      }
   }

   public function getUserId()
   {
      $this->setUserId();
      return (int)$this->mUserId;
   }

   public function getPeriodeTahun($param = array())
   {
      $default    = array(
         'active' => false,
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($options['active'] === false),
         (int)($options['open'] === false)
      ));

      return $return;
   }

   public function getPeriodeTahunAktifOpen()
   {
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun_aktif_open'], array());

      return $return;
   }

   public function Count($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['count'], array(
         $param['ta_id'],
         '%'.$param['kode'].'%',
         '%'.$param['kode'].'%',
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id']
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getData($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data'], array(
         $param['ta_id'],
         '%'.$param['kode'].'%',
         '%'.$param['kode'].'%',
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $offset,
         $limit
      ));

      return $return;
   }

   public function getTotalPerUnit($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_total_perunit'], array(
         $param['ta_id'],
         '%'.$param['kode'].'%',
         '%'.$param['kode'].'%',
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id']
      ));

      return $return;
   }

   public function doSaveRencanaPenerimaan($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $result     &= $this->Execute($this->mSqlQueries['do_save_rencana_penerimaan'], array(
         $param['unit_id'],
         $param['kode_penerimaan_id'],
         $param['total_penerimaan'],
         $param['keterangan'],
         $param['ta_id'],
         $param['volume'],
         $param['tarif'],
         $param['nominal'],
         $param['realisasi_pagu'],
         $param['nominal_pagu'],
         $param['keterangan'],
         $param['sumber_dana_id'],
         $param['keterangan'],
         $param['alokasi_unit'],
         $param['alokasi_unit'],
         $param['alokasi_unit'],
         $param['alokasi_pusat'],
         $param['alokasi_pusat'],
         $param['alokasi_pusat'],
         $param['satuan'],
         $param['alokasi_pusat_id']
      ));

      $rencanaPenerimaanId    = $this->LastInsertId();
      if(!empty($param['rincian'])){
         // set rincian rencana penerimaan per bulan
         $dataRincian         = $param['rincian'];
         $result  &= $this->Execute($this->mSqlQueries['do_set_rincian_bulan_rencana_penerimaan'], array(
            $dataRincian[1]['nominal'],
            $dataRincian[2]['nominal'],
            $dataRincian[3]['nominal'],
            $dataRincian[4]['nominal'],
            $dataRincian[5]['nominal'],
            $dataRincian[6]['nominal'],
            $dataRincian[7]['nominal'],
            $dataRincian[8]['nominal'],
            $dataRincian[9]['nominal'],
            $dataRincian[10]['nominal'],
            $dataRincian[11]['nominal'],
            $dataRincian[12]['nominal'],
            $dataRincian[1]['persen'],
            $dataRincian[2]['persen'],
            $dataRincian[3]['persen'],
            $dataRincian[4]['persen'],
            $dataRincian[5]['persen'],
            $dataRincian[6]['persen'],
            $dataRincian[7]['persen'],
            $dataRincian[8]['persen'],
            $dataRincian[9]['persen'],
            $dataRincian[10]['persen'],
            $dataRincian[11]['persen'],
            $dataRincian[12]['persen'],
            $rencanaPenerimaanId
         ));
      }

      // insert alokasi unit
      $result     &= $this->Execute($this->mSqlQueries['do_insert_detail_alokasi_unit'], array(
         $rencanaPenerimaanId,
         $param['alokasi_unit_id']
      ));
      return $this->EndTrans($result);
   }

   public function doUpdateRencanaPenerimaan($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $rencanaPenerimaanId    = $param['id'];
      $result     &= $this->Execute($this->mSqlQueries['do_delete_rencana_penerimaan_detail'], array(
         $rencanaPenerimaanId
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_update_rencana_penerimaan'], array(
         $param['unit_id'],
         $param['kode_penerimaan_id'],
         $param['total_penerimaan'],
         $param['keterangan'],
         $param['ta_id'],
         $param['volume'],
         $param['tarif'],
         $param['nominal'],
         $param['realisasi_pagu'],
         $param['nominal_pagu'],
         $param['keterangan'],
         $param['sumber_dana_id'],
         $param['keterangan'],
         $param['alokasi_unit'],
         $param['alokasi_unit'],
         $param['alokasi_unit'],
         $param['alokasi_pusat'],
         $param['alokasi_pusat'],
         $param['alokasi_pusat'],
         $param['satuan'],
         $param['alokasi_pusat_id'],
         $rencanaPenerimaanId
      ));

      if(!empty($param['rincian'])){
         // set rincian rencana penerimaan per bulan
         $dataRincian         = $param['rincian'];
         $result  &= $this->Execute($this->mSqlQueries['do_set_rincian_bulan_rencana_penerimaan'], array(
            $dataRincian[1]['nominal'],
            $dataRincian[2]['nominal'],
            $dataRincian[3]['nominal'],
            $dataRincian[4]['nominal'],
            $dataRincian[5]['nominal'],
            $dataRincian[6]['nominal'],
            $dataRincian[7]['nominal'],
            $dataRincian[8]['nominal'],
            $dataRincian[9]['nominal'],
            $dataRincian[10]['nominal'],
            $dataRincian[11]['nominal'],
            $dataRincian[12]['nominal'],
            $dataRincian[1]['persen'],
            $dataRincian[2]['persen'],
            $dataRincian[3]['persen'],
            $dataRincian[4]['persen'],
            $dataRincian[5]['persen'],
            $dataRincian[6]['persen'],
            $dataRincian[7]['persen'],
            $dataRincian[8]['persen'],
            $dataRincian[9]['persen'],
            $dataRincian[10]['persen'],
            $dataRincian[11]['persen'],
            $dataRincian[12]['persen'],
            $rencanaPenerimaanId
         ));
      }

      // insert alokasi unit
      $result     &= $this->Execute($this->mSqlQueries['do_insert_detail_alokasi_unit'], array(
         $rencanaPenerimaanId,
         $param['alokasi_unit_id']
      ));
      return $this->EndTrans($result);
   }

   public function getDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_Data_detail'], array(
         $id
      ));

      return $return[0];
   }

   public function getDataDetailRincian($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_rincian_penerimaan_bulan'], array(
         $id
      ));
      $dataList   = array();
      if(!empty($return)){
         $dataReturn       = self::ChangeKeyName($return[0]);
         while(list($rKey, $rValue) = each($dataReturn)){
            list($key, $value)      = explode('_', $rKey);
            $dataList[$key][$value] = $rValue;
         }
      }

      return $dataList;
   }

   public function doDeleteRencanaPenerimaan($id)
   {
      $result     = true;
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }

      $result     &= $this->Execute($this->mSqlQueries['do_delete_rencana_penerimaan_detail'], array(
         $id
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_rencana_penerimaan'], array(
         $id
      ));

      return $this->EndTrans($result);
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