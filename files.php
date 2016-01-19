<?php 

$BXAF_CONFIG_CUSTOM['SIGNIN_REQUIRED'] = true;

include_once("../demo1_bxaf/config/config.php");





if(isset($_GET['action']) && $_GET['action'] == 'upload_file'){ 

	$errors = array();
	if (!empty($_FILES)) {
		for($i = 0; $i < count($_FILES['Files']['error']); $i++){
			if($_FILES['Files']['error'][$i] == 0){
				$sql_file = "SELECT * FROM `tbl_bxaf_file` WHERE `Table_Name`= ?s AND `Field_Name`= 'Files' AND `Record_ID`= ?i AND `Name` = ?s";
				$data_file = $BXAF_CONN->get_row($sql_file, $BXAF_CONFIG['TBL_BXAF_CONTACT'], intval($BXAF_CONFIG['BXAF_USER_CONTACT_ID']), $_FILES['Files']['name'][$i]);
				if(is_array($data_file) && count($data_file)>1){
					$errors[] = "<li>File '" . $_FILES['Files']['name'][$i] . "' exists. Please rename your file and upload again.</li>"; 
				}
			}
		}
	}
	if(count($errors) > 0){
		echo "<h3>Error:</h3><ul>" . implode("", $errors) . "</ul>";
	}
	else {
		$files = bxaf_upload_files($BXAF_CONFIG['TBL_BXAF_CONTACT'], 'Files', intval($BXAF_CONFIG['BXAF_USER_CONTACT_ID']), $_FILES['Files']);
	}
	exit();
}



else if(isset($_GET['action']) && $_GET['action'] == 'rename_file'){ 

	$sql_file = "SELECT * FROM `tbl_bxaf_file` WHERE `bxafStatus` < 5 AND `Table_Name`= ?s AND `Field_Name`= 'Files' AND `Record_ID`= ?i AND `Name` = ?s AND `ID`!= ?i";
	$data_file = $BXAF_CONN->get_row($sql_file, $BXAF_CONFIG['TBL_BXAF_CONTACT'], intval($BXAF_CONFIG['BXAF_USER_CONTACT_ID']), $_POST['file_name'], intval($_POST['file_rowid']));
	
	if(is_array($data_file) && count($data_file)>1){
		echo "<h3>Error:</h3><p>File '" . $_POST['file_name'] . "' exists. Please rename your file and upload again.</p>";
		exit();
	}
	
	$BXAF_CONN->update('tbl_bxaf_file', array('Name' => $_POST['file_name']), "`ID` = " . intval($_POST['file_rowid']));
	
	exit();
}







$sql = "SELECT * FROM ?n WHERE `bxafStatus` < 5 AND `Table_Name`= ?s AND `Field_Name`= 'Files' AND `Record_ID`= ?i";
$data_server_files = $BXAF_CONN->get_all($sql, 'tbl_bxaf_file', $BXAF_CONFIG['TBL_BXAF_CONTACT'], intval($BXAF_CONFIG['BXAF_USER_CONTACT_ID']));



include_once("page_common_head.php");

?>



<script src="<?php echo $BXAF_CONFIG['BXAF_LIBRARY_URL']; ?>jquery/jquery.form.min.js.php"></script>

<link rel="stylesheet" type="text/css" href="<?php echo $BXAF_CONFIG['BXAF_LIBRARY_URL']; ?>datatables/media/css/jquery.dataTables.min.css">
<script src="<?php echo $BXAF_CONFIG['BXAF_LIBRARY_URL']; ?>datatables/media/js/jquery.dataTables.min.js"></script>

<script src="<?php echo $BXAF_CONFIG['BXAF_LIBRARY_URL']; ?>bootstrap_plugin/bootbox.min.js"></script>


<script type="text/javascript">

$(document).ready(function(){
	
	$('.dataTable').DataTable();
	
	var options_upload_file = { 
		url: '<?php echo $_SERVER['PHP_SELF']; ?>?action=upload_file',
		type: 'post',
		beforeSubmit: function(formData, jqForm, options){
			if($('#Files').val() == ''){
				bootbox.alert('You have to select a file to upload', '');
				return false
			}
			
			return true;
		},
		success: function(responseText, statusText){
			if(responseText != ''){
				bootbox.alert(responseText);
			} else {
				window.location.assign("<?php echo $_SERVER['PHP_SELF']; ?>");	
			}
		}
	}
	
	$('#form_upload_file').ajaxForm(options_upload_file);
	
	
	$(document).on('click', '.edit_name', function(){
		var rowid = $(this).attr('rowid');
		var content = $(this).attr('content');
		$('#myModal_edit_name').modal();
		$('#file_rowid').val(rowid);
		$('#file_name').val(content);
		$('#file_old_name').val(content);
	});
	
	var options_edit_name = { 
		url: '<?php echo $_SERVER['PHP_SELF']; ?>?action=rename_file',
		type: 'post',
		beforeSubmit: function(formData, jqForm, options){
			if($('#file_name').val() == ''){
				bootbox.alert('Please enter a valid file name to continue.');
				return false;	
			}
			else if( $('#file_name').val() == $('#file_old_name').val() ){
				return false;
			}
			return true;
		},
		success: function(responseText, statusText){
			if(responseText != ''){
				bootbox.alert(responseText);
			} else {
				window.location.assign("<?php echo $_SERVER['PHP_SELF']; ?>");	
			}
		}
	}
	$('#form_edit_name').ajaxForm(options_edit_name);
	
});

</script>







<h2>File Management <small><a href="Javascript: void(0);" onClick="$('#form_upload_file').removeClass('hidden');"><i class="fa fa-plus"></i> Upload Files</a></small></h2>

<form id="form_upload_file" enctype="multipart/form-data" role="form" class="hidden form-inline" >
<input class="form-control" type="file" name="Files[]" id="Files" multiple /> 
<button type="submit" class="btn btn-primary">Upload File</button>
<span class="help-block">Tip: multiple files can be selected simultaneously.</span>
</form>






<?php 
	if (is_array($data_server_files) && count($data_server_files) > 0){ 
?>
		<hr>
		<table class="table table-bordered dataTable">
			<thead>
				<tr class="table-info">
					<th>Name</th>
					<th>Type</th>
					<th>Size</th>
				</tr>
			</thead>
			<tbody>
			<?php 
				foreach($data_server_files as $key=>$value){
					echo '
						<tr>
							<td><a href="file.php?dl='.urlencode(bxaf_encrypt($value['ID'], $BXAF_CONFIG['BXAF_KEY'])).'">'.$value['Name'].' <a href="javascript: void(0);" class="edit_name" rowid="'.$value['ID'].'" content="'.htmlentities($value['Name']).'"><i class="fa fa-pencil green"></i></a></td>
							<td>'.$value['Type'].'</td>
							<td>' . bxaf_format_file_size($value['Size']) . '</td>
						</tr>';
				} 
			?>
			</tbody>
		</table>
<?php } else echo "<h3 class='help-block'>No files uploaded yet.</h3>"; ?>
		
		
	


<form id="form_edit_name" enctype="multipart/form-data" role="form">
<div class="modal fade" id="myModal_edit_name" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
		
		    <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				    <span aria-hidden="true">&times;</span>
				    <span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">EDIT FILE NAME</h4>
		    </div>
			
		  	<div class="modal-body p-l-lg" id="myModal_content">
			
				<div class="row m-a-0">
					<div class="col-md-8 col-md-offset-2">
						Please enter the new file name below:
						<input class="form-control" name="file_name" id="file_name" required>
						<input class="form-control hidden" name="file_old_name" id="file_old_name" >
						<input class="form-control hidden" name="file_rowid" id="file_rowid" >
					</div>
				</div>
				
		  	</div>
			
		  	<div class="modal-footer">
				<button type="submit" class="btn btn-primary">SAVE</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">CANCEL</button>
				<button type="reset" class="btn btn-secondary">RESET</button>
		  	</div>
			
		</div>
	</div>
</div>
</form>




<div id='debug'></div>




									
<?php include_once("page_common_footer.php"); ?>
