<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_alirankas/business/AppLapAliranKas.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapAliranKas2 extends XlsResponse {
   var $mWorksheets = array('Data');
   
   function GetFileName() {
      // name it whatever you want
      return 'LapAliranKas'.date('d-m-Y').'.xls';
   }

   function ProcessRequest() {
      $Obj = new AppLapAliranKas();
      $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      $tglKas = Dispatcher::Instance()->Decrypt($_GET['tgl_kas']);
      $gridList = $Obj->GetLaporanAll($tgl_awal,$tgl_akhir);
	   $dataAliranKas=$Obj->GetSaldoCoaAliranKas();
	   $gridListKasSetaraKas = $Obj->GetLaporanKasSetaraKas($tglKas);
	   
	   
      if (empty($gridList)) {
         $this->mWorksheets['Data']->write(0, 0, 'Data kosong');
      } else {
         $fTitle = $this->mrWorkbook->add_format();
         $fTitle->set_bold();
         $fTitle->set_size(12);
         $fTitle->set_align('vcenter');
         
         #set colom
         $fColNomorbold = $this->mrWorkbook->add_format();
         $fColNomorbold->set_border(1);
         $fColNomorbold->set_bold();
         $fColNomorbold->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fColNomorbold->set_align('center');
         
         $fColNilaibold = $this->mrWorkbook->add_format();
         $fColNilaibold->set_border(1);
         $fColNilaibold->set_bold();
         $fColNilaibold->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fColNilaibold->set_align('right');
         
         $fColNomor = $this->mrWorkbook->add_format();
         $fColNomor->set_border(1);
         #$fColNomor->set_bold();
         $fColNomor->set_size(10);
         //$fColKegiatan->set_column(1,1, 300);
         $fColNomor->set_align('center');
         
         $fColNomorakun = $this->mrWorkbook->add_format();
         $fColNomorakun->set_border(1);
         $fColNomorakun->set_size(10);
         $fColNomorakun->set_align('left');
         
         $fColCtn = $this->mrWorkbook->add_format();
         $fColCtn->set_border(1);
         $fColCtn->set_size(10);
         $fColCtn->set_align('left');
         
         $fColCtnBold = $this->mrWorkbook->add_format();
         $fColCtnBold->set_border(1);
         $fColCtnBold->set_size(10);
         $fColCtnBold->set_bold();
         $fColCtnBold->set_align('left');
         
         $fColNilai = $this->mrWorkbook->add_format();
         $fColNilai->set_border(1);   
         $fColNilai->set_size(10);
         $fColNilai->set_align('right');
         
         $fColCtnitalic = $this->mrWorkbook->add_format();
         $fColCtnitalic->set_border(1);
         $fColCtnitalic->set_italic();
         $fColCtnitalic->set_size(10);
         $fColCtnitalic->set_align('left');
         
         $fColNilaitalic = $this->mrWorkbook->add_format();
         $fColNilaitalic->set_border(1);   
         $fColNilaitalic->set_italic();
         $fColNilaitalic->set_size(10);
         $fColNilaitalic->set_align('right');
         
         #set header
         $this->mWorksheets['Data']->write(0, 0, 'Laporan Aliran Kas', $fTitle);
		
		$row+=2;
		$coll=0;
        $this->mWorksheets['Data']->write($row, $coll, 'Untuk Interval Waktu '.IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd'), $fTitle);
        $this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll+6);
		
			//------------------------------------------untuk ARUS KAS DARI AKTIVITAS OPERASI----------------------------------------------------------------	
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS KAS DARI AKTIVITAS OPERASI', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, '', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS MASUK', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			for($i=0;$i<count($gridList);$i++){
				if($gridList[$i]['kelJnsNama']=='Operasional' and $gridList[$i]['status'] == 'Ya')
					{
					$row+=2;
					$coll=0;
					$this->mWorksheets['Data']->write($row, $coll, $gridList[$i]['nama_kel_lap'], $td);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					$coll++;
					$this->mWorksheets['Data']->write($row, $coll, number_format($gridList[$i]['nilai'], 2, ',', '.'), $fColNilai);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					
					if($gridList[$i]['status']=='Ya'){$jmlOperasi +=$gridList[$i]['nilai'];}
					else{$jmlOperasi -=$gridList[$i]['nilai'];}
					}
			}
			
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS KELUAR', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			for($i=0;$i<count($gridList);$i++){
				if($gridList[$i]['kelJnsNama']=='Operasional' and $gridList[$i]['status'] == 'Tidak')
					{
					$row+=2;
					$coll=0;
					$this->mWorksheets['Data']->write($row, $coll, $gridList[$i]['nama_kel_lap'], $td);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					$coll++;
					$this->mWorksheets['Data']->write($row, $coll, number_format($gridList[$i]['nilai'], 2, ',', '.'), $fColNilai);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					
					if($gridList[$i]['status']=='Tidak'){$jmlOperasi +=$gridList[$i]['nilai'];}
					else{$jmlOperasi -=$gridList[$i]['nilai'];}
					}
			}
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'Jumlah Arus Kas Bersih Dari Aktivitas Operasi', $fColCtnitalic);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, number_format($jmlOperasi, 2, ',', '.'), $fColNilaitalic);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			
			//------------------------------------------untuk ARUS KAS DARI AKTIVITAS INVESTASI----------------------------------------------------------------
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS KAS DARI AKTIVITAS INVESTASI', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, '', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS MASUK', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			for($i=0;$i<count($gridList);$i++){
				if($gridList[$i]['kelJnsNama']=='Investasi' and $gridList[$i]['status'] == 'Ya')
					{
					$row+=2;
					$coll=0;
					$this->mWorksheets['Data']->write($row, $coll, $gridList[$i]['nama_kel_lap'], $td);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					$coll++;
					$this->mWorksheets['Data']->write($row, $coll, number_format($gridList[$i]['nilai'], 2, ',', '.'), $fColNilai);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					
					if($gridList[$i]['status']=='Ya'){$jmlInvestasi +=$gridList[$i]['nilai'];}
					else{$jmlInvestasi -=$gridList[$i]['nilai'];}
					}
			}
			
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS KELUAR', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			for($i=0;$i<count($gridList);$i++){
				if($gridList[$i]['kelJnsNama']=='Investasi' and $gridList[$i]['status'] == 'Tidak')
					{
					$row+=2;
					$coll=0;
					$this->mWorksheets['Data']->write($row, $coll, $gridList[$i]['nama_kel_lap'], $td);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					$coll++;
					$this->mWorksheets['Data']->write($row, $coll, number_format($gridList[$i]['nilai'], 2, ',', '.'), $fColNilai);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					
					if($gridList[$i]['status']=='Tidak'){$jmlInvestasi +=$gridList[$i]['nilai'];}
					else{$jmlInvestasi -=$gridList[$i]['nilai'];}
					}
			}
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'Jumlah Arus Kas Bersih Dari Aktivitas Investasi ', $fColCtnitalic);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, number_format($jmlInvestasi, 2, ',', '.'), $fColNilaitalic);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			
		//------------------------------------------untuk  ARUS KAS DARI AKTIVITAS PENDANAAN----------------------------------------------------------------
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS KAS DARI AKTIVITAS PENDANAAN', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS MASUK', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
         for($i=0;$i<count($gridList);$i++){
				if($gridList[$i]['kelJnsNama']=='Pendanaan' and $gridList[$i]['status'] == 'Ya')
					{
					$row+=2;
					$coll=0;
					$this->mWorksheets['Data']->write($row, $coll, $gridList[$i]['nama_kel_lap'], $td);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					$coll++;
					$this->mWorksheets['Data']->write($row, $coll, number_format($gridList[$i]['nilai'], 2, ',', '.'), $fColNilai);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					
					if($gridList[$i]['status']=='Ya'){$jmlPendanaan +=$gridList[$i]['nilai'];}
					else{$jmlPendanaan -=$gridList[$i]['nilai'];}
					}
			}
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'ARUS KELUAR', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
         for($i=0;$i<count($gridList);$i++){
				if($gridList[$i]['kelJnsNama']=='Pendanaan' and $gridList[$i]['status'] == 'Tidak')
					{
					$row+=2;
					$coll=0;
					$this->mWorksheets['Data']->write($row, $coll, $gridList[$i]['nama_kel_lap'], $td);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					$coll++;
					$this->mWorksheets['Data']->write($row, $coll, number_format($gridList[$i]['nilai'], 2, ',', '.'), $fColNilai);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					
					if($gridList[$i]['status']=='Tidak'){$jmlPendanaan +=$gridList[$i]['nilai'];}
					else{$jmlPendanaan -=$gridList[$i]['nilai'];}
					}
			}
			$row+=2;
			$coll=0;
			$this->mWorksheets['Data']->write($row, $coll, 'Jumlah Arus Kas Bersih dari Kegiatan Pendanaan', $fColCtnitalic);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
			$coll++;
			$this->mWorksheets['Data']->write($row, $coll, number_format($jmlPendanaan, 2, ',', '.'), $fColNilaitalic);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
		
		//-------------------KENAIKAN (PENURUNAN) BERSIH KAS DAN SETARA KAS'---
		$row+=2;
		$coll=0;
		$totalKenaikan = $jmlOperasi+$jmlInvestasi+$jmlPendanaan;
		$this->mWorksheets['Data']->write($row, $coll, 'KENAIKAN (PENURUNAN) BERSIH KAS DAN SETARA KAS', $fColCtnBold);
		$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
		$coll++;
		$this->mWorksheets['Data']->write($row, $coll, number_format($totalKenaikan, 2, ',', '.'), $fColNilai);
		$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
		
		//-------------------KAS DAN SETARA KAS AWAL TAHUN---
		$row+=2;
		$coll=0;
		$this->mWorksheets['Data']->write($row, $coll, 'KAS DAN SETARA KAS AWAL TAHUN', $fColCtnBold);
		$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
		$coll++;
		$this->mWorksheets['Data']->write($row, $coll, 'JUMLAH (RP)', $fColNomorbold);
		$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
		for($i=0;$i<count($gridListKasSetaraKas);$i++){
					$row+=2;
					$coll=0;
					$this->mWorksheets['Data']->write($row, $coll, $gridListKasSetaraKas[$i]['nama_kel_lap'], $td);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					$coll++;
					$this->mWorksheets['Data']->write($row, $coll, number_format($gridListKasSetaraKas[$i]['nilai'], 2, ',', '.'), $fColNilai);
					$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
					
					if($gridList[$i]['status']=='Ya'){$jmlKasSetaraKas +=$gridListKasSetaraKas[$i]['nilai'];}
					else{$jmlKasSetaraKas -=$gridListKasSetaraKas[$i]['nilai'];}
			}
		
		//-------------------KAS DAN SETARA KAS AKHIR TAHUN---
		$row+=2;
		$coll=0;
		$totalKenaikan = $jmlOperasi+$jmlInvestasi+$jmlPendanaan;
		$this->mWorksheets['Data']->write($row, $coll, 'KAS DAN SETARA KAS AKHIR TAHUN', $fColCtnBold);
		$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
		$coll++;
		$this->mWorksheets['Data']->write($row, $coll, number_format($totalKenaikan+$jmlKasSetaraKas, 2, ',', '.'), $fColNilai);
		$this->mWorksheets['Data']->merge_cells($row, $coll, $row+1, $coll);
      }
      
   }
}
?>