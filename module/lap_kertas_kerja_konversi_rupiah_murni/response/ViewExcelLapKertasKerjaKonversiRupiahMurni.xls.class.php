<?php
/**
 * Class ViewExcelLapKertasKerjaKonversiRupiahMurni
 * @package lap_kertas_kerja_konversi_rupiah_murni
 * @copyright 2011 Gamatechno
 */
 
require_once GTFWConfiguration::GetValue('application', 'docroot') . 
	'module/lap_kertas_kerja_konversi_rupiah_murni/business/'.
	'LapKertasKerjaKonversiRupiahMurni.class.php';

/**
 * Class ViewExcelLapKertasKerjaKonversiRupiahMurni
 * @todo untuk menyetak Lap Kertas Kerja Konversi Rupiah Murni
 */
 class ViewExcelLapKertasKerjaKonversiRupiahMurni extends XlsResponse 
 {
   	var $mWorksheets = array('Data');

   	public function GetFileName() {
      // name it whatever you want
      return 'Lap_Kertas_Kerja_Konversi_RM.xls';
   }

   public function ProcessRequest() 
   {
   		$LapKertarKerjaKonversiRupiahMurni = new LapKertasKerjaKonversiRupiahMurni();

      	if (isset($_GET['idTa'])) 
			$idTahunAnggaran = Dispatcher::Instance()->Decrypt($_GET['idTa']);

        if (isset($_GET['start_rec']))            
        $start_rec = Dispatcher::Instance()->Decrypt($_GET['start_rec']);
        
        if (isset($_GET['item_viewed']))
        $item_viewed = Dispatcher::Instance()->Decrypt($_GET['item_viewed']);  
      	
      	/**
      	 * membuat tanggal
      	 */
      	if(isset($_GET['trans_tanggal'])){
			$trans_tanggal_selected = Dispatcher::Instance()->Decrypt($_GET['trans_tanggal']);
		} else {
			$trans_tanggal_selected= date('Y').'-'.date('m').'-'.date('d');
		}
		$data['tanggal_sekarang']= $trans_tanggal_selected;
      	$data['tahun_anggaran'] = $LapKertarKerjaKonversiRupiahMurni->GetTahunAnggaranById($_GET['idTa']);
 		$data['data'] = 
	  		$LapKertarKerjaKonversiRupiahMurni->GetDataLapKertasKerjaKonversiRupiahMurni($idTahunAnggaran,
		  	$trans_tanggal_selected,$start_rec,$item_viewed);
      
  		
	

		$fColHeader = $this->mrWorkbook->add_format();
		$fColHeader->set_border(1);
		$fColHeader->set_bold();
		$fColHeader->set_size(12);
		$fColHeader->set_align('center');

		$fColHeader2 = $this->mrWorkbook->add_format();
		$fColHeader2->set_bold();
		$fColHeader2->set_align('vcenter');

		$fColText = $this->mrWorkbook->add_format();
		$fColText->set_border(1);
		$fColText->set_bold();
		
		$fColTextCenter = $this->mrWorkbook->add_format();
		$fColTextCenter->set_border(1);
		$fColTextCenter->set_bold();
		$fColTextCenter->set_align('center');

		$fTitle = $this->mrWorkbook->add_format();
	 	$fTitle->set_bold();
	 	$fTitle->set_size(12);
	 	$fTitle->set_align('vcenter');

	 	$formatCurrency = $this->mrWorkbook->add_format();
   		$formatCurrency->set_border(1);
   		$formatCurrency->set_align('right');

		$this->mWorksheets['Data']->write(2, 0, 'KERTAS KERJA KONVERSI RUPIAH MURNI', $fTitle);
  		$this->mWorksheets['Data']->write(4, 0, 'Periode s/d '.$trans_tanggal_selected ,$fColHeader2);
  		

		$this->mWorksheets['Data']->merge_cells(6,0,6,2);
		$this->mWorksheets['Data']->merge_cells(6,3,6,5);
		$this->mWorksheets['Data']->write(6,0,'',$fColHeader);
		$this->mWorksheets['Data']->write(6,1,'SAK',$fColHeader);
		$this->mWorksheets['Data']->write(6,2,'',$fColHeader);
		$this->mWorksheets['Data']->write(6,3,'',$fColHeader);
        $this->mWorksheets['Data']->write(6,4,'SAP',$fColHeader);
        $this->mWorksheets['Data']->write(6,5,'',$fColHeader);
		$row = 7;

		$this->mWorksheets['Data']->write($row,0,'AKUN',$fColHeader);
        $this->mWorksheets['Data']->write($row,1,'URAIAN',$fColHeader);
        $this->mWorksheets['Data']->write($row,2,'JUMLAH',$fColHeader);
        $this->mWorksheets['Data']->write($row,3,'AKUN',$fColHeader);
        $this->mWorksheets['Data']->write($row,4,'URAIAN',$fColHeader);
        $this->mWorksheets['Data']->write($row,5,'JUMLAH',$fColHeader);


        $no =$row+1;
		for ($i = 0;$i < sizeof($data['data']);$i++)
         {
         	$sak_jumlah = number_format($data['data'][$i]['sak_jumlah'],0,',','.');
         	$sap_jumlah = number_format($data['data'][$i]['sap_jumlah'],0,',','.');
            $this->mWorksheets['Data']->write($no,0,$data['data'][$i]['sak_akun'],$fColTextCenter);
       		$this->mWorksheets['Data']->write($no,1,$data['data'][$i]['sak_uraian'],$fColText);
       		$this->mWorksheets['Data']->write($no,2,$sak_jumlah,$formatCurrency);
       		$this->mWorksheets['Data']->write($no,3,$data['data'][$i]['sap_akun'],$fColTextCenter);
       		$this->mWorksheets['Data']->write($no,4,$data['data'][$i]['sap_uraian'],$fColText);
       		$this->mWorksheets['Data']->write($no,5,$sap_jumlah,$formatCurrency);
       		$no++;
         }
   }

}
?>