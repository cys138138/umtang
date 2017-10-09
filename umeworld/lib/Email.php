<?php
namespace umeworld\lib;

use Yii;
class Email extends \yii\base\Component{
	public $to = '';
	public $subject = '';
	public $body = '';
	public $cc = '';
	public $bcc = '';
	public $smtpHost = '';
	public $port = '';
	public $username = '';
	public $password = '';
	public $timeOut = 20;

	public function send($aParams){
		$this->to = $aParams['to'];
		$this->subject = $aParams['subject'];
		$this->body = $aParams['body'];
		$this->cc = $aParams['cc'];
		$this->bcc = $aParams['bcc'];


		$return = true;
		$errorInfo = '';
		$eol = "\r\n";
		$headers = 'Content-Type: text/html; charset="utf-8"' . $eol . 'Content-Transfer-Encoding: base64';
		$aHeaders = explode($eol,$headers);
		if($this->body){
			$bodyContent = preg_replace("/^\./", '..', explode($eol, $this->body));
		}

		$aSmtp = array(
		   array('EHLO ' . 'UMFun' 				. $eol, '220,250', 	"HELO error: "),
		   array('AUTH LOGIN' 					. $eol, '334', 		"AUTH error:"),
		   array(base64_encode($this->username) 	. $eol, '334', 		"AUTHENTIFICATION error : "),
		   array(base64_encode($this->password)	. $eol, '235', 		"AUTHENTIFICATION error : "),
		   array('MAIL FROM: <' . $this->username . '>'	. $eol,	'250',		"MAIL FROM error: "),
		);

		$SendArray = explode(',', $this->to);

		if($this->cc){
			$ccArray = explode(',', $this->cc);
			for($i = 0; $i < count($ccArray); $i++){
				$SendArray[]=$ccArray[$i];
			}
		}

		if($this->bcc){
			$bccArray = explode(',', $this->bcc);
			for($i = 0; $i < count($bccArray); $i++){
				$SendArray[]=$bccArray[$i];
			}
		}

		for($i = 0; $i < count($SendArray); $i++){
			if(trim($SendArray[$i])){
				$aSmtp[] = array('RCPT TO: <' . $SendArray[$i] . '>' . $eol, '250', 'RCPT TO error: ');
			}
		}

		$aSmtp[] = array('DATA' . $eol, '354', 'DATA error: ');
		$aSmtp[] = array('From: ' . $this->username . $eol, '', '');
		$aSmtp[] = array('To: '. $this->to . $eol , '', '');
		if($this->cc){
			$aSmtp[] = array('Cc: ' . $this->cc . $eol, '', '');
		}
		if($this->bcc){
			$aSmtp[] = array('Bcc: ' . $this->bcc . $eol, '', '');
		}

		$aSmtp[] = array('Subject: ' . $this->subject . $eol, '', '');

		foreach($aHeaders as $header){
			$aSmtp[] = array($header . $eol, '', '');
		}

		$aSmtp[] = array($eol, '', '');

		if($bodyContent){
			foreach($bodyContent as $b){
				$aSmtp[] = array(base64_encode($b . $eol) . $eol, '', '');
			}
		}

		$aSmtp[] = array('.' . $eol, '250', 'DATA(end)error: ');
		$aSmtp[] = array('QUIT'. $eol, '221', 'QUIT error: ');

		$fsockHandle = fsockopen($this->smtpHost, $this->port, $errno, $errstr, $this->timeOut);
		if($fsockHandle){
			while($result = fgets($fsockHandle, 1024)){
				if(substr($result, 3, 1) == ' '){
					break;
				}
			}

			foreach($aSmtp as $command){
				fputs($fsockHandle, $command[0]);
				if($command[1]){

					while($result = fgets($fsockHandle, 1024)){
						if(substr($result, 3, 1) == ' '){
							break;
						}
					}

					if(!strstr($command[1],substr($result, 0, 3))){
						//The codes below will recorde the detail process, when the response code is not an expected code, please don't delete them.
						//$errorInfo .= $command[2] . $result . $eol;
					}
				}
			}

			fclose($fsockHandle);
		}else{
			$errorInfo .= 'Send Mail Error: Cannot conect to Smtp host: ' . $this->smtpHost . $eol . $errno . $eol . $errstr . $eol;
		}

		if($errorInfo){
			$errorInfo .= 'Mail Data>>>>TO: ' . $this->to . $eol;
			$errorInfo .= 'Mail Data>>>>CC: ' . $this->cc . $eol;
			$errorInfo .= 'Mail Data>>>>BCC: ' . $this->bcc . $eol;
			$errorInfo .= 'Mail Data>>>>SUBJECT: ' . $this->subject . $eol;
			$errorInfo .= 'Mail Data>>>>BODY: ' . $this->body . $eol;
			$return = array();
			$return['error'] = $errorInfo;
		}

		return $return;
	}
}