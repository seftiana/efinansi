<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/jurnal/business/Jurnal.class.php';

class ProcJurnal
{
	
	protected $msg;
	
	public $data;
	
	public $moduleName = 'jurnal';
	
	public $moduleHome = 'jurnal';
	
	public $moduleInput = 'inputJurnal';
	
	public $moduleAdd = 'addJurnal';
	
	public $moduleUpdate = 'updateJurnal';
	
	public $moduleDelete = 'deleteJurnal';
	
	public $db; //yang berhubungan ke database

	function ProcJurnal() 
	{ //constructor

		$this->db = new Jurnal;
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
				$i = 0;
				
				foreach($data['kredit']['datalist']['id'] as $key => $val) 
				{
					$data['kredit']['datalist'][$i]['id'] = $val;
					$data['kredit']['datalist'][$i]['nama'] = $data['kredit']['datalist']['nama'][$key];
					$data['kredit']['datalist'][$i]['keterangan'] = $data['referensi_keterangan'];
					$data['kredit']['datalist'][$i]['nilai'] = $data['kredit']['datalist']['nilai'][$key];
					$data['kredit']['datalist'][$i]['kode'] = $data['kredit']['datalist']['kode'][$key];
					$data['kredit']['datalist'][$i]['detail_id'] = $data['kredit']['datalist']['detail_id'][$key];
					$i++;
				}
				unset($data['kredit']['datalist']['id']);
				unset($data['kredit']['datalist']['detail_id']);
				unset($data['kredit']['datalist']['nama']);
				unset($data['kredit']['datalist']['keterangan']);
				unset($data['kredit']['datalist']['nilai']);
				unset($data['kredit']['datalist']['kode']);
			} //end if issetdata list

			
			if (isset($data['kredit']['tambah'])) 
			{
				$i = 0;
				
				foreach($data['kredit']['tambah']['id'] as $key => $val) 
				{
					$data['kredit']['tambah'][$i]['id'] = $val;
					$data['kredit']['tambah'][$i]['nama'] = $data['kredit']['tambah']['nama'][$key];
					$data['kredit']['tambah'][$i]['keterangan'] = $data['referensi_keterangan'];
					$data['kredit']['tambah'][$i]['nilai'] = $data['kredit']['tambah']['nilai'][$key];
					$data['kredit']['tambah'][$i]['kode'] = $data['kredit']['tambah']['kode'][$key];
					$data['kredit']['tambah'][$i]['detail_id'] = $data['kredit']['tambah']['detail_id'][$key];
					$i++;
				}
				unset($data['kredit']['tambah']['id']);
				unset($data['kredit']['tambah']['detail_id']);
				unset($data['kredit']['tambah']['nama']);
				unset($data['kredit']['tambah']['keterangan']);
				unset($data['kredit']['tambah']['nilai']);
				unset($data['kredit']['tambah']['kode']);
			} //end ifisset tambah

			
		} //end if isset post

		
		return $data;
	}
	function Add() 
	{

		#print_r($this->data); exit;
		
		if (isset($this->data['skenario']['id'])) $grp = '&grp=' . Dispatcher::Instance()->Encrypt($this->data['skenario']['id']);
		else $grp = '';
		
		if ($this->validation('Penambahan')) 
		{
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
		
		if (empty($this->data['debet']['coa_id'])) $this->msg.= "Rekening debet belum dipilih.<br/>\r\n";
		
		if (!isset($_POST['data'])) 
		{ //kalo gak ada data yang di POST apa yang mau di  validasi

			$this->msg = $action . ' data gagal dilakukan ';
			
			return false;
		}

		//debug($this->data);
		
		if (trim($this->data['referensi_id']) == '') $this->msg.= 'Data referensi transaksi tidak boleh kosong <br />';
		$totalnilaidatalist = 0;
		$errkredit = false;
		
		if (isset($this->data['kredit']['datalist'])) 
		{
			
			foreach($this->data['kredit']['datalist'] as $key => $val) 
			{
				
				if (trim($val['nilai']) == '') 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  tidak boleh Kosong <br />';
					$errorkredit = true;
				}
				elseif (!is_numeric($val['nilai'])) 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  harus berupa angka <br />';
					$errorkredit = true;
				}
				$totalnilaidatalist+= $val['nilai'];
			}
		}
		$totalnilai = 0;
		
		if (isset($this->data['kredit']['tambah'])) 
		{
			
			foreach($this->data['kredit']['tambah'] as $key => $val) 
			{
				
				if (trim($val['nilai']) == '') 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  tidak boleh Kosong <br />';
					$errorkredit = true;
				}
				elseif (!is_numeric($val['nilai'])) 
				{
					$this->msg.= 'Nilai Kredit Akun <b>' . $val['nama'] . '</b>  harus berupa angka' . $val['nilai'] . '<br />';
					$errorkredit = true;
				}
				$totalnilai+= $val['nilai'];
			}
		}
		elseif ($this->data['action'] == 'add') 
		{
			$this->msg.= 'Anda belum memilih kredit <br />';
			$errorkredit = true;
		}
		$totalnilai+= $totalnilaidatalist;
		
		if (!$errorkredit) 
		if ($totalnilai != $this->data['debet']['nilai']) $this->msg.= 'Debet tidak sama dengan kredit <br />';

		//}
		
		if ($this->msg == '') 
		return true;
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
}
?>
