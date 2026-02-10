<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/business/BKK.class.php';

class ProcessInput
{
	function __construct()
   {
		$this->Obj = new BKK;
		$this->_POST = $_POST->AsArray();
      $this->_POST['transTanggal'] = $this->_POST['transTanggal_year'] . '-' . $this->_POST['transTanggal_mon'] . '-' . $this->_POST['transTanggal_day'];
      $this->_POST['transDueDate'] = $this->_POST['transDueDate_year'] . '-' . $this->_POST['transDueDate_mon'] . '-' . $this->_POST['transDueDate_day'];
	}
   
   function GenerateReturnParam ($return)
   {
      if ($return['status'] == 'success')
         $return['message'] = array(1 => $return['message'], 'notebox-done');
      elseif ($return['status'] == 'failed')
         $return['message'] = array(1 => $return['message'], 'notebox-warning');
      else $return['message'] = array($this->_POST, $return['message'], 'notebox-alert');
      
      return $return;
   }
   
   function CheckInput ()
   {
      if (isset($this->_POST['btnbatal']))
      {
         $return['status'] = 'canceled';
         return $return;
      }
      
      $msg = array();
      if ($this->_POST['transdetTransCarId'] == '')
         $msg[] = "Transaksi CAR yang akan direalisasi belum dipilih!";
      
      if ($this->Obj->IsTransRefExist($this->_POST['transReferensi']))
         array_unshift($msg, "Transaksi dengan kode ".$this->_POST['transReferensi']." sudah ada!");
      
      if (!empty($this->_POST['COA']))
      {
         $total_debet = $total_kredit = 0;
         $subAccountLabel = array
         (
            array('bgbuId', 'Bisnis Unit', '0'),
            array('bgdeptId', 'Departemen', '00'),
            array('bgprojectId', 'Project', '000'),
            array('bgcofId', 'Classification of Fund', '0'),
            array('bgdnfId', 'Donor', '0000')
         );
         foreach ($this->_POST['COA'] as $coaId => $value)
         {
            if ($value['typeRekening'] == 'D') $total_debet += $value['nominal'];
            elseif ($value['typeRekening'] == 'K') $total_kredit += $value['nominal'];
            
            $subAccountError = false;
            $subAccountId = $this->Obj->GetSubAccountId($value['subAccount']);
            $tmp = explode('-', $value['subAccount']);
            foreach ($subAccountLabel as $key=>$label)
            {
               if ($subAccountId[$label[0]] == '') $tmp[$key] = $label[2];
               if (!$subAccountError AND $tmp[$key] == $label[2])
                  $msg[] = $subAccountError = "Kode Sub Account untuk untuk COA $value[namaRekening] salah!";
            }
            $this->_POST['COA'][$coaId]['subAccount'] = implode('-', $tmp);
            $this->_POST['COA'][$coaId] += $subAccountId;
         }
         if ($total_debet != $total_kredit) $msg[] = "Debet dan kredit tidak sesuai!";
         if ($total_debet != $this->_POST['transNilai']) $msg[] = "Debet dan kredit tidak sesuai dengan Nominal!";
      }
      else $msg[] = "Tabel COA belum dipilih!";
      
      if (!empty($msg))
      {
         $return['status'] = 'redo';
         if (isset($_GET['id'])) $return['id'] = (float) $_GET['id']->Raw();
         $return['message'] = array($this->_POST, $msg, 'notebox-alert');
         return $return;
      }
      
      return array();
   }
   
   function Add ()
   {
      $return = $this->CheckInput();
      if (!empty($return)) return $return;
      
      $return = $this->Obj->Add($this->_POST);
      return $this->GenerateReturnParam($return);
   }
   
   function Edit ()
   {
      $return = $this->CheckInput();
      if (!empty($return)) return $return;
      
      $return = $this->Obj->Edit($_GET['id'], $this->_POST);
      return $this->GenerateReturnParam($return);
   }
   
   function Delete ()
   {
      $return = $this->Obj->Delete($this->_POST['idDelete']);
      return $this->GenerateReturnParam($return);
   }
}
?>