<?php
namespace App\Tools;
use MF\Model\Container;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class SendMail {
	protected $email;
	protected $nome;

	public $nameMailer;
	public $subject;
	public $body;

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		$this->$attr = $value;
	}

	public function send() {
		// Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer(true);

		try {
		    //Server settings
		    $mail->isSMTP();
		    $mail->Host       = MAILER['host'];
		    $mail->SMTPAuth   = true;
		    $mail->Username   = MAILER['user'];
		    $mail->Password   = MAILER['password'];
		    $mail->SMTPSecure = MAILER['smtpSecure'];
		    $mail->Port       = MAILER['port'];
		    $mail->CharSet = 'UTF-8';
		    $mail->Encoding = 'base64';

		    //Recipients
		    $nameMailer = $this->__get('nameMailer') ?? '';
		    $mail->setFrom(MAILER['user'], 'Receipts' . $nameMailer);
		    $mail->addAddress($this->__get('email'), $this->__get('nome'));

		    // Content
		    $assunto = $this->__get('subject');
		    $corpo = $this->__get('body');

		    $mail->isHTML(true);
		    $mail->Subject = $assunto ?? $this->subjectDefault();
		    $mail->Body    = $corpo ?? $this->bodyDefault();
		    $mail->AltBody = $this->altBodyDefault();

		    $mail->send();
		} catch (Exception $e) {
		    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}

	public function bodyDefault() {
		return 'This is the HTML message body <b>in bold!</b>';
	}

	public function subjectDefault() {
		return 'Here is the subject';
	}

	public function altBodyDefault() {
		return 'This is the body in plain text for non-HTML mail clients';
	}

	public function modelForgotPassword($dados) {
		$this->__set('subject', "Código de recuperação de conta");
		$this->__set('body', "
			<p>Olá ".$this->__get('nome')."!</p>
			<p>Recebemos um pedido de recuperação da conta com o email <i>".$this->__get('email')."</i></p>
			<p>Seu código para recuperar a sua conta é ".$dados['code']."</p>
		");
	}
}