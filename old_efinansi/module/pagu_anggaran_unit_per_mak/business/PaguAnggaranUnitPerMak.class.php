<?php
class PaguAnggaranUnitPerMak extends Database
{
   protected $mSqlFile;
   public $_POST;
   public $_GET;

   function __construct($connectionNumber=0)
   {
      $this->mSqlFile   = 'module/pagu_anggaran_unit_per_mak/business/paguanggaranunitpermak.sql.php';
      $this->_POST      = is_object($_POST) ? $_POST->AsArray() : $_POST;
      $this->_GET       = is_object($_GET) ? $_GET->AsArray() : $_GET;

      parent::__construct($connectionNumber);
   }

   function GetDataPaguAnggaranUnit(
      $offset,
      $limit,
      $tahun_anggaran='',
      $satker='',
      $unitkerja=''
   ) {
      $result           = $this->Open($this->mSqlQueries['get_data_pagu'], array(
         $tahun_anggaran,
         (int)($tahun_anggaran == '' OR $tahun_anggaran == null),
         $satker,
         $satker,
         (int)($satker == '' OR $satker == null),
         $offset,
         $limit
      ));

      return $result;
   }

   function GetCountDataPaguAnggaranUnit($tahun_anggaran='', $satker='', $unitkerja='') {
      $return        = $this->Open($this->mSqlQueries['get_count_data_pagu'], array(
         $tahun_anggaran,
         (int)($tahun_anggaran == '' OR $tahun_anggaran == null),
         $satker,
         $satker,
         (int)($satker == '' OR $satker == null)
      ));

      if($return){
         return $return[0]['total'];
      }else{
         return 0;
      }
   }

   function GetDataPaguAnggaranUnitById($id) {
      $ret = $this->Open($this->mSqlQueries['get_data_pagu_by_id'], array($id));
     return $ret[0];
   }

   function DoAddPaguAnggaranUnit($tahun_anggaran, $unitkerja, $nominal,$sumber_dana, $makId, $programId, $kegiatanId, $outputId, $subkegId, $status) {
        if(empty($sumber_dana)){
            $sumber_dana    = NULL;
        }else{
            $sumber_dana    = $sumber_dana;
        }
      $result    = $this->Execute(
         $this->mSqlQueries['do_add_pagu'],
         array(
            $unitkerja,
            $tahun_anggaran,
            $nominal,
            $sumber_dana,
            $makId,
            $programId,
            $kegiatanId,
            $outputId,
            $subkegId,
            $status
         )
      );
      if($result){
         return true;
      }else{
         return $this->GetLastError();
      }
   }

   function DoUpdatePaguAnggaranUnit($tahun_anggaran, $unitkerja, $makId, $nominal, $sumber_dana,$programId, $kegiatanId, $outputId, $subkegId,$status, $pagu_id) {
       if(empty($sumber_dana)){
            $sumber_dana    = NULL;
        }else{
            $sumber_dana    = $sumber_dana;
        }
      $result = $this->Execute($this->mSqlQueries['do_update_pagu'], array($unitkerja, $tahun_anggaran, $makId, $nominal,$sumber_dana,$programId, $kegiatanId, $outputId, $subkegId, $status, $pagu_id));
      //$debug = sprintf($this->mSqlQueries['do_update_usulan_kegiatan'], $unitkerja, $program, $latar_belakang, $tahun_anggaran, $kegiatan_id);
      //echo $debug;
      if($result){
         return true;
      }else{
         return $this->GetLastError();
      }
   }

   function DoCopyPaguAnggaranUnitNaik($tahun_anggaran_tujuan, $nilai_perubahan, $tahun_anggaran_asal, $unitkerja) {
      $result     = $this->Execute($this->mSqlQueries['do_copy_pagu_naik'], array(
         $tahun_anggaran_tujuan,
         $nilai_perubahan,
         $tahun_anggaran_asal,
         $unitkerja
      ));
      //$debug = sprintf($this->mSqlQueries['do_copy_pagu_naik'], $tahun_anggaran_tujuan, $nilai_perubahan,$tahun_anggaran_asal, $unitkerja);
      //echo $debug;exit;
      return $result;
   }

   function DoCopyPaguAnggaranUnitTurun($tahun_anggaran_tujuan, $nilai_perubahan, $tahun_anggaran_asal, $unitkerja) {
      $result = $this->Execute($this->mSqlQueries['do_copy_pagu_turun'], array($tahun_anggaran_tujuan, $nilai_perubahan,$tahun_anggaran_asal, $unitkerja));
      return $result;
   }

   //get combo tahun anggaran
   function GetComboTahunAnggaran($isAktif = FALSE, $isOpen = FALSE) {
      $result = $this->Open($this->mSqlQueries['get_combo_tahun_anggaran'], array(
         (int)($isAktif === FALSE),
         (int)($isOpen === FALSE)
      ));
      return $result;
   }

   function GetComboBas() {
      $result = $this->Open($this->mSqlQueries['get_combo_bas'], array());
      return $result;
   }

   function GetTahunAnggaranAktif() {
      $result = $this->Open($this->mSqlQueries['get_tahun_anggaran_aktif'], array());
      return $result[0];
   }

   function DoDeletePaguAnggaranUnitById($paguId) {
      $result  = true;
      $this->StartTrans();
      $result  &= $this->Execute($this->mSqlQueries['delete_history_pagu'], array(
         $paguId
      ));
      $result  &=  $this->Execute($this->mSqlQueries['do_delete_pagu_by_id'], array(
         $paguId
      ));
      return $this->EndTrans($result);

   }

   function DoDeletePaguAnggaranUnitByArrayId($arrPaguId) {
      $arrPaguId = @implode("', '", $arrPaguId);
      $result=$this->Execute($this->mSqlQueries['do_delete_pagu_by_array_id'], array($arrPaguId));
      return $result;
   }

   // check availabelity
   function CheckAvailabelity($ta,$program,$kegiatan,$output,$sub,$unit_id)
   {
       $return = $this->Open(
           $this->mSqlQueries['check_availabelity'],
           array(
               $ta,
               $program,
               $kegiatan,
               $output,
               $sub,
               $unit_id
           )
       );

       return $return[0]['count'];
   }

   function Count(){
      $return     = $this->Open($this->mSqlQueries['count'], array());

      return $return[0]['total'];
   }

   function CheckNominalPagu($unitId, $paguId = ''){

      $result        = $this->Open($this->mSqlQueries['check_pagu_anggaran'], array(
         $unitId,
         $paguId,
         (int)($paguId == '' OR $paguId == null),
         $unitId
      ));
      if($result){
         #print_r($return);
         return $result[0]['nominal'];
      }else{
         return 0;
      }
   }


   function CountMakData($unit, $tahun_anggaran, $makId, $program, $kegiatan, $output, $sub){
      $return   = $this->Open($this->mSqlQueries['cek_mak'], array($unit, $tahun_anggaran, $makId, $program, $kegiatan, $output, $sub));

      return $return[0]['total_data'];
   }

   // get data program
   function GetProgram(){
      $return = $this->Open($this->mSqlQueries['get_program_kegiatan'], array());

      return $return;
   }

   // get nominal TUP
   function GetNominalTup(){
      $return     = $this->Open($this->mSqlQueries['get_nominal_tup'], array());
      return $return[0]['sp2dNominal'];

   }

   // get MAK TUP
   function GetMakTup(){
      $return     = $this->Open($this->mSqlQueries['get_mak_tup'], array());
      return $return[0]['makId'];
   }

   // get data pagu anggaran unit per mak

   public function GetData($offset, $limit, $param = array())
   {
      $unitId        = $param['unit_id'];
      $ta            = $param['ta'];
      $programId     = $param['programId'];
      $program       = $param['program'];
      $kegiatanId    = $param['kegiatanId'];
      $kegiatan      = $param['kegiatan'];
      $outputId      = $param['outputId'];
      $output        = $param['output'];
      $komponenId    = $param['komponenId'];
      $komponen      = $param['komponen'];
      $makId         = $param['makId'];
      $userId        = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $return        = $this->Open($this->mSqlQueries['get_data'], array(
         $ta,
         (int)($ta == '' OR $ta == null OR strtolower($ta) == 'all'),
         $programId,
         (int)($programId == '' OR $programId == null OR strtolower($programId) == 'all' OR strtolower($programId) == 'semua'),
         $kegiatanId,
         (int)($kegiatanId == '' OR $kegiatanId == null OR strtolower($kegiatanId) == 'all' OR strtolower($kegiatanId) == 'semua'),
         $outputId,
         (int)($outputId == '' OR $outputId == null OR strtolower($outputId) == 'all' OR strtolower($outputId) == 'semua'),
         $komponenId,
         (int)($komponenId == '' OR $komponenId == null OR strtolower($komponenId) == 'all' OR strtolower($komponenId) == 'semua'),
         $makId,
         (int)($makId == '' OR $makId == null OR strtolower($makId) == 'all' OR strtolower($makId) == 'semua'),
         $userId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $unitId,
         $offset,
         $limit
      ));

      return $return;
   }

   /**
    * @param Array $data, post data form
    * @return Boolean $return, true if data not exist false if data exits
    */
   public function CheckAvailabelityPaguAnggaran($data = array())
   {
      $paguId           = $data['id'];
      $tahunAnggaran    = $data['tahun_anggaran'];
      $unitKerja        = $data['unitkerja'];
      $programId        = $data['program_id'];
      $kegiatanId       = $data['kegiatan_id'];
      $outputId         = $data['output_id'];
      $komponenId       = $data['komponen_id'];
      $makId            = $data['mak_id'];

      $return           = $this->Open($this->mSqlQueries['check_availabelity_pagu'], array(
         $paguId,
         (int)($paguId == ''),
         $tahunAnggaran,
         (int)($tahunAnggaran == ''),
         $unitKerja,
         (int)($unitKerja == ''),
         $programId,
         (int)($programId == ''),
         $kegiatanId,
         (int)($kegiatanId == ''),
         $outputId,
         (int)($outputId == ''),
         $komponenId,
         (int)($komponenId == ''),
         $makId,
         (int)($makId == '')
      ));

      if(!$return){
         return false;
      }else{
         if($return[0]['count'] <> 0){
            return false;
         }else{
            return true;
         }
      }
   }

   public function GetDataTahunAnggaran()
   {
      $return        = $this->Open($this->mSqlQueries['get_data_tahun_anggaran'], array());

      return $return;
   }

   /**
    * @Description method untuk pengecekan pagu anggaran unit/Batas Pagu anggaran per unit
    * @param Array $data, parameter yang di gunakan untuk melakukan Pengecekan
    * @param Int data.unitkerja Id unit kerja
    * @param Int data.id Id Pagu anggaran unit per mak, tidak berpengaruh untuk ID yang di maksud
    * @param Int data.id Untuk pengecekan nominal dana pagu ketika edit pagu
    * @return Array, Data array
    * */
   public function CheckPaguAnggaranUnit($data = array())
   {
      $return        = $this->Open($this->mSqlQueries['check_pagu_anggaran_unit'], array(
         $data['id'],
         (int)($data['id'] == '' OR $data['id'] === NULL),
         $data['unitkerja'],
         (int)($data['unitkerja'] == '' OR $data['unitkerja'] === NULL),
         $data['tahun_anggaran'],
         (int)($data['tahun_anggaran'] == '' OR $data['tahun_anggaran'] === NULL)
      ));

      return $return[0];
   }

   /**
    * @Description GET list all data pagu anggaran unit per mak
    * @return Array data pagu anggaran unit per mak
    * */
   public function GetListPaguAnggaran($params = array())
   {
      $return     = $this->Open($this->mSqlQueries['get_list_pagu_anggaran'], array(
         $params['unitId'],
         (int)($params['unitId'] === NULL OR $params['unitId'] == ''),
         $params['srcTaId'],
         (int)($params['srcTaId'] === NULL OR $params['srcTaId'] == '')
      ));

      return (array)$return;
   }
   /**
    * Array $params, parameter untuk mendapatkan data nilai pagu anggaran unit per mak
    * Array $data, data pagu anggaran setelah ada perubahan, berupa data detail
    * */
   public function DoCopyPaguAnggaranUnitPerMak($params = array(), $data = array())
   {
      $getListPaguAnggaran       = $this->GetListPaguAnggaran($params);
      $dataList        = array();
      $result          = true;
      $this->StartTrans();
      $index            = 0;
      foreach ($getListPaguAnggaran as $pagu) {
         if(!empty($data)){
            $pagu['nominal']     = $data[$pagu['id']]['nominal'];
         }else{
            $pagu['nominal']     = $pagu['nominal'];
         }
         $result                 = $this->Execute($this->mSqlQueries['delete_detail_on_copy'], array(
            $params['destTaId'],
            $pagu['unitId'],
            $pagu['programId'],
            $pagu['kegiatanId'],
            $pagu['outputId'],
            $pagu['komponenId'],
            $pagu['makId']
         ));

         if($this->CheckAvailabelityPaguAnggaran(array(
            'tahun_anggaran' => $params['destTaId'],
            'unitkerja' => $pagu['unitId'],
            'program_id' => $pagu['programId'],
            'kegiatan_id' => $pagu['kegiatanId'],
            'output_id' => $pagu['output_id'],
            'mak_id' => $pagu['makId']
         )) === true){
            $result        &= $this->Execute($this->mSqlQueries['do_add_pagu'], array(
               $pagu['unitId'],
               $params['destTaId'],
               $pagu['nominal'],
               $pagu['sumberDana'],
               $pagu['makId'],
               $pagu['programId'],
               $pagu['kegiatanId'],
               $pagu['outputId'],
               $pagu['komponenId'],
               'F'
            ));
         }else{
            $result        &= $this->Execute($this->mSqlQueries['update_pagu_on_copy'], array(
               $pagu['nominal'],
               $params['destTaId'],
               $pagu['unitId'],
               $pagu['programId'],
               $pagu['kegiatanId'],
               $pagu['outputId'],
               $pagu['komponenId'],
               $pagu['makId']
            ));
         }

         $index++;
      }

      return $this->EndTrans($result);
   }

   public function GetPeriodeTahun($pagu = true)
   {
      $return        = $this->Open($this->mSqlQueries['get_periode_tahun_pagu_anggaran'], array(
         (int)($pagu === true),
         (int)($pagu === false)
      ));

      return $return;
   }

   /**
    * @param Array $data, post data form
    * @return Boolean $return, true if data not exist false if data exits
    */
   public function CheckPaguService($data = array())
   {
      $paguId           = $data['id'];
      $tahunAnggaran    = $data['tahun_anggaran'];
      $unitKerja        = $data['unitkerja'];
      $programId        = $data['program_id'];
      $kegiatanId       = $data['kegiatan_id'];
      $outputId         = $data['output_id'];
      $komponenId       = $data['komponen_id'];
      $makId            = $data['mak_id'];

      $return           = $this->Open($this->mSqlQueries['check_availabelity_pagu'], array(
         $paguId,
         (int)($paguId == ''),
         $tahunAnggaran,
         (int)($tahunAnggaran == ''),
         $unitKerja,
         (int)($unitKerja == ''),
         $programId,
         (int)($programId == ''),
         $kegiatanId,
         (int)($kegiatanId == ''),
         $outputId,
         (int)($outputId == ''),
         $komponenId,
         (int)($komponenId == ''),
         $makId,
         (int)($makId == '')
      ));

      if(!$return){
         return false;
      }else{
         return $return[0];
      }
   }

   public function DoUpdatePaguUsulan($param = array())
   {
      $result     = true;
      $this->StartTrans();
      $result     &= $this->Execute($this->mSqlQueries['do_update_pagu_usulan'], array(
         $param['nominal'],
         $param['id']
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
      if(!is_array($input))
      {
         return $input;
      }

      foreach ($input as $key => $value) {
         if(is_array($value))
         {
            foreach ($value as $k => $v) {
               $array[$key][self::humanize($k, $case)] = $v;
            }
         }
         else
         {
            $array[self::humanize($key, $case)]  = $value;
         }
      }

      return (array)$array;
   }

   function kekata($x)
   {
      $x = abs($x);
      $angka = array(
         "",
         "satu",
         "dua",
         "tiga",
         "empat",
         "lima",
         "enam",
         "tujuh",
         "delapan",
         "sembilan",
         "sepuluh",
         "sebelas"
      );
      $temp = "";

      if ($x < 12)
      {
         $temp = " " . $angka[$x];
      }
      else
      if ($x < 20)
      {
         $temp = $this->kekata($x - 10) . " belas";
      }
      else
      if ($x < 100)
      {
         $temp = $this->kekata($x / 10) . " puluh" . $this->kekata($x % 10);
      }
      else
      if ($x < 200)
      {
         $temp = " seratus" . $this->kekata($x - 100);
      }
      else
      if ($x < 1000)
      {
         $temp = $this->kekata($x / 100) . " ratus" . $this->kekata($x % 100);
      }
      else
      if ($x < 2000)
      {
         $temp = " seribu" . $this->kekata($x - 1000);
      }
      else
      if ($x < 1000000)
      {
         $temp = $this->kekata($x / 1000) . " ribu" . $this->kekata($x % 1000);
      }
      else
      if ($x < 1000000000)
      {
         $temp = $this->kekata($x / 1000000) . " juta" . $this->kekata($x % 1000000);
      }
      else
      if ($x < 1000000000000)
      {
         $temp = $this->kekata($x / 1000000000) . " milyar" . $this->kekata(fmod($x, 1000000000));
      }
      else
      if ($x < 1000000000000000)
      {
         $temp = $this->kekata($x / 1000000000000) . " trilyun" . $this->kekata(fmod($x, 1000000000000));
      }

      return $temp;
   }

   function terbilang($x, $style = 4)
   {

      if ($x < 0)
      {
         $hasil = "minus " . trim($this->kekata($x));
      }
      else
      {
         $hasil = trim($this->kekata($x));
      }

      switch ($style)
      {
      case 1:
         $hasil = strtoupper($hasil);
      break;
      case 2:
         $hasil = strtolower($hasil);
      break;
      case 3:
         $hasil = ucwords($hasil);
      break;
      default:
         $hasil = ucfirst($hasil);
      break;
      }

      return $hasil . ' Rupiah';
   }

   /**
    * @description REST Service
    * @package GetReferensiUnitKerja
    * @param String $kode; kode yang di kirim untuk mendapatkan data unit kerja
    * @return Array $return OR NULL
    */
   public function GetReferensiUnitKerja($kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_referensi_unit_kerja'], array(
         $kode
      ));

      if($return){
         return (array)$return[0];
      }else{
         return NULL;
      }
   }

   /**
    * @description REST Service
    * @package GetReferensiTahunAnggaran
    * @param String $nama; Nama Tahun Anggaran yang di pakai
    * @return Array $return OR NULL
    */
   public function GetReferensiTahunAnggaran($nama)
   {
      $return     = $this->Open($this->mSqlQueries['get_referensi_tahun_anggaran'], array(
         $nama
      ));

      if($return){
         return (array)$return[0];
      }else{
         return NULL;
      }
   }

   /**
    * @description REST Service
    * @package GetReferensiProgram
    * @param String $kode; Kode Program
    * @return Array $return
    */
   public function GetReferensiProgram($kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_referensi_program'], array(
         $kode
      ));

      if($return){
         return (array)$return[0];
      }else{
         return null;
      }
   }

   /**
    * @description REST Service
    * @package GetReferensiOutput
    * @param String $outputKode; Kode Output
    * @param String $kegiatankode; Kode Kegiatan
    * @return Array $return
    */
   public function GetReferensiOutput($outputKode, $kegiatankode)
   {
      $return     = $this->Open($this->mSqlQueries['get_referensi_output'], array(
         $outputKode,
         $kegiatankode
      ));

      if($return){
         return (array)$return[0];
      }else{
         return NULL;
      }
   }

   /**
    * @description REST Service
    * @package GetReferensiKomponen
    * @param String $kode; Kode Komponen
    * @return Array $return
    */
   public function GetReferensiKomponen($kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_referensi_komponen'], array(
         $kode
      ));

      if($return){
         return (array)$return[0];
      }else{
         return null;
      }
   }

   /**
    * @description REST Service
    * @package GetReferensiMak
    * @param String $kode; Kode MAK
    * @return Array $return
    */
   public function GetReferensiMak($kode)
   {
      $return     = $this->Open($this->mSqlQueries['get_referensi_mak'], array(
         $kode
      ));

      if($return){
         return (array)$return[0];
      }else{
         return null;
      }
   }

   public function GetReferensiSumberDana($nama)
   {
      $return     = $this->Open($this->mSqlQueries['get_referensi_sumber_dana'], array(
         $nama
      ));

      if($return){
         return (array)$return[0];
      }else{
         return null;
      }
   }
}
?>