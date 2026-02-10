<?php
require_once GTFWConfiguration::GetValue( 'application', 'docroot') . 'module/lap_transaksi/business/AppLapTransaksi.class.php';

class ViewLapTransaksi extends HtmlResponse {

	var $Pesan;

	function TemplateModule() {
		$this->SetTemplateBasedir(GTFWConfiguration::GetValue('application','docroot').'module/lap_transaksi/template');
		$this->SetTemplateFile('view_lap_transaksi.html');
	}

	function ProcessRequest() {
		$Obj = new AppLapTransaksi();

		$arrTipeTransaksi = $Obj->GetDataTipeTransaksi();

		$post = $_POST->AsArray();
		if(!empty($post['key']))
			$key = $post['key'];
		else
			$key = '';
		if(!empty($post['tanggal_awal_day']))
			$tanggal_awal = $post['tanggal_awal_year'] ."-". $post['tanggal_awal_mon'] ."-". $post['tanggal_awal_day'];
		else
			$tanggal_awal = date("Y-01-01");
		if(!empty($post['tanggal_akhir_day']))
			$tanggal_akhir = $post['tanggal_akhir_year'] ."-". $post['tanggal_akhir_mon'] ."-". $post['tanggal_akhir_day'];
		else
			$tanggal_akhir = date("Y-m-d");
		if(!empty($post['tipe_transaksi']))
			$tipeTransaksi = $post['tipe_transaksi'];
		else
			$tipeTransaksi = 'all';

		if(isset($_GET['cari'])) {
			$get_data = $_GET->AsArray();
			$tanggal_awal = $get_data['tgl_awal'];
			$tanggal_akhir = $get_data['tgl_akhir'];
			$key = $get_data['key'];
			$tipeTransaksi = $get_data['tipe_transaksi'];
		}
		//tahun untuk combo
		$tahunTrans = $Obj->GetMinMaxThnTrans();
		Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_awal',
         array($tanggal_awal, $tahunTrans['minTahun'], $tahunTrans['maxTahun']), Messenger::CurrentRequest);

	   Messenger::Instance()->SendToComponent('tanggal', 'Tanggal', 'view', 'html', 'tanggal_akhir',
         array($tanggal_akhir, $tahunTrans['minTahun'], $tahunTrans['maxTahun']), Messenger::CurrentRequest);

		//view
		$itemViewed = 20;
		$currPage = 1;
		$startRec = 0 ;
		if(isset($_GET['page'])) {
			$currPage = (string)$_GET['page']->StripHtmlTags()->SqlString()->Raw();
			$startRec =($currPage-1) * $itemViewed;
		}

		$totalData = $Obj->GetCountData($tanggal_awal, $tanggal_akhir, $key, $tipeTransaksi);
		$data_transaksi = $Obj->GetData($startRec, $itemViewed, $tanggal_awal, $tanggal_akhir, $key, $tipeTransaksi);
		$total_transaksi_nilai = $Obj->GetTotalTransaksiNilai($tanggal_awal, $tanggal_akhir, $key, $tipeTransaksi);
		#print_r($data_transaksi);
		$url = Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule, Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType .
		'&key=' . Dispatcher::Instance()->Encrypt($key) .
		'&tgl_awal=' . Dispatcher::Instance()->Encrypt($tanggal_awal) .
		'&tgl_akhir=' . Dispatcher::Instance()->Encrypt($tanggal_akhir) .
		'&tipe_transaksi=' . Dispatcher::Instance()->Encrypt($tipeTransaksi) .
		'&cari=' . Dispatcher::Instance()->Encrypt(1));

		Messenger::Instance()->SendToComponent(
	        'combobox',
	        'Combobox',
	        'view',
	        'html',
	        'tipe_transaksi',
	        array(
	           'tipe_transaksi',
	           $arrTipeTransaksi,
	           $tipeTransaksi,
	           true,
	           'id="tipe_transaksi" style="width: 250px;"'
	        ),
	        Messenger::CurrentRequest
	    );

		Messenger::Instance()->SendToComponent('paging', 'Paging', 'view', 'html', 'paging_top', array($itemViewed,$totalData, $url, $currPage), Messenger::CurrentRequest);

		$msg = Messenger::Instance()->Receive(__FILE__);
		$this->Pesan = $msg[0][1];
		$this->css = $msg[0][2];

		$return['data_transaksi'] = $data_transaksi;
		$return['total_transaksi_nilai'] = $total_transaksi_nilai;
		$return['start'] = $startRec+1;

		$return['search']['key'] = $key;
		$return['search']['tgl_awal'] = $tanggal_awal;
		$return['search']['tgl_akhir'] = $tanggal_akhir;
		$return['search']['tipe_transaksi'] = $tipeTransaksi;
		return $return;
	}

	function ParseTemplate($data = NULL) {
		$search = $data['search'];
		#echo "<br />ini data nya"; print_r($data['data_transaksi']);
		$this->mrTemplate->AddVar('content', 'KEY', $search['key']);
		$this->mrTemplate->AddVar('content', 'URL_SEARCH',
		Dispatcher::Instance()->GetUrl('lap_transaksi', 'LapTransaksi', 'view', 'html'));

		$this->mrTemplate->AddVar('content', 'URL_CETAK',
		Dispatcher::Instance()->GetUrl('lap_transaksi', 'CetakLapTransaksi', 'view', 'html') .
		'&key=' . Dispatcher::Instance()->Encrypt($search['key']) .
		'&tgl_awal=' . Dispatcher::Instance()->Encrypt($search['tgl_awal']) .
		'&tgl_akhir=' . Dispatcher::Instance()->Encrypt($search['tgl_akhir']) .
		'&tipe_transaksi=' . Dispatcher::Instance()->Encrypt($search['tipe_transaksi']) .
		'&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

		$this->mrTemplate->AddVar('content', 'URL_EXCEL',
		Dispatcher::Instance()->GetUrl('lap_transaksi', 'ExcelLapTransaksi', 'view', 'xlsx') .
		'&key=' . Dispatcher::Instance()->Encrypt($search['key']) .
		'&tgl_awal=' . Dispatcher::Instance()->Encrypt($search['tgl_awal']) .
		'&tgl_akhir=' . Dispatcher::Instance()->Encrypt($search['tgl_akhir']) .
		'&tipe_transaksi=' . Dispatcher::Instance()->Encrypt($search['tipe_transaksi']) .
		'&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

		$this->mrTemplate->AddVar('content', 'URL_RTF',
		Dispatcher::Instance()->GetUrl('lap_transaksi', 'RtfLapTransaksi', 'view', 'html') .
		'&key=' . Dispatcher::Instance()->Encrypt($search['key']) .
		'&tgl_awal=' . Dispatcher::Instance()->Encrypt($search['tgl_awal']) .
		'&tgl_akhir=' . Dispatcher::Instance()->Encrypt($search['tgl_akhir']) .
		'&tipe_transaksi=' . Dispatcher::Instance()->Encrypt($search['tipe_transaksi']) .
		'&cetak=' . Dispatcher::Instance()->Encrypt('yes'));

		if (empty($data['data_transaksi'])) {
			$this->mrTemplate->AddVar('data_transaksi', 'TRANSAKSI_EMPTY', 'YES');
		} else {
			$this->mrTemplate->AddVar('data_transaksi', 'TRANSAKSI_EMPTY', 'NO');
			$data_transaksi = $data['data_transaksi'];

			for ($i=0; $i<sizeof($data['data_transaksi']); $i++) {
				$no = $i+$data['start'];
				$data['data_transaksi'][$i]['number'] = $no;
				if (strtoupper($data['data_transaksi'][$i]['transaksi_is_jurnal']) == 'Y')
					$data['data_transaksi'][$i]['class_td_name'] = 'table-common-even2';
				elseif (strtoupper($data['data_transaksi'][$i]['transaksi_is_jurnal']) == 'T')
					$data['data_transaksi'][$i]['class_td_name'] = '';

				/*if ($no % 2 != 0)
					$data['data_transaksi'][$i]['class_name'] = 'table-common-even';
				else
					$data['data_transaksi'][$i]['class_name'] = '';*/

				$data['data_transaksi'][$i]['transaksi_nilai'] = number_format($data['data_transaksi'][$i]['transaksi_nilai'],2,',','.');
				$data['data_transaksi'][$i]['transaksi_tanggal'] = $this->date2string($data['data_transaksi'][$i]['transaksi_tanggal']);
				$this->mrTemplate->AddVars('data_transaksi_item', $data['data_transaksi'][$i], '');
				$this->mrTemplate->parseTemplate('data_transaksi_item', 'a');
			}
			$this->mrTemplate->AddVar('data_transaksi', 'TOTAL_TRANSAKSI_NILAI', number_format($data['total_transaksi_nilai'],2,',','.'));
		}
	}

	function date2string($date) {
	   $bln = array(
	                1  => 'Januari',
					2  => 'Februari',
					3  => 'Maret',
					4  => 'April',
					5  => 'Mei',
					6  => 'Juni',
					7  => 'Juli',
					8  => 'Agustus',
					9  => 'September',
					10 => 'Oktober',
					11 => 'November',
					12 => 'Desember'
	               );
	   $arrtgl = explode('-',$date);
	   return $arrtgl[2].' '.$bln[(int) $arrtgl[1]].' '.$arrtgl[0];
	}
}
?>
