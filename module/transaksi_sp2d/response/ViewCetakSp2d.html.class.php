<?php
#doc
# package:     ViewCetakSp2d
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2012-09-06
# @Modified    2012-09-06
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc

require_once GTFWConfiguration::GetValue('application','docroot').
'module/transaksi_sp2d/business/Sppd.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot').
'main/function/terbilang.php';

class ViewCetakSp2d extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/transaksi_sp2d/template/');
      $this->SetTemplateFile('view_cetak_sp2d.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   
   function ProcessRequest(){
      $sppdObj    = new Sppd();
      $_GET       = $_GET->AsArray();
      $data_id    = $_GET['data_id'];
      $transId    = $_GET['transId'];
      $spmId      = $_GET['spmId'];
      
      $dataSppd   = $sppdObj->GetDataSp2dCetak($data_id);
      // echo 'Print';
      $return['dataSppd']     = $dataSppd;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $numberObj        = new Number();
      $timestamp        = date('Y/m/d H:i:s', time());
      $username         = Security::Instance()->mAuthentication->GetCurrentUser()->GetRealName();
      $satker_nomor     = GTFWConfiguration::GetValue('organization','satker_no');
      $satker_nama      = GTFWConfiguration::GetValue('organization', 'company_name');
      $nss_nomor        = GTFWConfiguration::GetValue('organization', 'nss');
      $lembaga          = GTFWConfiguration::GetValue('organization', 'kementerian_lembaga_nama');
      $eselon_nama      = GTFWConfiguration::GetValue('organization', 'unit_org_eselon_nama');
      $kota             = GTFWConfiguration::GetValue('organization', 'city');
      $nm_bendahara     = GTFWConfiguration::GetValue('organization', 'nama_bendahara_penerimaan_blu');
      $nip_bendahara    = GTFWConfiguration::GetValue('organization', 'nip_bendahara_penerimaan_blu');
      $jbtn_bendahara   = GTFWConfiguration::GetValue('organization', 'jabatan_bendahara_penerimaan_blu');
      $nm_penguasa      = GTFWConfiguration::GetValue('organization', 'nama_kuasa_pengguna_anggaran');
      $nip_penguasa     = GTFWConfiguration::GetValue('organization', 'nip_kuasa_pengguna_anggaran');
      $jbtn_penguasa    = GTFWConfiguration::GetValue('organization', 'jabatan_kuasa_pengguna_anggaran');
      
      $dataSppd         = $data['dataSppd'];
      
      $this->mrTemplate->AddVar('content', 'NOMOR_SATKER', $satker_nomor);
      $this->mrTemplate->AddVar('content', 'NAMA_SATKER', $satker_nama);
      $this->mrTemplate->AddVar('content', 'NSS_NOMOR', $nss_nomor);
      $this->mrTemplate->AddVar('content', 'LEMBAGA', $lembaga);
      $this->mrTemplate->AddVar('content', 'ESELON_NAMA', $eselon_nama);
      $this->mrTemplate->AddVar('content', 'KOTA', $kota);
      $this->mrTemplate->AddVar('content', 'NAMA_BENDAHARA', $nm_bendahara);
      $this->mrTemplate->AddVar('content', 'NIP_BENDAHARA', $nip_bendahara);
      $this->mrTemplate->AddVar('content', 'JABATAN_BENDAHARA', $jbtn_bendahara);
      $this->mrTemplate->AddVar('content', 'NAMA_KUASA_ANGGARAN', $nm_penguasa);
      $this->mrTemplate->AddVar('content', 'NIP_KUASA_ANGGARAN', $nip_penguasa);
      $this->mrTemplate->AddVar('content', 'JABATAN_KUASA_ANGGARAN', $jbtn_penguasa);
      $this->mrTemplate->AddVar('content', 'NILAI_SPPD', number_format($dataSppd['sppdNominal'], 0, ',','.'));
      $this->mrTemplate->AddVar('content', 'NILAI_TERBILANG', $numberObj->terbilang($dataSppd['sppdNominal'],1));
      $this->mrTemplate->AddVar('content', 'TIMESTAMP', $timestamp);
      $this->mrTemplate->AddVar('content', 'USERNAME', $username);
      foreach($dataSppd as $key => $val):
         $this->mrTemplate->AddVar('content', strtoupper($key), $val);
      endforeach;
   }
}
?>
