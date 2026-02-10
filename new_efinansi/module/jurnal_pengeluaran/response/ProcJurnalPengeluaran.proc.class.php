<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/jurnal_pengeluaran/business/JurnalPengeluaran.class.php';

class ProcJurnalPengeluaran {

   protected $msg;
   public $data;

   public $moduleName   = 'jurnal_pengeluaran';
   public $moduleHome   = 'jurnalPengeluaran';
   public $moduleInput  = 'inputJurnalPengeluaran';
   public $moduleAdd    = 'addJurnalPengeluaran';
   public $moduleUpdate = 'updateJurnalPengeluaran';
   public $moduleDelete = 'deleteJurnalPengeluaran';
   public $db;  //yang berhubungan ke database

   function __construct(){ //constructor
     $this->db      = new JurnalPengeluaran;
     $this->data    = $this->getPOST();
   }

   function getPOST() {
      $data          = false;

      if(isset($_POST['data'])) {
         if(is_object($_POST['data'])){
            $data    = $_POST['data']->AsArray();
         }else{
            $data    = $_POST['data'];
         }

         if(isset($data['debet']['tambah'])) {
            $i=0;
            foreach($data['debet']['tambah']['id'] as $key => $val) {
               $data['debet']['tambah'][$i]['id']           = $val;
               $data['debet']['tambah'][$i]['nama']         = $data['debet']['tambah']['nama'][$key];
               $data['debet']['tambah'][$i]['keterangan']   = $data['referensi_keterangan'];
               $data['debet']['tambah'][$i]['nilai']        = $data['debet']['tambah']['nilai'][$key];
               $data['debet']['tambah'][$i]['kode']         = $data['debet']['tambah']['kode'][$key];
               $data['debet']['tambah'][$i]['detail_id']    = $data['debet']['tambah']['detail_id'][$key];
               $i++;
            }
            unset($data['debet']['tambah']['id']);
            unset($data['debet']['tambah']['detail_id']);
            unset($data['debet']['tambah']['nama']);
            unset($data['debet']['tambah']['keterangan']);
            unset($data['debet']['tambah']['nilai']);
            unset($data['debet']['tambah']['kode']);
         }
         if(isset($data['debet']['tambah_edit'])) {
            $i=0;
            foreach($data['debet']['tambah_edit']['id'] as $key => $val) {
               $data['debet']['tambah_edit'][$i]['id']         = $val;
               $data['debet']['tambah_edit'][$i]['nama']       = $data['debet']['tambah_edit']['nama'][$key];
               $data['debet']['tambah_edit'][$i]['keterangan'] = $data['referensi_keterangan'];;
               $data['debet']['tambah_edit'][$i]['nilai']      = $data['debet']['tambah_edit']['nilai'][$key];
               $data['debet']['tambah_edit'][$i]['kode']       = $data['debet']['tambah_edit']['kode'][$key];
               $data['debet']['tambah_edit'][$i]['detail_id']  = $data['debet']['tambah_edit']['detail_id'][$key];
               $i++;
            }
            unset($data['debet']['tambah_edit']['id']);
            unset($data['debet']['tambah_edit']['detail_id']);
            unset($data['debet']['tambah_edit']['nama']);
            unset($data['debet']['tambah_edit']['keterangan']);
            unset($data['debet']['tambah_edit']['nilai']);
            unset($data['debet']['tambah_edit']['kode']);
         }
         /*
         if(isset($data['akun_list_delete'])) {
            $i=0;
            foreach($data['akun_list_delete']['id'] as $key => $val) {
               $data['akun_list_delete'][$i]['id']=$val;
               $data['akun_list_delete'][$i]['nama']=$data['akun_list_delete']['nama'][$key];
               $i++;
            }
            unset($data['akun_list_delete']['id']);
            unset($data['akun_list_delete']['nama']);
         }
         */
         $data['akun_list_delete'] = $data['akun_list_delete'];
      }

      return $data;
    }

    function Add() {
         if(isset($this->data['skenario']['id'])){
            $grp  = '&grp='.Dispatcher::Instance()->Encrypt($this->data['skenario']['id']);
         }else{
            $grp  = '';
         }
         if($this->validation('Penambahan')){
            $add=$this->db->DoAdd($this->data,$msg);
            if($add) {
               $this->msg     = 'Penambahan data berhasil dilakukan';
               $urlRedirect   = $this->generateUrl('msg',false,null,$grp);
            } else {
               $this->msg     = 'Penambahan data gagal dilakukan <br />';
               if(is_array($msg)){
                  $this->msg     .= $msg['kredit']['msg'].$msg['debet']['msg'];
               }else{
                  $this->msg     .= $msg;
               }
               $urlRedirect      = $this->generateUrl('err',false,null,$grp);
               //$ret = false;
            }
         } else {
            $urlRedirect      = $this->generateUrl('err',false,null,$grp);
         }
      return $urlRedirect;
   }

   function Delete () {
      if(isset($_POST['idDelete'])) {
         $grp     = Dispatcher::Instance()->Decrypt($_POST['idDelete']);
         $grp2    = Dispatcher::Instance()->Decrypt($_POST->AsArray());
         $update_status_jurnal_dulu    = $this->db->UpdateStatusJurnalSetelahDelete('T',$grp2['idDelete']);
         $del     = $this->db->DoDelete($grp);
         if($del) {
            $this->msg     ='Penghapusan data berhasil dilakukan';
            $urlRedirect   = $this->generateUrl('msg',false,$this->moduleHome);
         } else {
            #jika gagal delete, kembalikan status seperti semula
            $update_status_awal  = $this->db->UpdateStatusJurnalSetelahDelete('Y',$grp2['idDelete']);
            $this->msg           = 'Penghapusan data gagal dilakukan';
            $urlRedirect         = $this->generateUrl('err',false,$this->moduleHome);
         }
      } else {
         $this->msg     = 'Penghapusan data gagal dilakukan';
         $urlRedirect   = $this->generateUrl('err',false,$this->moduleHome);
      }
      return $urlRedirect;
   }

   function Update() {
      if($this->validation('Perubahan')) {
         $this->data['tambah']['id']= Dispatcher::Instance()->Decrypt($this->data['tambah']['id']);
         $update=$this->db->DoUpdate($this->data,$msg);
         if($update) {
            $this->msg     = 'Perubahan data berhasil dilakukan';
            $urlRedirect   = $this->generateUrl('msg');
         } else {
           $this->msg      = 'Perubahan data gagal dilakukan silahkan ulangi lagi';
            $urlRedirect   = $this->generateUrl('err');
         }
      } else {
         $urlRedirect      = $this->generateUrl('err');
      }

      return $urlRedirect;
   }

   function validation($action) {
      $this->msg='';
      if (empty($this->data['kredit']['coa_id'])) {
         $this->msg .= "Rekening kredit belum dipilih.<br/>\r\n";
      }
      if(!isset($_POST['data'])) { //kalo gak ada data yang di POST apa yang mau di  validasi
         $this->msg  = $action.' data gagal dilakukan ';
         return false;
      }
      //debug($this->data);
      if(trim($this->data['referensi_id'])==''){
         $this->msg  .= 'Data referensi transaksi tidak boleh kosong <br />';
      }

      $totalnilai    = 0;
      $errdebet      = false;
      if(isset($this->data['debet']['tambah'])) {
         foreach($this->data['debet']['tambah'] as $key => $val) {
            if(trim($val['nilai'])=='') {
               $this->msg     .= 'Nilai Debet Akun <b>'.$val['nama'].'</b>  tidak boleh Kosong <br />';
               $errordebet    =true;
            } elseif(!is_numeric($val['nilai'])) {
               $this->msg     .= 'Nilai Debet Akun <b>'.$val['nama'].'</b>  harus berupa angka <br />';
               $errordebet    = true;
            }
            $totalnilai += $val['nilai'];
         }
      }

      if(isset($this->data['debet']['tambah_edit'])) {
         foreach($this->data['debet']['tambah_edit'] as $key => $val) {
            if(trim($val['nilai'])=='') {
               $this->msg     .= 'Nilai Debet Akun <b>'.$val['nama'].'</b>  tidak boleh Kosong <br />';
               $errordebet    = true;
            } elseif(!is_numeric($val['nilai'])) {
               $this->msg     .= 'Nilai Debet Akun <b>'.$val['nama'].'</b>  harus berupa angka <br />';
               $errordebet    = true;
            }
            $totalnilai += $val['nilai'];
           }
         }
         if(!$this->data['debet']['tambah'] && !$this->data['debet']['tambah_edit']) {
            $this->msg     .= 'Anda belum memilih debet <br />';
            $errordebet    = true;
         }
         if(!$errordebet){
            if($totalnilai != $this->data['kredit']['nilai'] ){
              $this->msg .= 'Kredit('.$this->data['kredit']['nilai'].') tidak sama dengan debet ('.$totalnilai.')<br />';
            }
         }
      //}

      if($this->msg==''){
         return true;
      }else{
         return false;
      }
   }




   function generateUrl($type,$isHome=false,$url = null , $additional=null){
      if(!is_null($url)){
        $submodule   = $url;
      }elseif($type=='msg' || $isHome ) {
         $submodule  = $this->moduleHome;
      }else {
         $submodule  = $this->moduleInput;
      }

      Messenger::Instance()->Send(
         $this->moduleName,
         $submodule,
         'view',
         'html',
         array(
            $this->data,
            $type,
            $this->msg
         ),Messenger::NextRequest);
      $urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html');
      if(!is_null($additional)){
         $urlRedirect .= $additional;
      }
      return $urlRedirect;
   }

   function parsingUrl($file) {
      $msg = Messenger::Instance()->Receive($file);

      if(!empty($msg)) {
         $tmp['data']=$msg[0][0];
         $tmp['msg']['action']=$msg[0][1];
         $tmp['msg']['message']=$msg[0][2];
         return $tmp;
      } else {
        return false;
      }
   }

   function BalikJurnal() {
      $_GET          = $_GET->AsArray();
      $jurnal_balik  = $this->db->BalikJurnal($_GET['grp']);
      #print_r($jurnal_balik); exit;
      if($jurnal_balik) {
         $this->msg     = 'Proses jurnal balik berhasil dilakukan';
         $urlRedirect   = $this->generateUrl('msg');
      } else {
         $this->msg     = 'Proses jurnal balik gagal dilakukan';
         $urlRedirect   = $this->generateUrl('msg');
      }
      #print_r($tes); exit;
      return $urlRedirect;
   }
}
?>