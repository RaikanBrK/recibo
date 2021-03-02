<?php
function road($state = 1) {
	function renderPassRoad($state, $road) {
		return $state > $road ? 'pass-road' : '';
	}

	function renderActiveRoad($state, $road) {
		return $state == $road ? 'active-road' : '';
	}
?>
	<div class="road-circle-help">
		<div class="icon-circle-help icon-1 <?= renderPassRoad($state, 1) ?>" title="Email">
			<i class="far fa-envelope <?= renderActiveRoad($state, 1) ?>"></i>
		</div>
		<div class="icon-circle-help icon-2 <?= renderPassRoad($state, 2) ?>" title="Código">
			<i class="fas fa-envelope-open-text <?= renderActiveRoad($state, 2) ?>"></i>
		</div>
		<div class="icon-circle-help icon-3 <?= renderPassRoad($state, 3) ?>" title="Mudar Senha">
			<i class="fas fa-unlock-alt <?= renderActiveRoad($state, 3) ?>"></i>
		</div>
	</div>
<?php }
?>