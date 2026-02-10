<?php
class Spp extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;
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

   function __construct($connectionNumber = 0){
      $this->mSqlFile      = 'module/realisasi_pencairan_2/business/spp.sql.php';
      $this->_POST         = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET          = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method        = $_SERVER['REQUEST_METHOD'];
      parent::__construct($connectionNumber);
   }

   function ComboSifatPembayaran(){
      $return = $this->Open($this->mSqlQueries['sifat_pembayaran'], array());

      return $return;
   }

   function ComboJenisPembayaran(){
      $return  = $this->Open($this->mSqlQueries['jenis_pembayaran'], array());

      return $return;
   }

   public function GetPeriodeTahun($param = array())
   {
      $default       = array(
         'active' => false,
         'open' => false
      );
      $options       = array_merge($default, (array)$param);
      $return        = $this->Open($this->mSqlQueries['get_periode_tahun'], array(
         (int)($options['active'] === false),
         (int)($options['open'] === false)
      ));

      return $return;
   }

   public function GetDipa()
   {
      $return      = $this->Open($this->mSqlQueries['get_dipa'], array());

      return $return[0];
   }

   public function GetDataPengajuanRealisasi($id)
   {
      if(!$id){
         return null;
      }

      $return        = $this->Open($this->mSqlQueries['get_data_pengajuan_realisasi'], array(
         $id
      ));

      if($return){
         return $return[0];
      }else{
         return null;
      }
   }

   public function GetPengajuanRealisaiDetail($id)
   {
      if(!$id){
         return null;
      }

      $return     = $this->Open($this->mSqlQueries['get_data_realisasi_detail'], array(
         $id
      ));

      return $return;
   }

   /**
    * @description : Do Insert data SPP
    * @param Array $param; data
    * @return Boolean $result
    */
   public function DoInsertDataSpp($param = array())
   {
      $userId        = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $tanggal       = date('Y-m-d', time());
      $dataDipa      = $this->GetDipa();
      $dipaId        = $dataDipa['id'];
      $result        = true;
      $this->StartTrans();
      $result        &= $this->Execute($this->mSqlQueries['do_insert_spp'], array(
         $param['ta_id'],
         $param['unit_id'],
         $tanggal,
         $tanggal,
         $tanggal,
         $tanggal,
         $tanggal,
         $dipaId,
         $param['sifat_pembayaran'],
         $param['jenis_pembayaran'],
         $param['keperluan'],
         $param['jenis_belanja'],
         $param['nama'],
         $param['alamat'],
         $param['rekening'],
         $param['spk_nominal'],
         $param['spk_nomor'],
         date('Y-m-d', strtotime($param['spk_tanggal'])),
         $param['npwp'],
         $param['nominal'],
         $userId
      ));
      $sppId         = $this->LastInsertId();
      $result        &= $this->Execute($this->mSqlQueries['delete_spp_det'], array(
         $sppId
      ));
      if(!empty($param['detail'])){
         foreach ($param['detail'] as $list) {
            $result  &= $this->Execute($this->mSqlQueries['insert_spp_det'], array(
               $sppId,
               $list['id'],
               $list['nominal'],
               $userId
            ));
         }
      }
      return $this->EndTrans($result);
   }

   public function GetDataSpp($id)
   {
      if(!$id){
         return null;
      }

      $return     = $this->Open($this->mSqlQueries['get_data_spp'], array(
         $id
      ));

      if($return){
         return $return[0];
      }else{
         return null;
      }
   }

   /**
    * @description Do Update data SPP
    * @param Array $param
    * @return Boolean $result
    */
   public function DoUpdateDataSpp($param = array())
   {
      $userId        = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $tanggal       = date('Y-m-d', time());
      $result        = true;
      $sppId         = $param['id'];
      $this->StartTrans();
      $result        &= $this->Execute($this->mSqlQueries['do_update_data_spp'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['sifat_pembayaran'],
         $param['jenis_pembayaran'],
         $param['keperluan'],
         $param['jenis_belanja'],
         $param['nama'],
         $param['alamat'],
         $param['rekening'],
         $param['spk_nominal'],
         $param['spk_nomor'],
         date('Y-m-d', strtotime($param['spk_tanggal'])),
         $param['npwp'],
         $param['nominal'],
         $userId,
         $sppId
      ));

      $result        &= $this->Execute($this->mSqlQueries['delete_spp_det'], array(
         $sppId
      ));
      if(!empty($param['detail'])){
         foreach ($param['detail'] as $list) {
            $result  &= $this->Execute($this->mSqlQueries['insert_spp_det'], array(
               $sppId,
               $list['id'],
               $list['nominal'],
               $userId
            ));
         }
      }
      return $this->EndTrans($result);
   }
   // ---------------------------------------------------------------------- //

   function GetDataById($id){
      $return     = $this->Open($this->mSqlQueries['get_data_by_id'], array($id));

      return $return;
   }

   function DeleteSpp($id){
      $return  = $this->Execute($this->mSqlQueries['delete_spp'],array($id));

      return $return;
   }

   // get detil spp
   function GetDetilSpp($spp_id){
      $return  = $this->Open($this->mSqlQueries['get_detil_spp'], array($spp_id));

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

   /**
    * @required indonesianMonths
    * @param String $date date format YYYY-mm-dd H:i:s, YYYY-mm-dd
    * @param String $format long, short
    * @return String  Indonesian Date
    */
   public function indonesianDate($date, $format = 'long')
   {
      $timeFormat          = '%02d:%02d:%02d';
      $patern              = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/';
      $patern1             = '/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/';
      switch ($format) {
         case 'long':
            $dateFormat    = '%02d %s %04d';
            break;
         case 'short':
            $dateFormat    = '%02d-%s-%04d';
            break;
         default:
            $dateFormat    = '%02d %s %04d';
            break;
      }

      if(preg_match($patern, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $hour    = (int)$matches[4];
         $minute  = (int)$matches[5];
         $second  = (int)$matches[6];
         $mon     = $this->indonesianMonth[$month];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;

      }elseif(preg_match($patern1, $date, $matches)){
         $year    = (int)$matches[1];
         $month   = (int)$matches[2];
         $day     = (int)$matches[3];
         $mon     = $this->indonesianMonth[$month]['name'];

         $date    = sprintf($dateFormat, $day, $mon, $year);

         $result  = $date;
      }else{
         $date    = getdate();
         $year    = (int)$date['year'];
         $month   = (int)$date['mon'];
         $day     = (int)$date['mday'];
         $hour    = (int)$date['hours'];
         $minute  = (int)$date['minutes'];
         $second  = (int)$date['seconds'];
         $mon     = $this->indonesianMonth[$month]['name'];

         $date    = sprintf($dateFormat, $day, $mon, $year);
         $time    = sprintf($timeFormat, $hour, $minute, $second);
         $result  = $date.' '.$time;
      }

      return $result;
   }
}
?>