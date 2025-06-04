
document.addEventListener("DOMContentLoaded", function() {
	console.log(document.getElementById("search-card-form"));
	document.getElementById("search-card-form").addEventListener("submit", function(event) {
		event.preventDefault();
	
		

		const valor = this.cardName.value.trim();
		if (valor) {
			const base = this.action;
			window.location.href = base.replace("__name__", encodeURIComponent(valor));
		}
	});
});