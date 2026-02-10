<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/komponen/business/Komponen.class.php';

class ViewInputKomponen extends HtmlResponse {
   var $Pesan;
   var $id;
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') .
         'module/komponen/template');
      $this->SetTemplateFile('input_komponen.html');
   }

   function ProcessRequest() {
      $idDec            = Dispatcher::Instance()->Decrypt($_REQUEST['dataId']);
      $nama             = Dispatcher::Instance()->Decrypt($_REQUEST['nama']);
      if(isset($_REQUEST['page'])){
         $page       = (string) $_REQUEST['page']->StripHtmlTags()->SqlString()->Raw();
      }
      
      $page = ($page =='') ? '1' : $page;
      
      $ObjKomponen      = new Komponen();
      $msg              = Messenger::Instance()->Receive(__FILE__);
      $return['Pesan']  = $msg[0][1];
      $return['Data']   = $msg[0][0];
      $return['Css']    = $msg[0][2];

      $arrKomponen  = $ObjKomponen->GetSatuanKomponen();
      $arrMak       = $ObjKomponen->GetDataMak();
      $data         = $ObjKomponen->GetKomponenFromId($idDec);
      Messenger::Instance()->SendToComponent('combobox', 'Combobox', 'view', 'html', 'nama_satuan', array('nama_satuan', $arrKomponen, $data[0]['kompNamaSatuan'], '', ' style="width:200px;" '), Messenger::CurrentRequest);
      $return['decDataId'] = $idDec;
      $return['dataK'] = $data;
      $return['nama'] = '&nama='.Dispatcher::Instance()->Encrypt($nama);
      $return['page'] = '&page='.$page;
      return $return;
   }

   function ParseTemplate($data = NULL) {
       $qNama = $data['nama']. $data['page'];
      if ($data['Pesan']) {
         $this->mrTemplate->SetAttribute('warning_box', 'visibility', 'visible');
         $this->mrTemplate->AddVar('warning_box', 'ISI_PESAN', $data['Pesan']);
         $this->mrTemplate->AddVar('warning_box', 'CSS_PESAN', $data['Css']);
      }
      $dataK = $data['dataK'];

      if ($_REQUEST['dataId']=='') {
         $this->mrTemplate->AddVar('content', 'OPERASI', 'add');
         $operasi="Tambah";
      } else {
         $this->mrTemplate->AddVar('content', 'OPERASI', 'edit');
         $operasi="Ubah";
      }
      //set aksi input
      $this->mrTemplate->AddVar('content', 'URL_ACTION', Dispatcher::Instance()->GetUrl('komponen', 'InputKomponen', 'do', 'html') .$qNama. "&dataId=" . Dispatcher::Instance()->Encrypt($data['decDataId']));

     //popup COA
     $url_popup_coa = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'coa', 'popup', 'html');
     $this->mrTemplate->AddVar("content", "URL_POPUP_COA", $url_popup_coa);

     $url_popup_mak = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'MAK', 'popup', 'html');
     $this->mrTemplate->AddVar("content", "URL_POPUP_MAK", $url_popup_mak);

      $url_popup_sumber_dana = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, 'SumberDana', 'popup', 'html');
     $this->mrTemplate->AddVar("content", "URL_POPUP_SUMBER_DANA", $url_popup_sumber_dana);

      /**
       * popup komponen aset
       */
        $url_popup_kode_aset = Dispatcher::Instance()->GetUrl(
                                            Dispatcher::Instance()->mModule,
                                            'PopupKodeAset',
                                            'view',
                                            'html');
     $this->mrTemplate->AddVar("content", "URL_POPUP_KODE_ASET", $url_popup_kode_aset);
       /**
        * end
        */

      //set title
      $this->mrTemplate->AddVar('content', 'JUDUL', $operasi);

     $this->mrTemplate->AddVar('content', 'NAMA_KOMPONEN', empty($dataK[0]['kompNama'])?$data['Data']['nama_komponen']:$dataK[0]['kompNama']);
      $this->mrTemplate->AddVar('content', 'KOMPONEN_ID', empty($dataK[0]['kompId'])?$data['Data']['id_komponen']:$dataK[0]['kompId']);
     $this->mrTemplate->AddVar('content', 'NAMA_SATUAN', empty($dataK[0]['kompNamaSatuan'])?$data['Data']['nama_satuan']:$dataK[0]['kompNamaSatuan']);
      $this->mrTemplate->AddVar('content', 'DESKRIPSI', empty($dataK[0]['kompDeskripsi'])?$data['Data']['deskripsi']:$dataK[0]['kompDeskripsi']);
     $this->mrTemplate->AddVar('content', 'FORMULA', empty($dataK[0]['kompFormula'])?$data['Data']['formula']:$dataK[0]['kompFormula']);
      $this->mrTemplate->AddVar('content', 'ID_COA', empty($dataK[0]['coaId'])?$data['Data']['id_coa']:$dataK[0]['coaId']);
     $this->mrTemplate->AddVar('content', 'KODE_COA', empty($dataK[0]['coaKodeAkun'])?$data['Data']['kode_coa']:$dataK[0]['coaKodeAkun']);
      $this->mrTemplate->AddVar('content', 'NAMA_COA', empty($dataK[0]['coaNamaAkun'])?$data['Data']['nama_coa']:$dataK[0]['coaNamaAkun']);
     $this->mrTemplate->AddVar('content', 'HARGA_SATUAN', empty($dataK[0]['kompHargaSatuan'])?$data['Data']['harga_satuan']:$dataK[0]['kompHargaSatuan']);
      $this->mrTemplate->AddVar('content', 'KODE_MAK', empty($dataK[0]['paguBasKode'])?$data['Data']['kode_mak']:$dataK[0]['paguBasKode']);
     $this->mrTemplate->AddVar('content', 'ID_SUMBER_DANA', empty($dataK[0]['kompSumberDanaId'])?$data['Data']['id_sumber_dana']:$dataK[0]['kompSumberDanaId']);
      $this->mrTemplate->AddVar('content', 'NAMA_SUMBER_DANA', empty($dataK[0]['sumberdanaNama'])?$data['Data']['nama_sumber_dana']:$dataK[0]['sumberdanaNama']);
      $this->mrTemplate->AddVar('content', 'ID_MAK', empty($dataK[0]['kompMakId'])?$data['Data']['id_mak']:$dataK[0]['kompMakId']);
      $this->mrTemplate->AddVar('content', 'KODE_ASET', empty($dataK[0]['kompKodeAset'])?$data['Data']['kode_aset']:$dataK[0]['kompKodeAset']);
      if ($dataK[0]['kompIsSBU']== 1 || $data['Data']['coa_is_kas'] == 1)
         $this->mrTemplate->AddVar('content', 'KAS_1_CHEKED', "checked='checked'");
      else
         $this->mrTemplate->AddVar('content', 'KAS_0_CHEKED', "checked='checked'");

      if ($dataK[0]['kompIsLangsung']== 1 || $data['Data']['biaya1'] == 1)
         $this->mrTemplate->AddVar('content', 'B1_1_CHEKED', "checked='checked'");
      else
         $this->mrTemplate->AddVar('content', 'B1_0_CHEKED', "checked='checked'");

      if ($dataK[0]['kompIsTetap']== 1 || $data['Data']['biaya2'] == 1)
         $this->mrTemplate->AddVar('content', 'B2_1_CHEKED', "checked='checked'");
      else
         $this->mrTemplate->AddVar('content', 'B2_0_CHEKED', "checked='checked'");

      if ($dataK[0]['kompIsPengadaan']== Y || $data['Data']['pengadaan'] == Y)
         $this->mrTemplate->AddVar('content', 'P_Y_CHEKED', "checked='checked'");
      else
         $this->mrTemplate->AddVar('content', 'P_T_CHEKED', "checked='checked'");
   }
}
?>
