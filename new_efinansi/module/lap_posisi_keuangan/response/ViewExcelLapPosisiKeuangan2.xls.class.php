<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/lap_posisi_keuangan/business/AppLapPosisiKeuangan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewExcelLapPosisiKeuangan2 extends XlsResponse
{
	var $mWorksheets = array(
		'Data'
	);
	
	function GetFileName() 
	{

		// name it whatever you want
		
		return 'LapPosisiKeuangan' . date('d-m-Y') . '.xls';
	}
	
	function ProcessRequest() 
	{
		$Obj = new AppLapPosisiKeuangan();
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
		$tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
		
		$gridList = $Obj->GetLaporanAll($tgl_awal,$tgl_akhir);
		
		if (empty($gridList)) 
		{
			$this->mWorksheets['Data']->write(0, 0, 'Data kosong');
		}
		else
		{
			$fTitle = $this->mrWorkbook->add_format();
			$fTitle->set_bold();
			$fTitle->set_size(12);
			$fTitle->set_align('center');
			
			$fFormat = $this->mrWorkbook->add_format();
			$fFormat->set_size(10);
			$fFormat->set_align('center');
			
			$fFormatKelompok = $this->mrWorkbook->add_format();
			$fFormatKelompok->set_bold();
			$fFormatKelompok->set_size(10);
			$fFormatKelompok->set_align('center');

			#set colom
			$fColNomorbold = $this->mrWorkbook->add_format();
			$fColNomorbold->set_border(1);
			$fColNomorbold->set_bold();
			$fColNomorbold->set_size(10);
			$fColNomorbold->set_align('center');
			$fColNomorbold->set_align('vcenter');
			
			$fColNilaibold = $this->mrWorkbook->add_format();
			$fColNilaibold->set_border(1);
			$fColNilaibold->set_bold();
			$fColNilaibold->set_size(10);
			$fColNilaibold->set_num_format(4);
			$fColNilaibold->set_align('right');
			$fColNilaibold->set_align('vright');
			
			$fColNomor = $this->mrWorkbook->add_format();
			$fColNomor->set_border(1);
			$fColNomor->set_size(10);
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
			$fColNilai->set_num_format(4);
			
			$fColCtnitalic = $this->mrWorkbook->add_format();
			$fColCtnitalic->set_border(1);
			$fColCtnitalic->set_italic();
			$fColCtnitalic->set_size(10);
			$fColCtnitalic->set_align('left');
			$fColCtnitalic->set_num_format(4);
			
			$fColNilaitalic = $this->mrWorkbook->add_format();
			$fColNilaitalic->set_border(1);
			$fColNilaitalic->set_italic();
			$fColNilaitalic->set_size(10);
			$fColNilaitalic->set_align('right');
			$fColNilaitalic->set_num_format(4);
			
			#format widht column
			$this->mWorksheets['Data']->set_column($coll, $coll, 50);
			$this->mWorksheets['Data']->set_column($coll+1, $coll+1, 20);
			
			#set header
			$this->mWorksheets['Data']->write(0, 0, 'Badan Layanan Umum Universitas Sriwijaya', $fTitle);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);
			$row++;	$coll = 0;
			$this->mWorksheets['Data']->write($row, $coll, 'Neraca', $fTitle);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);
			$row++;$coll = 0;
			$this->mWorksheets['Data']->write($row, $coll, 'Untuk Interval Waktu Mulai ' . IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd') , $fFormat);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);
			$row ++;$coll = 0;
			$this->mWorksheets['Data']->write($row, $coll, '(Dinyatakan dalam Rupiah kecuali dinyatakan lain)', $fFormat);
			$this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);
			$row ++;$coll = 0;
			//$this->mWorksheets['Data']->write($row, $coll, 'ASET', $fTitle);
			//$this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll + 1);

		foreach($gridList as $key => $value)
         {

            if ($value['status'] == 'Ya') $aktiva[$value['kelJnsNama']][] = array(
               "nama_kel_lap" => $value['nama_kel_lap'],
               "nilai" => $value['nilai'],
               "kelJnsNama" => $value['kelJnsNama'],
               "kellapId" => $value['kellapId']
            );
            else $kewajiban[$value['kelJnsNama']][] = array(
               "nama_kel_lap" => $value['nama_kel_lap'],
               "nilai" => $value['nilai'],
               "kelJnsNama" => $value['kelJnsNama'],
               "kellapId" => $value['kellapId']
            );
         }
         $totalAktiva = 0;
         $totalKewajiban = 0;

         $row+= 2;
         $coll = 0;
         $this->mWorksheets['Data']->write($row, $coll,GTFWConfiguration::GetValue('language','aset'), $fFormatKelompok);
         //$this->mWorksheets['Data']->write($row, $coll+1, '', $fColNomorbold);
         $this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);
         //$this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll + 1);

         foreach ($aktiva as $key => $value)
         {
            $row++;//= 2;
            $coll = 0;
            $jmlAktiva=0;

            $this->mWorksheets['Data']->write($row, $coll, $key, $fColNomorbold);
            $this->mWorksheets['Data']->write($row+1, $coll, '', $fColNomorbold);
            $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
            $coll++;
            $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language','jumlah_rp') , $fColNomorbold);
            $this->mWorksheets['Data']->write($row+1, $coll, '' , $fColNomorbold);
            $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
            $row++;
			
			$rAwal= $row+ 2;
            foreach ($value as $detilKey => $detilValue)
            {
               $row++;
               $coll = 0;
               $this->mWorksheets['Data']->write($row, $coll, $detilValue['nama_kel_lap'],$fColCtn);
               $coll++;
               $this->mWorksheets['Data']->write($row, $coll, $detilValue['nilai'], $fColNilai);
               $jmlAktiva+= $detilValue['nilai'];
            }
            $rAkhir= $row + 1;
            $row++;
            $coll = 0;
            $this->mWorksheets['Data']->write($row, $coll, "Total ".$key,$fColCtnBold);
            $coll++;
            //$this->mWorksheets['Data']->write($row, $coll, $jmlAktiva , $fColNilai);
            $rTotalA[]= 'B'.($row +1);
            $this->mWorksheets['Data']->write($row, $coll, '=SUM(B'.$rAwal.':B'.$rAkhir.')' , $fColNilaibold );

            $row++;
            $totalAktiva+=$jmlAktiva;
         }
         /* untuk menghitung total */
		 $row++;
		 $coll = 0;
		 $this->mWorksheets['Data']->write($row, $coll,GTFWConfiguration::GetValue('language','jumlah_aktiva'),$fColCtnBold);
         $coll++;
         if(!empty($rTotalA)){
			$totalFAktiva = implode('+',$rTotalA);
		 } else {
			$totalFAktiva = '';
	     }	
         $this->mWorksheets['Data']->write($row, $coll, '=('.$totalFAktiva.')' , $fColNilaibold);
         /** end hitung total **/
            
         $row+= 2;
         $coll = 0;
         $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language','kewajiban_dan_aktiva_bersih'), $fFormatKelompok);
         //$this->mWorksheets['Data']->write($row, $coll+1, '', $fColNomorbold);
         $this->mWorksheets['Data']->merge_cells($row, $coll, $row, $coll + 1);
         //$this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll + 1);

         foreach ($kewajiban as $key => $value)
         {
            $row++;//= 2;
            $coll = 0;
            $jmlKewajiban=0;

            $this->mWorksheets['Data']->write($row, $coll, $key, $fColNomorbold);
            $this->mWorksheets['Data']->write($row+1, $coll,'', $fColNomorbold);
            $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
            $coll++;
            $this->mWorksheets['Data']->write($row, $coll, GTFWConfiguration::GetValue('language','jumlah_rp') , $fColNomorbold);
            $this->mWorksheets['Data']->write($row+1, $coll, '' , $fColNomorbold);
            $this->mWorksheets['Data']->merge_cells($row, $coll, $row + 1, $coll);
            $row++;
			$rAwal= $row+ 2;
            foreach ($value as $detilKey => $detilValue)
            {
               $row++;
               $coll = 0;
               $this->mWorksheets['Data']->write($row, $coll, $detilValue['nama_kel_lap'], $fColCtn);
               $coll++;
               $this->mWorksheets['Data']->write($row, $coll, $detilValue['nilai'] , $fColNilai);
               $jmlKewajiban+= $detilValue['nilai'];
            }
			$rAkhir= $row + 1;	
            $row++;
            $coll = 0;
            $this->mWorksheets['Data']->write($row, $coll, "Total ".$key,$fColCtnBold);
            $coll++;
            //$this->mWorksheets['Data']->write($row, $coll, $jmlKewajiban , $fColNilai);
            $rTotalK[]= 'B'.($row +1);            
            $this->mWorksheets['Data']->write($row, $coll, '=SUM(B'.$rAwal.':B'.$rAkhir.')',$fColNilaibold);

            $row++;
            $totalKewajiban+=$jmlKewajiban;
         }
         
         /* untuk menghitung total */
		 $row++;
		 $coll = 0;
		 $this->mWorksheets['Data']->write($row, $coll,GTFWConfiguration::GetValue('language','jumlah_kewajiban_dan_aktiva_bersih'),$fColCtnBold);
         $coll++;
         if(!empty($rTotalK)){
			$totalFKewajiban = implode('+',$rTotalK);
		 }	else {
			 $totalFKewajiban = '';
		 }
         $this->mWorksheets['Data']->write($row, $coll, '=('.$totalFKewajiban.')' , $fColNilaibold);
         /** end hitung total **/
		}
	}
}
?>
