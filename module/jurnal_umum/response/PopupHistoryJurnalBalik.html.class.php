<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/jurnal_umum/business/JurnalUmum.class.php';

class PopupHistoryJurnalBalik extends HtmlResponse
{
   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/jurnal_umum/template');
      $this->SetTemplateFile('popup_history_jurnal_balik.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-common-popup.html');
      $this->SetTemplateFile('layout-common-popup.html');
   }

   function ProcessRequest() {
      $mObj          = new JurnalUmum();
      $transaksiId   = Dispatcher::Instance()->Decrypt($mObj->_GET['data_id']);
      $pembukuanId   = Dispatcher::Instance()->Decrypt($mObj->_GET['pr_id']);

      $dataJurnal    = $mObj->getDataJurnalDetail($transaksiId, $pembukuanId);
      $dataDetails   = $mObj->getDataHistoryJurnal($transaksiId);

      $return['data_jurnal']  = $mObj->ChangeKeyName($dataJurnal);
      $return['data_list']    = $mObj->ChangeKeyName($dataDetails);

      return $return;
   }

   function ParseTemplate($data = NULL) {
      $dataDetail       = $data['data_jurnal'];
      $dataList         = $data['data_list'];
      $start            = 1;

      $this->mrTemplate->AddVars('content', $dataDetail);
      if(empty($dataList)){
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      }else{
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');
         $dataGrid      = array();
         $pembukuanId   = '';
         $transaksiId   = '';
         $index         = 0;
         $idx           = 0;
         $dataJurnal    = array();
         $rows          = array();
         for ($i=0; $i < count($dataList);) {
            if((int)$transaksiId === (int)$dataList[$i]['id']
               && (int)$pembukuanId === (int)$dataList[$i]['pembukuan_id']){
               $ks      = $pembukuanId.'.'.$transaksiId;
               $dataJurnal[$ks][$idx]['akun_id']         = $dataList[$i]['coa_id'];
               $dataJurnal[$ks][$idx]['kode']            = $dataList[$i]['coa_kode_akun'];
               $dataJurnal[$ks][$idx]['nama']            = $dataList[$i]['coa_nama_akun'];
               $dataJurnal[$ks][$idx]['sub_account']     = $dataList[$i]['sub_account'];
               $dataJurnal[$ks][$idx]['nominal_debet']   = number_format($dataList[$i]['nominal_debet'], 2, ',','.');
               $dataJurnal[$ks][$idx]['nominal_kredit']  = number_format($dataList[$i]['nominal_kredit'], 2, ',','.');
               $dataJurnal[$ks][$idx]['class_name']      = $className;
               $rows[$ks]['row_span']        += 1;
               $i++;
               $idx++;
            }else{
               unset($idx);
               $idx              = 0;
               $pembukuanId      = (int)$dataList[$i]['pembukuan_id'];
               $transaksiId      = (int)$dataList[$i]['id'];
               $kodeSistem       = $pembukuanId.'.'.$transaksiId;
               if($start % 2 <> 0){
                  $className     = 'table-common-even';
               }else{
                  $className     = '';
               }

               if($dataList[$i]['status_posting'] == 'T'){
                  $className     = 'posting';
               }
               $dataJurnal[$kodeSistem][$idx]['id']             = $dataList[$i]['id'];
               $dataJurnal[$kodeSistem][$idx]['nomor']          = $start;
               $dataJurnal[$kodeSistem][$idx]['pembukuan_id']   = $dataList[$i]['pembukuan_id'];
               $dataJurnal[$kodeSistem][$idx]['kode_sistem']    = $kodeSistem;
               $dataJurnal[$kodeSistem][$idx]['referensi']      = $dataList[$i]['referensi'];
               $dataJurnal[$kodeSistem][$idx]['deskripsi']      = $dataList[$i]['catatan'];
               $dataJurnal[$kodeSistem][$idx]['tanggal']        = $dataList[$i]['tanggal'];
               $dataJurnal[$kodeSistem][$idx]['penanggung_jawab'] = $dataList[$i]['penanggung_jawab'];
               $dataJurnal[$kodeSistem][$idx]['type']          = 'parent';
               $dataJurnal[$kodeSistem][$idx]['status_approval']  = $dataList[$i]['status_approve'];
               $dataJurnal[$kodeSistem][$idx]['status_posting']   = $dataList[$i]['status_posting'];
               $dataJurnal[$kodeSistem][$idx]['jurnal_balik']     = $dataList[$i]['jurnal_balik'];
               $dataJurnal[$kodeSistem][$idx]['has_jurnal']       = strtoupper($dataList[$i]['has_jurnal']);
               $dataJurnal[$kodeSistem][$idx]['jurnal']           = $dataList[$i]['jurnal'];
               $dataJurnal[$kodeSistem][$idx]['class_name']       = $className;
               $dataJurnal[$kodeSistem][$idx]['status_jurnal']    = ((int)$dataList[$i]['jurnal_balik'] <> 0) ? 'JURNAL BALIK' : '';
               $rows[$kodeSistem]['row_span']      = 0;
               $index++;
               $start++;
            }
         }

         foreach ($dataJurnal as $grid) {
            foreach ($grid as $jurnal) {
               if($jurnal['type'] AND strtoupper($jurnal['type']) == 'PARENT'){
                  $jurnal['row_span']     = $rows[$jurnal['kode_sistem']]['row_span'];
                  $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'PARENT');
                  // url delete
                  $urlAccept              = 'jurnal_umum|DeleteJurnalUmum|do|json-search|'.$keyUrl.'-1|'.$valueUrl;
                  $urlReturn              = 'jurnal_umum|JurnalUmum|view|html-search|'.$keyUrl.'-1|'.$valueUrl;
                  $label                  = GTFWConfiguration::GetValue('language', 'jurnal_umum');
                  $message                = 'Penghapusan Data ini akan menghapus Data secara permanen.';
                  $jurnal['url_delete']   = Dispatcher::Instance()->GetUrl(
                     'confirm',
                     'confirmDelete',
                     'do',
                     'html'
                  ).'&urlDelete='. $urlAccept
                  .'&urlReturn='.$urlReturn
                  .'&id='.$jurnal['id'].'.'.$jurnal['pembukuan_id']
                  .'&label='.$label
                  .'&dataName='.$jurnal['referensi']
                  .'&message='.$message;

                  // condition status approve
                  if($jurnal['status_approval'] == 'Y'){
                     $this->mrTemplate->AddVar('status_approval', 'APPROVE', 'YES');
                  }else{
                     $this->mrTemplate->AddVar('status_approval', 'APPROVE', 'NO');
                  }
                  // condition status posting
                  if($jurnal['status_posting'] == 'Y'){
                     $this->mrTemplate->AddVar('status_posting', 'POSTING', 'YES');
                  }else{
                     $this->mrTemplate->AddVar('status_posting', 'POSTING', 'NO');
                  }

                  if((int)$jurnal['jurnal'] <> 0){
                     $this->mrTemplate->SetAttribute('history_jurnal', 'visibility', 'visible');
                     $this->mrTemplate->AddVar('history_jurnal', 'URL_DETAIL', $historyJurnal.'&data_id='.$jurnal['id'].'&pr_id='.$jurnal['pembukuan_id']);
                  }

                  // condition links
                  if($jurnal['status_approval'] == 'T'){
                     $this->mrTemplate->AddVar('content_links', 'STATUS', 'UNAPPROVE');
                  }

                  if($jurnal['status_approval'] == 'Y' AND $jurnal['has_jurnal'] === 'YES'){
                     $this->mrTemplate->AddVar('content_links', 'STATUS', 'APPROVE');
                  }

                  if($jurnal['status_approval'] == 'Y' AND $jurnal['has_jurnal'] == 'NO'){
                     $this->mrTemplate->AddVar('content_links', 'STATUS', 'POSTING');
                  }
                  $this->mrTemplate->AddVar('content_links', 'URL_EDIT', $urlEdit.'&data_id='.Dispatcher::Instance()->Encrypt($jurnal['id']).'&pr_id='.Dispatcher::Instance()->Encrypt($jurnal['pembukuan_id']));
                  $this->mrTemplate->AddVar('content_links', 'URL_DELETE', $jurnal['url_delete']);
                  $this->mrTemplate->AddVar('content_links', 'ID', $jurnal['id']);
                  $this->mrTemplate->AddVar('content_links', 'PEMBUKUAN_ID', $jurnal['pembukuan_id']);
                  $this->mrTemplate->AddVar('content_links', 'REFERENSI', $jurnal['referensi']);
                  $this->mrTemplate->AddVar('content_links', 'URL_JURNAL_BALIK', $urlJurnalBalik);
                  $this->mrTemplate->AddVars('data_jurnal', $jurnal);
               }else{
                  $this->mrTemplate->AddVar('data_jurnal', 'LEVEL', 'DATA');
                  $this->mrTemplate->AddVars('data_jurnal', $jurnal);
               }
               $this->mrTemplate->parseTemplate('data_list', 'a');
            }
         }
      }
   }
}
?>