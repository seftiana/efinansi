<?php
   $sql['input_penyesuaian'] ="
      INSERT INTO `finansi_keu_penyesuaian_setting`
      SET
         `setPenyesuaianKode` = '%s',
         `setPenyesuaianNama` = '%s',
         `setPenyesuaianTotalPenyesuaian` = '%s',
         `setPenyesuaianSisaPenyesuaian` = '%s',
         `setPenyesuaianNilaiPenyesuaian` = '%s',
         `setPenyesuaianUserUbah` = '%s'
   ";

   $sql['update_penyesuaian']= "
      UPDATE `finansi_keu_penyesuaian_setting`
      SET
         `setPenyesuaianKode` = '%s',
         `setPenyesuaianNama` = '%s',
         `setPenyesuaianTotalPenyesuaian` = '%s',
         `setPenyesuaianSisaPenyesuaian` = '%s',
         `setPenyesuaianNilaiPenyesuaian` = '%s',
         `setPenyesuaianUserUbah` = '%s'
      WHERE `setPenyesuaianId` = '%s';
   ";

   $sql['add_detil_penyesuaian'] = "
      INSERT INTO `finansi_keu_penyesuaian_setting_detil`
      SET
         `setDetPenyesuaianPenyesuaianId` = '%s',
         `setDetPenyesuaianCoaId` = '%s',
         `setDetPenyesuaianNominal` = '%s',
         `setDetPenyesuaianTipeCoa` = '%s',
         `setDetPenyesuaianUserUbah` = '%s'
   ";

   $sql['delete_penyesuaian_detil'] = "
      DELETE FROM
         `finansi_keu_penyesuaian_setting_detil`
      WHERE
         `setDetPenyesuaianPenyesuaianId` = '%s'
   ";

   $sql['get_last_mst_id'] = "
      SELECT MAX(setPenyesuaianId) AS setPenyesuaianId FROM finansi_keu_penyesuaian_setting
   ";

   $sql['get_list_penyesuaian'] = "
      SELECT
         SQL_CALC_FOUND_ROWS
         `setPenyesuaianId`,
         `setPenyesuaianKode`,
         `setPenyesuaianNama`,
         `setPenyesuaianTotalPenyesuaian`,
         `setPenyesuaianSisaPenyesuaian`,
         `setPenyesuaianNilaiPenyesuaian`
      FROM `finansi_keu_penyesuaian_setting`
      WHERE
	      setPenyesuaianKode='%s'
      OR
	      setPenyesuaianNama LIKE '%s'
      ORDER BY setPenyesuaianSisaPenyesuaian DESC
      LIMIT %s, %s;
   ";

   $sql['get_search_count'] = "
      SELECT FOUND_ROWS() AS total
   ";

   $sql['get_penyesuaian_by_id'] = "
      SELECT
         `setPenyesuaianId`,
         `setPenyesuaianKode`,
         `setPenyesuaianNama`,
         `setPenyesuaianTotalPenyesuaian`,
         `setPenyesuaianSisaPenyesuaian`,
         `setPenyesuaianNilaiPenyesuaian`
      FROM `finansi_keu_penyesuaian_setting`
      WHERE
	      setPenyesuaianId = '%s'
   ";

   $sql['get_penyesuaian_detil_by_mst_id'] = "
      SELECT
         `setDetPenyesuaianCoaId` AS coaId,
	      `coaKodeAkun` AS kodeRekening,
	      `coaNamaAkun` AS namaRekening,
         `setDetPenyesuaianNominal` AS nominal,
         IF(`setDetPenyesuaianTipeCoa`='D','debet','kredit') AS typeRekening
      FROM `finansi_keu_penyesuaian_setting_detil`
      LEFT JOIN coa ON setDetPenyesuaianCoaId = coaId
      WHERE
         setDetPenyesuaianPenyesuaianId = '%s'
   ";

   $sql['delete_penyesuaian'] = "
      DELETE
      FROM
         `finansi_keu_penyesuaian_setting`
      WHERE
         `setPenyesuaianId` = '%s'
   ";
?>