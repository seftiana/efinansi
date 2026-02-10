<?php
$sql['get_user_info'] ="
SELECT
   UserId,
   RealName,
   UserName,
   Password,
   NoPassword,
   Active,
   ForceLogout,
   PhoneNumber,
   GroupId,
   GroupName,
   unitkerjaId,
   unitkerjaKode,
   unitkerjaNama,
   unitkerjaTipeunitId,
   unitkerjaNamaPimpinan,
   unitKerjaUnitStatusId,
   roleId,
   roleName,
   roleKeterangan
FROM
   gtfw_user
   LEFT JOIN gtfw_group USING (GroupId)
   LEFT JOIN user_unit_kerja ON userunitkerjaUserId = UserId
   LEFT JOIN unit_kerja_ref ON unitkerjaId = userunitkerjaUnitkerjaId
   LEFT JOIN gtfw_role ON roleId = userunitkerjaRoleId
WHERE
   UserId = %s
LIMIT 1
";

$sql['get_combo_signer_group'] = "
SELECT
   signgroupId AS id,
   signgroupLabel AS name
FROM
   finansi_ref_sign_group
ORDER BY
   signgroupLabel ASC
";

$sql['get_coa_list_by_search'] = "
SELECT SQL_CALC_FOUND_ROWS
   coaId,
   coaKodeAkun,
   coaNamaAkun
FROM
   coa
   JOIN (SELECT IFNULL(%s,'') AS keyword) param
   JOIN (SELECT CONCAT(',',GROUP_CONCAT(DISTINCT coaParentAkun),',') AS notAllowedId FROM coa) coaParent
WHERE
   (coaKodeAkun LIKE CONCAT('%%',keyword,'%%') OR
   coaNamaAkun LIKE CONCAT('%%',keyword,'%%')) AND
   LOCATE(CONCAT(',',coaId,','),notAllowedId) = 0
ORDER BY
   coaKodeAkun ASC
LIMIT %s, %s
";

$sql['get_user_list_by_search'] = "
SELECT SQL_CALC_FOUND_ROWS
   UserId,
   RealName,
   unitkerjaNama
FROM
   gtfw_user
   JOIN (SELECT IFNULL(%s,'') AS keyword) param
   LEFT JOIN user_unit_kerja ON userunitkerjaUserId = UserId
   LEFT JOIN unit_kerja_ref ON unitkerjaId = userunitkerjaUnitkerjaId
WHERE
   RealName LIKE CONCAT('%%',keyword,'%%') OR
   UserName LIKE CONCAT('%%',keyword,'%%')
ORDER BY
   RealName ASC
LIMIT %s, %s
";

$sql['get_transaksi_list_by_search'] = "
SELECT SQL_CALC_FOUND_ROWS
   transId,
   transReferensi,
   transCatatan,
   transNilai
FROM
   transaksi
   JOIN (SELECT IFNULL(%s,'') AS keyword) param
   LEFT JOIN (SELECT transId AS childId, transTransRefId AS parentId FROM transaksi) bkk ON parentId = transTransRefId AND transId != childId
   LEFT JOIN transaksi_tipe_ref ON ttId = transTtId
WHERE
   ttKodeTransaksi IN ('CAR', 'AR', 'CLAIM') AND
   transReferensi LIKE CONCAT('%%',keyword,'%%') AND
   parentId IS NULL
ORDER BY
   transReferensi ASC
LIMIT %s, %s
";

$sql['get_search_count'] = "
SELECT FOUND_ROWS() AS total
";

$sql['get_form_komponen_coa'] = "
SELECT
   formCoaCoaId AS coaId,
   formCoaDK AS typeRekening,
   coaKodeAkun AS kodeRekening,
   coaNamaAkun AS namaRekening
FROM
   finansi_ref_form_coa
   LEFT JOIN transaksi_tipe_ref ON ttId = formCoaTTId
   LEFT JOIN coa ON coaId = formCoaCoaId
WHERE
   ttKodeTransaksi = 'AR'
ORDER BY
   formCoaDK,
   coaKodeAkun
";

$sql['get_form_komponen_signer'] = "
SELECT
   formsignUserId,
   IF(RealName != '', RealName, UserName) AS RealName,
   formsignSignGroupId,
   signgroupLabel
FROM
   finansi_ref_form_sign
   LEFT JOIN transaksi_tipe_ref ON ttId = formsignTtId
   LEFT JOIN gtfw_user ON UserId = formsignUserId
   LEFT JOIN finansi_ref_sign_group ON signgroupId = formsignSignGroupId
WHERE
   ttKodeTransaksi = 'AR'
ORDER BY
   signgroupLabel ASC
";

$sql['get_pr_detail_by_pr_number'] = "
SELECT
   prId,
   prNumber,
   prDescription
FROM
   finansi_purchase_request
WHERE
   prNumber = %s
";

$sql['get_pr_detail_by_pr_id'] = "
SELECT
   prId,
   prNumber,
   prDescription
FROM
   finansi_purchase_request
WHERE
   prId = %s
";

$sql['get_count_data'] = "
   SELECT FOUND_ROWS() as total
";

$sql['get_data'] = "
   SELECT
      SQL_CALC_FOUND_ROWS
      transId as id,
      transTanggalEntri as tanggal,
      transReferensi as kkb,
      transjenNama as jenis,
      ttNamaTransaksi as tipe,
      transTtId as tipe_id,
      transCatatan as uraian,
      transNilai as nominal,
      transIsJurnal as is_jurnal
   FROM 
      transaksi
      INNER JOIN transaksi_tipe_ref ON (ttId = transTtId)
      INNER JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
   WHERE
      transTtId IN (12)
      AND transTanggalEntri BETWEEN '%s' AND '%s'
   ORDER BY tanggal
   LIMIT %s, %s
";

$sql['get_sub_account_id_by_code'] = "
SELECT
   bgbuId,
   bgdeptId,
   bgprojectId,
   bgcofId,
   bgdnfId
FROM
   (SELECT 1 AS dummy) dummy
   LEFT JOIN finansi_bg_ref_bu ON bgbuCode = %s
   LEFT JOIN finansi_bg_ref_dept ON bgdeptCode = %s
   LEFT JOIN finansi_bg_ref_project ON bgprojectCode = %s
   LEFT JOIN finansi_bg_ref_cof ON bgcofCode = %s
   LEFT JOIN finansi_bg_ref_donor ON bgdnfCode = %s
";

$sql['get_transaksi_detail'] = "
SELECT
   transId AS transTransRefId,
   transReferensi AS transReferensiParent,
   transCatatan AS transCatatanParent,
   transNilai,
   prId,
   prNumber,
   prDescription
FROM
   transaksi
   LEFT JOIN transaksi_detil_car ON transdetTransId = transId OR transdetTransId = transTransRefId
   LEFT JOIN finansi_purchase_request_det ON prDetId = transdetPrDetId
   LEFT JOIN finansi_purchase_request USING(prId)
WHERE
   transId = %s
";

$sql['get_pr_list_from_transaksi'] = "
SELECT
   prDetId,
   budgetKode,
   prDetDescription,
   prDetSpesification,
   transdetNominal,
   transdetNominal AS prDetExtCost
FROM
   transaksi
   LEFT JOIN transaksi_tipe_ref ON ttId = transTtId
   LEFT JOIN transaksi_detil_car ON transdetTransId = transId OR transdetTransId = transTransRefId
   LEFT JOIN finansi_purchase_request_det ON prDetId = transdetPrDetId
   LEFT JOIN finansi_bg_setup_budget_det ON rqbgdetId = prRqbgdetId
   LEFT JOIN finansi_bg_setup_budget ON rqbgId = rqbgdetBgId
   LEFT JOIN finansi_bg_ref_budget ON budgetId = rqbgdetBgrefId
WHERE
   transId = %s
";

/////////
// Do Query
/////////

$sql['add_transaksi_ar'] = "
INSERT INTO
   transaksi
   (
      transTtId,
      transTransjenId,
      transUnitkerjaId,
      transReferensi,
      transUserId,
      transTanggal,
      transTanggalEntri,
      transDueDate,
      transCatatan,
      transNilai,
      transPenanggungJawabNama,
      transIsJurnal
   )
VALUES
   (
      (SELECT ttId FROM transaksi_tipe_ref WHERE ttKodeTransaksi = 'AR'),
      1,
      %s,
      %s,
      %s,
      NOW(),
      %s,
      %s,
      %s,
      %s,
      %s,
      'Y'
   )
";

$sql['add_transaksi_ar_detail'] = "
INSERT INTO
   transaksi_detil_ar
   (
      transdetTransId,
      transdetTransCarId,
      transdetUserId,
      transdetTglUbah
   )
VALUES
   (
      %s,
      %s,
      %s,
      NOW()
   )
";

$sql['add_pembukuan_referensi'] = "
INSERT INTO
   pembukuan_referensi
   (
      prTransId,
      prUserId,
      prTanggal,
      prKeterangan
   )
VALUES
   (
      %s,
      %s,
      NOW(),
      %s
   )
";

$sql['add_pembukuan_referensi_detail'] = "
INSERT INTO
   pembukuan_detail
   (
      pdPrId,
      pdCoaId,
      pdNilai,
      pdKeterangan,
      pdStatus,
      pdBuId,
      pdCofId,
      pdDeptId,
      pdDonId,
      pdProjId
   )
VALUES
   (
      %s,
      %s,
      %s,
      %s,
      %s,
      %s,
      %s,
      %s,
      %s,
      %s
   )
";
?>