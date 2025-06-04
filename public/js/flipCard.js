

document.addEventListener("DOMContentLoaded", function() {
	const buttons = document.getElementsByClassName('flip-card-button');

	Array.from(buttons).forEach(button => {
		button.addEventListener('click', function(event) {
			event.preventDefault(); // Cancel the default action of the <a> tag
			event.stopPropagation();    // Evita que el click se propague al <a>
			const parent = event.target.parentElement;
			if (parent) {
				parent.setAttribute('data-shown-face', parent.getAttribute('data-shown-face') === '0' ? '1' : '0');
			}
		});
	});
});