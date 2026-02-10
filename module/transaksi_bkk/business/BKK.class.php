<?php
require_once GTFWConfiguration::GetValue('application', 'docroot') . 'module/generate_number/business/GenerateNumber.class.php';

class BKK extends Database
{
   protected $mSqlFile;

	function __construct ($connectionNumber = 0)
   {
      $this->mSqlFile = 'module/'.Dispatcher::Instance()->mModule.'/business/BKK.sql.php';
		parent::__construct($connectionNumber);
	}
   
   function GetUserInfo ($userId)
   {
      $result = $this->Open($this->mSqlQueries['get_user_info'], array($userId));
      return $result[0];
   }
   
   function GetComboBisnisUnit ()
   {
      $result = $this->Open($this->mSqlQueries['get_combo_bisnis_unit'], array());
      array_unshift($result, array('id' => 0, 'name' => 'Bisnis Unit'));
      return $result;
   }
   
   function GetComboCOF ()
   {
      $result = $this->Open($this->mSqlQueries['get_combo_cof'], array());
      array_unshift($result, array('id' => 0, 'name' => 'Classification of Fund'));
      return $result;
   }
   
   function GetComboDepartemen ()
   {
      $result = $this->Open($this->mSqlQueries['get_combo_departemen'], array());
      array_unshift($result, array('id' => 0, 'name' => 'Departemen'));
      return $result;
   }
   
   function GetComboDonor ()
   {
      $result = $this->Open($this->mSqlQueries['get_combo_donor'], array());
      array_unshift($result, array('id' => 0, 'name' => 'Donor'));
      return $result;
   }
   
   function GetComboProject ()
   {
      $result = $this->Open($this->mSqlQueries['get_combo_project'], array());
      array_unshift($result, array('id' => 0, 'name' => 'Project'));
      return $result;
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
   
   function GetTransaksiListBySearch ($filter, $start, $limit)
   {
      extract($filter);
      $result = $this->Open($this->mSqlQueries['get_transaksi_list_by_search'], array($transReferensi, $start, $limit));
      return $result;
   }
   
   function GetSearchCount ()
   {
      $result = $this->Open($this->mSqlQueries['get_search_count'], array());
      return $result[0]['total'];
   }
   
   function GetFormKomponenCOA ()
   {
      $result = $this->Open($this->mSqlQueries['get_form_komponen_coa'], array());
      return $result;
   }
   
   function GetFormKomponenSigner ()
   {
      $result = $this->Open($this->mSqlQueries['get_form_komponen_signer'], array());
      return $result;
   }
   
   function GetPRDetail ($data)
   {
      extract($data);
      if ($prNumber != '') $result = $this->Open($this->mSqlQueries['get_pr_detail_by_pr_number'], array($prNumber));
      else $result = $this->Open($this->mSqlQueries['get_pr_detail_by_pr_id'], array($prId));
      return $result[0];
   }
		
	function GetData($offset, $limit, $awal, $akhir) {
		$result = $this->Open($this->mSqlQueries['get_data'], array($awal, $akhir, $offset, $limit));
		return $result;
	}

	function GetCountData() {
		$result = $this->Open($this->mSqlQueries['get_count_data'], array());
		if (!$result) {
			return 0;
		} else {
			return $result[0]['total'];
		}
	}
   
   function GetSubAccountId ($kode)
   {
      if (!isset($this->GetSubAccountIdData[$kode]))
      {
         $arg = explode('-', $kode);
         $result = $this->Open($this->mSqlQueries['get_sub_account_id_by_code'], $arg);
         $this->GetSubAccountIdData[$kode] = $result[0];
      }
      return $this->GetSubAccountIdData[$kode];
   }
   
   function GetTransaksiDetail ($id)
   {
      $result = $this->Open($this->mSqlQueries['get_transaksi_detail'], array($id));
      if (empty($result)) return false;
      $return = $result[0];
      
      $result = $this->Open($this->mSqlQueries['get_pr_list_from_transaksi'], array($id));
      if (is_array($result)) foreach ($result as $PR)
         $return['PR_List'][$PR['prDetId']] = $PR;
      return $return;
   }
   
   /////////
   // Do Function
   /////////
   
   function Add ($data)
   {
      extract($data);
      $userId = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserId();
      $this->StartTrans();
      
      $generateNumber = new GenerateNumber;
      $transReferensi = $generateNumber->GetGenerateNumber('advance_realisasi');
      $arg = array
      (
         $transUnitkerjaId,
         $transReferensi,
         $userId,
         $transTanggal,
         $transDueDate,
         $transCatatan,
         $transNilai,
         $transPenanggungJawabNama,
      );
      $result = $this->Execute($this->mSqlQueries['add_transaksi_ar'], $arg);
      if ($result)
      {
         $id = $this->Insert_ID();
         $arg = array
         (
            $id,
            $transdetTransCarId,
            $userId
         );
         $result = $this->Execute($this->mSqlQueries['add_transaksi_ar_detail'], $arg);
      }
      if ($result) $result = $this->Execute($this->mSqlQueries['add_pembukuan_referensi'], array($id, $userId, $transCatatan));
      if ($result) $idPr = $this->Insert_ID();
      if ($result) foreach ($COA as $pdCoaId => $value)
      {
         if ($value['nominal'] <= 0) continue;
         $arg = array
         (
            $idPr,
            $pdCoaId,
            $value['nominal'],
            $transCatatan,
            $value['typeRekening'],
            $value['bgbuId'],
            $value['bgcofId'],
            $value['bgdeptId'],
            $value['bgdnfId'],
            $value['bgprojectId']
         );
         
         $result = $this->Execute($this->mSqlQueries['add_pembukuan_referensi_detail'], $arg);
         if (!$result) break;
      }
      
      $this->EndTrans($result);
      if ($result)
      {
         $return['status'] = 'success';
         $return['message'][] = 'Pencatatan transaksi berhasil!';
      }
      else
      {
         $return['status'] = 'failed';
         $return['message'][] = 'Pencatatan transaksi gagal!';
         if (isset($msg)) $return['message'][] = $msg;
      }
      
      return $return;
   }
}
?>