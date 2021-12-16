<?php
$target_dir = "uploads";
$result_dir = "result";
if(!is_dir($target_dir)){
	if(file_exists($target_dir))
		unlink($target_dir);
    mkdir($target_dir, 0777, true);
}
if(!is_dir($result_dir)){
	if(file_exists($result_dir))
		unlink($result_dir);
    mkdir($result_dir, 0777, true);
}
$target_file = md5_file($_FILES["fileToUpload"]["tmp_name"]);
$target_path = $target_dir.'/'.$target_file;
$uploadOk = 1;
$imageFileType = strtolower(pathinfo(basename($_FILES["fileToUpload"]["name"]),PATHINFO_EXTENSION));
if(isset($_POST["submit"])){
	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	if($check !== false) {
		echo "File is an image - " . $check["mime"] . ".";
		$uploadOk = 1;
	} else {
		echo "File is not an image.";
		$uploadOk = 0;
	}
}
if (file_exists($target_path)) {
	echo "Sorry, file already exists.";
	$uploadOk = 0;
}
if ($_FILES["fileToUpload"]["size"] > 5000000) {
	echo "Sorry, your file is too large.";
	$uploadOk = 0;
}
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ){
	echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
	$uploadOk = 0;
}
if($uploadOk == 0){
	echo "Sorry, your file was not uploaded.";
}else{
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_path)) {
		header("Location: ocr3.php?name=".$target_file);
	} else {
		echo "Sorry, there was an error uploading your file.";
	}
}
?>
