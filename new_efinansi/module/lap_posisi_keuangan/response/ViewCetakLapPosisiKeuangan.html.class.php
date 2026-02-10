<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/number_format.class.php';

class ViewCetakLapPosisiKeuangan extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/lap_posisi_keuangan/template');
      $this->SetTemplateFile('view_cetak_lap_posisi_keuangan.html');
   }
   
   function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   
   function ProcessRequest() {
      $Obj = new AppLapPosisiKeuangan();
      $get = $_GET->AsArray();
      if(!empty($get['tgl_awal']))
         $tglAwal = $get['tgl_awal'];
      else
         $tglAwal = date("Y-m-d");
      
      if(!empty($get['tgl_akhir']))
         $tgl = $get['tgl_akhir'];
      else
         $tgl = date("Y-m-d");
      
      $return['laporan_all'] = $Obj->GetLaporanAll($tglAwal,$tgl);

      $return['tgl_awal'] = $tglAwal;
      $return['tgl_akhir'] = $tgl;
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
      $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
      
      $this->mrTemplate->AddVar('content', 'EKUITAS_AWAL',  NumberFormat::Accounting($data['laba_thn_lalu']['saldo_akhir'], 2));

      $gridList = $data['laporan_all'];
      
      foreach($gridList as $key => $value)
      {
         if ($value['status'] == 'Ya') $aktiva[$value['kelJnsNama']][] = array(
            "nama_kel_lap" => $value['nama_kel_lap'],
            "nilai" => $value['nilai'],
            "kelJnsNama" => $value['kelJnsNama'],
            "kellapId" =>$value['kellapId']
         );
         else $kewajiban[$value['kelJnsNama']][] = array(
            "nama_kel_lap" => $value['nama_kel_lap'],
            "nilai" => $value['nilai'],
            "kelJnsNama" => $value['kelJnsNama'],
            "kellapId" =>$value['kellapId']
         );
      }
      $totalAktiva = 0;
      $totalKewajiban = 0;

      foreach($aktiva as $key => $value)
      {
         $total = 0;
         $this->mrTemplate->ClearTemplate('aktiva_item');

         foreach($value as $detilKey => $detilValue)
         {
            $detilValue['url_detil'] =$urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir']. '&tgl_awal=' . $data['tgl_awal'];
            $total+= $detilValue['nilai'];
            $detilValue['nilai'] =  NumberFormat::Accounting($detilValue['nilai'], 2);
            $this->mrTemplate->AddVars('aktiva_item', $detilValue, '');
            $this->mrTemplate->parseTemplate('aktiva_item', 'a');
         }
         $totalAktiva+= $total;
         $this->mrTemplate->AddVar('aktiva', 'KELJNSNAMA', $key);
         $this->mrTemplate->AddVar('aktiva', 'TOTAL_NILAI',  NumberFormat::Accounting($total, 2));
         $this->mrTemplate->parseTemplate('aktiva', 'a');
      }

      foreach($kewajiban as $key => $value)
      {
         $total = 0;
         $this->mrTemplate->ClearTemplate('kewajiban_item');

         foreach($value as $detilKey => $detilValue)
         {
            $total+= $detilValue['nilai'];
            $detilValue['url_detil'] =$urlDetil . '&dataId=' . $detilValue['kellapId'] . '&tgl=' . $data['tgl_akhir']. '&tgl_awal=' . $data['tgl_awal'];
            $detilValue['nilai'] =  NumberFormat::Accounting($detilValue['nilai'], 2);
            $this->mrTemplate->AddVars('kewajiban_item', $detilValue, '');
            $this->mrTemplate->parseTemplate('kewajiban_item', 'a');
         }
         $totalKewajiban+= $total;
         $this->mrTemplate->AddVar('kewajiban', 'KELJNSNAMA', $key);
         $this->mrTemplate->AddVar('kewajiban', 'TOTAL_NILAI',  NumberFormat::Accounting($total, 2));
         $this->mrTemplate->parseTemplate('kewajiban', 'a');
      }

      
      $jumlahAktiva =  NumberFormat::Accounting(($totalAktiva), 2);
      $jumlahKewajiban =  NumberFormat::Accounting(($totalKewajiban), 2);
      $this->mrTemplate->AddVar('content', 'JUMLAH_AKTIVA',$jumlahAktiva);
      $this->mrTemplate->AddVar('content', 'JUMLAH_KEWAJIBAN_AKTIVA_BERSIH',$jumlahKewajiban); 
   }
   
}
?>
