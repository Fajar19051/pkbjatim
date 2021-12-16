<?PHP
set_time_limit(0);
while(@ob_end_flush());
ob_implicit_flush(true);
class ApiClient{
	const CURL_TIMEOUT = 3600;
	const CONNECT_TIMEOUT = 30;
	const UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:94.0) Gecko/20100101 Firefox/94.0";
	private $ch;
	private $url;
	public function __construct($kuki){
		$this->url = 'https://info.dipendajatim.go.id';
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_ENCODING, '');
		//curl_setopt($this->ch, CURLOPT_RESOLVE, ['mall.shopee.co.id:443:124.158.128.34']);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_COOKIEJAR, $kuki);
		curl_setopt($this->ch, CURLOPT_COOKIEFILE, $kuki);
		curl_setopt($this->ch, CURLOPT_USERAGENT, self::UA);
		if(!is_dir('captcha')){
			if(file_exists('captcha'))
				unlink('captcha');
			mkdir('captcha', 0777, true);
		}
		if(!is_dir('captcha_result')){
			if(file_exists('captcha_result'))
				unlink('captcha_result');
			mkdir('captcha_result', 0777, true);
		}
	}
	function get_home(){
		$url = $this->url.'/index.php?page=info_pkb';
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$data = curl_exec($this->ch);
		if(curl_error($this->ch))
			throw new \Exception('cURL error (' . curl_errno($this->ch) . '): ' . curl_error($this->ch));
		return $data;
	}
	function get_captcha(){
		$url = $this->url.'/logic_pkb.php?act=captcha';
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$data = curl_exec($this->ch);
		if(curl_error($this->ch))
			throw new \Exception('cURL error (' . curl_errno($this->ch) . '): ' . curl_error($this->ch));
		return $data;
	}
	function get_captcha_image($arg1){
		$url = $this->url.$arg1;
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$data = curl_exec($this->ch);
		if(curl_error($this->ch))
			throw new \Exception('cURL error (' . curl_errno($this->ch) . '): ' . curl_error($this->ch));
		file_put_contents("captcha/".md5($_SERVER['REMOTE_ADDR']).".jpg",$data);
		if(@is_array(getimagesize("captcha/".md5($_SERVER['REMOTE_ADDR']).".jpg")))
			return true;
		else
			return false;
	}
	function get_hasil($arg1,$arg2){
		$url = $this->url.'/logic_pkb.php?act=cek';
		curl_setopt($this->ch, CURLOPT_URL, $url);
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, 'nopol='.$arg1.'&code='.$arg2);
		$data = curl_exec($this->ch);
		if(curl_error($this->ch))
			throw new \Exception('cURL error (' . curl_errno($this->ch) . '): ' . curl_error($this->ch));
		return $data;
	}
	function isHTML($string){
		return $string != strip_tags($string) ? true:false;
	}
	function isJson($string) {
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}
	function format($string){
		$string = strtoupper(trim($string));
		$pattern = '/^([A-Z]{1,3})(\s|-)*([1-9][0-9]{0,3})(\s|-)*([A-Z]{0,3}|[1-9][0-9]{1,2})$/i';
		if(preg_match($pattern, $string)){
			return trim(strtoupper(preg_replace($pattern, '$1 $3 $5', $string)));
		}
		// militer dan kepolisian
		$pattern = '/^([0-9]{1,5})(\s|-)*([0-9]{2}|[IVX]{1,5})*/';
		if(preg_match($pattern, $string)){
			return trim(strtoupper(preg_replace($pattern, '$1-$3', $string)));
		}  
		return null;
	}
	public function __destruct(){
		curl_close($this->ch);
	}
}
$ke=0;
$api = new ApiClient(md5($_SERVER['REMOTE_ADDR']));
if(isset($_GET['nopol']) && !empty($_GET['nopol']) && !is_null($api->format($_GET['nopol']))) echo "";else exit("NOPOL KOSONG");
try{
	mulai:
	if(!$api->isHTML($api->get_home())) exit("Error get_home(), ada yang salah pada server");

	$get_captcha = $api->get_captcha();
	if(!$api->isHTML($get_captcha)) exit("Error get_captcha(), ada yang salah pada server");

	$doc = new DOMDocument();
	$doc->loadHTML($get_captcha);
	$imageTags = $doc->getElementsByTagName('img');
	if(count($imageTags)<1) exit('Unknown Error, ada yang salah pada server');

	if(!$api->get_captcha_image($imageTags[0]->getAttribute('src'))) exit('Gagal get_captcha_image("'.$tag->getAttribute('src').'")');

	shell_exec('tesseract captcha/'.md5($_SERVER['REMOTE_ADDR']).'.jpg captcha_result/'.md5($_SERVER['REMOTE_ADDR']));
	$res = file_get_contents('captcha_result/'.md5($_SERVER['REMOTE_ADDR']).'.txt');
	preg_match('/^[a-zA-Z0-9]+$/mi', $res, $captcha);
	//if(count($captcha)<1) exit('Gagal baca captcha');
	if(count($captcha)<1) goto mulai;

	$get_hasil = $api->get_hasil($_GET['nopol'],$captcha[0]);
	if(!$api->isJson($get_hasil)) exit('Error get_hasil("'.$_GET['nopol'].'","'.$captcha[0].'"), ada yang salah pada server');

	$get_hasil = json_decode($get_hasil);
	if(isset($get_hasil->html) && !empty($get_hasil->html)){
		$kirim = "";
		$hasil = $get_hasil->html;
		$pecah1 = explode("<h6>",$hasil);
		for($i=1;$i<count($pecah1);$i++){
			$pecah2 = explode("</h6>",$pecah1[$i]);
			$kirim .= "<b>".$pecah2[0]."</b>\n";
			$pecah3 = explode("<tr><td>",$pecah2[1]);
			for($j=1;$j<count($pecah3);$j++){
				$kirim .= explode("<",$pecah3[$j])[0]." : ";
				$kirim .= explode("<",explode(">",$pecah3[$j])[2])[0]."\n";
			}
			$kirim .= "\n";
		}
		echo $kirim;
	}else{
		if(strpos($get_hasil->msg,'kode captcha tidak cocok')!== false)
			goto mulai;
		else{
			echo strip_tags($get_hasil->msg);
		}
	}
}catch(Exception $e){
	print_r($e);
}