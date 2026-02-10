<?php
/**
* ================= doc ====================
* FILENAME     : Sppu.class.php
* @package     : Sppu
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-06
* @Modified    : 2015-04-06
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/


require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/generate_number/business/GenerateNumber.class.php';

class Sppu extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   protected $mUserId = NULL;
   public $method;
   private $mNumber;
   public $indonesianMonth    = array(
       array(
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
   # Constructor
   function __construct ($connectionNumber = 0 )
   {
      $this->mSqlFile   = 'module/finansi_sppu/business/sppu.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);      
      $this->mNumber    = new GenerateNumber($connectionNumber);
   }


    public function getNomorSPPU($tanggal) {
      
        $result = $this->Open($this->mSqlQueries['do_set_nomor_sppu'], array(
            $tanggal,
            $tanggal,
            $tanggal,
            $tanggal,
            '%',
            $tanggal,
            $tanggal
        ));
      
        return $result[0]['nomor_sppu'];
   }
   
   public function getNomorCR($tanggal)
   {
   	  /*
      $result  = $this->Open($this->mSqlQueries['get_nomor_cr'], array(
        $tanggal,
        $tanggal,
        $tanggal,
        $tanggal,'%',
        $tanggal,
        $tanggal
       ));
	  */
      return $this->mNumber->getNomorCRBank($tanggal);
       
   }
   
   public function getNomorBp($tanggal)
   {
   	  /*
      $result  = $this->Open($this->mSqlQueries['get_nomor_bp'], array(
        $tanggal,
        $tanggal,
        $tanggal,
        $tanggal,'%',
        $tanggal,
        $tanggal
       ));
	  */
      return $this->mNumber->getNomorBpBank($tanggal);
       
   }
   /**
    * [setUserId set user ID]
    */
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

   /**
    * [getUserId getUserId from setUserId]
    * @return Interger userId
    */
   public function getUserId()
   {
      $this->setUserId();

      return (int)$this->mUserId;
   }

   /**
    * [getSettingValue description]
    * @param  string $name [description]
    * @return String $name [description]
    */
   public function getSettingValue($name = '')
   {
      $return     = $this->Open($this->mSqlQueries['get_setting_name'], array(
         $name
      ));

      return $return[0]['name'];
   }

   public function setDate() {
        $return = $this->Open($this->mSqlQueries['get_date_range'], array());

        return self::ChangeKeyName($return[0]);
    }

   /**
    * [getPeriodeTahunAnggaran description]
    * @param  array
    * @return [type]
    */
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

   /**
    * [getPeriodeTahunPembukuan description]
    * @param  array
    * @return [type]
    */
   public function getTahunPembukuanPeriode($param = array())
   {
      $default    = array(
         'open' => false
      );
      $options    = array_merge($default, (array)$param);
      $return     = $this->Open($this->mSqlQueries['get_tahun_pembukuan_periode'], array(
         (int)($options['open'] === false)
      ));

      return $return;
   }

   /**
    * [GetDataProgram description]
    * @param [type]
    */
   function GetDataProgram($idTa = NULL){
      $result     = $this->Open($this->mSqlQueries['get_data_program'],array(
         $idTa,
         (int)($idTa == NULL OR $idTa == '')
      ));
      $return     = array();
      $index      = 0;
      $count      = count($result);
      if(!empty($result)){
         $taId    = '';
         for ($i=0; $i < $count;) {
            if((int)$result[$i]['taId'] === (int)$taId){
               $return[$taId][$index]['id']     = $result[$i]['id'];
               $return[$taId][$index]['name']   = $result[$i]['name'];
               $i++;
               $index+=1;
            }else{
               $index      = 0;
               $taId       = (int)$result[$i]['taId'];
            }
         }

      }
      return $return;
   }

   /**
    * [Count description]
    */
   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   function CountDataRealisasi($param = array())
   {
      $return  = $this->Open($this->mSqlQueries['count_data_realisasi'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['program_id'],
         (int)(($param['program_id'] === NULL OR $param['program_id'] == '') OR strtolower($param['program_id']) == 'all'),
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['jenis_kegiatan'],
         (int)(($param['jenis_kegiatan'] === NULL OR $param['jenis_kegiatan'] == '') OR strtolower($param['jenis_kegiatan']) == 'all'),
         $param['bulan'],
         (int)(($param['bulan'] === NULL OR $param['bulan'] == '') OR strtolower($param['bulan']) == 'all')
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getDataRealisasi($offset, $limit, $param = array())
   {//$this->SetDebugOn();
      $return  = $this->Open($this->mSqlQueries['get_data_realisasi'], array(
         $param['ta_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['unit_id'],
         $param['program_id'],
         (int)(($param['program_id'] === NULL OR $param['program_id'] == '') OR strtolower($param['program_id']) == 'all'),
         '%'.$param['kode'].'%',
         '%'.$param['nama'].'%',
         $param['jenis_kegiatan'],
         (int)(($param['jenis_kegiatan'] === NULL OR $param['jenis_kegiatan'] == '') OR strtolower($param['jenis_kegiatan']) == 'all'),
         $param['bulan'],
         (int)(($param['bulan'] === NULL OR $param['bulan'] == '') OR strtolower($param['bulan']) == 'all'),
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function getDataDetailRealisasi($id = array())
   {//$this->SetDebugOn();
      $return     = $this->Open($this->mSqlQueries['get_data_detail_realisasi'], array(
         implode("','", (array)$id)
      ));

      return self::ChangeKeyName($return);
   }

   function GetLastId()
   {
      $return     = $this->Open($this->mSqlQueries['get_last_insert_id'], array()
      );
      
      return $return[0]['last_id'];
   }

   function DoInsertTransaksiBank($data = array())
   {

      $nomor            = $data['transaksiBankNomor'];
      $bpkb             = $data['transaksiBankBpkb'];
      $tanggal          = $data['transaksiBankTanggal'];
      $sppuId           = $data['transaksiBankSppuId'];
      $coaIdPenerima    = $data['transaksiBankCoaIdPenerima'];
      $penerima         = $data['transaksiBankPenerima'];
      $rekeningPenerima = $data['transaksiBankRekeningPenerima'];
      $coaIdTujuan      = $data['transaksiBankCoaIdTujuan'];
      $tujuan           = $data['transaksiBankTujuan'];
      $rekeningTujuan   = $data['transaksiBankRekeningTujuan'];
      $nominal          = $data['transaksiBankNominal'];
      $tipe             = $data['transaksiBankTipe'];
      $userId           = $data['transaksiBankUserId'];
      
      $result     = $this->Execute(
         $this->mSqlQueries['do_insert_transaksi_bank'], 
         array(
            $nomor,
            $bpkb,
            $tanggal,
            $sppuId,
            $coaIdPenerima,
            $penerima,
            $rekeningPenerima,
            $coaIdTujuan,
            $tujuan,
            $rekeningTujuan,
            $nominal,
            $tipe,
            $userId
         )
      );
      
      if($result)
      {
         $result     = $this->Execute(
         $this->mSqlQueries['update_nomor_bp'], 
         array(
            $bpkb,
            $sppuId
         )
      );
      }
      else
      {
         return $this->GetLastError();
      }
   }

   function DoUpdateTransaksiBank($data = array())
   {

      $nomor            = $data['transaksiBankNomor'];
      $bpkb             = $data['transaksiBankBpkb'];
      $tanggal          = $data['transaksiBankTanggal'];
      $sppuId           = $data['transaksiBankSppuId'];
      $coaIdPenerima    = $data['transaksiBankCoaIdPenerima'];
      $penerima         = $data['transaksiBankPenerima'];
      $rekeningPenerima = $data['transaksiBankRekeningPenerima'];
      $coaIdTujuan      = $data['transaksiBankCoaIdTujuan'];
      $tujuan           = $data['transaksiBankTujuan'];
      $rekeningTujuan   = $data['transaksiBankRekeningTujuan'];
      $nominal          = $data['transaksiBankNominal'];
      $tipe             = $data['transaksiBankTipe'];
      $userId           = $data['transaksiBankUserId'];
      
      $result     = $this->Execute(
         $this->mSqlQueries['do_update_transaksi_bank'], 
         array(
            $nomor,
            $bpkb,
            $tanggal,
            $sppuId,
            $coaIdPenerima,
            $penerima,
            $rekeningPenerima,
            $coaIdTujuan,
            $tujuan,
            $rekeningTujuan,
            $nominal,
            $tipe,
            $userId,
            $sppuId
         )
      );
      
      if($result)
      {
         $result     = $this->Execute(
         $this->mSqlQueries['update_nomor_bp'], 
         array(
            $bpkb,
            $sppuId
         )
      );
      }
      else
      {
         return $this->GetLastError();
      }
   }

   public function doSaveSppu($param = array())
   {
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $tanggal    = date('Y-m-d', strtotime($param['tanggal']));
      $userId     = $this->getUserId();
      $nomorCR    = $this->getNomorCR($tanggal);
      $nomorBP    = $this->getNomorBp($tanggal);
      
      $nomorSPPU  = $this->getNomorSPPU($tanggal);
      $tpAktif    = $this->getTahunPembukuanPeriode(array('open' => true));
      
      $result     &= $this->Execute($this->mSqlQueries['do_insert_sppu'], array(
         $nomorSPPU,
         $param['nomor_bukti'],
         $tpAktif[0]['id'],
         $tanggal,
         $param['no_cek_giro'],
         $param['no_rekening'],
         $param['bank'],
         $param['nominal'],
         $userId,
         (empty($param['bp']) ? 'T' : $param['bp']),
         (empty($param['cr']) ? 'T' : $param['cr']),
         $param['keterangan']
      ));
      $sppuId     = $this->LastInsertId();
      if(!empty($param['items'])){
         foreach ($param['items'] as $item) {
            /* proses simpan sppu per realisasi detail belanja
            $result  &= $this->Execute($this->mSqlQueries['do_insert_sppu_detail'], array(
               $sppuId,
               $item['id'],
               $item['nominal'],
               $userId
            ));
            */
            /** sppu detail per detial belanja by fpa id /realisasi */
            $result  &= $this->Execute($this->mSqlQueries['do_insert_sppu_detail_by_fpa_id'], array(
               $sppuId,
               $userId,
               $item['realisasi_id']
            ));            
         }
      }

      $return['result']    = $this->EndTrans($result);
      $return['data_id']   = $sppuId;
      return $return;
   }

   public function doUpdateSppu($param = array())
   {
       //$this->setDebugOn();
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      
      $tanggal  = date('Y-m-d', strtotime($param['tanggal']));
      $bulan    = date('m', strtotime($param['tanggal']));
      $tahun    = date('Y', strtotime($param['tanggal']));
      //cek sppu_tanggal
       $getTanggal = $this->Open($this->mSqlQueries['get_sppu_tanggal'], array(
         $param['id']
      ));

      
      if(($getTanggal[0]['bulan'] == $bulan) && ($getTanggal[0]['tahun'] == $tahun)){
            $nomorSPPU    = (empty($getTanggal[0]['nomorSPPU']) ?  $this->getNomorSPPU($tanggal) : $getTanggal[0]['nomorSPPU']);
      } else {
            $nomorSPPU  = $this->getNomorSPPU($tanggal);
      }
      
        
      $userId     = $this->getUserId();
      $result     &= $this->Execute($this->mSqlQueries['do_update_sppu'], array(
         $nomorSPPU,
         $param['nomor_bukti'], 
         $tanggal,
         $param['no_cek_giro'],
         $param['no_rekening'],
         $param['bank'],
         $param['nominal'],
         $userId,
         (empty($param['bp']) ? 'T' : $param['bp']),
         (empty($param['cr']) ? 'T' : $param['cr']),
         $param['keterangan'],
         $param['id']
      ));

      if(!empty($param['items'])){
         $result     &= $this->Execute($this->mSqlQueries['do_delete_sppu_det'], array($param['id']));
         foreach ($param['items'] as $item) {
           /*
            $result  &= $this->Execute($this->mSqlQueries['do_insert_sppu_detail'], array(
               $param['id'],
               $item['id'],
               $item['nominal'],
               $userId
            ));
            */
             /** sppu detail per detial belanja by fpa id /realisasi */
            $result  &= $this->Execute($this->mSqlQueries['do_insert_sppu_detail_by_fpa_id'], array(
               $param['id'],
               $userId,
               $item['realisasi_id']
            )); 
         }
      }

      $return['result']    = $this->EndTrans($result);
      $return['data_id']   = $param['id'];
      return $return;
   }

   public function getDataTransaksiBank($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_transaksi_bank'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }

   public function getDataDetailSppu($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_sppu_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }

   public function getDataSppuItems($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_sppu_items'], array(
         $id
      ));

      return self::ChangeKeyName($return);
   }

   public function getDataSppuItemsDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_sppu_items_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return);
   }
   
   public function getDataSppu($offset, $limit, $param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_data_sppu'], array(
         '%'.trim($param['kode']).'%',
         '%'.trim($param['nomorPengajuan']).'%', 
         $param['tanggal_awal'],
         $param['tanggal_akhir'],
         '%'.trim($param['nomorBp']).'%',
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function doDeleteSppu($id)
   {
      $result     = true;
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }
      /**
       * hapus bp jika ada
       */

      $result     &= $this->Execute($this->mSqlQueries['do_delete_bp_det'], array($id));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_bp'], array($id));
      /**
       * hapus sppu
       */      
      $result     &= $this->Execute($this->mSqlQueries['do_delete_sppu_det'], array($id));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_sppu'], array($id));
      return $this->EndTrans($result);
   }
   
   public function doDeleteBp($id)
   {
      $result     = true;
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }
      
      $result     &= $this->Execute($this->mSqlQueries['do_delete_bp_det'], array($id));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_bp'], array($id));

      $result     &= $this->Execute($this->mSqlQueries['update_no_bp_sppu'], array($id));
      
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

   function _dateToIndo($date) {
        $indonesian_months = array(
            'N/A',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'Nopember',
            'Desember'
        );

        if (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[(int) $patch[2]];
            $day = (int) $patch[3];
            $hour = (int) $patch[4];
            $min = (int) $patch[5];
            $sec = (int) $patch[6];

            $return = $day . ' ' . $month . ' ' . $year;
        } elseif (preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $date, $patch)) {
            $year = (int) $patch[1];
            $month = (int) $patch[2];
            $month = $indonesian_months[$month];
            $day = (int) $patch[3];

            $return = $day . ' ' . $month . ' ' . $year;
        } else {
            $return = (int) $date;
        }
        return $return;
    }
}
?>