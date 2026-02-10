<?php

class AppUser extends Database
{

	protected $mSqlFile = 'module/user/business/appuser.sql.php';
	function __construct($connectionNumber = 0)
	{
		parent::__construct($connectionNumber);
	}
	function GetDataUser($offset, $limit, $userName = '', $realName = '')
	{

		if (($userName != '') and ($realName != '')) $str = ' OR ';
		else $str = ' AND ';
		$sql = sprintf($this->mSqlQueries['get_data_user'], '%s', $str, '%s', '%d', '%d');
		$result = $this->Open($sql, array(
			'%' . $userName . '%',
			'%' . $realName . '%',
			$offset,
			$limit
		));

		return $result;
	}
	function GetDataUserByInstansi($realName, $instansiId, $offset, $limit)
	{
		$result = $this->Open($this->mSqlQueries['get_data_user_by_instansi'], array(
			$realName,
			$instansiId,
			$offset,
			$limit
		));

		return $result;
	}
	function GetDataUserByUsername($userName)
	{
		$result = $this->Open($this->mSqlQueries['get_data_user_by_username'], array(
			$userName
		));

		return $result[0];
	}
	function GetCountDataUser($userName = '', $realName = '')
	{
		$result = $this->Open($this->mSqlQueries['get_count_data_user'], array(
			'%' . $userName . '%',
			'%' . $realName . '%'
		));

		if (!$result)
		{

			return 0;
		}
		else
		{

			return $result[0]['total'];
		}
	}
	function GetCountDataUserByInstansi($realName, $instansiId)
	{
		$result = $this->Open($this->mSqlQueries['get_count_data_user_by_instansi'], array(
			$realName,
			$instansiId
		));

		if (!$result)
		{

			return 0;
		}
		else
		{

			return $result[0]['total'];
		}
	}
	function GetDataUserById($userId)
	{
		$result = $this->Open($this->mSqlQueries['get_data_user_by_id'], array(
			$userId
		));

		return $result;
	}
	function GetNoPegawai($id)
	{

		return $this->Open($this->mSqlQueries['get_no_pegawai'], array(
			$id
		));
	}
	function GetMaxId()
	{
		$rs = $this->Open($this->mSqlQueries['get_max_id'], array());

		return $rs[0]['id'];
	}

	//===DO==
	function DoAddUser($userName, $password, $realName, $description, $active, $groupId)
	{
		$result = $this->Execute($this->mSqlQueries['do_add_user'], array(
			$userName,
			$password,
			$realName,
			$description,
			$active,
			$groupId
		));
		$this->DoAddUserGroup($groupId);

		return $result;
	}
	function DoUpdateUser($userName, $realName, $active, $groupId, $decription, $userId)
	{
		$result = $this->Execute($this->mSqlQueries['do_update_user'], array(
			$userName,
			$realName,
			$active,
			$groupId,
			$decription,
			$userId
		));

		if ($result) $this->DoUpdateUserGroup($groupId, $userId);

		return $result;
	}
	function DoUpdateProfile($realName, $description, $userId)
	{

		return $this->Execute($this->mSqlQueries['do_update_profile'], array(
			$realName,
			$description,
			$userId
		));
	}
	function DoDeleteUser($userId)
	{
		$result = $this->Execute($this->mSqlQueries['do_delete_user'], array(
			$userId
		));

		return $result;
	}

	//tambahan
	function DoUpdatePasswordUser($password, $userId)
	{
		$result = $this->Execute($this->mSqlQueries['do_update_password_user'], array(
			$password,
			$userId
		));
		return $result;
	}
	function DoAddNoPeg($noPeg)
	{

		return $this->Execute($this->mSqlQueries['do_add_no_peg'], array(
			$noPeg
		));
	}
	function DoUpdateAddNoPeg($id, $noPeg)
	{

		return $this->Execute($this->mSqlQueries['do_update_add_no_peg'], array(
			$id,
			$noPeg
		));
	}
	function DoUpdateNoPeg($id, $noPeg)
	{

		return $this->Execute($this->mSqlQueries['do_update_no_peg'], array(
			$noPeg,
			$id
		));
	}
	function DoAddUserGroup($group)
	{
      $this->SetDefaultUserGroup($group);
		return $this->Execute($this->mSqlQueries['add_user_group'], array(
			$group
		));
	}
	function DoUpdateUserGroup($group, $id)
	{
	   $this->UpdateDefaultGroup($group,$id);
		$result = $this->Execute($this->mSqlQueries['update_user_group'], array(
			$group,
			$id
		));
		return $result;
	}

	public function SetDefaultUserGroup($group){
	   $this->Execute($this->mSqlQueries['default_user_group'], array($group));
	   return $this->AffectedRows();
	}

	public function UpdateDefaultGroup($groupId,$userId){
	   $this->Execute($this->mSqlQueries['update_default_user_group'], array($groupId,$userId));
	   return $this->AffectedRows();
	}


}
?>