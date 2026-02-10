<?php
require_once GTFWConfiguration::GetValue('application', 'docroot').
   'module/'.Dispatcher::Instance()->mModule.'/business/FormCOA.class.php';

class ProcessInput
{
	function __construct()
   {
		$this->Obj = new FormCOA;
		$this->_POST = $_POST->AsArray();
      if (isset($this->_POST['COA']))
      {
         $debet = $kredit = array();
         foreach ($this->_POST['COA'] AS $key => $value)
            if ($value['formCoaDK'] == 'D')
               $debet[$key] = $value;
            else $kredit[$key] = $value;
         $this->_POST['COA'] = $debet + $kredit;
      }
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
      //if (empty($this->_POST['formCode']) OR empty($this->_POST['formName']))
      if (empty($this->_POST['formName']))
         $msg[] = "Nama form harus diisi";
      
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