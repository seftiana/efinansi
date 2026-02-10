<?php

require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 
				'module/history_transaksi_realisasi/business/AppTransaksi.class.php';

require_once GTFWConfiguration::GetValue('application', 'docroot') . 'main/function/date.php';

class ViewCetakBuktiTransaksi extends HtmlResponse 
{

	protected $Pesan;

	/*function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/transaksi_realisasi/template');
      $get = $_GET->AsArray(); #print_r($get); exit;
      if($get['tipe'] == 'bkm')
         $template = 'cetak_bkm.html';
      elseif($get['tipe'] == 'bkk')
         $template = 'cetak_bkk.html';
         
		$this->SetTemplateFile($template);
	}*/
   
   public function TemplateBase() 
   {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot') . 
		'module/history_transaksi_realisasi/template');
      #$this->SetTemplateFile('document-print.html');
      #$this->SetTemplateFile('layout-common-print.html');
      $get = $_GET->AsArray(); #print_r($get);
      if($get['tipe'] == 'bkm')
         $template = 'cetak_bkm.html';
      elseif($get['tipe'] == 'bkk')
         $template = 'cetak_bkk.html';
      elseif($get['tipe'] == 'bm')
         $template = 'cetak_bm.html';
      $this->SetTemplateFile($template);
   }
	
	public function ProcessRequest() 
	{
		$Obj = new AppTransaksi();
		$get = $_GET->AsArray();
		$id_trans = Dispatcher::Instance()->Decrypt($get['dataId']);
		$return['transaksi'] = $Obj->GetTransaksiById($id_trans);
		$getPejabat = $Obj->GetPejabatArray();
		///print_r($getPejabat);
		#bkk 
		$return['rektor'] = $get['pejabat_rektorat'];
		$return['bendahara'] = $get['pejabat_bendahara'];
		$return['mengetahui'] = $get['mengetahui'];
		$return['no_pos'] = $get['pos'];
      
		#bkm
		$return['biro_keuangan'] = $get['pejabat_keuangan'];
		$return['dari'] = $get['dari'];
		$return['yang_menerima'] = $get['penerima'];		
      
		#bm
		$return['finansial'] = $get['pejabat_finansial'];
		$return['auditor'] = $get['auditor'];
      
		/**
		 * tambahan
		 * @since 18 november 2013
		 */
		$return['diketahui'] = $getPejabat[$get['pejabat_diketahui']]['nama'];
		$return['diterima'] = $getPejabat[$get['pejabat_diterima']]['nama'];
		$return['diserahkan'] = $getPejabat[$get['pejabat_diserahkan']]['nama'];
		$return['dikeluarkan'] = $getPejabat[$get['pejabat_dikeluarkan']]['nama'];	
		
		$return['jabatan_diketahui'] = $getPejabat[$get['pejabat_diketahui']]['jabatan'];
		$return['jabatan_diterima'] = $getPejabat[$get['pejabat_diterima']]['jabatan'];
		$return['jabatan_diserahkan'] = $getPejabat[$get['pejabat_diserahkan']]['jabatan'];
		$return['jabatan_dikeluarkan'] = $getPejabat[$get['pejabat_dikeluarkan']]['jabatan'];
		
		$return['dibayarkan_kepada'] = $get['dibayarkan_kepada'];
		$return['keterangan'] = $get['keterangan'];
		
		return $return;
	}
	
	public function ParseTemplate($data = NULL) 
	{
      $cetak = $data['transaksi']; #print_r($cetak);
      $this->mrTemplate->AddVar('content', 'PJ', $cetak['penanggung_jawab']);
      $this->mrTemplate->AddVar('content', 'NOMINAL', number_format($cetak['nominal'], 2, ',', '.'));
      $this->mrTemplate->AddVar('content', 'NOMINAL_TERBILANG', $this->terbilang($cetak['nominal']));
      $this->mrTemplate->AddVar('content', 'NO_BUKTI', $cetak['no_kkb']);
      $this->mrTemplate->AddVar('content', 'TANGGAL', IndonesianDate($cetak['tanggal'], 'yyyy-mm-dd'));
      $this->mrTemplate->AddVar('content', 'URAIAN', $cetak['catatan_transaksi']);
      $this->mrTemplate->AddVar('content', 'REKTOR', $data['rektor']);
      $this->mrTemplate->AddVar('content', 'PR', $data['PR']);
      $this->mrTemplate->AddVar('content', 'BENDAHARA', $data['bendahara']);
      $this->mrTemplate->AddVar('content', 'BIRO', $data['biro_keuangan']);
      $this->mrTemplate->AddVar('content', 'DARI', $data['dari']);
      $this->mrTemplate->AddVar('content', 'PENERIMA', $data['yang_menerima']);
      $this->mrTemplate->AddVar('content', 'MENGETAHUI', $data['mengetahui']);
      $this->mrTemplate->AddVar('content', 'NO_POS', $data['no_pos']);
      $this->mrTemplate->AddVar('content', 'FINANSIAL', $data['finansial']);
      $this->mrTemplate->AddVar('content', 'AUDITOR', $data['auditor']);
      
		/**
		 * tambahan
		 * @since 18 november 2013
		 */
		$space = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$space .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$space .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; 
		$space .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; 
		
		$this->mrTemplate->AddVar('content','DIBAYARKAN_KEPADA',$data['dibayarkan_kepada']);
		$this->mrTemplate->AddVar('content','DIKETAHUI',
								(!empty($data['diketahui']) ? $data['diketahui'] : '('.$space.')'));
		$this->mrTemplate->AddVar('content','DITERIMA',
								(!empty($data['diterima']) ? $data['diterima'] : '('.$space.')'));
		$this->mrTemplate->AddVar('content','DISERAHKAN',
								(!empty($data['diserahkan']) ? $data['diserahkan'] : '('.$space.')'));
		$this->mrTemplate->AddVar('content','DIKELUARKAN',
								(!empty($data['dikeluarkan']) ? $data['dikeluarkan'] : '('.$space.')'));
		
		$this->mrTemplate->AddVar('content','JABATAN_DIKETAHUI',$data['jabatan_diketahui']);
		$this->mrTemplate->AddVar('content','JABATAN_DITERIMA',$data['jabatan_diterima']);
		$this->mrTemplate->AddVar('content','JABATAN_DISERAHKAN',$data['jabatan_diserahkan']);
		$this->mrTemplate->AddVar('content','JABATAN_DIKELUARKAN',$data['jabatan_dikeluarkan']);
		
		$this->mrTemplate->AddVar('content','KETERANGAN',$data['keterangan']);
      
	}
	
	public function kekata($x) 
	{
		$x = abs($x);
		$angka = array("", "satu", "dua", "tiga", "empat", "lima",
		"enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($x <12) {
			$temp = " ". $angka[$x];
		} else if ($x <20) {
			$temp = $this->kekata($x - 10). " belas";
		} else if ($x <100) {
			$temp = $this->kekata($x/10)." puluh". $this->kekata($x % 10);
		} else if ($x <200) {
			$temp = " seratus" . $this->kekata($x - 100);
		} else if ($x <1000) {
			$temp = $this->kekata($x/100) . " ratus" . $this->kekata($x % 100);
		} else if ($x <2000) {
			$temp = " seribu" . $this->kekata($x - 1000);
		} else if ($x <1000000) {
			$temp = $this->kekata($x/1000) . " ribu" . $this->kekata($x % 1000);
		} else if ($x <1000000000) {
			$temp = $this->kekata($x/1000000) . " juta" . $this->kekata($x % 1000000);
		} else if ($x <1000000000000) {
			$temp = $this->kekata($x/1000000000) . " milyar" . $this->kekata(fmod($x,1000000000));
		} else if ($x <1000000000000000) {
			$temp = $this->kekata($x/1000000000000) . " trilyun" . $this->kekata(fmod($x,1000000000000));
		}
		return $temp;
   }
   
	public function terbilang($x, $style=4) 
	{
		if($x<0) {
			$hasil = "minus ". trim($this->kekata($x));
		} else {
			$hasil = trim($this->kekata($x));
		}
		switch ($style) {
			case 1:
				$hasil = strtoupper($hasil);
				break;
			case 2:
				$hasil = strtolower($hasil);
				break;
			case 3:
				$hasil = ucwords($hasil);
				break;
			default:
				$hasil = ucfirst($hasil);
				break;
		}
		return $hasil.' Rupiah' ;
	}
}
?>