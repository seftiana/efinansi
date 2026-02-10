<?php
	$sql['get_template_name']="
		SELECT templateFile FROM finansi_ref_template WHERE templateCode = '%s' AND templateAktive = 'Y'
	";
?>
