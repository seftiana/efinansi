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
   SELECT COUNT(*) AS total FROM tahun_pembukuan_periode WHERE YEAR(tppTanggalAkhir) > 2016;
";

?>
