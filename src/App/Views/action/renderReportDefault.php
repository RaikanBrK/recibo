<?php
	if (isset($_SESSION['report'])) {
		foreach ($_SESSION['report'] as $indice => $report) {
			if (isset($report['modelo']) && $report['modelo'] != false) {
				$action = $this->report->getReport($report['name']);
				$model = 'modelo'.$action['modelo'];

				if (method_exists($this->report, $model)) {
					$this->report->$model($action);
				} else {
					$this->report->modelo1($action);
				}
				$this->report->renderModel();
			}
		}
	}
?>