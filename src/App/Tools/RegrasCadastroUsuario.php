<?php
namespace App\Tools;
use MF\Model\Container;

class RegrasCadastroUsuario {
	protected $nome;
	protected $email;
	protected $senha;
	protected $confirmSenha;
	protected $fail = [];

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		return $this->$attr = $value;
	}

	public function validarAll(Array $array) {
		if (
			isset($array) &&
			isset($array['nome']) && isset($array['email']) &&
			isset($array['senha']) && isset($array['confirmSenha'])
		) {

			$this->__set('nome', trim($array['nome']));
			$this->__set('email', trim($array['email']));
			$this->__set('senha', $array['senha']);
			$this->__set('confirmSenha', $array['confirmSenha']);

			$this->validar('nome', 'validarNome');
			$this->validar('email', 'validarEmail');
			$this->validar('senha', 'validarSenha');
			$this->validar('confirmSenha', 'validarConfirmSenha');

			$fail = $this->__get('fail');

			if (count($fail) == 0) {
				return true;
			}
		}

		return false;
	}

	public function validar($action, $nameFunciton) {
		$dados = $this->$nameFunciton();
		if ($dados['value']) {
			$this->__set($action, $dados['action'] != '' ? $dados['action'] : $this->$action);
		} else {
			$this->addFail($dados['action']);
		}
	}

	public function addFail($action) {
		$fail = $this->__get('fail');
		$fail[$action] = 'error';
		$this->__set('fail', $fail);
	}

	public function validarNome() {
		$name = $this->__get('nome');

		if (strlen($name) >= 5) {
			$nomeSeparado = explode(' ', $name);

			$nome = $nomeSeparado[0];
			$sobrenome = $nomeSeparado[1] ?? false;

			$stringName = $nome . ' ' . ($sobrenome ?? '');
			$name = strtolower($stringName);
			$name = ucwords($name);

			return $this->retorno($name, 'ok', true);
		}

		return $this->retorno('nome');
	}

	public function validarEmail() {
		$email = $this->__get('email');

		if (strpos($email, '@') && strpos($email, '.') && strlen($email) > 5) {
			return $this->retorno('', 'ok', true);
		}

		return $this->retorno('email');
	}

	public function validarSenha() {
		$senha = $this->__get('senha');

		if (strlen($senha) >= 8) {
			return $this->retorno('', 'ok', true);
		}

		return $this->retorno('senha');
	}

	public function validarConfirmSenha() {
		$senha = $this->__get('senha');
		$confirmSenha = $this->__get('confirmSenha');

		if ($senha == $confirmSenha) {
			return $this->retorno('', 'ok', true);
		}

		return $this->retorno('confirmSenha');
	}

	public function retorno($action = '', $code = 'error', $value = false) {
		return [
			"code" => $code,
			"value" => $value,
			"action" => $action,
		];
	}
}
?>