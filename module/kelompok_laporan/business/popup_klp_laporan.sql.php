<?php


$sql['count']     = "
SELECT FOUND_ROWS() AS `count`
";

$sql['get_data']    = "
SELECT
  `kellapId` AS kellap_id,
  `kellapKodeSistem` AS kellap_ks,
  `kellapParentId` AS kellap_pid,
  `kellapNama` AS kellap_nama,
  `kellapLevel` AS kellap_level,
  `kellapOrderBy` AS kellap_order_by,
  `kellapKelompok` AS kellap_kelompok,
  `kellapTipe` AS kellap_tipe,
  `kellapIsTambah` AS kellap_is_tambah,
  `kellapIsSummary` AS kellap_is_summary
FROM `kelompok_laporan_ref` klp
WHERE
( klp.kellapKodeSistem LIKE '%s' OR klp.kellapKodeSistem = '%s')

ORDER BY
  kellapParentId,
  kellapOrderBy
";

$sql['get_kelompok_laporan_root'] ="
SELECT
  `kellapKodeSistem` AS id,
  `kellapNama` AS `name`
FROM `kelompok_laporan_ref`
WHERE 
kellapParentId = 0
ORDER BY `name`
";

?>