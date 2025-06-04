
document.addEventListener("DOMContentLoaded", function() {
	const texts = document.getElementsByClassName('replace-text-with-symbol');
	// console.log(texts);
	
	Array.from(texts).forEach(text => {
		replaceSymbols(text);
	});
});

function replaceSymbols(htmlText) {
	// console.log(htmlText);
	// console.log(htmlText.innerHTML);

	htmlText.innerHTML = htmlText.innerHTML.replace('\n', ''); // para evitar textos que empiezan con <br>
	htmlText.innerHTML = htmlText.innerHTML.replaceAll('\n', '<br>');
	htmlText.innerHTML = htmlText.innerHTML.replaceAll('(', '<i>(');
	htmlText.innerHTML = htmlText.innerHTML.replaceAll(')', ')</i>');

	const texts = htmlText.innerHTML.split(/[\{\}]/)
	// console.log(texts);
	let finalText = texts[0];
	for (let i = 1; i < texts.length; i+= 2) {
		const symbolText = texts[i];

		const abbr = document.createElement('abbr');
		abbr.className = 'symbol-replacement';
		abbr.innerText = '{' +symbolText + '}'
		abbr.style.backgroundImage = `url(${generateSVG(symbolText.replaceAll('/', ''))})`;
		finalText += abbr.outerHTML;


		finalText += texts[i+1]
	}
	

	htmlText.innerHTML = finalText
}

function generateSVG(text) {
	return "https://svgs.scryfall.io/card-symbols/"+text+".svg";
}