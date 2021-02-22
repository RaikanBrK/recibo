<?php
namespace App\Tools;

class Crypt {
	protected $text;

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		return $this->$attr = $value;
	}

	public function encryptSenha() {
		return password_hash($this->text, PASSWORD_DEFAULT);
	}

	public function encrypt() {
		$textEncrypted = openssl_encrypt($this->text, ENCRYPT['algoritmo'], ENCRYPT['chave'], OPENSSL_RAW_DATA, ENCRYPT['iv']);

		return base64_encode($textEncrypted);
	}

	public function decrypt() {
		return openssl_decrypt(base64_decode($this->text), ENCRYPT['algoritmo'], ENCRYPT['chave'], OPENSSL_RAW_DATA, ENCRYPT['iv']);
	}

	public function encryptSenhaComplet() {
		$this->__set('text', $this->encryptSenha());
		$this->__set('text', $this->encrypt());

		return $this->text;
	}
}
?>