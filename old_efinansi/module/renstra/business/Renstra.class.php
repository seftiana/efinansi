<?php
/**
* @module renstra
* @author Rendi Kasigi,
* @mail to rkasigi@gmail.com
* @copyright 2008&copy;Gamatechno
* @modified 2014-12-16 by Eko Susilo
*/

class Renstra extends Database
{
   protected $mSqlFile;
   private $mUserId;
   public $_POST;
   public $_GET;
   function __construct($connectionNumber=0) {
      $this->mSqlFile      = 'module/renstra/business/renstra.sql.php';
      $this->_POST         = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET          = is_object($_GET) ? $_GET->AsArray() : $_GET;

      if(class_exists('Security')){
         $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      }else{
         $this->mUserId    = $this->GetUserService();
      }

      parent::__construct($connectionNumber);
   }

   public function getUserId()
   {
      return $this->mUserId;
   }

   public function GetUserService()
   {
      $return     = $this->Open($this->mSqlQueries['get_user_service'], array());
      if($return){
         return (int)$return[0]['UserId'];
      }else{
         return (int)1;
      }
   }

   public function GetRangeTahun()
   {
      $return     = $this->Open($this->mSqlQueries['get_range_tahun'], array());
      $result     = self::ChangeKeyName($return[0]);
      $startYear  = $return[0]['startYear'];
      $endYear    = $return[0]['endYear'];
      $range      = range($startYear, $endYear);
      $index      = 0;
      foreach ($range as $year) {
         $range_year[$index]['id']     = $year;
         $range_year[$index]['name']   = $year;
         $index++;
      }
      return compact('result', 'range_year');
   }

   public function Count()
   {
      $return        = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function GetDataRenstra($offset, $limit, $params = array())
   {
      $return        = $this->Open($this->mSqlQueries['get_data_renstra'], array(
         '%'.$params['nama'].'%',
         $params['start_year'],
         $params['end_year'],
         $offset,
         $limit
      ));

      return $return;
   }

   public function GetTanggalRenstra($id = null)
   {
      $return           = $this->Open($this->mSqlQueries['get_tahun_renstra'], array(
         $id,
         (int)($id === null OR $id == '')
      ));

      if($return[0]['tanggalAkhir'] === NULL){
         $renstraDay    = (int)1;
         $renstraMon    = (int)date('m', time());
         $renstraYear   = (int)date('Y', time());

         $result['startDate']    = date('Y-m-d', mktime(0,0,0, $renstraMon, $renstraDay, $renstraYear));
         $result['endDate']      = date('Y-m-d', mktime(0,0,0, $renstraMon, $renstraDay+(365.242*5), $renstraYear));
         $result['startYear']    = date('Y', time());
         $result['endYear']      = date('Y', time())+10;
      }else{
         if($id !== null){
            $result['startDate']    = date('Y-m-d', strtotime($return[0]['renstraTanggalAwal']));
            $result['endDate']      = date('Y-m-d', strtotime($return[0]['renstraTanggalAkhir']));
            $result['startYear']    = date('Y', strtotime($return[0]['renstraTanggalAwal']));
            $result['endYear']      = date('Y', strtotime($return[0]['renstraTanggalAkhir']));
         }else{
            $renstraDay    = (int)date('d', strtotime($return[0]['tanggalAkhir']));
            $renstraMon    = (int)date('m', strtotime($return[0]['tanggalAkhir']));
            $renstraYear   = (int)date('Y', strtotime($return[0]['tanggalAkhir']));

            $result['startYear']    = $return[0]['tahunAkhir'];
            $result['endYear']      = $return[0]['tahunMaksimal'];
            $result['startDate']    = date('Y-m-d', mktime(0,0,0, $renstraMon, $renstraDay, $renstraYear));
            $result['endDate']      = date('Y-m-d', mktime(0,0,0, $renstraMon, $renstraDay+(365.242*5), $renstraYear));
         }
      }

      return $result;
   }

   public function CheckRenstra($nama, $id = null)
   {
      $return     = $this->Open($this->mSqlQueries['check_renstra'], array(
         strtolower($nama),
         $id,
         (int)($id === null OR $id == '')
      ));

      if($return){
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }else{
         return false;
      }
   }

   public function DoAddRenstra($param = array())
   {
      $user_id       = $this->mUserId;
      $result        = true;
      $this->StartTrans();
      if(strtoupper($param['status']) === 'Y'){
         $result     &= $this->Execute($this->mSqlQueries['do_set_deaktif'], array());
      }
      $result        &= $this->Execute($this->mSqlQueries['do_add'], array(
         $param['nama'],
         $param['tanggal_awal'],
         $param['tanggal_akhir'],
         $user_id,
         $param['pimpinan'],
         $param['visi'],
         $param['misi'],
         $param['tujuan_umum'],
         $param['tujuan_khusus'],
         $param['catatan'],
         $param['sasaran'],
         $param['strategi'],
         $param['kebijakan'],
         strtoupper($param['status'])
      ));

      return $this->EndTrans($result);
   }

   function DoDelete($Id) {
      $result     = true;
      $this->StartTrans();
      $result     &= $this->Execute($this->mSqlQueries['do_delete_tahun_anggaran_renstra'], array($Id));
      $result     &= $this->Execute($this->mSqlQueries['do_delete'], array($Id));
      return $this->EndTrans($result);
   }

   public function GetDataDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      if($return){
         return $return[0];
      }else{
         return null;
      }
   }

   public function DoUpdateRenstra($param = array())
   {
      $result        = true;
      $user_id       = $this->mUserId;
      $this->StartTrans();
      if(strtoupper($param['status']) === 'Y'){
         $result     &= $this->Execute($this->mSqlQueries['do_set_deaktif'], array());
      }
      $result        &= $this->Execute($this->mSqlQueries['do_update'], array(
         $param['nama'],
         $param['tanggal_awal'],
         $param['tanggal_akhir'],
         $user_id,
         $param['pimpinan'],
         $param['visi'],
         $param['misi'],
         $param['tujuan_umum'],
         $param['tujuan_khusus'],
         $param['catatan'],
         $param['sasaran'],
         $param['strategi'],
         $param['kebijakan'],
         strtoupper($param['status']),
         $param['id']
      ));

      return $this->EndTrans($result);
   }

   function DoSetAktif($id) {
      $result        = true;
      $this->StartTrans();
      $result        &= $this->Execute($this->mSqlQueries['do_set_deaktif'], array());
      $result        &= $this->Execute($this->mSqlQueries['do_set_aktif'], array($id));

      return $this->EndTrans($result);
   }

   public function CheckDataRenstra($nama, $id = null)
   {
      $return     = $this->Open($this->mSqlQueries['do_check_renstra'], array(
         $nama,
         $id,
         (int)($id === NULL OR $id == '')
      ));

      if($return){
         return $return[0];
      }else{
         return null;
      }
   }

   public function GetPeriodeTahunRenstra($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_periode_tahun_renstra'], array(
         $id
      ));

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