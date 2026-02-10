<?php

class AppTransaksi extends Database {

   protected $mSqlFile;
   public $_POST;
   public $_GET;
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

   function __construct($connectionNumber=0) {
      $this->mSqlFile   = 'module/history_transaksi_realisasi_penerimaan/business/apptransaksi.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;
      parent::__construct($connectionNumber);
   }

   public function setDate()
   {
      $return     = $this->Open($this->mSqlQueries['get_date_range'], array());

      return self::ChangeKeyName($return[0]);
   }

   public function getTransaksiDetail($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_data_detail'], array(
         $id
      ));

      return self::ChangeKeyName($return[0]);
   }

   public function getInvoiceTransaksi($id)
   {
      $return     = $this->Open($this->mSqlQueries['get_invoice_transaksi'], array(
         $id
      ));

      return self::ChangeKeyName($return);
   }

   function CekTransaksi($kkb) {
      $result = $this->Open($this->mSqlQueries['cek_transaksi'], array($kkb));
      if($result[0]['total'] > 0) return false;
      else return true;
   }

   function GetComboJenisTransaksi() {
      $result = $this->Open($this->mSqlQueries['get_combo_jenis_transaksi'], array());
      return $result;
   }

   function GetComboTipeTransaksi() {
      $result = $this->Open($this->mSqlQueries['get_combo_tipe_transaksi'], array($_SESSION['username']));
      return $result;
   }

   function GetTransaksiById($id) {
      $result = $this->Open($this->mSqlQueries['get_transaksi_by_id'], array($id));
      return $result[0];
   }

   function GetTransaksiFile($transId) {
      $result = $this->Open($this->mSqlQueries['get_transaksi_file'], array($transId));
      return $result;
   }

   function GetTransaksiInvoice($transId) {
      $result = $this->Open($this->mSqlQueries['get_transaksi_invoice'], array($transId));
      return $result;
   }

   function GetTransaksiMAK($transId) {
      $result = $this->Open($this->mSqlQueries['get_transaksi_mak'], array($transId));
      if(empty($result))
         $result = $this->Open($this->mSqlQueries['get_transaksi_mak_untuk_pencairan'], array($transId));
      //echo sprintf($this->mSqlQueries['get_transaksi_mak'], $transId);
      return $result[0];
   }

   function DoAddTransaksi($arrData) {
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi'],
         array(
            $arrData['transTtId'],
            $arrData['transTransjenId'],
            $arrData['transUnitkerjaId'],
            $arrData['transReferensi'],
            $arrData['transUserId'],
            $arrData['transTanggal'],
            $arrData['transDueDate'],
            $arrData['transCatatan'],
            $arrData['transNilai'],
            $arrData['transPenanggungJawabNama'],
            $arrData['transIsJurnal'])
         );
      /*$query = sprintf($this->mSqlQueries['do_add_transaksi'], $arrData['transTtId'],
            $arrData['transTransjenId'],
            $arrData['transUnitkerjaId'],
            $arrData['transReferensi'],
            $arrData['transUserId'],
            $arrData['transTanggal'],
            $arrData['transDueDate'],
            $arrData['transCatatan'],
            $arrData['transNilai'],
            $arrData['transPenanggungJawabNama'],
            $arrData['transIsJurnal']);
      */
      $insertId = $this->LastInsertId();
      //echo $this->getLastError();
      //$this->DoAddLog("Tambah Transaksi", $query);
      return $insertId;
   }

   function DoAddRealisasiPerencanaan($totalterima, $diskripsi, $renid, $bln0, $bln1, $bln2, $bln3, $bln4, $bln5, $bln6, $bln7, $bln8, $bln9, $bln10, $bln11, $transid){

      $result = $this->Execute($this->mSqlQueries['add_realisasi_penerimaan'], array($totalterima, $diskripsi, $renid, $bln0, $bln1, $bln2, $bln3, $bln4, $bln5, $bln6, $bln7, $bln8, $bln9, $bln10, $bln11, $transid));
      //file_put_contents('C:/test.txt', print_r($this->getLastError(),1));
      return $result;
   }

   function DoAddTransaksiDetilPengembalianAnggaran ($transId, $mak)
   {
      list($kegdetId, $pengrealId) = explode("|", $mak);
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pengembalian_anggaran'], array($transId, $kegdetId, $pengrealId));

      return $result;
   }

   /*function DoAddTransaksiDetilAnggaran($transId, $mak) {
      $arrMak = explode("|", $mak); #print_r($arrMak); exit;
      $kegdetId = $arrMak[0];
      $pengrealId = $arrMak[1];
      if(!empty($arrMak[1]))
         $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_anggaran'],             array($transId, $kegdetId, $pengrealId));
      else
         $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_anggaran_penerimaan'], array($transId, $mak));
      /*
      echo sprintf($this->mSqlQueries['do_add_transaksi_detil_anggaran'],
            $transId, $kegdetId, $pengrealId);*/
   //   return $result;
   //}

   //tambahan untuk insert transaksi_detail pencaian
   /*function DoAddTransaksiDetilPencairan($transId, $mak) {
      $arrMak = explode("|", $mak); #print_r($arrMak); exit;
      $kegdetId = $arrMak[0];
      $pengrealId = $arrMak[1];
      $result = $this->Execute($this->mSqlQueries['do_add_transaksi_detil_pencairan'],             array($transId, $kegdetId, $pengrealId));
      /*
      echo sprintf($this->mSqlQueries['do_add_transaksi_detil_anggaran'],
            $transId, $kegdetId, $pengrealId);*/
      //return $result;
   //}

   function DoAddTransaksiFile($transId, $arrNama, $path) {
      $arrInsert = array();
      for($i=0;$i<sizeof($arrNama);$i++) {
         $arrInsert[]= "('".$transId."', '".$arrNama[$i]."', '".$path."')";
      }
         $strInsert = implode(", ", $arrInsert);
         $sql = sprintf($this->mSqlQueries['do_add_transaksi_file'], $strInsert);
      $result = $this->Execute($sql, array());
         return $result;
   }

   function DoAddTransaksiInvoice($transId, $arrInvoice) {
      for($i=0;$i<sizeof($arrInvoice);$i++) {
         $arrInsert[]= "('".$transId."', '".$arrInvoice[$i]."')";
      }
      $strInsert = implode(", ", $arrInsert);
      $sql = sprintf($this->mSqlQueries['do_add_transaksi_invoice'], $strInsert);
      $result = $this->Execute($sql, array());
      return $result;
   }

   function DoAddPembukuan($transId, $userId) {
      $result = $this->Execute($this->mSqlQueries['do_add_pembukuan'], array($transId, $userId));
      //echo sprintf($this->mSqlQueries['do_add_pembukuan'], $transId, $userId);
      return $this->LastInsertId();
   }

   function DoAddPembukuanDetil($idPembukuan, $nilai, $arrSkenarioId=array()) {

      $strSkenarioId = implode("', '", $arrSkenarioId);
      $result_debet = $this->Execute($this->mSqlQueries['do_add_pembukuan_detil_debet'], array($idPembukuan, $nilai, $strSkenarioId));
      $result_kredit = $this->Execute($this->mSqlQueries['do_add_pembukuan_detil_kredit'], array($idPembukuan, $nilai, $strSkenarioId));

      return $result_debet;
   }
//MULAI EDIT DATA


   function CekTransaksiUpdate($kkb, $transId) {
      $result = $this->Open($this->mSqlQueries['cek_transaksi_update'], array($kkb, $transId));
      if($result[0]['total'] > 0) return true;
      else return false;
   }

   function DoUpdateTransaksi($arrData) {
      //echo sprintf($this->mSqlQueries['do_update_transaksi'], $arrData['transTtId'],  $arrData['transTransjenId'],  $arrData['transUnitkerjaId'],  $arrData['transReferensi'],  $arrData['transUserId'], $arrData['transTanggal'], $arrData['transDueDate'], $arrData['transCatatan'], $arrData['transNilai'], $arrData['transPenanggungJawabNama'], $arrData['transIsJurnal'],$arrData['transId']);
      $result = $this->Execute($this->mSqlQueries['do_update_transaksi'],
         array(
            $arrData['transTtId'],
            $arrData['transTransjenId'],
            $arrData['transUnitkerjaId'],
            $arrData['transReferensi'],
            $arrData['transUserId'],
            $arrData['transTanggal'],
            $arrData['transDueDate'],
            $arrData['transCatatan'],
            $arrData['transNilai'],
            $arrData['transPenanggungJawabNama'],
            $arrData['transIsJurnal'],
            $arrData['transId'])
         );
      /*$query = sprintf($this->mSqlQueries['do_update_transaksi'],
            $arrData['transTtId'],
            $arrData['transTransjenId'],
            $arrData['transUnitkerjaId'],
            $arrData['transReferensi'],
            $arrData['transUserId'],
            $arrData['transTanggal'],
            $arrData['transDueDate'],
            $arrData['transCatatan'],
            $arrData['transNilai'],
            $arrData['transPenanggungJawabNama'],
            $arrData['transIsJurnal'],
            $arrData['transId']);
      //echo $query;
      $this->DoAddLog("Update Transaksi", $query);*/
      return $result;
   }

   function DoUpdateRealisasiPerencanaan(
      $totalterima, $diskripsi, $renid, $bln0, $bln1, $bln2, $bln3, $bln4, $bln5, $bln6, $bln7, $bln8, $bln9, $bln10, $bln11, $transid){

      $result = $this->Execute($this->mSqlQueries['update_realisasi_penerimaan'], array($totalterima, $diskripsi, $renid, $bln0, $bln1, $bln2, $bln3, $bln4, $bln5, $bln6, $bln7, $bln8, $bln9, $bln10, $bln11, $transid));
      //file_put_contents('C:/test.txt', print_r($this->getLastError(),1));
      return $result;
   }
   /*function DoUpdateTransaksiDetilAnggaran($transId, $mak) {
      $arrMak = explode("|", $mak);
      $kegdetId = $arrMak[0];
      $pengrealId = $arrMak[1];
      $result = $this->Execute(
         $this->mSqlQueries['do_update_transaksi_detil_anggaran'],
            array($transId, $kegdetId, $pengrealId)
         );
      //echo sprintf($this->mSqlQueries['do_update_transaksi_detil_anggaran'], $transId, $kegdetId, $pengrealId);
      return $result;
   }

   function DoDeleteTransaksiDetilAnggaran($makId) {
      $result = $this->Execute(
         $this->mSqlQueries['do_delete_transaksi_detil_anggaran'],
            array($makId)
         );
      //echo sprintf($this->mSqlQueries['do_delete_transaksi_detil_anggaran'], $makId);
      return $result;
   }*/

   function DoDeleteTransaksiInvoice($arrDataId) {
      $dataId = implode("', '", $arrDataId);
      $result = $this->Execute($this->mSqlQueries['do_delete_transaksi_invoice'], array($dataId));
      //echo sprintf($this->mSqlQueries['do_delete_transaksi_invoice'], $dataId);
      return $result;
   }

   function DoDeleteTransaksiFile($arrDataId) {
      $dataId = implode("', '", $arrDataId);
      $result = $this->Execute($this->mSqlQueries['do_delete_transaksi_file'], array($dataId));
      //echo sprintf($this->mSqlQueries['do_delete_transaksi_file'], $dataId);
      return $result;
   }
//SELESAI EDIT DATA


   function DoDeleteDataByArrayId($arrDataId) {
      $dataId = implode("', '", $arrDataId);
      $result = $this->Execute($this->mSqlQueries['do_delete_data_by_array_id'], array($dataId));
      return $result;
   }

   function DoDeleteDataById($dataId) {
      $result = $this->Execute($this->mSqlQueries['do_delete_data_by_id'], array($dataId));
      //file_put_contents('C:/test.txt', print_r($result,1);
      return $result;
   }

//MULAI CETAK DATA
//FORM CETAK
   function GetDataFormCetak($dataId) {
      $result = $this->Open($this->mSqlQueries['get_data_form_cetak'], array($dataId));
      //$result = $this->Open($this->mSqlQueries['get_transaksi_by_id'], array($dataId));
      //echo sprintf($this->mSqlQueries['get_transaksi_mak'], $transId);
      return $result[0];
   }

   function GetComboTahunAnggaran() {
      $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array());
      return $result;
   }

   function GetComboTahunAnggaranAktif() {
      $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran_aktif'], array());
      return $result[0];
   }
   //LOGGER LOGGER LOGGER

   /*function DoAddLog($keterangan, $query) {
      $userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
      $ip = $_SERVER['REMOTE_ADDR'];
      $result = $this->Execute($this->mSqlQueries['do_add_log'], array($userId, $ip, $keterangan));
      $this->DoAddLogDetil($this->LastInsertId(), $query);
      return $result;
   }*/

   /*function DoAddLogDetil($id, $query) {
      $result = $this->Execute($this->mSqlQueries['do_add_log_detil'], array($id, addslashes($query)));
      return $result;
   }*/

   #tambahan untuk update status transaksi pencairan anggaran (pengajuan_realisaisi)
   function DoUpdateStatusTransaksiDiPengajuanRealisasi($peng_real_id) {
      $result = $this->Execute(
         $this->mSqlQueries['update_status_transaksi_di_pengajuan_realisasi'],
            array($peng_real_id)
         );
      return $result;
   }

   #tambahan untuk cetak bukti transaksi
   function GetJabatanNama($key) {
      $result = $this->Open($this->mSqlQueries['get_jabatan'], array('%'.$key.'%'));
      return $result;
   }
   /**
    * tambahan
    * @since 18 November 2013
    */
   function GetPerjabatRef() {
      $result = $this->Open($this->mSqlQueries['get_pejabat_ref'], array());
      return $result;
   }

   function GetPejabatArray() {

     $data = array();
      $result = $this->Open($this->mSqlQueries['get_pejabat_array'], array());

      if(!empty($result) && is_array($result)){
         foreach($result as $key => $v){
            $data[$v['id_pejabat']]['nama'] = $v['nama_pejabat'];
            $data[$v['id_pejabat']]['jabatan'] = $v['nama_jabatan'];
            $data[$v['id_pejabat']]['nip'] = $v['nip_pejabat'];
         }
     }
      return $data;
   }

   function GetJabatan($jab) {
      $result = $this->Open($this->mSqlQueries['get_nama_pejabat'], array($jab));
      return $result[0]['nama'];
   }

   #tambahan untuk otomasi count bukti transaksi
   /**
    * tidak digunakan diganti dengan teknik auto generate number
    *
   function CountBuktiTrans($tipe_trans, $bulan, $unit_kerja) {
      switch($tipe_trans){
         case "1":
         case "5":
            $kode = "BKM";
            break;
         case "2":
         case "4":
            $kode = "BKK";
            break;
         case "3":
            $kode = "BM";
            break;
         default:
            $kode = "Bxx";
            break;
      }

      $result = $this->Open($this->mSqlQueries['count_bukti'], array("$kode%", $bulan, $unit_kerja));
      if (empty($result)) return array('count_trans'=>1);
      for ($i = count($result) - 1; $i >= 0; $i--) $tmp[] = $result[$i]['transReferensi'];
      natsort($tmp); end($tmp);
      return array('count_trans'=>preg_replace('/^[a-z]+(\d+)[^\d].*$/i', '\1', current($tmp)) + 1);
   }
   */

   /**
    * fungsi GetLastInsertTransId
    * untuk mendapatkan trasaksi id yang baru saja di simpan
    * @since 16 Januari 2012
    * @access public
    * @return number
    */
   function GetLastInsertTransId()
   {
         $result = $this->Open($this->mSqlQueries['get_last_insert_trans_id'],array());
         return $result[0]['lastTransId'];
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