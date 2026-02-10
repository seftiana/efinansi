<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/TahunPembukuan.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/TahunPembukuanPeriode.class.php';
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/tahun_pembukuan/business/BukuBesar.class.php';

class ProsessTahunPembukuan
{
	var $POST;
	
	function SetPost($param){
		$this->POST = $param;
	}
	
	function NeracaBalance(){
		$TahunPembukuan = new TahunPembukuan();

		//rumus balance  aktiva = modal + kewajiba
		#$aktiva = $TahunPembukuan->GetAktiva('Aktiva');
		#$modal = $TahunPembukuan->GetModal('Modal');
		#$kewajiban = $TahunPembukuan->GetKewajiban('Pasiva');

		//cek balance, jika balance maka return fungsi ini true jika tdk balance return false
		//echo 'aktiva'; print_r($aktiva);
		//echo 'modal'; print_r($modal);
		//echo 'passiva / modal'; print_r($kewajiban);
		#if ($aktiva == ($modal + $kewajiban))
		#$result = $TahunPembukuan->GetCheckBalance();

		$result = $TahunPembukuan->GetCheckBalance();
		
		return $result;
	}
	
	function IsTransaksiPosting() 
	{
		$TahunPembukuan = new TahunPembukuan();
		$jml_transaksi_not_jurnal = $TahunPembukuan->GetJumlahTransaksiNotJurnal();
		$jml_jurnal_not_posting = $TahunPembukuan->GetJumlahJurnalNotPosting();
		
		if (($jml_transaksi_not_jurnal == '0') && ($jml_jurnal_not_posting == '0')) 
			return true;
		else			
			return false;
	}
	
	function BukaBuku() {
		// 1. insert tahun pembukuan periode
		// 2. ubah buku besar
		// 3. ubah tahun pembukuan periodenya

		$TahunPembukuan = new TahunPembukuan();
		$TahunPembukuanPeriode = new TahunPembukuanPeriode();
		$BukuBesar = new BukuBesar();
		$user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();

		$tanggal_awal = $this->POST['tanggal_awal_year'] . '-' . $this->POST['tanggal_awal_mon'] . '-' . $this->POST['tanggal_awal_day'];
		$tanggal_akhir = $this->POST['tanggal_akhir_year'] . '-' . $this->POST['tanggal_akhir_mon'] . '-' . $this->POST['tanggal_akhir_day'];
		$param_tahun_periode = array(
			'1',
			$tanggal_awal,
			$tanggal_akhir,
			'Y'
		);
		$TahunPembukuanPeriode->InsertTahunPembukuanPeriode($param_tahun_periode);

		//get tahun periode aktif
		$rs_tpr = $TahunPembukuanPeriode->GetTahunPembukuanPeriodeAktif();
		$id_tahun_aktif = $rs_tpr[0]['tppId'];

		//update tahun pembukuan
		if ($id_tahun_aktif != '') 
		{
			//tampilkan semua coa root terkecil
			$dt_coa = $TahunPembukuan->GetListCoaAsTahunPembukuan();
			if (!empty($dt_coa)) {
				foreach($dt_coa as $row => $value) {
					$is_coa_exist = $TahunPembukuan->IsCoaTahunPembukuanExist($value['coaId']);
					if (empty($is_coa_exist)) {
						//insert coa ke tahun pembukuan jika belum ada
						$defaultSubAcc = str_replace('9','0',GTFWConfiguration::GetValue('application','subAccFormat'));
						$param_insert_tp = array(
							$id_tahun_aktif,
							$value['coaId'],
							'1',
							$user_id
						);
						$TahunPembukuan->InsertTahunPembukuanAsBukaBuku($param_insert_tp,$defaultSubAcc);
					}else{
						//update coa tahun pembukuan jika sudah ada
						$param_update_tp = array(
							$id_tahun_aktif,
							$user_id,
							$value['coaId']
						);
						$TahunPembukuan->UpdateTahunPembukuanAsBukaBuku($param_update_tp);
					}
				}
			}
		}

		//update buku besar
		$BukuBesar->UpdateTahunPeriodeBukuBesar($id_tahun_aktif);

		//update buku besar histori is tahun periode is null
		$BukuBesar->UpdateTahunPeriodeBukuBesarHistoriIsNull($id_tahun_aktif);
	}
	
	function TutupBuku() {
		//1. get data coa terkecil
		//2. get tahun id aktif
		//3. get buku besar as tahun pembukuan
		//4. update tahun pembukuan
		//5. insert tahun pembukuan history
		//6. non aktifkan tahun pembukuan periode

		$TahunPembukuan = new TahunPembukuan();
		$TahunPembukuanPeriode = new TahunPembukuanPeriode();
		$data_coa = $TahunPembukuan->GetAllCoaAsTahunPembukuan();
		$rs_tahun_aktif = $TahunPembukuanPeriode->GetTahunPembukuanPeriodeAktif();
		$user_id = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
		$id_tahun_aktif = $rs_tahun_aktif[0]['tppId'];
		
		if (!empty($data_coa)) 
		{
			
			foreach($data_coa as $row => $value) 
			{
				$coaIsDebetPos = $value['coaIsDebetPositif'];
				$data2 = $TahunPembukuan->GetBukuBesarAsTahunPembukuan($value['coaId']);
				$coa_id = $value['coaId'];
				$coa_unit_kerja = $value['coaUnitkerjaId'];

				#$debet = 0;
				#$kredit = 0;

				if (!empty($data2)) 
				{
					//coaid
					foreach($data2 as $row => $valueX) 
					{

						/*if ($coaIsDebetPos == '1'){
							$debet = $valueX['saldo_akhir'];
							$kredit = 0;
						}else{
							$debet = 0;
							$kredit = $valueX['saldo_akhir'];
						}*/
						
						$saldo_awal = $valueX['saldo_awal'];
						$debet = $valueX['debet'];
						$kredit = $valueX['kredit'];
						$saldo = $valueX['saldo'];
						$saldo_akhir = $valueX['saldo_akhir'];
						$subAcc = $valueX['subacc'];
						
						# set param insert
						$param_insert = array(
								$coa_id,
								$coa_unit_kerja,
								$saldo_awal,
								$debet,
								$kredit,
								$saldo,
								$saldo_akhir,
								$user_id
						);
						
						# set param update
						$param_update = array(
								$saldo_awal,
								$debet,
								$kredit,
								$saldo,
								$saldo_akhir,
								$user_id,
								$coa_id
						);
						
						# cek coa untuk insert/update thn pembukuan
						$cek_coa_thn_pembukuan = $TahunPembukuan->CekCoaTahunPembukuan($coa_id,$subAcc);
						if (empty($cek_coa_thn_pembukuan)){
							$TahunPembukuan->InsertTahunPembukuanByCoaAsTutupBuku($param_insert,$subAcc);
						}else{
							$TahunPembukuan->UpdateTahunPembukuanByCoaAsTutupBuku($param_update,$subAcc);
						}
					}
				}

				# get data dari tahun pembukuan
				$rs_tp = $TahunPembukuan->GetTahunPembukuanFromCoa($coa_id);
				foreach($rs_tp as $val){
					# jika user buka kosong
					$user = ($val['tpBukaBukuUserId'] != '') ? $rs_tp[0]['tpBukaBukuUserId'] : $user = $user_id;
					$subAccTPH = $val['subacc'];
					
					$param_insert_his = array(
							$id_tahun_aktif,
							$coa_id,
							'1',
							$val['tpSaldoAwal'],
							$val['tpDebet'],
							$val['tpKredit'],
							$val['tpSaldo'],
							$val['tpSaldoAkhir'],
							$val['tpAnggaran'],
							$user,
							$user_id
					);
					
					# insert ke tahun pembukuan history dari tahun pembukuan
					$TahunPembukuan->InsertTahunPembukuanHistoryAsTutupBuku($param_insert_his,$subAccTPH);
				}
			}

			# non aktifkan tahun periode aktif
			$TahunPembukuanPeriode->SetNonAktifTahunPembukuanPeriode($id_tahun_aktif);
		}

		//$list_coa = $TahunPembukuan->GetAllCoaBukuBesar($id_tahun_aktif);
		//update buku besar dan tahun pembukuanya tahun periodenya jadi 0 ini ga usah aja, konfirm besok kalu dah jadi
		/*if (!empty($list_coa)){
			foreach ($list_coa as $rLc => $vLc){
				$this->mTahunPembukuanItem->UpdateBukuBesarSetTahunPembukuan($thn_pembukuan_id_awal,$vLc['bbCoaId']);
			}
		}
		$thn_pembukuan_id_awal=0;
		$this->mTahunPembukuanItem->UpdateTahunPembukuanSetTahunPembukuan($thn_pembukuan_id_awal,$UpdId);
		*/
	}

	// do input data
	function InputTahunPembukuan() 
	{

		//jika aksi simpan dan operasi ubah
		
		if ((isset($this->POST['btnsimpan'])) && ($this->POST['btnsimpan'] == 'Tutup Tahun Pembukuan')) 
		{

			//cek transaksi adakah transaksi/jurnal yg blm diposting
			$is_transaksi_ok = $this->IsTransaksiPosting();
			
			if ($is_transaksi_ok == true) 
			{
				$this->POST['done'] = 'ok';

				//jika transaksi dan jurnal sudah diposting semua baru bisa tutup buku
				$this->TutupBuku();
				Messenger::Instance()->Send('tahun_pembukuan', 'TahunPembukuan', 'view', 'html', array(
					$this->POST,
					'Tahun pembukuan berhasil di tutup'
				) , Messenger::NextRequest);
				$urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'TahunPembukuan', 'view', 'html&ascomponent=1');
			}
			else
			{
				Messenger::Instance()->Send('tahun_pembukuan', 'TahunPembukuan', 'view', 'html', array(
					$this->POST,
					'Masih ada transaksi/jurnal yang belum di posting'
				) , Messenger::NextRequest);
				$urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'TahunPembukuan', 'view', 'html&ascomponent=1');
			}
		}
		else 
		if ((isset($this->POST['btnsimpan'])) && ($this->POST['btnsimpan'] == 'Set Tahun Pembukuan Baru')) 
		{

			//cek balance
			$is_balance =  $this->NeracaBalance();
			
			if ($is_balance == true) 
			{
				$this->POST['done'] = 'ok';

				//jika sudah balance bisa di buka buku
				$this->BukaBuku();
				Messenger::Instance()->Send('tahun_pembukuan', 'TahunPembukuan', 'view', 'html', array(
					$this->POST,
					'Pembukaan tahun pembukuan berhasil'
				) , Messenger::NextRequest);
				$urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'TahunPembukuan', 'view', 'html&ascomponent=1');
			}
			else
			{
				Messenger::Instance()->Send('tahun_pembukuan', 'TahunPembukuan', 'view', 'html', array(
					$this->POST,
					'Neraca belum <i>balance</i> !!!<br />Silahkan melakukan <i>setting</i> ulang Tahun Pembukuan.'
				) , Messenger::NextRequest);
				$urlRedirect = Dispatcher::Instance()->GetUrl('tahun_pembukuan', 'TahunPembukuan', 'view', 'html&ascomponent=1');
			}
		}
		
		return $urlRedirect;
	}
}
?>
