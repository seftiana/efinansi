<?php
$sql['get_tahun_pembukuan_periode_aktif']="
   SELECT 
      tppId,
      tppUnitkerjaId,
      tppTanggalAwal,
      tppTanggalAkhir,
      tppIsBukaBuku
   FROM tahun_pembukuan_periode
   WHERE tppIsBukaBuku = 'Y'
";

$sql['get_tahun_pembukuan_periode_selected'] = "
SELECT
   tppId AS tpp_id
FROM tahun_pembukuan_periode
WHERE
    ('%s' BETWEEN tppTanggalAwal AND tppTanggalAkhir) AND
    ('%s' BETWEEN tppTanggalAwal AND tppTanggalAkhir)
";

$sql['set_non_aktif_tahun_pembukuan_periode'] = "
   UPDATE tahun_pembukuan_periode
   SET
    tppIsBukaBuku = 'T'
   WHERE tppId = '%s'
";


$sql['insert_tahun_pembukuan_periode'] = "
   INSERT INTO tahun_pembukuan_periode(tppUnitkerjaId,tppTanggalAwal,tppTanggalAkhir,tppIsBukaBuku)
   VALUES('%s','%s','%s','%s')
";

$sql['get_unit_kerja'] = "
   SELECT unitkerjaId AS id, CONCAT(unitkerjaKode, ' - ' ,unitkerjaNama) AS `name` FROM unit_kerja_ref
";

/*
 * hardkode disable periode tahun pembukuan 2016
 */
$sql['get_count_tahun_pembukuan'] = "
   SELECT COUNT(*) AS total FROM tahun_pembukuan_periode WHERE YEAR(tppTanggalAkhir) > 2020;
";

$sql['get_tahun_pembukuan_is_rolledback'] = "
SELECT
   tppId,
   tppUnitkerjaId,
   tppTanggalAwal,
   tppTanggalAkhir,
   tppIsBukaBuku
FROM tahun_pembukuan_periode
WHERE tppIsRolledBack = 'Y'
";

$sql['update_data_sppu'] = "
UPDATE
   finansi_pa_sppu
SET sppuTppId = '%s'
WHERE sppuTppId = '%s'
";

$sql['update_data_transaksi'] = "
UPDATE
   transaksi
SET transTppId = '%s'
WHERE transTppId = '%s'
";

$sql['delete_tpp_aktif_lama'] = "
DELETE
FROM
   `tahun_pembukuan_periode`
WHERE tppId = '%s'
";
