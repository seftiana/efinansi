<?php
/**
* ================= doc ====================
* FILENAME     : TransaksiPengeluaranBank.class.php
* @package     : TransaksiPengeluaranBank
* scope        : PUBLIC
* @Author      : Eko Susilo
* @modified by: noor hadi <noor.hadi@gamatechno.com>
* @Created     : 2016-03-10
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2016 Gamatechno
* ================= doc ====================
*/

require_once GTFWConfiguration::GetValue('application', 'docroot') .
        'module/user_unit_kerja/business/UserUnitKerja.class.php';

require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
        'module/generate_number/business/GenerateNumber.class.php';

class TransaksiPengeluaranBank extends Database
{
   # internal variables
   protected $mSqlFile;
   protected $mUserId = NULL;
   public $_POST;
   public $_GET;
   public $method;
   
   private $mUnitObj;    
   private $mNumber;
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_transaksi_pengeluaran_bank/business/finansi_transaksi_pengeluaran_bank.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);
      
      
      $this->mUnitObj = new UserUnitKerja();
      $this->mNumber    = new GenerateNumber($connectionNumber);
   }
   
   public function getTransaksiDetil($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_transaksi_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }
    public function getListTransaksiDetail($id)
    {
      $return     = $this->Open($this->mSqlQueries['get_list_transaksi_detil'], array($id));

      return self::ChangeKeyName($return);
    }
   
    public function getCekNoref($noref) {
        $return   = $this->Open($this->mSqlQueries['get_cek_noref'], array($noref));
        //$return_2 = $this->Open($this->mSqlQueries['get_cek_noref_sppu'], array($noref,$noref));
        if($return[0]['total'] > 0 ) {//|| $return_2[0]['total'] > 0 ) {
            return TRUE;
        } else {
            return FALSE;
        }
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

   public function getDataJurnal($offset, $limit, $param = array())
   {  //$this->SetDebugOn();
      $result     = $this->Open($this->mSqlQueries['get_data_jurnal_pengeluaran'], array(
         '%'.$param['referensi'].'%',
         $param['posting'],
         (int)($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $offset,
         $limit
      ));
      
      $rCount     = $this->Open($this->mSqlQueries['count_jurnal_pengeluaran'], array(
         '%'.$param['referensi'].'%',
         $param['posting'],
         (int)($param['posting'] == '' OR strtolower($param['posting']) == 'all'),
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date']))
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
   

   public function getTahunAnggaranYear(){
       $return = $this->Open($this->mSqlQueries['get_range_tahun_periode_anggaran'], array());
       
       if(empty($return)){
           $return[0]['tahun_awal'] = date('Y') - 5;
           $return[0]['tahun_akhir'] = date('Y') + 5;
       }
       
       return $return[0];
   }
   
   
   public function getTransaksiBankById($id, $prId)
   {
      $return     = $this->Open($this->mSqlQueries['get_transaksi_bank_by_id'], array(
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

   public function getSppuTransaksiBank($id, $param)
   {
      $return     = $this->Open($this->mSqlQueries['get_sppu_transaksi_bank'], array(
         $id,
         $param
      ));
      return self::ChangeKeyName($return[0]);
   }

   public function doSaveTransaksiPengeluranBank($param = array())
   {//$this->SetDebugOn();
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $statusApproved = 'T';
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
        $tanggal          = date('Y-m-d', strtotime($param['tanggal']));        
        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        
        // set tahun pembukuan dan tahun anggaran
        $result &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
        $result &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
        $result &= $this->Execute($this->mSqlQueries['get_set_tahun_pembukuan'], array());
        $result &= $this->Execute($this->mSqlQueries['get_set_tahun_anggaran'], array());
        
        $result &= $this->Execute($this->mSqlQueries['do_set_realname_user'], array(
            $userId
        ));
        if($param['is_ref'] ==='Y') {
            $nomorReferensi   = $param['referensi_no'];// berisi nomor referensi sppu 
            $referensiId      = $param['referensi_id'];//dari referensi sppu id
        } else {
            $nomorReferensi = $this->mNumber->getNomorBpBank($tanggal);
            $referensiId      = NULL;
        }
        
        $result &= $this->Execute($this->mSqlQueries['do_save_transaksi'], array(
            $unitId,
            $nomorReferensi,
            $userId,
            $tanggal,
            $tanggal,
            $param['keterangan'],
            $param['nominal']
        ));
        
        $transaksiId = $this->LastInsertId();
        //simpan log
        $queryLog[] = sprintf($this->mSqlQueries['do_set_tahun_pembukuan']);
        $queryLog[] = sprintf($this->mSqlQueries['do_set_tahun_anggaran']);
        $queryLog[] = sprintf($this->mSqlQueries['get_set_tahun_pembukuan']);
        $queryLog[] = sprintf($this->mSqlQueries['get_set_tahun_anggaran']);
        $queryLog[] = sprintf($this->mSqlQueries['do_set_realname_user'], $userId);
        $queryLog[] = sprintf($this->mSqlQueries['do_save_transaksi'], $unitId, $nomorReferensi,$userId, $tanggal, $tanggal, $param['keterangan'], $param['nominal_debet']);

        
        //transaksi bank 
        //tanpa referensi sppu maka dilakukan INSERT di table finansi_pa_transaksi_bank
        if($param['is_ref'] != 'Y'){
          $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_bank'], array(
                  $nomorReferensi ,
                  $nomorReferensi ,
                  $param['tanggal'],
                  $param['nama_penyetor'],
                  $param['nama_penerima'],
                  $param['nominal'],
                  $userId,
                  $referensiId
          ));
        }else{
          //apabila dengan referensi sppu maka dilakukan UPDATE di table finansi_pa_transaksi_bank
          $result &= $this->Execute($this->mSqlQueries['do_update_sppu_transaksi_bank'], array(
             $param['tanggal'],
             $param['nama_penyetor'],
             $param['nama_penerima'],
             $param['nominal'],
             $userId,
             $referensiId
          ));
        }

        if($param['is_ref'] != 'Y'){
          $transaksi_bank_id  = $this->LastInsertId();
        }else{
          //ambil id transaksi bank berdasarkan id sppu dan bpkb
          $dataTransBank      = $this->getSppuTransaksiBank($referensiId, $param['referensi_no']);
          $transaksi_bank_id  = $dataTransBank['id'];
        }

        // insert transaksi detail penerimaan bank
        $result &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_pengeluaran_bank'], array(
            $transaksiId,
            $transaksi_bank_id
        ));
        
        //tanpa referensi sppu maka dilakukan INSERT di table finansi_pa_transaksi_bank
        if($param['is_ref'] != 'Y'){
        $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_bank'],
                                  $nomorReferensi ,
                                  $nomorReferensi ,
                                  $param['tanggal'],
                                  $param['nama_penyetor'],
                                  $param['nama_penerima'],
                                  $param['nominal_debet'],
                                  $userId,
                                  $referensiId);
        }else{
          //apabila dengan referensi sppu maka dilakukan UPDATE di table finansi_pa_transaksi_bank
          $queryLog[] = sprintf($this->mSqlQueries['do_update_sppu_transaksi_bank'],
                      $param['tanggal'],
                      $param['nama_penyetor'],
                      $param['nama_penerima'],
                      $param['nominal_debet'],
                      $userId,
                      $referensiId);
        }

        $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_det_pengeluaran_bank'],
                                $transaksiId,
                                $transaksi_bank_id);
            
         if($param['is_ref'] ==='Y') {
            //isi tabel finansi_pa_transaksi_bank_detail
            $result  &= $this->Execute($this->mSqlQueries['do_insert_transaksi_bank_detail_from_sppu'], array(
                $transaksi_bank_id,
                $param['tanggal'],
                $userId,
                $referensiId
            ));
            $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_bank_detail_from_sppu'],
                                $transaksi_bank_id,
                                $param['tanggal'],
                                $userId,
                                $referensiId);
        }
        // end transaksi bank

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
            $queryLog[] = sprintf($this->mSqlQueries['do_insert_transaksi_bank_detil'], $transaksi_bank_id,$kredit['nama'],$param['tanggal'],$kredit['nominal'],$userId);
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

      // update status sppu
      $result        &= $this->Execute($this->mSqlQueries['update_status_sppu'], array('Ya', $referensiId));
      $queryLog[]    = sprintf($this->mSqlQueries['update_status_sppu'], 'Ya', $referensiId);

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Tambah Transaksi Pengeluaran Bank'
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

   public function doUpdateTransaksiPengeluranBank($param = array())
   {
      $userId     = $this->getUserId();
      $ipAddress  = (string)$this->GetRealIP();
      $queryLog   = array();
      $result     = true;
      $this->StartTrans();
      if(!is_array($param)){
         $result  &= false;
      }
      
      $tanggal_old          = date('Y-m', strtotime($param['tanggal_ymd']));
      $tanggal_curr         = date('Y-m', strtotime($param['tanggal']));
      $nomorReferensi       = $param['referensi_no'];
      if($param['is_ref'] ==='Y') {
          $tanggal          = date('Y-m-d', strtotime($param['tanggal_ymd']));
      } else {
          $tanggal          = date('Y-m-d', strtotime($param['tanggal']));
          if($tanggal_old !== $tanggal_curr) {
              $nomorReferensi = $this->mNumber->getNomorBpBank($tanggal);
          }
      }
      
      $transaksiId      = $param['id'];
      $pembukuanId      = $param['pembukuan_id'];


        $unitKerja = $this->mUnitObj->GetUnitKerjaRefUser($userId);
        $unitId = $unitKerja['id'];
        $result &= $this->Execute($this->mSqlQueries['do_set_realname_user'], array(
            $userId
        ));

        // update table transaksi
        $result &= $this->Execute($this->mSqlQueries['do_update_transaksi'], array(
            $nomorReferensi,
            $userId,
            $tanggal,
            $tanggal,
            $param['keterangan'],
            $param['nominal'],
            $transaksiId
        ));
        $queryLog[] = sprintf($this->mSqlQueries['do_update_transaksi'],
            $nomorReferensi,                
            $userId,
            $tanggal,
            $tanggal,
            $param['keterangan'],
            $param['nominal'],
            $transaksiId);
        
        //update table finansi_pa_transaksi_bank
        $result &= $this->Execute($this->mSqlQueries['do_update_transaksi_bank'], array(
           $nomorReferensi,
           $nomorReferensi,
           $tanggal,
           $param['nama_penyetor'],
           $param['nama_penerima'],
           $param['nominal'],
           $userId,
           $transaksiId
        ));
     
        $queryLog[] = sprintf($this->mSqlQueries['do_update_transaksi_bank'],
                    $nomorReferensi,
                    $nomorReferensi,
                    $tanggal,
                    $param['nama_penyetor'],
                    $param['nama_penerima'],
                    $param['nominal_debet'],
                    $userId,
                    $transaksiId);        
        //end
    

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
         'Update Transaksi Pengeluaran Bank'
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

   public function doDeleteTransaksiPengeluaranBank($pembukuanId, $transaksiId, $sppuId)
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
      
        $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array($pembukuanId));
        $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array($pembukuanId));
        
        //transaksi pengeluaran bank
        //hapus data finansi_pa_transaksi_bank_detil
        $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi_bank_detail_transaksi'], array(
           $transaksiId
        ));
        
        if($sppuId != 0){ // update status transaksi pada finansi_pa_sppu
          $result &= $this->Execute($this->mSqlQueries['do_update_status_trans_sppu'], array(
              $sppuId
          ));
        }else{ // delete data dari finansi_pa_transaksi_bank, krn trans tdk pake sppu
          $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi_bank'], array(
              $transaksiId
          ));
        }

        //update data finansi_pa_sppu
        // $result &= $this->Execute($this->mSqlQueries['do_update_sppu'], array(
        //    $transaksiId
        // ));
        
        //hapus data transaksi_detail_pengeluaran_bank
        $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi_det_pengeluaran_bank'], array(
            $transaksiId
        ));        
        
        //end
        $result &= $this->Execute($this->mSqlQueries['do_delete_transaksi'], array(
            $transaksiId
        ));
        
        $queryLog[]    = sprintf($this->mSqlQueries['do_delete_transaksi_bank_detail_transaksi'], $transaksiId);
        $queryLog[]    = sprintf($this->mSqlQueries['do_delete_transaksi_bank'], $transaksiId);
        $queryLog[]    = sprintf($this->mSqlQueries['do_update_sppu'], $transaksiId);
        $queryLog[]    = sprintf($this->mSqlQueries['do_delete_transaksi_det_pengeluaran_bank'],$transaksiId);
        
        $queryLog[]    = sprintf($this->mSqlQueries['do_delete_pembukuan_detail'], $pembukuanId);
        $queryLog[]    = sprintf($this->mSqlQueries['do_delete_pembukuan_referensi'], $pembukuanId);

      // log query
      $result        &= $this->Execute($this->mSqlQueries['do_add_log'], array(
         $userId,
         $ipAddress,
         'Hapus Transaksi Pengeluaran Bank'
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