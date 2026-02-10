<?php


require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lppa_approval/business/Lppa.class.php';


class ViewCetakLaporanLppa extends HtmlResponse 
{
   function TemplateModule() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lppa_approval/template');
      $this->SetTemplateFile('view_cetak_laporan_lppa.html');
   }

   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }

   function ProcessRequest() {
      $mObj                = new Lppa;
      $requestData         = array();
      $requestData['data_id'] = Dispatcher::Instance()->Decrypt($mObj->_GET['dataId']);
   
      $dataList            = $mObj->GetLaporanLppa($requestData['data_id']);

      $return['data_laporan'] = $dataList;
      return $return;
   }

   function ParseTemplate($data = NULL) {     
      $dataList         = $data['data_laporan'];
      if (empty($dataList)) {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'YES');
      } else {
         $this->mrTemplate->AddVar('data_grid', 'DATA_EMPTY', 'NO');         
         //print_r($dataList);
         $total_fpa = 0;
         $total_fpa_realisasi = 0;
         foreach ($dataList as $key => $list) {
            if($key > 0 ){                
                if($dataList[$key]['kode_akun'] == $dataList[$key - 1]['kode_akun']){
                    $list['kode_akun'] ='';
                    $list['nama_akun'] ='';
                }
            }
            $total_fpa = number_format($list['total_fpa'],2,',','.');
            $total_fpa_realisasi = number_format($list['total_fpa_realisasi'],2,',','.');
            if($list['sisa'] > 0) {
                $sisa = number_format($list['sisa'],2,',','.');
            } else {
                $sisa = '';
            }
            $list['rincian'] = $list['detail_pengeluaran'];
            $list['nominal_approve'] = number_format($list['nominal_approve'],2,',','.');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_FPA', $total_fpa);
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_FPA_REALISASI', $total_fpa_realisasi);   
         $this->mrTemplate->AddVar('data_grid', 'SISA', $sisa);   
      }
      
   }
}
?>
