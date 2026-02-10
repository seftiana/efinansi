<?php
#doc
# package:     ViewAddSppd
# scope:       PUBLIC
# @created     ucil.619
# @Author      Eko Susilo
# @Created     2012-09-05
# @Modified    2012-09-05
# @Analysts    Nanang Ruswianto
# @copyright   Copyright (c) 2012 Gamatechno
#/doc
require_once GTFWConfiguration::GetValue('application','docroot').
'module/transaksi_sp2d/business/Sppd.class.php';

class ViewAddSppd extends HtmlResponse
{
   function TemplateModule(){
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
      'module/transaksi_sp2d/template/');
      $this->SetTemplateFile('view_add_sppd.html');
   }
   
   function ProcessRequest(){
      $objSppd             = new Sppd();
      $dataId              = Dispatcher::Instance()->Decrypt($_GET['trans_id']);
      $dataSppd            = $objSppd->GetTransaksiByTransId($dataId);
      
      $satkerNomor         = GTFWConfiguration::GetValue('organization','satker_no');
      $ssnNomor            = GTFWConfiguration::GetValue('organization','nss');
      $return['sppdNomor'] = $objSppd->GenerateNomor($ssnNomor, $satkerNomor , $dataSppd['unitkerjaKode']);
      
      $return['dataSppd']  = $dataSppd;
      return $return;
   }
   
   function ParseTemplate($data = null){
      $url_action    = Dispatcher::Instance()->GetUrl(
         'transaksi_sp2d',
         'CetakSppd',
         'do',
         'json'
      );
      
      $this->mrTemplate->AddVar('content', 'URL_ACTION', $url_action);
      $dataSppd      = $data['dataSppd'];
      $dataSppd['nominal']    = number_format($dataSppd['transNilai'], 0, ',','.');
      foreach($dataSppd as $key => $val):
         $this->mrTemplate->AddVar('content', strtoupper($key), $val);
      endforeach;
      $this->mrTemplate->AddVar('content', 'TANGGAL', date('Y-m-d', time()));
      $this->mrTemplate->AddVar('content', 'KEPADA', 'Bendahara Pengeluaran '. GTFWConfiguration::GetValue('organization', 'company_name'));
      $satker_nomor     = GTFWConfiguration::GetValue('organization','satker_no');
      $satker_nama      = GTFWConfiguration::GetValue('organization', 'company_name');
      $this->mrTemplate->AddVar('content', 'SATKER_NO', $satker_nomor);
      $this->mrTemplate->AddVar('content', 'SATKER_NAMA', $satker_nama);
      $this->mrTemplate->AddVar('content', 'NOMOR_SP2D', $data['sppdNomor']);
   }
}
?>
