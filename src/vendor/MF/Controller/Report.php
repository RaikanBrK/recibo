<?php
namespace MF\Controller;
use App\Tools\ReportModel;

class Report extends ReportModel {
	public function __construct() {
		$_SESSION ?? session_start();
	}

	public function createReport($status = 'error', $nameSession = 'undefined', $mensagemReport = '', $redirection = '/', $modelDefault = true) {

		$_SESSION['report'][$nameSession] = [
			'status' => $status,
			'mensagem' => $mensagemReport,
			'modelDefault' => $modelDefault,
		];

		if ($redirection != false) {
			header('location: '. $redirection);
			exit();
		}
	}

	public function getReport($nameSession = 'undefined', $status = '') {

		// Validando retorno
		$retorno = false;
		if ($status != '') {
			$retorno = (isset($_SESSION['report'][$nameSession]) && $_SESSION['report'][$nameSession]['status'] == $status) ? $_SESSION['report'][$nameSession] : false;
		} else {
			$retorno = $_SESSION['report'][$nameSession] ?? false;
		}

		// Verificando retorno;
		if ($retorno) {
			echo "<script src='/js/report.js'></script>";
			unset($_SESSION['report'][$nameSession]);
		}

		return $retorno;
	}
}
