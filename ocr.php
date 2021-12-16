<?PHP
// To format
function format($string){
	$string = strtoupper(trim($string));
	$pattern = '/^([A-Z]{1,3})(\s|-)*([1-9][0-9]{0,3})(\s|-)*([A-Z]{0,3}|[1-9][0-9]{1,2})$/i';
	if(preg_match($pattern, $string)){
		return trim(strtoupper(preg_replace($pattern, '$1 $3 $5', $string)));
	}
	// militer dan kepolisian
	$pattern = '/^([0-9]{1,5})(\s|-)*([0-9]{2}|[IVX]{1,5})*/';
	if (preg_match($pattern, $string)) {
		return trim(strtoupper(preg_replace($pattern, '$1-$3', $string)));
	}  
	return null;
}
if(isset($_GET['name']) && !empty($_GET['name'])){
	if(!file_exists('uploads/'.$_GET['name']))
		exit("File tidak ditemukan");
	shell_exec('tesseract uploads/'.$_GET['name'].' result/'.$_GET['name']);
	$res = file_get_contents('result/'.$_GET['name'].'.txt');
	$res = explode("\n",$res);
	foreach($res as $e){
		$formatted = format($e);
		if(!is_null($formatted)){
			echo $formatted;
			exit;
		}
	}
}else header("Location: index.php");