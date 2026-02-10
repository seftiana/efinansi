<?php


require_once GTFWConfiguration::GetValue( 'application', 'docroot') .
'module/lppa/business/Lppa.class.php';


class ViewCetakLaporanLppa extends HtmlResponse 
{
   function TemplateModule() 
   {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').
         'module/lppa/template');
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
         $total_fpa_lppa = 0;
         foreach ($dataList as $key => $list) {
            if($list['nominal_lppa'] != '0'){
            if($key > 0 ){                
                if($dataList[$key]['kode_akun'] == $dataList[$key - 1]['kode_akun']){
                    $list['kode_akun'] ='';
                    $list['nama_akun'] ='';
                }
            }
            $total_fpa += $list['nominal_approve'];
            $total_fpa_lppa += $list['nominal_lppa'];
           
            $list['rincian'] = $list['detail_pengeluaran'];
            $list['nominal_approve'] = number_format($list['nominal_approve'],2,',','.');
            $list['nominal_lppa'] = number_format($list['nominal_lppa'],2,',','.');
            $this->mrTemplate->AddVars('data_list', $list);
            $this->mrTemplate->parseTemplate('data_list', 'a');
         }
         }

         $this->mrTemplate->AddVar('data_grid', 'TOTAL_FPA', number_format($total_fpa,2,',','.'));
         $this->mrTemplate->AddVar('data_grid', 'TOTAL_FPA_REALISASI', number_format($total_fpa_lppa,2,',','.'));   
         $this->mrTemplate->AddVar('data_grid', 'SISA', number_format(($total_fpa - $total_fpa_lppa) ,2,',','.'));
         
         $this->mrTemplate->AddVar('content', 'TGL_LPPA',  $dataList[0]['tgl_lppa']);
         $this->mrTemplate->AddVar('content', 'TGL_CETAK', date('Y-m-d', time()));
         $this->mrTemplate->AddVar('content', 'PENANGGUNG_JAWAB', $dataList[0]['penanggung_jawab']);
         $this->mrTemplate->AddVar('content', 'MENGETAHUI',$dataList[0]['mengetahui']); 
         $this->mrTemplate->AddVar('content', 'UNIT_KERJA_NAMA',$dataList[0]['unit_kerja_nama']); 
      }
      
   }
}
?>
