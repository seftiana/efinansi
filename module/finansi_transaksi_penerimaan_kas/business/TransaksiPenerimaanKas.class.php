<?php
/**
* ================= doc ====================
* FILENAME     : TransaksiPenerimaanKas.class.php
* @package     : TransaksiPenerimaanKas
* scope        : PUBLIC
* @Author      : Eko Susilo
* @Created     : 2015-05-18
* @Modified    : 2015-05-18
* @Analysts    : Dyah Fajar N
* @copyright   : Copyright (c) 2012 Gamatechno
* ================= doc ====================
*/


require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/generate_number/business/GenerateNumber.class.php';

class TransaksiPenerimaanKas extends Database
{
   # internal variables
   protected $mSqlFile;
   public $_POST;
   public $_GET;
   public $method;
   private $mNumber;
   protected $mUserId = NULL;
   protected $mKeterangan = 'Penerimaan Kas';
   protected $mUnitId = 1;
   protected $mTipeTransaksi = 1; // tipe transaksi = penerimaan
   protected $mJenisTransaksi = 9; // penerimaan kas
   
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
   # Constructor
   function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile   = 'module/finansi_transaksi_penerimaan_kas/business/finansi_transaksi_penerimaan_kas.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      $this->method     = strtolower($_SERVER['REQUEST_METHOD']);
      parent::__construct($connectionNumber);      
      $this->mNumber    = new GenerateNumber($connectionNumber);
      //$this->SetDebugOn();
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

   public function getTahunAnggaranYear(){
       $return = $this->Open($this->mSqlQueries['get_range_tahun_periode_anggaran'], array());
       
       if(empty($return)){
           $return[0]['tahun_awal'] = date('Y') - 5;
           $return[0]['tahun_akhir'] = date('Y') + 5;
       }
       
       return $return[0];
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

   public function Count()
   {
      $return     = $this->Open($this->mSqlQueries['count'], array());

      if($return){
         return (int)$return[0]['count'];
      }else{
         return 0;
      }
   }

   public function getData($offset, $limit, $param = array())
   {
       //$this->SetDebugOn();
      $return     = $this->Open($this->mSqlQueries['get_data_transaksi'], array(
         '%'.$param['kode'].'%',
         date('Y-m-d', strtotime($param['start_date'])),
         date('Y-m-d', strtotime($param['end_date'])),
         $offset,
         $limit
      ));

      return self::ChangeKeyName($return);
   }

   public function doInsertTransaksi($param = array())
   {
      //$this->SetDebugOn();
      $rst        = true;
      $user_id    = $this->getUserId();
      $this->StartTrans();
      if(!is_array($param)){
         $rst     &= false;
      }
      
      $rst        &= $this->Execute($this->mSqlQueries['set_number'], array());
      $rst        &= $this->Execute($this->mSqlQueries['do_set_number'], array(
         'CR',
         'CR'
      ));
      
       $nomorReferensi   = $this->mNumber->getTransReferenceCR(
         date('Y-m-d', strtotime( $param['tanggal']))
      );
      
      // $nomorReferensi = $param['bpkb'];
      $rst        &= $this->Execute($this->mSqlQueries['do_insert_transaksi_kas'], array(
         $nomorReferensi,
         $param['tanggal'],
         $param['coa_id_penyetor'],
         $param['nama_penyetor'],
         $param['rekening_penyetor'],
         $param['coa_id_penerima'],
         $param['nama_penerima'],
         $param['rekening_penerima'],
         // $param['keterangan'],
         $param['nominal'],
         $user_id
      ));
      
      $transaksi_kas_id  = $this->LastInsertId();
      $rst        &= $this->Execute($this->mSqlQueries['do_delete_transaksi_kas_detail_transaksi'], array(
         $transaksi_kas_id
      ));
      
      if(!empty($param['komponen'])){
         foreach ($param['komponen'] as $komponen) {
            //$komponen['id']      = NULL;
            $rst  &= $this->Execute($this->mSqlQueries['do_insert_transaksi_kas_detil'], array(
               $transaksi_kas_id,
               $komponen['id'],
               $komponen['nama'],
               $param['tanggal'],
               $komponen['nominal'],
               $user_id
            ));
         }
      }else{
         $rst     &= false;
      }
        
      //proses pembukuan
      // set
      $rst     &= $this->Execute($this->mSqlQueries['set_tahun_anggaran'], array());
      $rst     &= $this->Execute($this->mSqlQueries['set_tahun_pembukuan'], array());
      $rst     &= $this->Execute($this->mSqlQueries['do_set_tahun_anggaran'], array());
      $rst     &= $this->Execute($this->mSqlQueries['do_set_tahun_pembukuan'], array());
      // insert into tabel transaksi
      $rst     &= $this->Execute($this->mSqlQueries['do_insert_transaksi'], array(
         $this->mTipeTransaksi,
         $this->mJenisTransaksi,
         $this->mUnitId,
         $nomorReferensi,
         $user_id ,
         date('Y-m-d', strtotime($param['tanggal'])),
         date('Y-m-d', strtotime($param['tanggal'])),
         $this->mKeterangan,
         $param['nominal'],
         'admin',
         'Y' // status Jurnal
      ));      
      
      $transaksiId   = $this->LastInsertId();
      // insert transaksi detail 
      $rst        &= $this->Execute($this->mSqlQueries['do_insert_transaksi_det_penerimaan_kas'], array(
         $transaksiId,
         $transaksi_kas_id
      ));

      // proses jurnal
      $subAccount    = $this->getSubAccount();
      $subAkun       = $subAccount['sub_akun'];
      list($subacc_1,$subacc_2,$subacc_3,$subacc_4,$subacc_5,$subacc_6,$subacc_7) = explode('-', $subAkun);
      $rst   &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_referensi'], array(
               $transaksiId,
               $user_id,
               date('Y-m-d', strtotime($param['tanggal'])),
               $this->mKeterangan
      ));

      $pembukuanId   = $this->LastInsertId();
      //debet
      $rst        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $param['coa_id_penerima'],
               $param['nominal'],
               $this->mKeterangan,
               'D',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));
      //kredit
      $rst        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $param['coa_id_penyetor'],               
               $param['nominal'],
               $this->mKeterangan,
               'K',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));
           //ubah status sppu transaksi
     $rst     &= $this->Execute($this->mSqlQueries['do_update_sppu_status'], array(
            'Ya',
            $param['sppu_id']
      ));

     //update nomor CR di finansi_pa_sppu
     $rst     &= $this->Execute($this->mSqlQueries['update_nomor_cr'], array(
            $nomorReferensi,
            $param['sppu_id']
      ));
            
             
      $result        = $this->EndTrans($rst);

      return compact('result', 'transaksi_kas_id');
   }

   public function doUpdateTransaksi($param = array())
   {
      $rst        = true;
      $user_id    = $this->getUserId();
      $this->StartTrans();
      if(!is_array($param)){
         $rst     &= false;
      }
      $transaksi_id  = $param['id'];
      $rst           &= $this->Execute($this->mSqlQueries['do_update_transaksi_kas'], array(
         $param['bpkb'],
         $param['tanggal'],
         $param['coa_id_penyetor'],
         $param['nama_penyetor'],
         $param['rekening_penyetor'],
         $param['coa_id_penerima'],
         $param['nama_penerima'],
         $param['rekening_penerima'],
         // $param['keterangan'],
         $param['nominal'],
         $user_id,
         $transaksi_id
      ));

      $rst           &= $this->Execute(
         $this->mSqlQueries['do_delete_transaksi_kas_detail_transaksi'],
         array(
            $transaksi_id
         )
      );
      if(!empty($param['komponen'])){
         foreach ($param['komponen'] as $komponen) {
            //$komponen['id']      = NULL;
            $rst  &= $this->Execute($this->mSqlQueries['do_insert_transaksi_kas_detil'], array(
               $transaksi_id,
               $komponen['id'],
               $komponen['nama'],
               $param['tanggal'],
               $komponen['nominal'],
               $user_id
            ));
         }
      }else{
         $rst     &= false;
      }
        
      // update transaksi pembukuan
       $rst           &= $this->Execute($this->mSqlQueries['do_update_transaksi'], array(
         $param['bpkb'],
         $param['tanggal'],
         $param['tanggal'],
         $param['nominal'],
         $user_id,
         $transaksi_id
      ));
      //proses jurnal
        $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
          $transaksi_id
        ));          
       $rst     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array(
         $transaksi_id
       ));   
      $getTransaksiId = $this->Open($this->mSqlQueries['get_transaksi_id'], array(
         $transaksi_id
       ));   
        
      $subAccount    = $this->getSubAccount();
      $subAkun       = $subAccount['sub_akun'];
      list($subacc_1,$subacc_2,$subacc_3,$subacc_4,$subacc_5,$subacc_6,$subacc_7) = explode('-', $subAkun);
      $rst   &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_referensi'], array(
               $getTransaksiId[0]['transaksiId'],
               $user_id,
               date('Y-m-d', strtotime($param['tanggal'])),
               $this->mKeterangan
      ));

      $pembukuanId   = $this->LastInsertId();
      //debet
      $rst        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $param['coa_id_penerima'],
               $param['nominal'],
               $this->mKeterangan,
               'D',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));
      //kredit
      $rst        &= $this->Execute($this->mSqlQueries['do_insert_pembukuan_detail'], array(
               $pembukuanId,
               $param['coa_id_penyetor'],
               $param['nominal'],
               $this->mKeterangan,
               'K',
               $subacc_1,
               $subacc_2,
               $subacc_3,
               $subacc_4,
               $subacc_5,
               $subacc_6,
               $subacc_7
            ));
      //kredit    
      $rst     &= $this->Execute($this->mSqlQueries['do_update_sppu_status'], array(
            'Belum',
            $param['sppu_id_old']
      ));
      $rst     &= $this->Execute($this->mSqlQueries['do_update_sppu_status'], array(
            'Ya',
            $param['sppu_id']
      ));
               
      $result        = $this->EndTrans($rst);

      return compact('result', 'transaksi_id');
   }

   public function getSubAccount()
   {
      $return     = $this->Open($this->mSqlQueries['get_sub_account_default'], array());
      $result['patern']    = $return[0]['patern'];
      $result['regex']     = $return[0]['regex'];
      $result['sub_akun']  = $return[0]['default'];

      return $result;
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

   public function doDeleteTransaksi($id)
   {
       //$this->SetDebugOn();
      $result     = true;
      $this->StartTrans();
      if(!$id){
         $result  &= false;
      }

      //ubah status sppu transaksi
      $result     &= $this->Execute($this->mSqlQueries['do_update_sppu_status_transaksi_by_kas_id'], array(
            'Belum',
            $id
      ));

      // update set NULL nomor CR
      $result     &= $this->Execute($this->mSqlQueries['do_update_hapus_nomor_cr'], array(
            NULL,
            $id
      ));
            
      // hapus data transaksi pembukuan
       $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_detail'], array(
         $id
      ));     
      $result     &= $this->Execute($this->mSqlQueries['do_delete_pembukuan_referensi'], array(
         $id
      ));      
      $result     &= $this->Execute($this->mSqlQueries['do_delete_transaksi'], array(
         $id
      ));

      //
      $result     &= $this->Execute($this->mSqlQueries['do_delete_transaksi_kas_detail_transaksi'], array(
         $id
      ));
      $result     &= $this->Execute($this->mSqlQueries['do_delete_transaksi_kas'], array(
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