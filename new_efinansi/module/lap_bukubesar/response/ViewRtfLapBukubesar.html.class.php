<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_bukubesar/business/AppLapBukubesar.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewRtfLapBukubesar extends HtmlResponse {
   
   function ProcessRequest() {
      $Obj = new AppLapBukubesar();
      $_GET = $_GET->AsArray();
      $rekening = Dispatcher::Instance()->Decrypt($_GET['rekening']);
      $tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
      $tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
      
      $data_cetak = $Obj->GetBukuBesarHis($rekening, $tgl_awal, $tgl_akhir);
      $info_coa = $Obj->GetInfoCoa($rekening);

      $return['rekening'] = $rekening;
      $return['tgl_awal'] = $tgl_awal;
      $return['tgl_akhir'] = $tgl_akhir;
      $return['bbhis'] = $data_cetak;
      $return['info_coa'] = $info_coa;
      return $return;
   }
   
   function ParseTemplate($data = NULL) {
   $contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_LapBukuBesar.rtf");
	$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
	$contents = str_replace('[HEADER]','LAPORAN BUKU BESAR', $contents);
   
		$contents = str_replace('[REKENING]', $data['rekening'], $contents);
		$contents = str_replace('[TGL_AWAL]', IndonesianDate($data['tgl_awal'], 'yyyy-mm-dd'), $contents);
        $contents = str_replace('[TGL_AKHIR]', IndonesianDate($data['tgl_akhir'], 'yyyy-mm-dd'), $contents);
        $contents = str_replace('[REKENING]', $data['info_coa']['rekening'], $contents);
        $contents = str_replace('[NO_REKENING]', $data['info_coa']['no_rekening'], $contents);

         
         for ($i=0; $i<count($data['bbhis']); $i++) {
            $data['bbhis'][$i]['saldo_awal'] =number_format($data['bbhis'][$i]['saldo_awal'], 2, ',', '.');
				$data['bbhis'][$i]['debet'] =number_format($data['bbhis'][$i]['debet'], 2, ',', '.');
				$data['bbhis'][$i]['kredit'] =number_format($data['bbhis'][$i]['kredit'], 2, ',', '.');
				$data['bbhis'][$i]['saldo'] =number_format($data['bbhis'][$i]['saldo'], 2, ',', '.');
				$data['bbhis'][$i]['saldo_akhir'] =number_format($data['bbhis'][$i]['saldo_akhir'], 2, ',', '.');
            if ($i % 2 != 0) 
               $data['bbhis'][$i]['class_name'] = 'table-common-even';
            else 
               $data['bbhis'][$i]['class_name'] = '';
            $data['bbhis'][$i]['indodate'] = IndonesianDate($data['bbhis'][$i]['bb_tanggal'], 'yyyy-mm-dd');

            $tglJurnal .=' '.$data['bbhis'][$i]['bb_tanggal'].'\par';
			$listRekening .=' '.$data['bbhis'][$i]['rekening'].'\par';
			$uraian .=' '.$data['bbhis'][$i]['keterangan'].'\par';
			$coa .=' '.$data['bbhis'][$i]['coa'].'\par';
			$referensi .=' '.$data['bbhis'][$i]['referensi'].'\par';
			$saldoAwal .=' '.$data['bbhis'][$i]['saldo_awal'].'\par';
			$debet .=' '.$data['bbhis'][$i]['debet'].'\par';
			$kredit .=' '.$data['bbhis'][$i]['kredit'].'\par';
			$saldoAkhir .=' '.$data['bbhis'][$i]['saldo_akhir'].'\par';
			
         }
		
		$contents = str_replace('[TGL_JURNAL]', $tglJurnal, $contents);
		$contents = str_replace('[LIST_REKENING]', $listRekening, $contents);
		$contents = str_replace('[COA]', $coa, $contents);
		$contents = str_replace('[URAIAN]', $uraian, $contents);
		$contents = str_replace('[REFERENSI]', $referensi, $contents);
		$contents = str_replace('[SALDO_AWAL]', $saldoAwal, $contents);
		$contents = str_replace('[DEBET]', $debet, $contents);
		$contents = str_replace('[KREDIT]', $kredit, $contents);
		$contents = str_replace('[SALDO_AKHIR]', $saldoAkhir, $contents);
		
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanBukuBesar_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents; 
   }
   
}
?>
