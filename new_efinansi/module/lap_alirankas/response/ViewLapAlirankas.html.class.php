<?php

/*
	@ClassName : ViewLapAlirankas
	@Copyright : PT Gamatechno Indonesia
	@Analyzed By : Nanang Ruswianto <nanang@gamatechno.com>
	@Designed By : Rosyid <rosyid@gamatechno.com>
	@Author By : Dyan Galih <galih@gamatechno.com>
	@Version : 1.0
	@StartDate : Jan 28, 2009
	@LastUpdate : Jan 28, 2009
	@Description :
*/

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_alirankas/business/AppLapAliranKas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewLapAlirankas extends HtmlResponse {
   public $return;

   function TemplateModule() {
      $this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/lap_alirankas/template');
      $this->SetTemplateFile('view_lap_aliran_kas.html');
   }

   function ProcessRequest() {

	  $Obj = new AppLapAliranKas();

      $post = $_POST->AsArray();

      if(!empty($post['tanggal_awal_day'])){
         $tglAwal = $post['tanggal_awal_year'] ."-". $post['tanggal_awal_mon'] ."-". $post['tanggal_awal_day'];
      }else{
         $tglAwal = date("Y-01-01");
      }

      if(!empty($post['tanggal_akhir_day'])){
         $tgl = $post['tanggal_akhir_year'] ."-". $post['tanggal_akhir_mon'] ."-". $post['tanggal_akhir_day'];
         $tglKas = $post['tanggal_akhir_year'] ."-01-01";
      }else{
         $tgl = date("Y-m-d");
         $tglKas = date("Y")."-01-01";
      }
      //tahun untuk combo
      $tahunTrans = $Obj->GetMinMaxThnTrans();
      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal',
         array($tglAwal, $tahunTrans['minTahun'], $tahunTrans['maxTahun']), Messenger::CurrentRequest);

      Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir',
         array($tgl, $tahunTrans['minTahun'], $tahunTrans['maxTahun']), Messenger::CurrentRequest);


      $return['laporan_all'] = $Obj->GetLaporanAll($tglAwal,$tgl);
      $return['laporan_kas_setara_kas'] = $Obj->GetLaporanKasSetaraKas($tglKas);

      $return['saldo_coa_aliran_kas'] = $Obj->GetSaldoCoaAliranKas();
	   #print_r($return['tgl_akhir']);
	   $return['tgl_awal'] = $tglAwal;
	   $return['tgl_akhir'] = $tgl;
	   $return['tgl_kas'] = $tglKas;
      return $return;
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->AddVar('content', 'URL_SEARCH', Dispatcher::Instance()->GetUrl('lap_alirankas', 'LapAlirankas', 'view', 'html'));
      $this->mrTemplate->AddVar('content', 'URL_CETAK', Dispatcher::Instance()->GetUrl('lap_alirankas', 'CetakLapAlirankas', 'view', 'html') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) . '&tgl_kas=' . Dispatcher::Instance()->Encrypt($data['tgl_kas']) .'&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

		$this->mrTemplate->AddVar('content', 'URL_EXCEL',
      Dispatcher::Instance()->GetUrl('lap_alirankas', 'ExcelLapAliranKas', 'view', 'xlsx') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) .'&tgl_kas=' . Dispatcher::Instance()->Encrypt($data['tgl_kas']) . '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

		$this->mrTemplate->AddVar('content', 'URL_RTF',
      Dispatcher::Instance()->GetUrl('lap_alirankas', 'RtfLapAliranKas', 'view', 'html') . '&tgl_awal=' . Dispatcher::Instance()->Encrypt($data['tgl_awal']) . '&tgl_akhir=' . Dispatcher::Instance()->Encrypt($data['tgl_akhir']) .'&tgl_kas=' . Dispatcher::Instance()->Encrypt($data['tgl_kas']) . '&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

      $gridList = $data['laporan_all'];
      //sprint_r($gridList);
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
			   $this->mrTemplate->AddVar($templateName,'URL_DETAIL', Dispatcher::Instance()->GetUrl('lap_alirankas', 'detilLapAliranKas', 'view', 'html'). '&dataId=' . $gridList[$i]['kellapId'].'&tgl='.$data['tgl_akhir'].'&tgl_kas='.$data['tgl_kas'].'&tgl_awal='.$data['tgl_awal']);
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
		   $this->mrTemplate->AddVar($templateName,'URL_DETAIL', Dispatcher::Instance()->GetUrl('lap_alirankas', 'detilLapAliranKas', 'view', 'html'). '&dataId=' . $gridListKasSetaraKas[$i]['kellapId'].'&tgl='.$data['tgl_akhir'].'&ksk=Ya'.'&tgl_kas='.$data['tgl_kas']);
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

		if($data['saldo_coa_aliran_kas'] < 0)
			$this->mrTemplate->AddVar('content','TOTAL_KAS_FROM_COA','('.number_format(str_replace('-','',$data['saldo_coa_aliran_kas']), 2, ',', '.').')');
		else
			$this->mrTemplate->AddVar('content','TOTAL_KAS_FROM_COA',number_format($data['saldo_coa_aliran_kas'], 2, ',', '.'));

		if($totalKas+$totalKasAwalTahun < 0)
			$this->mrTemplate->AddVar('content','SETARA_KAS_AKHIR_THN','('.number_format(str_replace('-','',$totalKas+$totalKasAwalTahun), 2, ',', '.').')');
		else
			$this->mrTemplate->AddVar('content','SETARA_KAS_AKHIR_THN',number_format($totalKas+$totalKasAwalTahun, 2, ',', '.'));

   }

}
?>
