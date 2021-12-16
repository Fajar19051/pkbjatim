<?PHP
header("Content-type: text/plain");
if(isset($_GET['name']) && !empty($_GET['name'])){
	if(!file_exists('uploads/'.$_GET['name']))
		exit("File tidak ditemukan");
	echo shell_exec('python3 ocr.py '.$_GET['name']);
}else header("Location: index.php");
