<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_alirankas/business/AppLapAliranKas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewCetakLapAlirankas extends HtmlResponse {

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/lap_alirankas/template');
      $this->SetTemplateFile('view_cetak_lap_aliran_kas.html');
   }
   
    function TemplateBase() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('document-print.html');
      $this->SetTemplateFile('layout-common-print.html');
   }
   
   function ProcessRequest() {
      $Obj = new AppLapAliranKas();
      $_GET = $_GET->AsArray();
      $tglAwal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $tglKas = Dispatcher::Instance()->Decrypt($_GET['tgl_kas']);
      
      $return['laporan_all'] = $Obj->GetLaporanAll($tglAwal,$tgl);
      $return['laporan_kas_setara_kas'] = $Obj->GetLaporanKasSetaraKas($tglKas);
      
      $return['saldo_coa_aliran_kas'] = $Obj->GetSaldoCoaAliranKas();
      $return['tgl_awal'] = $_GET['tgl_awal'];
      $return['tgl_akhir'] = $_GET['tgl_akhir'];
	   $return['tgl_kas'] = $_GET['tgl_kas'];
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'TGL_AWAL', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'));
      $this->mrTemplate->AddVar('content', 'TGL_AKHIR', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'));
      $gridList = $data['laporan_all'];
      
      
      for($i=0;$i<count($gridList);$i++){
         
         if($gridList[$i]['status']=='Ya')
            $plusValue[$gridList[$i]['kellapJnsId']] = $plusValue[$gridList[$i]['kellapJnsId']]+$gridList[$i]['nilai'];
         else
            $plusValue[$gridList[$i]['kellapJnsId']] = $plusValue[$gridList[$i]['kellapJnsId']]-$gridList[$i]['nilai'];
            
         if ($gridList[$i]['kellapJnsId'] == 3 and $gridList[$i]['status'] == 'Ya')
         {
            $templateName = 'data_item_operasional_masuk';
            $value[$gridList[$i]['kellapJnsId']]['masuk'] = $value[$gridList[$i]['kellapJnsId']]['masuk']+$gridList[$i]['nilai'];
         }
         elseif ($gridList[$i]['kellapJnsId'] == 3 and $gridList[$i]['status'] == 'Tidak')
         {
            $templateName = 'data_item_operasional_keluar';
            $value[$gridList[$i]['kellapJnsId']]['keluar'] = $value[$gridList[$i]['kellapJnsId']]['keluar']+$gridList[$i]['nilai'];
         }
         elseif ($gridList[$i]['kellapJnsId'] == 4 and $gridList[$i]['status'] == 'Ya')
         {
            $templateName = 'data_item_pendanaan_masuk';
            $value[$gridList[$i]['kellapJnsId']]['masuk'] = $value[$gridList[$i]['kellapJnsId']]['masuk']+$gridList[$i]['nilai'];
         }
         elseif ($gridList[$i]['kellapJnsId'] == 4 and $gridList[$i]['status'] == 'Tidak')
         {
            $templateName = 'data_item_pendanaan_keluar';
            $value[$gridList[$i]['kellapJnsId']]['keluar'] = $value[$gridList[$i]['kellapJnsId']]['keluar']+$gridList[$i]['nilai'];
         }
         elseif ($gridList[$i]['kellapJnsId'] == 5 and $gridList[$i]['status'] == 'Ya')
         {
            $templateName = 'data_item_investasi_masuk';
            $value[$gridList[$i]['kellapJnsId']]['masuk'] = $value[$gridList[$i]['kellapJnsId']]['masuk']+$gridList[$i]['nilai'];
         }
         elseif ($gridList[$i]['kellapJnsId'] == 5 and $gridList[$i]['status'] == 'Tidak')
         {
            $templateName = 'data_item_investasi_keluar';
            $value[$gridList[$i]['kellapJnsId']]['keluar'] = $value[$gridList[$i]['kellapJnsId']]['keluar']+$gridList[$i]['nilai'];
         }
         else
         {
            $templateName = "";
         }
            if(!empty($templateName)){
               $this->mrTemplate->AddVar($templateName,'LAP_NAMA_KEL_LAP',$gridList[$i]['nama_kel_lap']);
               
               if($gridList[$i]['nilai'] < 0)
						$this->mrTemplate->AddVar($templateName,'LAP_JML','('.number_format(str_replace('-','',$gridList[$i]['nilai']), 2, ',', '.').')');
					else
						$this->mrTemplate->AddVar($templateName,'LAP_JML', number_format($gridList[$i]['nilai'], 2, ',', '.'));
               $this->mrTemplate->parseTemplate($templateName,'a');
            }
         
      }
      
      $gridListKasSetaraKas = $data['laporan_kas_setara_kas'];

      for($i=0;$i<count($gridListKasSetaraKas);$i++){
         
         if($gridList[$i]['status']=='Ya')
            $plusValue[$gridListKasSetaraKas[$i]['kellapJnsId']] = $plusValue[$gridListKasSetaraKas[$i]['kellapJnsId']]+$gridListKasSetaraKas[$i]['nilai'];
         else
            $plusValue[$gridListKasSetaraKas[$i]['kellapJnsId']] = $plusValue[$gridListKasSetaraKas[$i]['kellapJnsId']]-$gridListKasSetaraKas[$i]['nilai'];
            
         $templateName = 'data_item_kas_setarakas_awal_tahun';
            
         if(!empty($templateName)){
            $this->mrTemplate->AddVar($templateName,'LAP_NAMA_KEL_LAP',$gridListKasSetaraKas[$i]['nama_kel_lap']);
            
				if($gridListKasSetaraKas[$i]['nilai'] < 0)
					$this->mrTemplate->AddVar($templateName,'LAP_JML','('.number_format(str_replace('-','',$gridListKasSetaraKas[$i]['nilai']), 2, ',', '.').')');
				else
					$this->mrTemplate->AddVar($templateName,'LAP_JML', number_format($gridListKasSetaraKas[$i]['nilai'], 2, ',', '.'));
		   $this->mrTemplate->AddVar($templateName,'URL_DETAIL', Dispatcher::Instance()->GetUrl('lap_alirankas', 'detilLapAliranKas', 'view', 'html'). '&dataId=' . $gridListKasSetaraKas[$i]['kellapId'].'&tgl='.$data['tgl_akhir'].'&ksk=Ya');
            $this->mrTemplate->parseTemplate($templateName,'a');
         }
      
      }
            
      if($plusValue['3'] < 0)
			$this->mrTemplate->AddVar('content','KAS_BERSIH_OPERASIONAL','('.number_format(str_replace('-','',$plusValue['3']), 2, ',', '.').')');
		else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_OPERASIONAL', number_format($plusValue['3'], 2, ',', '.'));
		
		if($plusValue['5'] < 0)
			$this->mrTemplate->AddVar('content','KAS_BERSIH_INVESTASI','('.number_format(str_replace('-','',$plusValue['5']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_INVESTASI', number_format($plusValue['5'], 2, ',', '.'));
		
		if($plusValue['4'] <=0 )
			$this->mrTemplate->AddVar('content','KAS_BERSIH_PENDANAAN','('.number_format(str_replace('-','',$plusValue['4']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_PENDANAAN', number_format($plusValue['4'], 2, ',', '.'));
      
      if($value['3']['masuk'] <=0 )
			$this->mrTemplate->AddVar('content','KAS_BERSIH_OPERASIONAL_MASUK','('.number_format(str_replace('-','',$value['3']['masuk']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_OPERASIONAL_MASUK', number_format($value['3']['masuk'], 2, ',', '.'));
		
      if($value['3']['keluar'] <=0 )
			$this->mrTemplate->AddVar('content','KAS_BERSIH_OPERASIONAL_KELUAR','('.number_format(str_replace('-','',$value['3']['keluar']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_OPERASIONAL_KELUAR', number_format($value['3']['keluar'], 2, ',', '.'));
			
		if($value['4']['masuk'] <=0 )
			$this->mrTemplate->AddVar('content','KAS_BERSIH_PENDANAAN_MASUK','('.number_format(str_replace('-','',$value['4']['masuk']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_PENDANAAN_MASUK', number_format($value['4']['masuk'], 2, ',', '.'));
		
      if($value['4']['keluar'] <=0 )
			$this->mrTemplate->AddVar('content','KAS_BERSIH_PENDANAAN_KELUAR','('.number_format(str_replace('-','',$value['4']['keluar']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_PENDANAAN_KELUAR', number_format($value['4']['keluar'], 2, ',', '.'));
      
      if($value['5']['masuk'] <=0 )
			$this->mrTemplate->AddVar('content','KAS_BERSIH_INVESTASI_MASUK','('.number_format(str_replace('-','',$value['5']['masuk']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_INVESTASI_MASUK', number_format($value['5']['masuk'], 2, ',', '.'));
		
      if($value['5']['keluar'] <=0 )
			$this->mrTemplate->AddVar('content','KAS_BERSIH_INVESTASI_KELUAR','('.number_format(str_replace('-','',$value['5']['keluar']), 2, ',', '.').')');
      else
			$this->mrTemplate->AddVar('content','KAS_BERSIH_INVESTASI_KELUAR', number_format($value['5']['keluar'], 2, ',', '.'));

		//summary
      $totalKas = $plusValue['5']+$plusValue['3']+$plusValue['4'];
      $totalKasAwalTahun = $plusValue['20'];
      
      if($totalKas < 0)
			$this->mrTemplate->AddVar('content','TOTAL_KAS','('.number_format(str_replace('-','',$totalKas), 2, ',', '.').')');
		else
			$this->mrTemplate->AddVar('content','TOTAL_KAS', number_format($totalKas, 2, ',', '.'));
		
      if($return['saldo_coa_aliran_kas'] < 0)
			$this->mrTemplate->AddVar('content','TOTAL_KAS_FROM_COA','('.number_format(str_replace('-','',$return['saldo_coa_aliran_kas']), 2, ',', '.').')');
		else
			$this->mrTemplate->AddVar('content','TOTAL_KAS_FROM_COA',number_format($return['saldo_coa_aliran_kas'], 2, ',', '.'));
			
		if($totalKas+$totalKasAwalTahun < 0)
			$this->mrTemplate->AddVar('content','SETARA_KAS_AKHIR_THN','('.number_format(str_replace('-','',$totalKas+$totalKasAwalTahun), 2, ',', '.').')');
		else
			$this->mrTemplate->AddVar('content','SETARA_KAS_AKHIR_THN',number_format($totalKas+$totalKasAwalTahun, 2, ',', '.'));
			
   }
}
?>
