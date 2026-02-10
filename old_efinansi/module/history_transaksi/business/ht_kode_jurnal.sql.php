<?php

//===GET===
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
      transPenanggungJawabNama as penanggung_jawab,
      transIsJurnal as is_jurnal
   FROM 
      transaksi
      INNER JOIN transaksi_tipe_ref ON (ttId = transTtId)
      INNER JOIN transaksi_jenis_ref ON (transjenId = transTransjenId)
   WHERE
      transTransjenId = 1 AND transIsJurnal = 'Y'
      AND transTanggalEntri BETWEEN '%s' AND '%s'
      AND transReferensi LIKE '%s' 
      %s
   ORDER BY tanggal DESC
   LIMIT %s, %s
";

$sql['get_combo_tipe_transaksi'] = "
   SELECT
      ttId as `id`,
      ttNamaTransaksi as `name`,
      (SELECT userunitkerjaRoleId FROM gtfw_user JOIN user_unit_kerja ON userunitkerjaUserId = UserId WHERE UserName = %s) as roleId
   FROM
      transaksi_tipe_ref
   HAVING
      roleId = 1 OR
      (roleId = 1 AND ttId != 5) OR
      (roleId != 1 AND ttId !=4)
   ORDER BY ttId ASC
";