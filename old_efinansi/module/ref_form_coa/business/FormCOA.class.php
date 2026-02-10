<?php
class FormCOA extends Database
{
   protected $mSqlFile;

	function __construct ($connectionNumber=0)
   {
      $this->mSqlFile = 'module/'.Dispatcher::Instance()->mModule.'/business/FormCOA.sql.php';
		parent::__construct($connectionNumber);
	}
   
   function GetComboSignerGroup ()
   {
      $result = $this->Open($this->mSqlQueries['get_combo_signer_group'], array());
      return $result;
   }
   
   function GetCoaListBySearch ($filter, $start, $limit)
   {
      extract($filter);
      $result = $this->Open($this->mSqlQueries['get_coa_list_by_search'], array($keyword, $start, $limit));
      return $result;
   }
   
   function GetUserListBySearch ($filter, $start, $limit)
   {
      extract($filter);
      $result = $this->Open($this->mSqlQueries['get_user_list_by_search'], array($keyword, $start, $limit));
      return $result;
   }
   
   function GetFormListBySearch ($filter, $start, $limit)
   {
      extract($filter);
      $result = $this->Open($this->mSqlQueries['get_form_list_by_search'], array($keyword, $start, $limit));
      return $result;
   }
   
   function GetSearchCount ()
   {
      $result = $this->Open($this->mSqlQueries['get_search_count'], array());
      return $result[0]['total'];
   }
   
   function GetFormDetail ($id)
   {
      $result = $this->Open($this->mSqlQueries['get_form_detail'], array($id));
      if (empty($result)) return false;
      else return $result[0];
   }
   
   function GetFormKomponenCOA ($id)
   {
      $result = $this->Open($this->mSqlQueries['get_form_komponen_coa'], array($id));
      return $result;
   }
   
   function GetFormKomponenSigner ($id)
   {
      $result = $this->Open($this->mSqlQueries['get_form_komponen_signer'], array($id));
      return $result;
   }
   
   /////////
   // Do Function
   /////////
   
   function Add ($data)
   {
      $result = false;
      if ($result)
      {
         $return['status'] = 'success';
         $return['message'][] = 'Penambahan data berhasil!';
      }
      else
      {
         $return['status'] = 'failed';
         $return['message'][] = 'Penambahan data gagal!';
         if (isset($msg)) $return['message'][] = $msg;
      }
      
      return $return;
      extract($data);
      $this->StartTrans();
      
      $result = $this->Execute($this->mSqlQueries['do_add_form'], 
	  	array($formName, $namaJurnal));
      if ($result) $id = $this->Insert_ID();
      if ($result && isset($COA) && is_array($COA))
      {
         $arg = array();
         foreach ($COA as $key => $value) array_push($arg, $id, $key, $value['formCoaDK']);
         $query = str_replace('(%s,%s,%s)',implode(',',array_fill(0,count($COA),'(%s,%s,%s)')),$this->mSqlQueries['do_add_form_coa']);
         $result = $this->Execute($query, $arg);
      }
      /*
      if ($result && isset($Signer) && is_array($Signer))
      {
         $arg = array();
         foreach ($Signer as $key => $value) array_push($arg, $id, $key, $value['formsignSignGroupId']);
         $query = str_replace('(%s,%s,%s)',implode(',',array_fill(0,count($Signer),'(%s,%s,%s)')),$this->mSqlQueries['do_add_form_sign']);
         $result = $this->Execute($query, $arg);
      }
      */
      $this->EndTrans($result);
   }
   
   function Edit ($id, $data)
   {
      extract($data);
      $this->StartTrans();
      
      $result = $this->Execute($this->mSqlQueries['do_edit_form'], 
	  	array( $formName, $namaJurnal, $id));
      if ($result) $result = $this->Execute($this->mSqlQueries['do_delete_form_coa'], array($id));
      if ($result && isset($COA) && is_array($COA))
      {
         $arg = array();
         foreach ($COA as $key => $value) array_push($arg, $id, $key, $value['formCoaDK']);
         $query = str_replace('(%s,%s,%s)',implode(',',array_fill(0,count($COA),'(%s,%s,%s)')),$this->mSqlQueries['do_add_form_coa']);
         $result = $this->Execute($query, $arg);
      }
      /*
      if ($result) $result = $this->Execute($this->mSqlQueries['do_delete_form_sign'], 
	        array($id));
      if ($result && isset($Signer) && is_array($Signer))
      {
         $arg = array();
         foreach ($Signer as $key => $value) array_push($arg, $id, $key, $value['formsignSignGroupId']);
         $query = str_replace('(%s,%s,%s)',implode(',',array_fill(0,count($Signer),'(%s,%s,%s)')),$this->mSqlQueries['do_add_form_sign']);
         $result = $this->Execute($query, $arg);
      }
      */
      $this->EndTrans($result);
      if ($result)
      {
         $return['status'] = 'success';
         $return['message'][] = 'Pengubahan data berhasil!';
      }
      else
      {
         $return['status'] = 'failed';
         $return['message'][] = 'Pengubahan data gagal!';
         if (isset($msg)) $return['message'][] = $msg;
      }
      
      return $return;
   }
   
   function Delete ($id)
   {
      $result = false;
      if ($result)
      {
         $return['status'] = 'success';
         $return['message'][] = "$count data berhasil dihapus!";
      }
      else
      {
         $return['status'] = 'failed';
         $return['message'][] = 'Penghapusan data gagal!';
         if (isset($msg)) $return['message'] = $msg;
      }
      
      return $return;
      
      $query = str_replace('%s',implode(',',array_fill(0,count($id),'%s')),$this->mSqlQueries['do_delete_form']);
      $result = $this->Execute($query, $id);
      $count = $this->Affected_Rows();
   }
}
?>
