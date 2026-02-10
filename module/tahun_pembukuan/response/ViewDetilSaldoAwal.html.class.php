<?php
/**
* @package : ViewDetilSaldoAwal
* @copyright : Copyright (c) PT Gamatechno Indonesia
* @Analyzed By : Dyan Galih
* @author : Didi Zuliansyah
* @version : 01
* @startDate : 2013-01-01
* @lastUpdate : 2013-01-01
* @description : Class Untuk melihat view detil saldo awal
*/

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/TahunPembukuan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/TahunPembukuanPeriode.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/user_unit_kerja/business/UserUnitKerja.class.php';

class ViewDetilSaldoAwal extends HtmlResponse {
   private $Post;
   private $Pesan;
   private $css;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue( 'application', 'docroot').'module/'.Dispatcher::Instance()->mModule.'/template');
      $this->SetTemplateFile('view_detil_saldo_awal.html');
   }

   function ProcessRequest() {
      # get message
      $msg = Messenger::Instance()->Receive(__FILE__);
         $this->Post = $msg[0][0];
         $this->Pesan = $msg[0][1];
         $this->css = $msg[0][2];

         $objThnPembukuan = new TahunPembukuan();
         $objThnPeriode = new TahunPembukuanPeriode();
         $coaId = Dispatcher::Instance()->Decrypt($_GET['coaid']);


         $idUnitKerja = $_POST['unitKerja'];
         if(!isset($_POST['unitKerja'])){
            $user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
            $obj = new UserUnitKerja();
            $arrUnitKerja = $obj->GetUnitKerjaUser($user_id);
            $idUnitKerja = $arrUnitKerja['unit_kerja_id'];
         }

         $return['coaId'] = $coaId;
         $return['data'] = $objThnPembukuan->GetBalancePembukuanSubAccCoa($coaId);
         $return['header'] = $objThnPembukuan->GetBalancePembukuanCoa($coaId);

         $return['tahun_aktif'] = $objThnPeriode->GetTahunPembukuanPeriodeAktif();
         $return['count_tpp'] = $objThnPeriode->GetCountTahunPembukuan();

      return $return;
   }

   function ParseTemplate($data = NULL) {
       # Show Pesan
       if($this->Pesan) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $this->Pesan);
         $this->mrTemplate->AddVar('warning_box', 'CLASS_PESAN', $this->css);
       }

       # cek tahun pembukuan periode
       if (empty($data['tahun_aktif'])){
         $this->SaldoAkhirIsUpdate = ($data['count_tpp'] > 0) ? 'Tidak' : 'Ya';
       }elseif ($data['tahun_aktif'][0]['tppTanggalAkhir'] < date('Y-m-d')){
         $this->SaldoAkhirIsUpdate = 'Tidak';
       }else{ $this->SaldoAkhirIsUpdate = 'Tidak'; }

       # Header
       $this->mrTemplate->AddVar('content', 'HEADER_KODE_AKUN', $data['header'][0]['coaKodeAkun']);
       $this->mrTemplate->AddVar('content', 'HEADER_NAMA_AKUN', $data['header'][0]['coaNamaAkun']);

       # init URL
       if ($this->SaldoAkhirIsUpdate == 'Ya'){
          $urlUpdate = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'UpdateSaldoAwal', 'view', 'html');
          $label = "Saldo Awal Tahun Pembukuan";
          $urlDelete = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'DeleteSaldoAwal', 'do', 'html');
          $urlReturn = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'DetilSaldoAwal', 'view', 'html').'&coaid='.$data['coaId'];
          Messenger::Instance()->Send('confirm', 'confirmDelete', 'do', 'html', array($label, $urlDelete, $urlReturn),Messenger::NextRequest);
          $this->mrTemplate->AddVar('content', 'URL_DELETE', Dispatcher::Instance()->GetUrl('confirm', 'confirmDelete', 'do', 'html'));
          $this->mrTemplate->AddVar('content', 'URL_TAMBAH', $urlUpdate.'&coaid='.$data['coaId']);
       }else{
         $this->mrTemplate->AddVar('content', 'DELETE_VIEW', 'none');
         $this->mrTemplate->AddVar('content', 'TAMBAH_VIEW', 'none');
       }
       $this->mrTemplate->AddVar('content', 'URL_BACK', Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'TahunPembukuan', 'view', 'html'));

       if (empty($data['data'])) {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'YES');
       } else {
         $this->mrTemplate->AddVar('data', 'DATA_EMPTY', 'NO');
         $arrSubAcc = $data['data'];
         for ($i=0; $i<sizeof($arrSubAcc); $i++) {
            $no = $i+$data['start'];
            $arrSubAcc[$i]['number'] = $no;
            if ($no % 2 != 0) {
                    $dataKodeJurnal[$i]['class_name'] = 'table-common-even';
                } else {
                    $dataKodeJurnal[$i]['class_name'] = '';
                }
                if ($i == 0) {
                    $this->mrTemplate->AddVar('content', 'FIRST_NUMBER', $no);
                }
                if($i == sizeof($dataKodeJurnal) - 1) {
                    $this->mrTemplate->AddVar('content', 'LAST_NUMBER', $no);
                }
                $idEnc = Dispatcher::Instance()->Encrypt($arrSubAcc[$i]['tpId']);
            //$arrSubAcc[$i]['url_edit'] = $urlUpdate.'&dataId='.$idEnc;

            $arrSubAcc[$i]['id'] = $arrSubAcc[$i]['tpId'];
            $arrSubAcc[$i]['kode_akun'] = $arrSubAcc[$i]['coaKodeAkun'];
            $arrSubAcc[$i]['tipe_akun'] = $arrSubAcc[$i]['ctrNamaTipe'];
            $arrSubAcc[$i]['debet'] = number_format($arrSubAcc[$i]['debet'],2,',','.');
            $arrSubAcc[$i]['kredit'] = number_format($arrSubAcc[$i]['kredit'],2,',','.');
            if($arrSubAcc[$i]['tpSaldoAwal'] >= 0) {
                $arrSubAcc[$i]['saldo_awal'] = number_format($arrSubAcc[$i]['tpSaldoAwal'],2,',','.');
            } else {
                $arrSubAcc[$i]['saldo_awal'] = '('.number_format($arrSubAcc[$i]['tpSaldoAwal'] * (-1),2,',','.').')';
            }
            if($arrSubAcc[$i]['tpSaldoAkhir'] >= 0) {
                $arrSubAcc[$i]['saldo_akhir'] = number_format($arrSubAcc[$i]['tpSaldoAkhir'],2,',','.');
            } else {
                $arrSubAcc[$i]['saldo_akhir'] = '('. number_format($arrSubAcc[$i]['tpSaldoAkhir'] * (-1),2,',','.') .')';
            }
            $arrSubAcc[$i]['saldo_normal'] = ($arrSubAcc[$i]['coaIsDebetPositif']=='1') ? 'Debet' : 'Kredit';

            # sembunyikan edit button dan checkbox
            if ($this->SaldoAkhirIsUpdate == 'Tidak'){
               $this->mrTemplate->AddVar('data_act', 'IS_EDIT', 'NO');
               $this->mrTemplate->AddVar('not_ubah', 'NOT_UBAH','-');
            }
            else{
               $this->mrTemplate->AddVar('data_act', 'IS_EDIT', 'YES');
               $this->mrTemplate->AddVar('data_act', 'DATA_ID', $arrSubAcc[$i]['id']);
               $this->mrTemplate->AddVar('data_act', 'DATA_NUMBER', $arrSubAcc[$i]['number']);
               $this->mrTemplate->AddVar('data_act', 'DATA_COAID', $arrSubAcc[$i]['coaId']);
               $this->mrTemplate->AddVar('data_act', 'DATA_SUBACC', $arrSubAcc[$i]['subacc']);
               if(!empty($arrSubAcc[$i]['subacc'])){
                   $subAccDel = str_replace('-', '.', $arrSubAcc[$i]['subacc']);
               }
               $this->mrTemplate->AddVar('data_act', 'DATA_SUBACC_DEL', $subAccDel);
               $this->mrTemplate->AddVar('aksi_ubah', 'URL_UBAH', $urlUpdate.'&id='.$idEnc);
            }

            $this->mrTemplate->AddVars('data_item', $arrSubAcc[$i], 'DATA_');
            $this->mrTemplate->parseTemplate('data_item', 'a');
         }
       }
   }
}
?>