<?php 
include_once("../demo1_bxaf/config/config.php");

//e.g. file.php?dl=fTKulefnRNpqvf33aCVmKHy_6X-lzrZMGVj5kELAsAE

$id = intval(bxaf_decrypt($_GET['dl'], $BXAF_CONFIG['BXAF_KEY']));

if($id > 0){
	
	$sql = "SELECT `Name`, `Directory`, `Stored_Name` FROM ?n WHERE `bxafStatus` < 5 AND `ID` = ?i";
	$data = $BXAF_CONN->get_row($sql, 'tbl_bxaf_file', $id );
	
	if(is_array($data) && count($data) > 0){
		
		$src = $BXAF_CONFIG['BXAF_UPLOAD_DIR'] . ltrim($data['Directory'], '/') . "" . $data['Stored_Name'];
		
		if (file_exists($src) && is_file($src) && is_readable($src)) {
			
			header('Content-Description: File Transfer');			
			header('Content-Type: ' . bxaf_get_file_mime_type($src)); 
			header('Content-Disposition: attachment; filename="'. $data['Name'] .'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($src));
			
			ob_end_flush();
			readfile($src);
		}
	}
}

?>
