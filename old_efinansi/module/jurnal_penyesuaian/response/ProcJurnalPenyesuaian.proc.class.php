<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal_penyesuaian/business/JurnalPenyesuaian.class.php';

class ProcJurnalPenyesuaian
{
	
	protected $msg;
	
	public $data;
	
	public $moduleName = 'jurnal_penyesuaian';
	public $moduleHome = 'jurnalPenyesuaian';
	public $moduleInput = 'inputJurnalPenyesuaian';
	public $moduleAdd = 'addJurnalPenyesuaian';
	public $moduleUpdate = 'updateJurnalPenyesuaian';
	public $moduleDelete = 'deleteJurnalPenyesuaian';
	private $totalValue;
	
	public $db; //yang berhubungan ke database

	function __construct()
	{ //constructor

		$this->db = new JurnalPenyesuaian;
		$this->data = $this->getPOST();
	}
	function getPOST() 
	{
		$data = false;
		
		if (isset($_POST['data'])) 
		{
			
			if (is_object($_POST['data'])) $data = $_POST['data']->AsArray();
			else $data = $_POST['data'];
			
			if (isset($data['kredit']['datalist'])) 
			{
            $tmp = array();
            foreach ($data['kredit']['datalist'] as $key => $array)
               foreach ($array as $i => $value) $tmp[$i][$key] = $value;
            $data['kredit']['datalist'] = $tmp;
         } //end if issetdata list

			
			if (isset($data['kredit']['tambah'])) 
			{
            $tmp = array();
				foreach ($data['kredit']['tambah'] as $key => $array)
               foreach ($array as $i => $value) $tmp[$i][$key] = $value;
				$data['kredit']['tambah'] = $tmp;
			} //end ifisset tambah

			
			if (isset($data['debet']['datalist'])) 
			{
            $tmp = array();
				foreach ($data['debet']['datalist'] as $key => $array)
               foreach ($array as $i => $value) $tmp[$i][$key] = $value;
				$data['debet']['datalist'] = $tmp;
			} //end if issetdata list

			
			if (isset($data['debet']['tambah'])) 
			{
            $tmp = array();
				foreach ($data['debet']['tambah'] as $key => $array)
               foreach ($array as $i => $value) $tmp[$i][$key] = $value;
				$data['debet']['tambah'] = $tmp;
			} //end ifisset tambah

			
		} //end if isset post
		$data['tgl_transaksi']=$_POST['referensi_tanggal_year'].'-'.$_POST['referensi_tanggal_mon'].'-'.$_POST['referensi_tanggal_day'];
		return $data;
	}
	function Add() 
	{
		
		if (isset($this->data['skenario']['id'])) $grp = '&grp=' . Dispatcher::Instance()->Encrypt($this->data['skenario']['id']);
		else $grp = '';
		
		if ($this->validation('Penambahan')) 
		{
			$this->db->StartTrans();
	
			if(empty($this->data['referensi_id']))
				$add = $this->AutoGenerateKuitansi();
	
			$add = $this->db->DoAdd($this->data, $msg);
			
			if ($add) 
			{
				$this->msg = 'Penambahan data berhasil dilakukan';
				$urlRedirect = $this->generateUrl('msg', false, null, $grp);
			}
			else
			{
				$this->msg = 'Penambahan data gagal dilakukan <br />';
				
				if (is_array($msg)) $this->msg.= $msg['debet']['msg'] . $msg['kredit']['msg'];
				else $this->msg.= $msg;
				$urlRedirect = $this->generateUrl('err', false, null, $grp);

				//$ret = false;
				
			}
		}
		else
		{
			$urlRedirect = $this->generateUrl('err', false, null, $grp);
		}
		
		if ($add) $this->db->EndTrans(true);
		else $this->db->EndTrans(false);
		
		return $urlRedirect;
	}
	function Delete() 
	{
		
		if (isset($_POST['idDelete'])) 
		{
			$grp = Dispatcher::Instance()->Decrypt($_POST['idDelete']);
			$grp2 = Dispatcher::Instance()->Decrypt($_POST->AsArray());
			$update_status_jurnal_dulu = $this->db->UpdateStatusJurnalSetelahDelete('T', $grp2['idDelete']);
			$del = $this->db->DoDelete($grp);
			
			if ($del) 
			{
				$this->msg = 'Penghapusan data berhasil dilakukan';
				$urlRedirect = $this->generateUrl('msg', false, $this->moduleHome);
			}
			else
			{

				#jika gagal delete, kembalikan status seperti semula
				$update_status_awal = $this->db->UpdateStatusJurnalSetelahDelete('Y', $grp2['idDelete']);
				$this->msg = 'Penghapusan data gagal dilakukan';
				$urlRedirect = $this->generateUrl('err', false, $this->moduleHome);
			}
		}
		else
		{
			$this->msg = 'Penghapusan data gagal dilakukan';
			$urlRedirect = $this->generateUrl('err', false, $this->moduleHome);
		}
		
		return $urlRedirect;
	}
	function Update() 
	{
		
		if ($this->validation('Perubahan')) 
		{
			$this->data['tambah']['id'] = Dispatcher::Instance()->Decrypt($this->data['tambah']['id']);
			$update = $this->db->DoUpdate($this->data, $msg);
			
			if ($update) 
			{
				$this->msg = 'Perubahan data berhasil dilakukan';
				$urlRedirect = $this->generateUrl('msg');
			}
			else
			{
				$this->msg = 'Perubahan data gagal dilakukan silahkan ulangi lagi';
				$this->msg.= debug($msg, 1);
				$urlRedirect = $this->generateUrl('err');
			}
		}
		else
		{
			$urlRedirect = $this->generateUrl('err');
		}
		
		return $urlRedirect;
	}
	function validation($action) 
	{
		$this->msg = '';
		
		if (!isset($_POST['data'])) 
		{ //kalo gak ada data yang di POST apa yang mau di  validasi

			$this->msg = $action . ' data gagal dilakukan ';
			
			return false;
		}

		//debug($this->data);
		#if(trim($this->data['referensi_id'])=='')

		#$this->msg .= 'Data referensi transaksi tidak boleh kosong <br />';

		$error = false;
		$totalnilaidebetdatalist = 0;
		
		if (isset($this->data['debet']['datalist'])) 
		{
			
			foreach($this->data['debet']['datalist'] as $key => $val) 
			{
				
				if (trim($val['nilai']) == '') 
				{
					$this->msg.= 'Nilai Debet Akun <b>' . $val['nama'] . '</b>  tidak boleh Kosong <br />';
					$error = true;
				}
				elseif (!is_numeric($val['nilai'])) 
				{
					$this->msg.= 'Nilai Debet Akun <b>' . $val['nama'] . '</b>  harus berupa angka <br />';
					$error = true;
				}
				$totalnilaidebetdatalist+= $val['nilai'];
			}
		}
		$totalnilaidebet = 0;
		
		if (isset($this->data['debet']['tambah'])) 
		{
			
			foreach($this->data['debet']['tambah'] as $key => $val) 
			{
				
				if (trim($val['nilai']) == '') 
				{
					$this->msg.= 'Nilai Debet Akun <b>' . $val['nama'] . '</b>  tidak boleh Kosong <br />';
					$error = true;
				}
				elseif (!is_numeric($val['nilai'])) 
				{
					$this->msg.= 'Nilai Debet Akun <b>' . $val['nama'] . '</b>  harus berupa angka <br />';
					$error = true;
				}
				$totalnilaidebet+= $val['nilai'];
			}
		}
		elseif ($this->data['action'] == 'add') 
		{
			$this->msg.= 'Anda belum memilih Debet <br />';
			$error = true;
		}
		$total_all_debet = $totalnilaidebetdatalist + $totalnilaidebet;
		$totalnilaikreditdatalist = 0;
		
		if (isset($this->data['kredit']['datalist'])) 
		{
			
			foreach($this->data['kredit']['datalist'] as $key => $val) 
			{
				
				if (trim($val['nilai']) == '') 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  tidak boleh Kosong <br />';
					$error = true;
				}
				elseif (!is_numeric($val['nilai'])) 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  harus berupa angka <br />';
					$error = true;
				}
				$totalnilaikreditdatalist+= $val['nilai'];
			}
		}
		$totalnilaikredit = 0;
		
		if (isset($this->data['kredit']['tambah'])) 
		{
			
			foreach($this->data['kredit']['tambah'] as $key => $val) 
			{
				
				if (trim($val['nilai']) == '') 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  tidak boleh Kosong <br />';
					$error = true;
				}
				elseif (!is_numeric($val['nilai'])) 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  harus berupa angka <br />';
					$error = true;
				}
				$totalnilaikredit+= $val['nilai'];
			}
		}
		elseif ($this->data['action'] == 'add') 
		{
			$this->msg.= 'Anda belum memilih kredit <br />';
			$error = true;
		}
		$total_all_kredit = $totalnilaikreditdatalist + $totalnilaikredit;
		$referensi_nilai = $this->data['referensi_nilai'];
		
		//if (!$error) 
		//if ($total_all_debet != $referensi_nilai and trim($this->data['referensi_id']) != '') $this->msg.= 'Jumlah Debet tidak sama dengan nilai referensi <br />';
		
		//if ($total_all_kredit != $referensi_nilai and trim($this->data['referensi_id']) != '') $this->msg.= 'Jumlah Kredit tidak sama dengan nilai referensi <br />';
		if (abs($total_all_kredit - $total_all_debet)>=0.009) $this->msg.= 'Jumlah Kredit tidak sama Debet <br />';

		if ($this->msg == '') 
		{
			
			if (trim($this->data['referensi_id']) == '') $this->totalValue = $total_all_debet;
			
			return true;
		}
		else 
		return false;
	}
	function generateUrl($type, $isHome = false, $url = null, $additional = null) 
	{
		
		if (!is_null($url)) $submodule = $url;
		elseif ($type == 'msg' || $isHome) $submodule = $this->moduleHome;
		else $submodule = $this->moduleInput;
		Messenger::Instance()->Send($this->moduleName, $submodule, 'view', 'html', array(
			$this->data,
			$type,
			$this->msg
		) , Messenger::NextRequest);
		$urlRedirect = Dispatcher::Instance()->GetUrl($this->moduleName, $submodule, 'view', 'html');
		
		if (!is_null($additional)) $urlRedirect.= $additional;
		
		return $urlRedirect;
	}
	function parsingUrl($file) 
	{
		$msg = Messenger::Instance()->Receive($file);
		
		if (!empty($msg)) 
		{
			$tmp['data'] = $msg[0][0];
			$tmp['msg']['action'] = $msg[0][1];
			$tmp['msg']['message'] = $msg[0][2];
			
			return $tmp;
		}
		else
		{
			
			return false;
		}
	}
	function BalikJurnal() 
	{
		$_GET = $_GET->AsArray();
		$jurnal_balik = $this->db->BalikJurnal($_GET['grp']);

		#print_r($jurnal_balik); exit;
		
		if ($jurnal_balik) 
		{
			$this->msg = 'Proses jurnal balik berhasil dilakukan';
			$urlRedirect = $this->generateUrl('msg');
		}
		else
		{
			$this->msg = 'Proses jurnal balik gagal dilakukan';
			$urlRedirect = $this->generateUrl('msg');
		}

		#print_r($tes); exit;
		
		return $urlRedirect;
	}
	function AutoGenerateKuitansi() 
	{
		$userId = trim(Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId());
		$this->data['referensi_id'] = $this->db->AutogenerateTranasaksi($userId, $this->totalValue, $this->data);
		
		if (!empty($this->data['referensi_id'])) 
			return true;
		else 
			return false;
	}
}
?>
