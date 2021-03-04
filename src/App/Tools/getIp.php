<?php
namespace App\Tools;

class getIp {
	static public function getIpUser() {
		// $_SERVER['HTTP_CF_CONNECTING_IP'] # Ip via CloudFlare;
		if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		}

		// $_SERVER['REMOTE_ADDR']; # Ip via site;
		if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != '::1') {
			return $_SERVER['REMOTE_ADDR'];
		}

		// ip local setando para testes;
		return IP_LOCAL;
	}
}
?>