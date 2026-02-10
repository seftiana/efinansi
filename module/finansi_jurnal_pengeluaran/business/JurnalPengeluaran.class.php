<?php
/**
* ================= doc ====================
* FILENAME     : JurnalPengeluaran.class.php
* @package     : JurnalPengeluaran
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-04-13
* @Modified    : 2015-04-13
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/

class JurnalPengeluaran extends Database
{
   # internal variables
   protected $mSqlFile;
   protected $mUserId = NULL;
   public $_POST;
   public $_GET;
   public $method;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_jurnal_pengeluaran/business/jurnal_pengeluaran.sql.php';
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

   public function getTahunPencatatan()
   {
      $getdate       = getdate();
      $year          = (int)$getdate['year'];
      $return        = $this->Open($this->mSqlQueries['get_min_max_tahun_pencatatan'], array());
      if($return && !empty($return)){
         $result['min_year']  = $return[0]['minTahun'];
         $result['max_year']  = $return[0]['maxTahun'];
      }else{
         $result['min_year']  = $year-5;
         $result['max_year']  = $year+5;
      }

      return $result;
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

   public function getPaternSubAccount()
   {
      $return     = $this->Open($this->mSqlQueries['get_patern_sub_account'], array());
      if($return && !empty($return)){
         $return['patern'] = $return[0]['patern'];
         $return['regex']  = '/^'.$return[0]['regex'].'$/';
      }else{
         $return['patern'] = GTFWConfiguration::GetValue('application', 'subAccFormat');
         $return['regex']  = '/^([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})-([a-zA-Z0-9]{1,2})$/';
      }

      return $return;
   }

   public function getDefaultSubAkun()
   {
      $return     = $this->Open($this->mSqlQueries['get_patern_sub_account'], array());
      $result['patern']    = $return[0]['patern'];
      $result['regex']     = '/^'.$return[0]['regex'].'$/';
      $result['default']   = $return[0]['default'];

      return $result;
   }

   public function getSubAccountCombo(){
      return $this->Open($this->mSqlQueries['get_sub_account_combobox'],array());
   }
   
   public function GetRealIP(){
      if ($_ENV["HTTP_CLIENT_IP"]) :
         $ip_address    = $_ENV["HTTP_CLIENT_IP"];
      elseif ($_ENV["HTTP_X_FORWARDED_FOR"]) :
         $ip_address    = $_ENV["HTTP_X_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_X_FORWARDED"]) :
         $ip_address    = $_ENV["HTTP_X_FORWARDED"];
      elseif ($_ENV["HTTP_FORWARDED_FOR"]) :
         $ip_address    = $_ENV["HTTP_FORWARDED_FOR"];
      elseif ($_ENV["HTTP_FORWARDED"]) :
         $ip_address    = $_ENV["HTTP_FORWARDED"];
      elseif ($_SERVER['REMOTE_ADDR']) :
         $ip_address    = $_SERVER['REMOTE_ADDR'];
      endif;

      return $ip_address;
   }

   public function getApplicationSetting($param = null)
   {
      $return     = $this->Open($this->mSqlQueries['get_setting_value'], array(
         strtoupper($param)
      ));

      if($return && !empty($return)){
         return $return[0]['setting'];
      }else{
         return null;
      }
   }

   public function doCheckSubAkun($kode)
   {
      $subAkun          = array(1 => NULL, NULL, NULL, NULL, NULL, NULL, NULL);
      $getSubAkun       = $this->Open($this->mSqlQueries['get_patern_sub_account'], array());
      $regex            = '/^'.$getSubAkun[0]['regex'].'$/';
      $default          = $getSubAkun[0]['default'];
      $kode             = preg_replace('/\s[\s]+/', '', $kode);
      $kode             = preg_replace('/[\_]+/', '', $kode);
      $kode             = preg_replace('/[\_]+/', '', $kode);

      if(preg_match($regex, $kode, $matches)){
         while (list($key, $subakunKode) = each($matches)) {
            if((int)$key === 0){
               continue;
            }
            $subAkun[$key]    = $subakunKode;
         }
      }else{
         preg_match($regex, $default, $matches);
         while (list($key, $subakunKode) = each($matches)) {
            if((int)$key === 0){
               continue;
            }
            $subAkun[$key]    = $subakunKode;
         }
      }

      $return        = $this->Open($this->mSqlQueries['get_sub_account'], array(
         $subAkun[2],
         $subAkun[3],
         $subAkun[4],
         $subAkun[5],
         $subAkun[6],
         $subAkun[7],
         $subAkun[1]
      ));

      $subAccount    = $return[0]['subAkun'];
      if(strcmp($kode, $subAccount) == 0){
         return true;
      }else{
         return false;
      }
   }

   public function GetBentukTransaksi(){
      $return     = $this->open($this->mSqlQueries['get_bentuk_transaksi'],array());
      return $return;
   }

   public function getDataJurnalPengeluaran($offset, $limit, $param = array())
   {  //$this->SetDebugOn();
      $result     = $this->Open($this->mSqlQueries['get_data_jurnal_pengeluaran'], array(
         '%'.$param['referensi'].'%',
         $param['posting'],
         (int)($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         '%'.$param['sub_account'].'%',
         (int)($param['sub_account'] == '' || strtolower($param['sub_account']) == 'all' ),
         $offset,
         $limit
      ));
      
      $rCount     = $this->Open($this->mSqlQueries['count_jurnal_pengeluaran'], array(
         '%'.$param['referensi'].'%',
         $param['posting'],
         (int)($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         '%'.$param['sub_account'].'%',
         (int)($param['sub_account'] == '' || strtolower($param['sub_account']) == 'all' )
      ));
      
      if($rCount){
         $count      =  ($rCount[0]['count']);
      }else{
         $count      = 0;
      }

      $return        = array(
         'result' => self::ChangeKeyName($result, 'lower'),
         'count' => (int)$count
      );
      return (array)$return;
   }

   public function getCoaKodeInTransaksi($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_coa_kode_in_transaksi'], array(
         $id,
      ));
      $coa = array();
      if(!empty($return)) {
          foreach($return as $coaKode) {
              $coa[$coaKode['coaKode']] = $coaKode['coaKode'];
          }
      }
      return $coa;
   }
   
   public function getDataReferensiTransaksi($id, $prId)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_referensi_transaksi'], array(
         $id,
         $prId
      ));

      return self::ChangeKeyName($return[0]);
   }

   public function getDataJurnalSubAkun($id, $prId)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_jurnal_sub_akun'], array(
         $id,
         $prId
      ));

      return self::ChangeKeyName($return, 'lower');
   }

   public function doSaveJurnal($param = array())
   {
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $tanggal          = date('Y-m-d', strtotime($param['tanggal']));
      $transaksiId      = $param['referensi_id'];
      // CHECK STATUS AUTO APPROVE JURNAL
      // $getSettingAutoApprove  = GTFWConfiguration::GetValue('application', 'auto_approve');
      $autoApprove      = $this->getApplicationSetting('JURNAL_AUTO_APPROVE');
      $statusKas        = NULL;
      $bentukTransaksi  = NULL;
      $statusApproved   = 'T';
      if($autoApprove !== NULL AND (bool)$autoApprove === TRUE){
         $statusKas        = $param['status'];
         $bentukTransaksi  = $param['bentuk_transaksi'];
         $statusApproved   = 'Y';
      }

      // insert into pembukuan referensi
      $result        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_referensi'], array(
         $transaksiId,
         $userId,
         $tanggal,
         $param['keterangan'],
         $statusApproved,
         $statusKas,
         $statusKas,
         $statusKas,
         $bentukTransaksi,
         $bentukTransaksi,
         $bentukTransaksi
      ));

      $queryLog[]    = sprintf($this->mSqlQueries['do_insert_pembukuan_referensi'], $transaksiId, $userId, $tanggal, $param['keterangan'], $statusApproved, $statusKas, $statusKas, $statusKas, $bentukTransaksi, $bentukTransaksi, $bentukTransaksi);

      // get pembukuan id
      $pembukuanId      = $this->LastInsertId();
      // delete pembukuan detail
      $result           &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
         $pembukuanId
      ));

      $queryLog[]       = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);

      if(!empty($param['akun_debet'])){
         // insert pembukuan detail debet
         foreach ($param['akun_debet'] as $debet) {
            $subAkun    = preg_replace('/\s[\s]+/', '', $debet['sub_akun']);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $subAkun);

            $result        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $debet['id'],
               $debet['nominal'],
               $debet['keterangan'],
               $debet['referensi'],
               'D',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));

            $queryLog[]    = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $debet['id'], $debet['nominal'], $debet['keterangan'], $debet['referensi'], 'D', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
         }
      }

      if(!empty($param['akun_kredit'])){
         // insert pembukuan detail kredit
         foreach ($param['akun_kredit'] as $kredit) {
            $subAkun    = preg_replace('/\s[\s]+/', '', $kredit['sub_akun']);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $subAkun);

            $result        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $kredit['id'],
               $kredit['nominal'],
               $kredit['keterangan'],
               $kredit['referensi'],
               'K',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));

            $queryLog[]    = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $kredit['id'], $kredit['nominal'], $kredit['keterangan'], $kredit['referensi'], 'K', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
         }
      }

      // update status jurnal transaksi
      $result        &= $this->Execute($this->mSqlQueries['update_status_jurnal'], array('Y', $transaksiId));
      $queryLog[]    = sprintf($this->mSqlQueries['update_status_jurnal'], 'Y', $transaksiId);

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Tambah Jurnal Pengeluaran'
      ));
      $loggerId      = $this->LastInsertId();
      if(is_array($queryLog)){
         foreach ($queryLog as $query) {
            $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
               $loggerId,
               addslashes($query)
            ));
         }
      }
      return $this->EndTrans($result);
   }

   public function doUpdateJurnal($param = array())
   {
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $tanggal          = date('Y-m-d', strtotime($param['tanggal']));
      $transaksiId      = $param['id'];
      $pembukuanId      = $param['pembukuan_id'];
      // CHECK STATUS AUTO APPROVE JURNAL
      // $getSettingAutoApprove  = GTFWConfiguration::GetValue('application', 'auto_approve');
      $autoApprove      = $this->getApplicationSetting('JURNAL_AUTO_APPROVE');
      $statusKas        = NULL;
      $bentukTransaksi  = NULL;
      $statusApproved   = 'T';
      if($autoApprove !== NULL AND (bool)$autoApprove === TRUE){
         $statusKas        = $param['status'];
         $bentukTransaksi  = $param['bentuk_transaksi'];
         $statusApproved   = 'Y';
      }

      $result           &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
         $pembukuanId
      ));

      // UPDATE PEMBUKUAN REFERENSI
      $result           &= $this->Execute($this->mSqlQueries['do_update_pembukuan_referensi'], array(
         $transaksiId,
         $userId,
         $tanggal,
         $param['keterangan'],
         $statusKas,
         $statusKas,
         $statusKas,
         $bentukTransaksi,
         $bentukTransaksi,
         $bentukTransaksi,
         $pembukuanId
      ));

      $queryLog[]       = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);
      $queryLog[]       = sprintf($this->mSqlQueries['do_update_pembukuan_referensi'], $transaksiId, $userId, $tanggal, $param['keterangan'], $statusKas, $statusKas, $statusKas, $bentukTransaksi, $bentukTransaksi, $bentukTransaksi, $pembukuanId);

      if(!empty($param['akun_debet'])){
         // insert pembukuan detail debet
         foreach ($param['akun_debet'] as $debet) {
            $subAkun    = preg_replace('/\s[\s]+/', '', $debet['sub_akun']);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $subAkun);

            $result        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $debet['id'],
               $debet['nominal'],
               $debet['keterangan'],
               $debet['referensi'],
               'D',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));

            $queryLog[]    = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $debet['id'], $debet['nominal'], $debet['keterangan'], $debet['referensi'], 'D', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
         }
      }

      if(!empty($param['akun_kredit'])){
         // insert pembukuan detail kredit
         foreach ($param['akun_kredit'] as $kredit) {
            $subAkun    = preg_replace('/\s[\s]+/', '', $kredit['sub_akun']);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            $subAkun    = preg_replace('/[\_]+/', '', $subAkun);
            list($subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7)   = explode('-', $subAkun);

            $result        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $kredit['id'],
               $kredit['nominal'],
               $kredit['keterangan'],
               $kredit['referensi'],
               'K',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));

            $queryLog[]    = sprintf($this->mSqlQueries['do_insert_pembukuan_detail'], $pembukuanId, $kredit['id'], $kredit['nominal'], $kredit['keterangan'], $kredit['referensi'], 'K', $subacc_1, $subacc_2, $subacc_3, $subacc_4, $subacc_5, $subacc_6, $subacc_7);
         }
      }

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Update Jurnal Pengeluaran'
      ));
      $loggerId      = $this->LastInsertId();
      if(is_array($queryLog)){
         foreach ($queryLog as $query) {
            $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
               $loggerId,
               addslashes($query)
            ));
         }
      }
      return $this->EndTrans($result);
   }

   public function doDeleteJurnal($pembukuanId, $transaksiId)
   {
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      if(!$pembukuanId){
         $result  &= false;
      }
      if(!$transaksiId){
         $result  &= false;
      }
      // reset status jurnal transaksi
      $result     &= $this->Execute($this->mSqlQueries['update_status_jurnal'], array('T', $transaksiId));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array($pembukuanId));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array($pembukuanId));
      $queryLog[]    = sprintf($this->mSqlQueries['update_status_jurnal'], 'T', $transaksiId);
      $queryLog[]    = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);
      $queryLog[]    = sprintf($this->mSqlQueries['do_delete_pembukuan_referensi'], $pembukuanId);

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Hapus Jurnal Pengeluaran'
      ));
      $loggerId      = $this->LastInsertId();
      if(is_array($queryLog)){
         foreach ($queryLog as $query) {
            $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
               $loggerId,
               addslashes($query)
            ));
         }
      }
      return $this->EndTrans($result);
   }

   public function doJurnalBalik($transaksiId, $pembukuanId)
   {
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      $autoApprove      = $this->getApplicationSetting('JURNAL_AUTO_APPROVE');
      $statusKas        = NULL;
      $bentukTransaksi  = NULL;
      $statusApproved   = 'Y'; // 'T'
      if($autoApprove !== NULL AND (bool)$autoApprove === TRUE){
         $statusKas        = $param['status'];
         $bentukTransaksi  = $param['bentuk_transaksi'];
         $statusApproved   = 'Y';
      }

      $result     &= $this->Execute($this->mSqlQueries['update_status_jurnal_balik'], array(
         $pembukuanId
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_jurnal_balik_pembukuan_referensi'], array(
         $userId,
         $statusApproved,
         $pembukuanId
      ));
      $jurnalBalikId    = $this->LastInsertId();
      $result     &= $this->Execute($this->mSqlQueries['do_update_due_date_transaksi'], array($transaksiId));
      $result     &= $this->Execute($this->mSqlQueries['do_jurnal_balik_pembukuan_detail'], array(
         $jurnalBalikId,
         $pembukuanId
      ));

      $queryLog[] = sprintf($this->mSqlQueries['update_status_jurnal_balik'], $pembukuanId);
      $queryLog[] = sprintf($this->mSqlQueries['do_jurnal_balik_pembukuan_referensi'], $userId, $statusApproved, $pembukuanId);
      $queryLog[] = sprintf($this->mSqlQueries['do_update_due_date_transaksi'], $transaksiId);
      $queryLog[] = sprintf($this->mSqlQueries['do_jurnal_balik_pembukuan_detail'], $jurnalBalikId, $pembukuanId);

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Jurnal Balik - Jurnal Pengeluaran'
      ));
      $loggerId      = $this->LastInsertId();
      if(is_array($queryLog)){
         foreach ($queryLog as $query) {
            $result  &= $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
               $loggerId,
               addslashes($query)
            ));
         }
      }

      return $this->EndTrans($result);
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
          if(!empty($requestData)){
         foreach ($requestData as $key => $value) {
            $query[$key]      = Dispatcher::Instance()->Encrypt($value);
         }
         $queryString         = urldecode(http_build_query($query));
          }
      }
      return $queryString;
   }
}
?>