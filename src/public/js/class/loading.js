class Loading {
	constructor() {
		this.query;
	}

	getQuery() {
		return this.query;
	}

	renderModel(elem) {
		if (this.query) {
			const element = $(elem);

			element.append(this.getQuery());
		} else {
			console.log('Escolha o modelo de loading!');
		}
	}

	modelSpinner() {
		this.query = `
			<div class="loading">
				<i class="fas fa-spinner loading-spinner"></i>
			</div>
		`;
	}

	modelSpinnerDefault() {
		this.query = `
			<div class="loading">
				<span class="spinner-border"></span>
			</div>
		`;
	}
}