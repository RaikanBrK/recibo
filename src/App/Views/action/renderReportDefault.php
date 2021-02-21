<?php
	if (isset($_SESSION['report'])) {
		foreach ($_SESSION['report'] as $indice => $report) {
			if ($report['modelDefault']) {
				$action = $this->report->getReport($indice);
				$this->report->modelo1($action);
				$this->report->renderModel();
			}
		}
	}
?>