<?php
	include '../../../wp-load.php';
?>
<?php
$post_id = $_GET['id'];
echo tsfc_showfile($post_id); 
?>