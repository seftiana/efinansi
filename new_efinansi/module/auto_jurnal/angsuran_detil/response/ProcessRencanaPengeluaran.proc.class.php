<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/angsuran_detil/business/RencanaPengeluaran.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') .
'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ProcessRencanaPengeluaran
{
   protected $msg;
   protected $data         = array();
   protected $komp;
   protected $moduleName   = 'angsuran_detil';
   protected $inputModule;
   protected $validateSbu  = 1; // option: 0,1
   protected $setValidasi  = 0;
   public $RencanaPengeluaran;
   public $UserUnitKerja;
   private $method;
   private $queryString    = '';
   private $queryReturn    = '';
   function __construct()
   {
      $this->RencanaPengeluaran  = new RencanaPengeluaran;
      $this->UserUnitKerja       = new UserUnitKerja;
      $this->method              = $_SERVER['REQUEST_METHOD'];
      $this->queryString         = $this->RencanaPengeluaran->_getQueryString();
	  $this->queryReturn      		 = (!empty( $this->queryString)) ? '&search=1&'. $this->queryString : '';
   }

   public function SetData()
   {
      $requestData      = array();
      if(strtoupper($this->method) === 'POST'){
         $requestData['kegiatan']['id']         = $this->RencanaPengeluaran->_POST['data']['kegiatan']['kegiatandetail_id'];
         $requestData['kegiatan']['subkeg_id']  = $this->RencanaPengeluaran->_POST['data']['kegiatan']['subkegiatan_id'];
         $requestData['kegiatan']['jenis']      = $this->RencanaPengeluaran->_POST['data']['kegiatan']['jenis_kegiatan'];
         $requestData['kegiatan']['unit_id']    = $this->RencanaPengeluaran->_POST['data']['kegiatan']['unit_kerja'];
         $requestData['kegiatan']['ta_id']      = $this->RencanaPengeluaran->_POST['data']['kegiatan']['ta_id'];
         $requestData['kegiatan']['ta_nama']    = $this->RencanaPengeluaran->_POST['data']['kegiatan']['tahun_anggaran'];
         $requestData['kegiatan']['action']     = strtoupper($this->RencanaPengeluaran->_POST['data']['kegiatan']['action']);
         $requestData['nominal_total']          = 0;
         $requestData['komponen']               = array();
         $index            = 0;
         $dataKomponenBas  = array();
         if(isset($this->RencanaPengeluaran->_POST['data']['komponen'])){
            $dataBas          = array();
            $bas              = '';
            foreach ($this->RencanaPengeluaran->_POST['data']['komponen'] as $komponen) {
               if(!isset($komponen['data_id'])){
                  continue;
               }
               $requestData['komponen'][$komponen['id']]    = $komponen;
               $requestData['komponen'][$komponen['id']]['nominal_total']  = ($komponen['jumlah'] * $komponen['biaya'] * $komponen['hasil_formula']);
               $requestData['nominal_total']                += ($komponen['jumlah'] * $komponen['biaya'] * $komponen['hasil_formula']);
               $dataKomponenBas[$index]                  = $komponen;
               $dataKomponenBas[$index]['nominal_total'] = ($komponen['jumlah'] * $komponen['biaya'] * $komponen['hasil_formula']);
            }
            unset($index);
            $index            = '';
            /*for ($i=0; $i < count($dataKomponenBas);) {
               echo $i.'<br />';
               print_r($dataKomponenBas[$i]);
               $i++;
            }*/
         }
      }

      $this->data       = (array)$requestData;
   }

   public function Validate()
   {
      $mData      = $this->data;
      if(empty($mData)){
         $err[]   = 'Tidak ada data '.GTFWConfiguration::GetValue('language', 'manajemen_angsuran_detil');
      }
      

      if(!empty($mData['komponen'])){
         foreach ($mData['komponen'] as $komp) {
            if((float)$komp['jumlah'] <= 0){
               $err[]   = '<strong>'.$komp['kode'].'</strong> : Isikan '.GTFWConfiguration::GetValue('language', 'volume').' dengan nilai yang sesuai';
            }
            //if((float)$komp['biaya'] <= 0){
            //   $err[]   = '<strong>'.$komp['kode'].'</strong> : Isikan nominal '.GTFWConfiguration::GetValue('language', 'komponen'). ' dengan nilai yang sesuai';
            //}
            /*if((int)$this->validateSbu === 1 && (float)$komp['biaya_max'] < (float)$komp['biaya']){
               $err[]   = '<strong>'.$komp['kode'].'</strong> : Nilai satuan '. GTFWConfiguration::GetValue('language', 'komponen') . ' melebihi batas maksimal. Silakan periksa batas maksimal nilai satuan Detail Belanja tersebut di detil komponen anggaran. Batas nilai maksiman Rp. '.number_format($komp['biaya_max'],0,',','.');
            }*/

            if((int)$komp['is_sbu'] === 1 && (float)$komp['biaya_max'] < (float)$komp['biaya']){
               $err[]   = '<strong>'.$komp['kode'].'</strong> : Nilai satuan '. GTFWConfiguration::GetValue('language', 'komponen') . ' melebihi batas maksimal. Silakan periksa batas maksimal nilai satuan Detail Belanja tersebut di detil komponen anggaran. Batas nilai maksiman Rp. '.number_format($komp['biaya_max'],0,',','.');
            }
         }
      }
      $tipeUnit   = $this->RencanaPengeluaran->GetTipeUnitKerja($mData['kegiatan']['unit_id']);
      $totalPengeluaran          = $this->RencanaPengeluaran->GetNominalTotalPengeluaran(
         $mData['kegiatan']['ta_id'],
         $mData['kegiatan']['unit_id'],
         $mData['kegiatan']['id']
      );
      $totalPengeluaranRkat      = $totalPengeluaran+$mData['nominal_total'];
      $totalRencanaPenerimaan    = $this->RencanaPengeluaran->GetNominalRencanaPenerimaan(
         $mData['kegiatan']['ta_id'],
         $mData['kegiatan']['unit_id']
      );

      // reset setvalidasi jika tipe unit = 1
      if($tipeUnit == 1){
         $this->setValidasi      = 0;
      }

      switch ((int)$this->setValidasi) {
         case 0:
            # abaikan
            # loloskan pengecekan nominal
            break;
         case 1:
            /**
             * Proses pengecekan rencana pengeluaran berdasarkan
             * Rencana penerimaan
             */
            if($totalRencanaPenerimaan < $totalPengeluaranRkat){
               $err[]   = 'Rencana pengeluaran melebihi Rencana Penerimaan <strong>Rp. '.number_format($totalRencanaPenerimaan, 0, ',','.').'</strong> yang telah dialokasikan dan disetujui. '.'Sesuaikan kuantitas komponen';
            }
            break;
         case 2:
            /**
             * Proses pengecekan rencana pengeluaran berdasarkan
             * Pagu BAS
             */
            $err[]      = 'Proses pengecekan rencana pengeluaran berdasarkan PAGU BAS';
            break;
         default:
            # abaikan
            # loloskan pengecekan nominal
            break;
      }
      if(isset($err)){
         $return['result']       = false;
         $return['message']      = implode('<br />', $err);
      }else{
         $return['result']       = true;
         $return['message']      = null;
      }

      return (array)$return;
   }

   public function Add()
   {
      $this->SetData();
      $jenisKegiatan       = strtoupper($this->data['kegiatan']['jenis']);
      if($jenisKegiatan == 'RUTIN'){
         return $this->AddRutin();
      }elseif($jenisKegiatan == 'NONRUTIN'){
         return $this->AddNonRutin();
      }
   }

   function AddRutin()
   {
      $validate      = $this->Validate();
 	  $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'AngsuranDetil',
               'view',
               'html'
            ).'&'.$this->queryReturn;	  
      if($validate['result'] === false){
         $message    = $validate['message'];
         $style      = 'notebox-warning';
         Messenger::Instance()->Send(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html',
               array(
                  $this->RencanaPengeluaran->_POST,
                  $message,
                  $style
               ),
               Messenger::NextRequest
            );

            $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html'
            ).'&'.$this->queryString;
      }else{
         $process    = $this->RencanaPengeluaran->DoAddRencanaPengeluaranRutin($this->data);
         if($process === true){
            $message    = 'Proses penambahan '.GTFWConfiguration::GetValue('language', 'manajemen_angsuran_detil').' Berhasil';
            $style      = 'notebox-done';
            Messenger::Instance()->Send(
               'angsuran_detil',
               'AngsuranDetil',
               'view',
               'html',
               array(
                  NULL,
                  $message,
                  $style
               ),
               Messenger::NextRequest
            );

            $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'AngsuranDetil',
               'view',
               'html'
            ).'&'.$this->queryReturn;
         }else{
            $message    = 'Proses penambahan '.GTFWConfiguration::GetValue('language', 'manajemen_angsuran_detil').' Gagal';
            $style      = 'notebox-warning';
            Messenger::Instance()->Send(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html',
               array(
                  $this->RencanaPengeluaran->_POST,
                  $message,
                  $style
               ),
               Messenger::NextRequest
            );

            $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html'
            ).'&'.$this->queryString;
         }
      }

      return $urlRedirect;
   }

   function AddNonRutin()
   {
      $urlRedirect      = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'AngsuranDetil',
         'view',
         'html'
      ).'&'.$this->queryString;

      return $urlRedirect;
   }

   function Delete()
   {
      $this->inputModule   = 'inputRencanaPengeluaranNonRutin';

      if (isset($_POST['idDelete'])){
         $salt    = Dispatcher::Instance()->Decrypt($_POST['idDelete']);
         $salt    = explode('*', $salt);
         $grp     = $salt[0];
         $this->data['kegiatan']['kegiatandetail_id']    = $salt[1];
         $this->data['kegiatan']['subkegiatan_id']       = $salt[2];
         $this->data['kegiatan']['subkegiatan_nama']     = $salt[3];
         $del = $this->RencanaPengeluaran->DoDelete($grp);
         if ($del){
            $this->msg     = 'Penghapusan data berhasil dilakukan';
            $urlRedirect   = $this->generateUrl('msg');
         }else{
            $this->msg     = 'Penghapusan data gagal dilakukan';
            $urlRedirect   = $this->generateUrl('err');
         }
      }else{
         $this->msg        = 'Penghapusan data gagal dilakukan';
         $urlRedirect      = $this->generateUrl('err');
      }

      return $urlRedirect;
   }

   function Update()
   {
      $this->SetData();
      $jenisKegiatan       = strtoupper($this->data['kegiatan']['jenis']);
      if($jenisKegiatan == 'RUTIN'){
         return $this->UpdateRutin();
      }elseif($jenisKegiatan == 'NONRUTIN'){
         return $this->UpdateNonRutin();
      }
   }

   function UpdateRutin()
   {
      $validate      = $this->Validate();
      
      $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'AngsuranDetil',
               'view',
               'html'
            ).'&'.$this->queryReturn;	  
      
      if($validate['result'] === false){
         $message    = $validate['message'];
         $style      = 'notebox-warning';
         Messenger::Instance()->Send(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html',
               array(
                  $this->RencanaPengeluaran->_POST,
                  $message,
                  $style
               ),
               Messenger::NextRequest
            );

            $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html'
            ).'&'.$this->queryString;
      }else{
         $process    = $this->RencanaPengeluaran->DoUpdateRencanaPengeluaranRutin($this->data);
         if($process === true){
            $message    = 'Proses update '.GTFWConfiguration::GetValue('language', 'manajemen_angsuran_detil').' Berhasil';
            $style      = 'notebox-done';
            Messenger::Instance()->Send(
               'angsuran_detil',
               'AngsuranDetil',
               'view',
               'html',
               array(
                  NULL,
                  $message,
                  $style
               ),
               Messenger::NextRequest
            );

            $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'AngsuranDetil',
               'view',
               'html'
            ).'&'.$this->queryReturn;
         }else{
            $message    = 'Proses update '.GTFWConfiguration::GetValue('language', 'manajemen_angsuran_detil').' Gagal';
            $style      = 'notebox-warning';
            Messenger::Instance()->Send(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html',
               array(
                  $this->RencanaPengeluaran->_POST,
                  $message,
                  $style
               ),
               Messenger::NextRequest
            );

            $urlRedirect      = Dispatcher::Instance()->GetUrl(
               'angsuran_detil',
               'inputRencanaPengeluaranRutin2',
               'view',
               'html'
            ).'&'.$this->queryString;
         }
      }

      return $urlRedirect;
   }

   function UpdateNonRutin()
   {
      $urlRedirect      = Dispatcher::Instance()->GetUrl(
         'angsuran_detil',
         'AngsuranDetil',
         'view',
         'html'
      ).'&'.$this->queryString;

      return $urlRedirect;
   }

   function validationNonRutin($action)
   {
      $this->msg     = '';
      if (!isset($_POST['data'])){
         //kalo gak ada data yang di POST apa yang mau di  validasi
         $this->msg  = $action . ' data gagal dilakukan ';
         return false;
      }

      /*
      if(trim($this->data['tambah']['kode'])=='')
      $this->msg .= 'Kode Detail Belanja Tidak Boleh Kosong <br />';
      */

      if (trim($this->data['tambah']['nama']) == ''){
         $this->msg  .= 'Nama Detail Belanja Tidak Boleh Kosong <br />';
      }

      if (trim($this->data['tambah']['satuan']) == '') {
         $this->msg  .= 'Satuan Detail Belanja Tidak Boleh Kosong <br />';
      }

      if (trim($this->data['tambah']['jumlah']) == '') {
         $this->msg  .= 'Jumlah Tidak Boleh Kosong <br />';
      }elseif (!is_numeric($this->data['tambah']['jumlah'])) {
         $this->msg  .= 'Jumlah harus berupa angka <br />';
      }

      if (trim($this->data['tambah']['biaya']) == '') {
         $this->msg  .= 'Nominal Tidak Boleh Kosong <br />';
      }elseif (!is_numeric($this->data['tambah']['biaya'])) {
         $this->msg  .= 'Nominal harus berupa angka <br />';
      }

      if ($this->msg == ''){
         return true;
      }else{
         return false;
      }
   }

   /**
    * method Validation()
    * method ini menggantikan method yang lama
    * untuk memvalidasi input rencana pengeluaran rutin
    * add
    * @since 9 Maret 2012
    */
   function validation($action)
   {
      $this->msg = '';
      if (!isset($_POST['data'])){
         //kalo gak ada data yang di POST apa yang mau di  validasi
         $this->msg = $action . ' data gagal dilakukan ';
         return false;
      }

      if (!isset($this->data['ceklis']) && $action =='Penambahan'){
         $this->msg  .= 'Centang untuk memilih komponen yang dipilih dan akan disimpan <br />';
      }
      //else biar bisa hapus detail belanja
      if (isset($this->data['ceklis'])){
         //print_r($this->data['komponen']);
         /**
          * mengaktifkan validasi sbu sbk
          * nilai : 0 disable
          * nilai : 1 enable
          */
         $set_validasi_sbu_sbk = 1;
         /**
          * end
          */
         foreach($this->data['ceklis'] as $key => $val)
         {
            //meyakinkan bahwa data yang di inputkan bertipe integer sesuai dengan tipe di tabel
            $this->data['komponen'][$key]['jumlah'] = intval($this->data['komponen'][$key]['jumlah']);


            $this->data['komponen'][$key]['total'] = $this->data['komponen'][$key]['jumlah'] *
                                           $this->data['komponen'][$key]['biaya'] *
                                           ($this->data['komponen'][$key]['hasil_formula'] < 1 ?
                                           '1' : $this->data['komponen'][$key]['hasil_formula']);
            if (trim($this->data['komponen'][$key]['jumlah']) == '') {
                  $this->msg.= 'Detail Belanja dengan kode ' . $this->data['komponen'][$key]['kode'] .
                  ' jumlah volume tidak boleh kosong <br />';
            }elseif ($this->data['komponen'][$key]['jumlah'] == 0) {
                  $this->msg.= 'Detail Belanja dengan kode ' . $this->data['komponen'][$key]['kode'] .
                  ' jumlah volume tidak boleh nol <br />';
            }elseif (!is_numeric($this->data['komponen'][$key]['jumlah'])) {
                  $this->msg.= 'Detail Belanja dengan kode ' . $this->data['komponen'][$key]['kode'] .
                  ' harus berupa angka <br />';
            }
            elseif (!empty($this->data['komponen'][$key]['biaya_max']) &&
               (($this->data['komponen'][$key]['biaya']) > ($this->data['komponen'][$key]['biaya_max'])) &&
                ($this->data['komponen'][$key]['is_sbu'] == 1 ) && ($set_validasi_sbu_sbk == 1)
                ) {
            $this->msg.= 'Nilai satuan Detail Belanja dengan kode ' . $this->data['komponen'][$key]['kode'] .
               ' melebihi batas maksimal. <br />Silakan periksa batas maksimal nilai satuan '.
               'Detail Belanja tersebut di detil komponen anggaran. Batas nilai maksiman Rp. '.number_format($this->data['komponen'][$key]['biaya_max'],0,',','.');
           }

         }
         $tahun_anggaran = $this->data['kegiatan']['tahun_anggaran'];
         $unit           = $this->data['kegiatan']['unit_kerja'];
         $kegiatanId    = $this->data['kegiatan']['kegiatandetail_id'];
         /**
          * untuk mendapatkan nilai status validasi
          * nilai 0 : berarti tidak tervalidasi / lolos
          * nilai 1 : berarti validasi berdasarkan rencana penerimaan yang sudah approved
          * nilai 2 : berarti validasi berdasarkan Pag, Bas
          */

             /**
              * cek tipe unit; jika unit tipe 1 maka loloskan
              */

              $tipe = $this->RencanaPengeluaran->GetTipeUnitKerja($unit);
              if($tipe == 1){
                $set_validasi = 0;
              } else {
                $set_validasi = GTFWConfiguration::GetValue('application', 'validasi_renkeluar');
              }

             /**
              * end
              */

         if($set_validasi == 1){
         /**
          * untuk proses validai rencana pengeluaran berdasarkan rencana pengeluaran yang sudah di approve
          */
            $nominal_komponen = 0;
            //untuk mendapatkan total nominal komponen yang sedang di edit
            $t_komponen_edit = $this->RencanaPengeluaran->GetTotalPengeluaranKomponenEdit($kegiatanId);

            //untuk mendapatkan total nominal rencana pengeluaran yang approved
            $max_rp_approved = $this->RencanaPengeluaran->GetMaxRencanaPenerimaanApproved($unit,$tahun_anggaran);

            //untuk mendapatkan total nominal pengeluaran
            $max_pengeluaran = $this->RencanaPengeluaran->GetMaxRencanaPengeluaran($unit,$tahun_anggaran);

            foreach($this->data['ceklis'] as $key => $val)
            {
               $nominal_komponen += $this->data['komponen'][$key]['total'];
            }

            $total_pengeluaran = ($max_pengeluaran - $t_komponen_edit) + $nominal_komponen;
            if ($total_pengeluaran > $max_rp_approved){
               $this->msg .= 'Rencana pengeluaran melebihi Rencana Penerimaan yang telah '.'dialokasikan dan disetujui. '.'Sesuaikan kuantitas komponen. <br />';
            }
         /**
          * end proses validasi
          */
          } elseif($set_validasi == 2 ){
         /**
          * untuk proses validasi rencana pengeluaran berdasarkan Pagu , Bas
          */

            $bas_komponen   = $this->RencanaPengeluaran->GetBasPengeluaran($kegiatanId);
            for ($i=0; $i<sizeof($bas_komponen);$i++) {
               $pagu       = $this->RencanaPengeluaran->GetPagu(
                  $bas_komponen[$i]['bas_id'],
                  $unit,
                  $tahun_anggaran
               );
               $posisiPagu          = $this->RencanaPengeluaran->GetPosisiPagu(
                  $bas_komponen[$i]['bas_id'],
                  $tahun_anggaran,
                  $unit
               );
               $pagu_sekarang[$i]   = $posisiPagu[0]['nominal_rencana'];

               $posisiPengeluaran   = $this->RencanaPengeluaran->GetJumlahPengeluaranPerBas($kegiatanId);
               $pengeluaran_sekarang[$i]  = $posisiPengeluaran[$i]['jumlah_sekarang'];

               $nominal_komponen = 0;
               foreach($this->data['ceklis'] as $key => $val)
               {
                  $nominal_komponen   = $nominal_komponen + $this->data['komponen'][$key]['total'];
                  $komponen_bas       = $this->data['komponen'][$key]['bas_id'];
                  if ($bas_komponen[$i]['bas_id'] == $komponen_bas){
                     $pagu_akhir = ($pagu_sekarang[$i] - $pengeluaran_sekarang[$i]) + $nominal_komponen;
                     if (($pagu[0]['nominal'] !== 0) AND ($pagu_akhir > $pagu[0]['nominal'])) $lebih = true;
                  }
               }

            }
            if ($lebih){
               $this->msg .= 'Rencana pengeluaran melebihi pagu  ' .
                     ucwords(strtolower($pagu[0]['bas_nama'])) . '. Sesuaikan kuantitas komponen. <br />';
            }
          /**
           * end proses validasi
         */
         } else {}

      }

      if ($this->msg == '')
      {
          return true;
      }
      else
      {
          return false;
      }
   }
   /**
    * end method validation
    */

   public function DeleteFile($type='html')
   {
      $id         = Dispatcher::Instance()->Decrypt($_GET['id']);
      $result     = $this->RencanaPengeluaran->DeleteFile($id);
      if($result){
         $this->msg     = 'Penghapusan File Sukses';
         $urlRedirect   = $this->generateUrl('msg');
      }else{
         $this->msg     = 'Penghapusan File Gagal';
         $urlRedirect   = $this->generateUrl('err');
      }

      if($type=='html'){
         return $urlRedirect;
      }else{
         return $result;
      }
   }
}
?>