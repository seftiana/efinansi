<?php
   $sql['insert_user_unit_kerja'] = "
   INSERT INTO user_unit_kerja(userunitkerjaUserId,userunitkerjaUnitkerjaId,userunitkerjaRoleId)
   VALUES('%s','%s','%s');
   ";

   $sql['get_role_user'] = "
   SELECT
      roleId as role_id,
	  roleName as role_name
   FROM
      gtfw_role
	  JOIN user_unit_kerja ON (userunitkerjaRoleId = roleId)
   WHERE
      userunitkerjaUserId=%s
   ";

   $sql['get_unit_kerja_user'] = "
   SELECT
      unitkerjaId as unit_kerja_id,
	  unitkerjaKode as unit_kerja_kode,
	  unitkerjaNama as unit_kerja_nama,
	  unitkerjaParentId as unit_kerja_parent_id,
	  unitkerjaParentId as is_unit_kerja
   FROM
      unit_kerja_ref
	  JOIN user_unit_kerja ON (unitkerjaId = userunitkerjaUnitkerjaId)
   WHERE
      userunitkerjaUserId=%s
   ";

   $sql['get_satker_unit_kerja_user'] = "
   SELECT
		(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS satker_id,
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS satker_kode,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker_nama,
		(if(tempUnitId IS NULL,'-',unitkerjaId)) AS unit_kerja_id,
		(if(tempUnitKode IS NULL,'-',unitkerjaKode)) AS unit_kerja_kode,
		(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unit_kerja_nama,
	  unitkerjaParentId as is_unit_kerja
   FROM
      unit_kerja_ref
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
	  LEFT JOIN user_unit_kerja ON (unitkerjaId = userunitkerjaUnitkerjaId)
   WHERE
      userunitkerjaUserId=%s
   ";

   $sql['get_satker_unit_kerja_user_dua'] = "
   SELECT
		/*(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS satker_id,
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS satker_kode,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker_nama,*/
		(if(tempUnitId IS NULL,unitkerjaId,unitkerjaId)) AS unit_kerja_id,
		(if(tempUnitKode IS NULL,unitkerjaKode,unitkerjaKode)) AS unit_kerja_kode,
		(if(tempUnitNama IS NULL,unitkerjaNama,CONCAT_WS('/ ',tempUnitNama, unitkerjaNama))) AS unit_kerja_nama,
	  unitkerjaParentId as is_unit_kerja
   FROM
      unit_kerja_ref
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
	  LEFT JOIN user_unit_kerja ON (unitkerjaId = userunitkerjaUnitkerjaId)
   WHERE
      userunitkerjaUserId=%s
   ";

   $sql['get_satker_unit_kerja'] = "
   SELECT
		(if(tempUnitId IS NULL,unitkerjaId,tempUnitId)) AS satker_id,
		(if(tempUnitKode IS NULL,unitkerjaKode,tempUnitKode)) AS satker_kode,
		(if(tempUnitNama IS NULL,unitkerjaNama,tempUnitNama)) AS satker_nama,
		(if(tempUnitId IS NULL,'-',unitkerjaId)) AS unit_kerja_id,
		(if(tempUnitKode IS NULL,'-',unitkerjaKode)) AS unit_kerja_kode,
		(if(tempUnitNama IS NULL,'-',unitkerjaNama)) AS unit_kerja_nama,
	  unitkerjaParentId as is_unit_kerja
   FROM
      unit_kerja_ref
		LEFT JOIN
			(SELECT
				unitkerjaId AS tempUnitId,
				unitkerjaKode AS tempUnitKode,
				unitkerjaNama AS tempUnitNama,
				unitkerjaParentId AS tempParentId
			FROM unit_kerja_ref WHERE unitkerjaParentId = 0) tmpUnitKerja ON(unitkerjaParentId=tempUnitId)
   WHERE
      unitkerjaId=%s
   ";

   $sql['update_user_unit_kerja_by_user_id'] = "
   UPDATE user_unit_kerja SET
      userunitkerjaUnitkerjaId = '%s',
      userunitkerjaRoleId = '%s'
   WHERE userunitkerjaUserId = '%s'
   ";

   $sql['get_unit_kerja'] = "
   SELECT
      unitkerjaId as unit_kerja_id,
	  unitkerjaKode as unit_kerja_kode,
	  unitkerjaNama as unit_kerja_nama
   FROM
      unit_kerja_ref
   WHERE
      unitkerjaId =%s
   ";
/**
 * added
 * @since 29 December 2011
 */
$sql['get_total_sub_unit_kerja']="
SELECT
	count(unitkerjaId) as total
FROM unit_kerja_ref
WHERE unitkerjaParentId = %s";

/**
 * added since 2014-06-24
 * @description get unit kerja user, parent-child
 * @param user_id
 */
$sql['get_unit_kerja_ref_user']     = "
SELECT
   unit.unitkerjaId AS id,
   unit.unitkerjaKodeSistem AS kodeSistem,
   unit.unitkerjaKode AS kode,
   unit.unitkerjaNama AS nama,
   unit.unitkerjaTipeunitId AS tipeId,
   tipe.tipeunitNama AS tipeNama,
   unit.unitKerjaUnitStatusId AS statusId,
   `status`.unitStatusNama AS statusNama,
   unit.unitkerjaNamaPimpinan AS namaPimpinan,
   unit.unitKerjaJenisId AS jenisId,
   jenis.unitkerjaJenisNama AS jenisNama,
   COUNT(tmp.unitkerjaId) AS child,
   IF(unit.unitkerjaParentId = '0' OR COUNT(tmp.unitkerjaId) <> 0, 'PARENT', 'CHILD') AS `status`
FROM unit_kerja_ref AS unit
LEFT JOIN user_unit_kerja AS usr
   ON usr.userunitkerjaUnitkerjaId = unit.unitkerjaId
LEFT JOIN unit_kerja_ref AS tmp
   ON SUBSTR(tmp.unitkerjaKodeSistem, 1, LENGTH(CONCAT(unit.unitkerjaKodeSistem, '.'))) = CONCAT(unit.unitkerjaKodeSistem, '.')
LEFT JOIN unit_kerja_jenis AS jenis
   ON jenis.unitKerjaJenisId = unit.unitKerjaJenisId
LEFT JOIN unit_status AS `status`
   ON status.unitStatusId = unit.unitKerjaUnitStatusId
LEFT JOIN tipe_unit_kerja_ref AS tipe
   ON tipe.tipeunitId = unit.unitkerjaTipeunitId
WHERE 1 = 1
AND (usr.userunitkerjaUserId = %s OR 1 = 0)
GROUP BY unit.unitkerjaId
";
?>