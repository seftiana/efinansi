<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_transaksi/business/AppLapTransaksi.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewRtfLapTransaksi extends HtmlResponse {	
	function __construct(){
      $this->arrBulan = array("Januari",
                        "Februari",
                        "Maret",
                        "April",
                        "Mei",
                        "Juni", 
                        "Juli",
                        "Agustus",
                        "September",
                        "Oktober",
                        "November",
                        "Desember");
	}
	
	function ProcessRequest() {	
		
		$Obj = new AppLapTransaksi();
		$_GET = $_GET->AsArray();
	
		$key = Dispatcher::Instance()->Decrypt($_GET['key']);
		$tgl_awal = Dispatcher::Instance()->Decrypt($_GET['tgl_awal']);
		$tgl_akhir = Dispatcher::Instance()->Decrypt($_GET['tgl_akhir']);
		$tipeTransaksi = Dispatcher::Instance()->Decrypt($_GET['tipe_transaksi']);
		
		$gridList = $Obj->GetDataCetak($tgl_awal,$tgl_akhir,$key,$tipeTransaksi);
		
		$contents = file_get_contents(GTFWConfiguration::GetValue( 'application', 'docroot')."doc/template_LaporanTransaksi.rtf");
		
		$contents = str_replace('[NAMA_COMPANY]',GTFWConfiguration::GetValue('organization', 'company_name'), $contents);
		$contents = str_replace('[HEADER]','\f1\fs25'.'LAPORAN TRANSAKSI'.'\par', $contents);
		
		$contents = str_replace('[INTERVAL_WAKTU]',' '.IndonesianDate($tgl_awal, 'yyyy-mm-dd').' s/d '.IndonesianDate($tgl_akhir, 'yyyy-mm-dd').'\par', $contents);
		
				for($i=0;$i<count($gridList);$i++){
						$tgl .= ' '.IndonesianDate($gridList[$i]['transaksi_tanggal'], 'yyyy-mm-dd').'\par';
						$ref .= ' '.$gridList[$i]['transaksi_referensi'].'\par';
						$catatan .= '\f1\fs16'.$gridList[$i]['transaksi_catatan'].'\par';
						$tipe .= '\f1\fs16'.$gridList[$i]['transaksi_tipe'].'\par';
						$nilai .= ' '.number_format($gridList[$i]['transaksi_nilai'], 2, ',', '.').'\par';
				}
		
		for($k=1;$k<=count($gridList);$k++){
		$no .= ' '.$k.'\par';}
		//-------------------------------------------------------------------------------
		$contents = str_replace('[NO]',$no, $contents);
		$contents = str_replace('[TGL]',$tgl, $contents);
		$contents = str_replace('[REFERENSI]',$ref, $contents);
		$contents = str_replace('[CATATAN]',$catatan, $contents);
		$contents = str_replace('[TIPE]',$tipe, $contents);
		$contents = str_replace('[NILAI]',$nilai, $contents);
		
		header("Content-type: application/msword");
		header("Content-disposition: inline; filename=laporanTransaksi_".date('d-m-Y').".rtf");
		header("Content-length: " . strlen($contents));
		print $contents;
      
	} 
}
?>
