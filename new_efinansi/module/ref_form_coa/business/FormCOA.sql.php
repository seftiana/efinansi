<?php
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

$sql['get_form_list_by_search'] = "
SELECT SQL_CALC_FOUND_ROWS
   ttId AS id,
   ttNamaJurnal AS nama_jurnal,
   ttKodeTransaksi AS formCode,
   ttNamaTransaksi AS formName
FROM
   transaksi_tipe_ref
   JOIN (SELECT IFNULL(%s,'') AS keyword) param
WHERE
   ttKodeTransaksi LIKE CONCAT('%%',keyword,'%%') OR
   ttNamaTransaksi LIKE CONCAT('%%',keyword,'%%')
   AND ttIsAktif = 'Y'
ORDER BY
   ttKodeTransaksi ASC
LIMIT %s, %s
";

$sql['get_search_count'] = "
SELECT FOUND_ROWS() AS total
";

$sql['get_form_detail'] = "
SELECT
   ttKodeTransaksi AS formCode,
   ttNamaTransaksi AS formName,
   ttNamaJurnal AS namaJurnal
FROM
   transaksi_tipe_ref
WHERE
   ttId = %s
";

$sql['get_form_komponen_coa'] = "
SELECT
   formCoaCoaId,
   formCoaDK,
   coaKodeAkun,
   coaNamaAkun
FROM
   finansi_ref_form_coa
   LEFT JOIN coa ON coaId = formCoaCoaId
WHERE
   formCoaTTId = %s
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
   LEFT JOIN gtfw_user ON UserId = formsignUserId
   LEFT JOIN finansi_ref_sign_group ON signgroupId = formsignSignGroupId
WHERE
   formsignTtId = %s
ORDER BY
   signgroupLabel ASC
";

/////////
// Do Query
/////////

$sql['do_add_form'] = "
INSERT INTO
   transaksi_tipe_ref (ttNamaTransaksi, ttNamaJurnal)
VALUES
   (%s, %s)
";

$sql['do_add_form_coa'] = "
INSERT INTO
   finansi_ref_form_coa (formCoaTTId, formCoaCoaId, formCoaDK)
VALUES
   (%s,%s,%s)
";

$sql['do_add_form_sign'] = "
INSERT INTO
   finansi_ref_form_sign (formsignTtId, formsignUserId, formsignSignGroupId)
VALUES
   (%s,%s,%s)
";

$sql['do_edit_form'] = "
UPDATE
   transaksi_tipe_ref
SET
   ttNamaTransaksi = %s,
   ttNamaJurnal = %s
WHERE
   ttId = %s
";

$sql['do_delete_form_coa'] = "
DELETE FROM
   finansi_ref_form_coa
WHERE
   formCoaTTId = %s
";

$sql['do_delete_form_sign'] = "
DELETE FROM
   finansi_ref_form_sign
WHERE
   formsignTtId = %s
";

$sql['do_delete_form'] = "
DELETE FROM
   transaksi_tipe_ref
WHERE
   ttId IN (%s)
";
?>
