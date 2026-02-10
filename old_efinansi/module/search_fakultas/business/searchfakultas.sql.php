<?php
$sql['get_data_prodi']=
   "SELECT
      prodiId AS `id`,
      CONCAT(prodiNamaProdi,IFNULL(CONCAT(' - ',IF(jenjangNama IS NULL,jenjangKode,jenjangNama)),'')) AS `name`,
      prodiFakultasId
   FROM 
      pm_program_studi_ref
   LEFT JOIN pm_jenjang ON jenjangId = prodiJenjangId
   ORDER BY prodiNamaProdi ASC";

$sql['get_data_prodi_all']=
   "SELECT
      prodiId AS `id`,
      CONCAT(prodiNamaProdi,IFNULL(CONCAT(' - ',IF(jenjangNama IS NULL,jenjangKode,jenjangNama)),'')) AS `name`
   FROM 
      pm_program_studi_ref
   LEFT JOIN pm_jenjang ON jenjangId = prodiJenjangId
   ORDER BY prodiNamaProdi ASC";

$sql['get_fakultas']=
   "SELECT
      fakultasId AS `id`,
      fakultasNamaFakultas AS `name`
   FROM
      pm_fakultas_ref
   ORDER BY
      fakultasNamaFakultas";
?>