<?php
require_once GTFWConfiguration::GetValue('application','docroot').
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class JurnalUmum extends Database
{
   private $mUnitObj;
   protected $mSqlFile;
   protected $mUserId   = NULL;
   public $_POST;
   public $_GET;
   public $method;
   # subaccount
   public $subAccName;
   public $subAccJml;
   public $defaultSubacc;

   public function __construct($connectionNumber = 0) {
      $this->mSqlFile   = 'module/jurnal_umum/business/jurnalumum.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      $this->mUnitObj   = new UserUnitKerja();
      parent::__construct($connectionNumber);
      $this->subAccName    = array('Pertama','Kedua','Ketiga','Keempat','Kelima','Keenam','Ketujuh');
      $this->subAccJml     = GTFWConfiguration::GetValue('application','subAccJml');
      $this->defaultSubacc = str_replace('9','0',GTFWConfiguration::GetValue('application','subAccFormat'));
   }

   private function setUserId() {
      if(class_exists('Security')){
         $this->mUserId    = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      }
   }

   public function getUserId() {
      $this->setUserId();
      return (int)$this->mUserId;
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

   public function GetBentukTransaksi(){
      $return     = $this->open($this->mSqlQueries['get_bentuk_transaksi'],array());
      return $return;
   }

   public function getDataJurnal($offset, $limit, $param = array())
   {

      $return     = $this->Open($this->mSqlQueries['get_data_jurnal'], array(
         '%'.$param['referensi'].'%',
         $param['posting'],
         (int)($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $offset,
         $limit
      ));

      return $return;
   }

   public function getCountJurnal($param = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_count_jurnal'], array(
         '%'.$param['referensi'].'%',
         $param['posting'],
         (int)($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
      ));

      if($return){
         return $return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getDataJurnalDetail($id, $prId)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_jurnal_detail'], array(
         $id,
         $prId
      ));

      return $return[0];
   }

   public function getDataHistoryJurnal($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_history_jurnal'], array(
         $id
      ));

      return $return;
   }

   public function getDataJurnalSubAkun($id, $prId)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_jurnal_sub_akun'], array(
         $id,
         $prId
      ));

      return $return;
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
      $unitKerja  = $this->mUnitObj->GetUnitKerjaRefUser($userId);
      $unitId     = $unitKerja['id'];
      $tanggal    = date('Y-m-d', strtotime($param['tanggal']));
      // set tahun pembukuan dan tahun anggaran
      $result     &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      $result     &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      $result     &= $this->Execute($this->mSqlQueries['get_set_tahun_pembukuan'], array());
      $result     &= $this->Execute($this->mSqlQueries['get_set_tahun_anggaran'], array());
      $result     &= $this->Execute($this->mSqlQueries['get_set_reference_number'], array(
         $tanggal,
         $tanggal,
         $unitId,
         $unitId,
         $tanggal,
         $tanggal
      ));

      $result     &= $this->Execute($this->mSqlQueries['do_set_realname_user'], array(
         $userId
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_save_transaksi'], array(
         $unitId,
         $userId,
         $tanggal,
         $tanggal,
         $param['keterangan'],
         $param['nominal_debet']
      ));

      $queryLog[]    = sprintf($this->mSqlQueries['do_set_tahun_pembukuan']);
      $queryLog[]    = sprintf($this->mSqlQueries['do_set_tahun_anggaran']);
      $queryLog[]    = sprintf($this->mSqlQueries['get_set_tahun_pembukuan']);
      $queryLog[]    = sprintf($this->mSqlQueries['get_set_tahun_anggaran']);
      $queryLog[]    = sprintf($this->mSqlQueries['get_set_reference_number'], $tanggal, $tanggal, $unitId, $unitId, $tanggal, $tanggal);
      $queryLog[]    = sprintf($this->mSqlQueries['do_set_realname_user'], $userId);
      $queryLog[]    = sprintf($this->mSqlQueries['do_save_transaksi'], $unitId, $userId, $tanggal, $tanggal, $param['keterangan'], $param['nominal_debet']);

      $transaksiId   = $this->LastInsertId();

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

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Tambah Jurnal Umum'
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

   public function doUpdateData($param = array())
   {
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      $transaksiId   = $param['id'];
      $pembukuanId   = $param['pembukuan_id'];
      $unitKerja  = $this->mUnitObj->GetUnitKerjaRefUser($userId);
      $unitId     = $unitKerja['id'];
      $tanggal    = date('Y-m-d', strtotime($param['tanggal']));
      $result     &= $this->Execute($this->mSqlQueries['do_set_realname_user'], array(
         $userId
      ));

      // update table transaksi
      $result     &= $this->Execute($this->mSqlQueries['do_update_transaksi_jurnal'], array(
         $userId,
         $tanggal,
         $tanggal,
         $param['keterangan'],
         $param['nominal_debet'],
         $transaksiId
      ));

      // delete pembukuan detail
      $result           &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
         $pembukuanId
      ));

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

      $queryLog[]       = sprintf($this->mSqlQueries['do_set_realname_user'], $userId);
      $queryLog[]       = sprintf($this->mSqlQueries['do_update_transaksi_jurnal'], $userId, $tanggal, $tanggal, $param['keterangan'], $param['nominal_debet'], $transaksiId);
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
         'Update Jurnal Umum'
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

   public function doDeleteDataJurnal($id, $pembukuanId)
   {
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
         $pembukuanId
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array(
         $pembukuanId
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_transaksi'], array(
         $id
      ));

      $queryLog[]    = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);
      $queryLog[]    = sprintf($this->mSqlQueries['do_delete_pembukuan_referensi'], $pembukuanId);
      $queryLog[]    = sprintf($this->mSqlQueries['do_delete_transaksi'], $id);

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Delete Jurnal Umum'
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
         'Jurnal Balik - Jurnal Umum'
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
   // ===================================================================================== //





   function DoApproveJurnal($iskas, $bentuk_trans, $id_pr) {
         $exec = $this->Execute($this->mSqlQueries['do_approve'], array($iskas, $bentuk_trans, $id_pr));
      if($exec) {
         $sql = sprintf($this->mSqlQueries['do_approve'], $iskas, $bentuk_trans, $id_pr);
         $this->DoAddLog("Approve Jurnal", $sql);
      }
      return $exec;
   }

   //===DO==
   function DoAddDetail($pembukuan_id, $coa_id, $nilai, $deskripsi, $keterangan, $tipe, $sub_acc, &$sql = '')
   {
      $sql = $this->mSqlQueries['do_add_pembukuan_detail'];

      # dynamic script insert subaccount
      if($sub_acc <> '') $arrSubAcc = explode('-',$sub_acc);
      else $arrSubAcc = explode('-',$this->defaultSubacc);

      $i=0;
      foreach($arrSubAcc as $val){
         $addSql .= ',`pdSubacc'.$this->subAccName[$i].'Kode` = "'.$val.'"';
         $i++;
      }
      $sql = str_replace('[INSERT_SUB_ACC]', $addSql, $sql);
      # dynamic script insert subaccount

      $ret = $this->Execute($sql, array(
         $pembukuan_id,
         $coa_id,
         $nilai,
         $deskripsi,
         $keterangan,
         $tipe
      ));

      if ($ret)
      {
         $sql = sprintf($this->mSqlQueries['do_add_pembukuan_detail'], $pembukuan_id, $coa_id, $nilai, $deskripsi, $keterangan, $tipe);
      }

      return $ret;
   }

   function DoAdd($data, &$msgerr)
   {
      //$this->SetDebugOn();

      $user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->StartTrans();
      $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_referensi'], array(
         empty($data['referensi_id']) ? NULL : $data['referensi_id'],
         $user_id,
         $data['tgl_transaksi'],
         $data['referensi_keterangan']
      ));
      $sql[] = sprintf($this->mSqlQueries['do_add_pembukuan_referensi'], $data['referensi_id'], $user_id, date('Y-m-d') , $data['referensi_nama']);
      $pembukuan_id = $this->Insert_ID();

      if($ok){
         if ((GTFWConfiguration::GetValue('application', 'auto_approve'))){
            $approve = $this->DoApproveJurnal($data['status_iskas'],$data['bentuk_transaksi'],$pembukuan_id);
            if ($approve){ $ok = true; }
            else { $ok = false; }
         }
      }


      if ($ok)
      {
         $this->Execute($this->mSqlQueries['update_status_is_jurnal'], array(
            $data['referensi_id']
         ));

         //add new debet
         if (is_array($data['debet']['tambah']) && $ok){
            foreach($data['debet']['tambah'] as $val){
               $detok = $this->DoAddDetail($pembukuan_id, $val['id'], $val['nilai'], $val['deskripsi'], $val['keterangan'], 'D', $val['sub_account'], $sqlret);
               $sql[] = $sqlret;

               if (!$detok){
                  $msgerr['debet']['id'][] = $val['id'];
                  $msgerr['debet']['msg'].= $val['nama'] . ', ';
                  $ok = false;
                  break;
               }
            } //end foreach
         } //end if

         //add new kredit
         if (is_array($data['kredit']['tambah']) && $ok){
            foreach($data['kredit']['tambah'] as $val){
               $detok = $this->DoAddDetail($pembukuan_id, $val['id'], $val['nilai'], $val['deskripsi'], $val['keterangan'], 'K', $val['sub_account'], $sqlret);
               $sql[] = $sqlret;

               if (!$detok){
                  $msgerr['kredit']['id'][] = $val['id'];
                  $msgerr['kredit']['msg'].= $val['nama'] . ', ';
                  $ok = false;

                  break;
               }
            } //end foreach
         } //end if array kredit
      } //end if
      else
         $msgerr = " jurnal ";

      //$ok=true;
      $this->EndTrans($ok);
      if ($ok) $this->DoAddLog('Tambah Jurnal Umum', $sql);

      return $ok;
   }

   function DoUpdate($data, &$msgerr)
   {
      $user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->StartTrans();

      #update table transaksi
      $updateTrans = $this->Execute($this->mSqlQueries['do_update_transaksi'],array(
                                                         $user_id,
                                                         $user_id,
                                                         $data['tgl_transaksi'],
                                                         $data['tgl_transaksi'],
                                                         $data['totalValue'],
                                                         $user_id,
                                                         $data['referensi_id']
                                                      ));
      $sql[] = sprintf($this->mSqlQueries['do_update_transaksi'],$user_id,$user_id,$data['tgl_transaksi'],$data['tgl_transaksi'],$data['totalValue'],$user_id,$data['referensi_id']);

      #update pembukuan_referensi
      $updatePembRef = $this->Execute($this->mSqlQueries['do_update_pembukuan_referensi'], array(
         $data['referensi_id'],
         $user_id,
         $data['tgl_transaksi'],
         $data['referensi_keterangan'],
         $data['pembukuan_referensi_id']
      ));
      $sql[] = sprintf($this->mSqlQueries['do_update_pembukuan_referensi'], $data['referensi_id'], $user_id, $data['tgl_transaksi'],$data['referensi_keterangan'], $data['pembukuan_referensi_id']);

      $ok = $updateTrans && $updatePembRef;

      //hayo kalo memang ada user yang mendelete akun ya didelete dulu lah
      if (isset($data['deleted']['id']) && $ok){
         foreach($data['deleted']['id'] as $val){
            $delok = $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
               $val
            ));
            $sql[] = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $val);

            if (!$delok){
               $ok = false;
               break;
            }
         }
      }

      if ($ok)
      {
         //update data debet existing
         if (is_array($data['debet']['datalist']) && $ok){
            foreach($data['debet']['datalist'] as $val){
               $detok = $this->DoUpdateDetail($val['id'], $val['nilai'], $val['deskripsi'], $val['keterangan'], $val['detail_id'], $val['sub_account'], $sqlret);
               $sql[] = $sqlret;

               if (!$detok){
                  $msgerr['debet']['id'][] = $val['id'];
                  $msgerr['debet']['msg'].= $val['keterangan'] . ', ';
                  $ok = false;
                  break;
               }
            } //end foreach
         } //end if

         //update data kredit existing
         if (is_array($data['kredit']['datalist']) && $ok){
            foreach($data['kredit']['datalist'] as $val){
               $detok = $this->DoUpdateDetail($val['id'], $val['nilai'], $val['deskripsi'], $val['keterangan'], $val['detail_id'], $val['sub_account'], $sqlret);
               $sql[] = $sqlret;

               if (!$detok)
               {
                  $msgerr['kredit']['id'][] = $val['id'];
                  $msgerr['kredit']['msg'].= $val['keterangan'] . ', ';
                  $ok = false;
                  break;
               }
            } //end foreach
         } //end if array kredit

         //add new debet
         if (is_array($data['debet']['tambah']) && $ok){
            foreach($data['debet']['tambah'] as $val){
               $detok = $this->DoAddDetail($data['pembukuan_referensi_id'], $val['id'], $val['nilai'], $val['deskripsi'], $val['keterangan'], 'D', $val['sub_account'], $sqlret);
               $sql[] = $sqlret;

               if (!$detok){
                  $msgerr['debet']['id'][] = $val['id'];
                  $msgerr['debet']['msg'].= $val['nama'] . ', ';
                  $ok = false;
                  break;
               }
            } //end foreach
         } //end if array kredit

         //add new kredit
         if (is_array($data['kredit']['tambah']) && $ok){
            foreach($data['kredit']['tambah'] as $val){
               $detok = $this->DoAddDetail($data['pembukuan_referensi_id'], $val['id'], $val['nilai'], $val['deskripsi'], $val['keterangan'], 'K', $val['sub_account'], $sqlret);
               $sql[] = $sqlret;

               if (!$detok){
                  $msgerr['kredit']['id'][] = $val['id'];
                  $msgerr['kredit']['msg'].= $val['nama'] . ', ';
                  $ok = false;
                  break;
               }
            } //end foreach
         } //end if array kredit
      }//end if $ok
      else
         $msgerr = " coa debet ";

      //$ok=true;
      $this->EndTrans($ok);
      if ($ok) $this->DoAddLog('Update Jurnal Umum', $sql);

      return $ok;
   }

   function DoUpdateDetail($coa_id, $nilai, $deskripsi, $keterangan, $detail_id, $sub_acc, &$sql = '')
   {
      $sql = $this->mSqlQueries['do_update_pembukuan_detail'];

      # dynamic script update subaccount
      if($sub_acc <> '') $arrSubAcc = explode('-',$sub_acc);
      else $arrSubAcc = explode('-',$this->defaultSubacc);

      $i=0;
      foreach($arrSubAcc as $val){
         $addSql .= ',`pdSubacc'.$this->subAccName[$i].'Kode` = "'.$val.'"';
         $i++;
      }
      $sql = str_replace('[UPDATE_SUB_ACC]', $addSql, $sql);
      # dynamic script update subaccount

      $ret = $this->Execute($sql, array(
         $coa_id,
         $nilai,
         $deskripsi,
         $keterangan,
         $detail_id
      ));

      //if($ret) {
      $sql = sprintf($this->mSqlQueries['do_update_pembukuan_detail'], $coa_id, $nilai, $deskripsi, $keterangan, $detail_id);
      //logger($this->mdebug(1));
      //}

      return $ret;
   }

   /**
    * fungsi GetTransId
    * untuk mendapatkan transaksi id bersarkan id tabel pembukuan referensi
    * yang akan digunakan untuk proses hapus data transaksi
    * added
    * @since 02-01-2012
    */

   function GetTransId($id)
   {
         $transId = $this->Open($this->mSqlQueries['get_trans_id'],array($id));
         return $transId[0]['trans_id'];
   }

   function DoDelete($id)
   {
      $this->StartTrans();

      $transId = $this->GetTransId($id);

      # delete pembukuan detail by pdPrId (unused since on delete cascade pembukuan referensi active)
//       $ok = $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array($id));
//       $sql[] = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $id);

      # delete pembukuan referensi
      $ok = $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array($id));
      $sql[] = sprintf($this->mSqlQueries['do_delete_pembukuan_referensi'], $id);

      # delete transaksi
      if($ok)  $ok = $this->Execute($this->mSqlQueries['do_delete_transaksi'],array($transId));
      $sql[] = sprintf($this->mSqlQueries['do_delete_transaksi'], $transId);

      $this->EndTrans($ok);

      if ($ok) $this->DoAddLog('Delete Jurnal Umum', $sql);

      return $ok;
   }

   function date2string($date)
   {
      $bln = array(
         1 => 'Januari',
         2 => 'Februari',
         3 => 'Maret',
         4 => 'April',
         5 => 'Mei',
         6 => 'Juni',
         7 => 'Juli',
         8 => 'Agustus',
         9 => 'September',
         10 => 'Oktober',
         11 => 'November',
         12 => 'Desember'
      );
      $arrtgl = explode('-', $date);

      return $arrtgl[2] . ' ' . $bln[(int)$arrtgl[1]] . ' ' . $arrtgl[0];
   }

   //LOGGER LOGGER LOGGER
   function DoAddLog($keterangan, $query)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $ip = $this->GetRealIP();
      $result = $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ip,
         $keterangan
      ));
      $id_logger = $this->LastInsertId();

      if (is_array($query))
      {

         foreach($query as $val)
         {
            $this->DoAddLogDetil($id_logger, $val);
         }
      }
      else $this->DoAddLogDetil($id_logger, $query);

      return $result;
   }
   function DoAddLogDetil($id, $query)
   {
      $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array(
         $id,
         addslashes($query)
      ));

      return $result;
   }

   function GetMaxIdPembukuanRef()
   {
      $ret = $this->open($this->mSqlQueries['get_max_pembukuan_referensi_id'], array());

      return $ret[0]['max_id'];
   }
   function UpdateStatusJurnal($id_trans)
   {

      return $this->Execute($this->mSqlQueries['update_status_jurnal'], array(
         $id_trans
      ));
   }

   private function SetJurnalBalikStatus($id)
   {
      $result = $this->Execute($this->mSqlQueries['set_jurnal_balik_status'], array(
         $id
      ));

      return $result;
   }
   function GetDataJurnalBalik($id_pemb)
   {

      return $this->open($this->mSqlQueries['get_data_jurnal_balik'], array(
         $id_pemb
      ));
   }
   function CekAkunBukuBesar($coa_id)
   {
      $result = $this->Open($this->mSqlQueries['cek_akun_buku_besar'], array(
         $coa_id
      ));

      return $result[0];
   }
   function DoInsertBukuBesar($coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_buku_besar'], array(
         $coa_id,
         $saldo_awal,
         $debet,
         $kredit,
         $saldo,
         $saldo_akhir,
         $userId
      ));
      $sql = sprintf($this->mSqlQueries['do_insert_buku_besar'], $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);

      if ($result) $this->DoAddLog('Insert Buku Besar', $sql);

      #echo $sql;

      return $result;
   }
   function DoUpdateBukuBesar($coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $bb_id)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_update_buku_besar'], array(
         $coa_id,
         $saldo_awal,
         $debet,
         $kredit,
         $saldo,
         $saldo_akhir,
         $userId,
         $bb_id
      ));
      $sql = sprintf($this->mSqlQueries['do_update_buku_besar'], $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id);

      if ($result) $this->DoAddLog('Update Buku Besar', $sql);

      return $result;
   }
   function DoInsertBukuBesarHis($pemb_ref_id, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_buku_besar_his'], array(
         $pemb_ref_id,
         $coa_id,
         $saldo_awal,
         $debet,
         $kredit,
         $saldo,
         $saldo_akhir,
         $userId
      ));
      $sql = sprintf($this->mSqlQueries['do_insert_buku_besar_his'], $pemb_ref_id, $coa_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);

      if ($result) $this->DoAddLog('Insert Buku Besar History', $sql);

      return $result;
   }
   function DoInsertLabaRugiBukuBesar($saldo_awal, $debet, $kredit, $saldo, $saldo_akhir)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_laba_rugi_buku_besar'], array(
         $userId,
         $saldo_awal,
         $debet,
         $kredit,
         $saldo,
         $saldo_akhir,
         $userId
      ));
      $sql = sprintf($this->mSqlQueries['do_insert_laba_rugi_buku_besar'], $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);

      if ($result) $this->DoAddLog('Insert Labarugi Buku Besar', $sql);

      #echo $sql;

      return $result;
   }
   function DoUpdateLabaRugiBukuBesar($saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $bb_id)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_update_laba_rugi_buku_besar'], array(
         $userId,
         $saldo_awal,
         $debet,
         $kredit,
         $saldo,
         $saldo_akhir,
         $userId,
         $bb_id
      ));
      $sql = sprintf($this->mSqlQueries['do_update_laba_rugi_buku_besar'], $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId, $bb_id);

      if ($result) $this->DoAddLog('Update Labarugi Buku Besar', $sql);

      return $result;
   }
   function DoInsertLabaRugiBukuBesarHis($pemb_ref_id, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $result = $this->Execute($this->mSqlQueries['do_insert_laba_rugi_buku_besar_his'], array(
         $pemb_ref_id,
         $userId,
         $saldo_awal,
         $debet,
         $kredit,
         $saldo,
         $saldo_akhir,
         $userId
      ));
      $sql = sprintf($this->mSqlQueries['do_insert_laba_rugi_buku_besar_his'], $pemb_ref_id, $userId, $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $userId);

      if ($result) $this->DoAddLog('Insert Labarugi Buku Besar History', $sql);

      return $result;
   }
   function GetCoaLabaRugi()
   {
      $result = $this->Open($this->mSqlQueries['get_coa_laba_rugi'], array());

      return $result;
   }
   function CekAkunLabaRugiBukuBesar()
   {
      $result = $this->Open($this->mSqlQueries['cek_akun_laba_rugi_buku_besar'], array());

      return $result[0];
   }
   function UpdateStatusPostingBalikPembukuanRef($pr_id)
   {
      $result = $this->Execute($this->mSqlQueries['update_status_posting_balik_pembukuan_ref'], array(
         $pr_id
      ));

      return $result;
   }
   function BalikJurnal($id_pembukuan_ref)
   {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $this->StartTrans();
      $jurnal_lama = $this->GetDataById($id_pembukuan_ref);

      #set jurnal balik status
      $this->SetJurnalBalikStatus($id_pembukuan_ref);

      //pemb ref
      $ok = $this->Execute($this->mSqlQueries['do_add_pembukuan_referensi'], array(
         $jurnal_lama[0]['referensi_id'],
         $userId,
         date('Y-m-d') ,
         $jurnal_lama[0]['referensi_nama']
      ));



      $sql[] = sprintf($this->mSqlQueries['do_add_pembukuan_referensi'], $jurnal_lama[0]['referensi_id'], $userId, date('Y-m-d') , $jurnal_lama[0]['referensi_nama']);
      $pembukuan_id = $this->GetMaxIdPembukuanRef();

      #echo 'pembukuan id = '.$pembukuan_id; print_r($sql); exit;
      //pemb detil


      if ($ok)
      {
         $this->Execute($this->mSqlQueries['update_status_posting_saat_jurnal_balik'], array(
            $id_pembukuan_ref
         ));

         for ($i = 0;$i < sizeof($jurnal_lama);$i++)
         {

            if ($jurnal_lama[$i]['detail_status'] == 'D') $tipe_balik = 'K';
            elseif ($jurnal_lama[$i]['detail_status'] == 'K') $tipe_balik = 'D';
            $ret = $this->Execute($this->mSqlQueries['do_add_pembukuan_detail'], array(
               $pembukuan_id,
               $jurnal_lama[$i]['coa_id'],
               $jurnal_lama[$i]['detail_nilai'],
               $jurnal_lama[$i]['detail_keterangan'],
               'Jurnal Balik',
               $tipe_balik
            ));

            if ($ret)
            {
               $sql_detil[$i] = sprintf($this->mSqlQueries['do_add_pembukuan_detail'], $pembukuan_id, $jurnal_lama[$i]['coa_id'], $jurnal_lama[$i]['detail_nilai'], $jurnal_lama[$i]['detail_keterangan'],'Jurnal Balik', $tipe_balik);
               $this->DoAddLog('Insert Detil Jurnal Balik Umum', $sql_detil[$i]);
            }
         }

         #get data pembukuan hasil jurnal balik untuk langusng di posting
         $data_jurnal_balik = $this->GetDataJurnalBalik($pembukuan_id);

         //proses posting jurnal balik

         if (!empty($data_jurnal_balik))
         {

            for ($i = 0;$i < count($data_jurnal_balik);$i++)
            {

               if (strtoupper($data_jurnal_balik[$i]['status_pembukuan']) == 'D')
               {
                  $debet = $data_jurnal_balik[$i]['nilai'];
                  $kredit = 0;
                  $kredit_lr = - $data_jurnal_balik[$i]['nilai'];
               }
               elseif (strtoupper($data_jurnal_balik[$i]['status_pembukuan']) == 'K')
               {
                  $debet = 0;
                  $kredit = $data_jurnal_balik[$i]['nilai'];
                  $kredit_lr = $data_jurnal_balik[$i]['nilai'];
               }
               $cek_akun_from_bb = $this->CekAkunBukuBesar($data_jurnal_balik[$i]['coa_id']);

               if (!empty($cek_akun_from_bb['bb_id']))
               { #echo 'tes'; exit;


                  if ($data_jurnal_balik[$i]['coa_status_debet'] == 1) $saldo = $debet - $kredit;
                  elseif ($data_jurnal_balik[$i]['coa_status_debet'] == 0) $saldo = $kredit - $debet;
                  $saldo_awal = $cek_akun_from_bb['saldo_akhir'];
                  $saldo_akhir = $saldo_awal + $saldo;

                  //update buku besar, karena akun coa nya sudah ada
                  $update_bb = $this->DoUpdateBukuBesar($data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir, $cek_akun_from_bb['bb_id']);

                  //insert buku besar hystory
                  $insert_bb_his = $this->DoInsertBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
               }
               else
               {

                  //insert bb disini

                  if ($data_jurnal_balik[$i]['coa_status_debet'] == 1) $saldo = $debet - $kredit;
                  elseif ($data_jurnal_balik[$i]['coa_status_debet'] == 0) $saldo = $kredit - $debet;
                  $saldo_awal = 0;
                  $saldo_akhir = $saldo_awal + $saldo;
                  $insert_bb = $this->DoInsertBukuBesar($data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);

                  //insert buku besar hystory
                  $insert_bb_his = $this->DoInsertBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $data_jurnal_balik[$i]['coa_id'], $saldo_awal, $debet, $kredit, $saldo, $saldo_akhir);
               }

               //update status is posting di pembukuan_referensi
               $this->UpdateStatusPostingBalikPembukuanRef($data_jurnal_balik[$i]['pembukuan_ref_id']);

               //cek coa laba ditahan
               $list_coa_laba_rugi = $this->GetCoaLabaRugi(); #print_r($list_coa_laba_rugi); exit;


               for ($l = 0;$l < sizeof($list_coa_laba_rugi);$l++)
               {
                  $arr_coa[] = $list_coa_laba_rugi[$l]['coaKelompokId'];
               }

               if (in_array($data_jurnal_balik[$i]['coa_kelompok'], $arr_coa))
               {
                  $cek_akun_laba_rugi_from_bb = $this->CekAkunLabaRugiBukuBesar();

                  if (!empty($cek_akun_laba_rugi_from_bb['bb_id']))
                  {
                     $saldo_awal_lr = $cek_akun_laba_rugi_from_bb['saldo_akhir'];
                     $saldo_akhir_lr = $saldo_awal_lr + ($kredit_lr - 0);

                     //proses insert bukubesar untuk coa laba rugi
                     $update_labarugi_bb = $this->DoUpdateLabaRugiBukuBesar($saldo_awal_lr, 0, $kredit_lr, $kredit_lr - 0, $saldo_akhir_lr, $cek_akun_laba_rugi_from_bb['bb_id']);
                     $insert_labarugi_bb_his = $this->DoInsertLabaRugiBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $saldo_awal_lr, 0, $kredit_lr, $kredit_lr - 0, $saldo_akhir_lr);
                  }
                  else
                  {
                     $saldo_awal_lr = 0;
                     $saldo_akhir_lr = $saldo_awal_lr + ($kredit_lr - 0);
                     $insert_labarugi_bb = $this->DoInsertLabaRugiBukuBesar($saldo_awal_lr, 0, $kredit_lr, $kredit_lr - 0, $saldo_akhir_lr);

                     //insert buku besar hystory
                     $insert_labarugi_bb_his = $this->DoInsertLabaRugiBukuBesarHis($data_jurnal_balik[$i]['pembukuan_ref_id'], $saldo_awal_lr, 0, $kredit_lr, $kredit_lr - 0, $saldo_akhir_lr);
                  }
               }
            }
         }

         //end of posting jurnal balik

      }
      else $ok = false;
      $this->EndTrans($ok);

      if ($ok) $this->DoAddLog('Insert Jurnal Balik Umum', $sql);

      return $ok;
   }
   function UpdateStatusJurnalSetelahDelete($status, $prId)
   {
      $result = $this->Execute($this->mSqlQueries['update_status_is_jurnal_ketika_delete'], array(
         $status,
         $prId
      ));

      return $result;
   }
   function AutogenerateTranasaksi($userId, $nominal, $data)
   {

      //untuk sementara unitId di samakan dengan unit User yang akses
      $noBuktitrans = $this->CountBuktiTrans($userId,$data['tgl_transaksi']);
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi'], array(
         $userId,
         $noBuktitrans,
         $userId,
         $data['tgl_transaksi'],
         $data['tgl_transaksi'],
         $nominal,
         $userId
      ));

      if ($result)
      {
         $result = $this->Open($this->mSqlQueries['get_last_kuitansi_id'], array());

         return $result['0']['transId'];
      }
      else
      return 0;
   }

   # tambahan untuk otomasi count bukti transaksi
   # format baru : BM[auto]/[kode_unit]/[tahun]
   function CountBuktiTrans($userId,$tglTransaksi='')
   {
      if($tglTransaksi <> '')$tgl = explode('-',$tglTransaksi);
      else $tgl[0] = date('Y');

      $result = $this->Open($this->mSqlQueries['count_bukti'], array(
         "BM%",
         //$tgl[1],
         $tgl[0],
         $userId
      ));
      $unitKerjaKode = $this->GetUnitKode($userId);

      if (empty($result))
      {

         return 'BM1/' . $unitKerjaKode . '/' .$tgl[0];
      }

      for ($i = count($result) - 1;$i >= 0;$i--) $tmp[] = $result[$i]['transReferensi'];
      natsort($tmp);
      end($tmp);
      $nilai = preg_replace('/^[a-z]+(\d+)[^\d].*$/i', '\1', current($tmp)) + 1;

      return 'BM' . $nilai . '/' . $unitKerjaKode . '/' .$tgl[0];
   }

   function GetUnitKode($userId)
   {
      $result = $this->Open($this->mSqlQueries['get_unit_kode'], array(
         $userId
      ));

      return $result[0]['unitkerjaKode'];
   }

   function InsertPembukuanDetailPembukuanRef($prId,$isApprove,$isJurnalBalik,$posisiDebetKredit)
   {
      # insert pembukuan_referensi
      $statusInsertPembRef = $this->Execute($this->mSqlQueries['insert_transaksi_untuk_jurnal_balik'], array(
         $isApprove,
         $isJurnalBalik,
         $prId
      ));

      # get data pembukuan_detail
      if($posisiDebetKredit == "terbalik"){
         $getDataPembDet = $this->Open($this->mSqlQueries['get_data_pembukuan_detail_jurnal_balik'], array($prId));
      }
      else{
         $getDataPembDet = $this->Open($this->mSqlQueries['get_data_pembukuan_detail'], array($prId));
      }

      # insert new pembukuan_detail
      $statusInsertPembDet = "1";
      foreach($getDataPembDet as $key => $value){
         $insertPembDet = $this->Execute($this->mSqlQueries['insert_data_pembukuan_detail'], array(
            $value['pdCoaId'],
            $value['pdNilai'],
            $value['pdKeterangan'],
            $value['pdKeteranganTambahan'],
            $value['pdStatus'],
            $value['pdSubaccPertamaKode'],
            $value['pdSubaccKeduaKode'],
            $value['pdSubaccKetigaKode'],
            $value['pdSubaccKeempatKode'],
            $value['pdSubaccKelimaKode'],
            $value['pdSubaccKeenamKode'],
            $value['pdSubaccKetujuhKode']));
         if($insertPembDet != true){
            $statusInsertPembDet = "0";
         }
      }

      return $statusInsertPembRef && $statusInsertPembDet;
   }

   function UpdateStatusJurnalBalik($prId){
      $statusUpdateJurnalBalik = $this->Execute($this->mSqlQueries['update_status_jurnal_balik'], array(
         $prId));
      return $statusUpdateJurnalBalik;
   }

   function GetDataHistoryJurnalByPrId($prId){
      $sql = $this->mSqlQueries['get_data_history_jurnal_by_pr_id'];

      # generate dynamic subaccount view
      if($this->subAccJml > 0){
         $defaultSubAcc = explode('-',str_replace('9','0',GTFWConfiguration::GetValue('application','subAccFormat')));
         for($i=0;$i<=($this->subAccJml-1);$i++){
            $arrView[$i] = 'IFNULL(pdSubacc'.$this->subAccName[$i].'Kode,"'.$defaultSubAcc[$i].'")';
         }
         $addSqlView = ',CONCAT('.implode(",'-',",$arrView).') AS subakun';
         $sql = str_replace('[SUBACC_VIEW]', $addSqlView, $sql);
      }else $sql = str_replace('[SUBACC_VIEW]', '', $sql);

      $data = $this->Open($sql, array($prId));
      return $data;
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
}
?>