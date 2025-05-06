class CardCollection {
	constructor(
		id, name,
		element,
		image, price, type,
		purchasePrice, isFoil, state
	) {
		this.id = id;
		this.name = name;
		this.element = element;
		this.image = image;
		this.price = price;
		this.type = type;
		this.purchasePrice = purchasePrice;
		this.isFoil = isFoil;
		this.state = state;
	}

	updateElement(JSON_DATA) {
		console.log(JSON_DATA);
		
		const cardImage = this.element.querySelector("#card-image");
		if (JSON_DATA["image_uris"] && JSON_DATA["image_uris"]["art_crop"]) {
			cardImage.src = JSON_DATA["image_uris"]["art_crop"];
		} else if (JSON_DATA["card_faces"] && JSON_DATA["card_faces"][0]["image_uris"] && JSON_DATA["card_faces"][0]["image_uris"]["art_crop"]) {
			cardImage.src = JSON_DATA["card_faces"][0]["image_uris"]["art_crop"];
		}

		const cardPrice = this.element.querySelector("#card-price");
		if (JSON_DATA["prices"] && JSON_DATA["prices"]["eur"]) {
			this.price = JSON_DATA["prices"]["eur"];
			cardPrice.innerText = JSON_DATA["prices"]["eur"] + "€";
		}

		if (JSON_DATA["type_line"]) {
			let types = JSON_DATA["type_line"].split("—")[0].trim().split(/\s+/);
			console.log(types);
			if (types[0] == "Legendary") {
				types.shift();
			}
			console.log(types);
			this.type = types[0];
			
		} else {
			this.type = "other"
		}
	}
}

// var cartas = []
// cartas.push(new CardCollection(
// 	"id", "primera criatura",
// 	null,
// 	"image", 0.1, "creature",
// 	0.3, true, 1)
// );
// cartas.push(new CardCollection(
// 	"id", "segunda criatura",
// 	null,
// 	"image", 0.2, "creature",
// 	0.3, true, 1)
// );
// cartas.push(new CardCollection(
// 	"id", "tercera criatura",
// 	null,
// 	"image", 0.3, "creature",
// 	0.3, true, 1)
// );
// cartas.push(new CardCollection(
// 	"id", "primer conjuro",
// 	null,
// 	"image", 0.4, "sorcery",
// 	0.3, true, 1)
// );
// cartas.push(new CardCollection(
// 	"id", "segundo conjuro",
// 	null,
// 	"image", 0.5, "sorcery",
// 	0.3, true, 1)
// );

// console.log(cartas);

// var grupo = "none";
// var grupos = {};
// grupos["none"] = createGroupDiv("none");

// window.onload = function() {
// 	addToBody();
// };

// function addToBody() {
// 	const lista = document.getElementById("lista");
// 	for (let i = 0; i < cartas.length; i++) {
// 		lista.appendChild(cartas[i].element);
// 	}
// }


// function sortBy() {
// 	const sortingMode = document.getElementById("sortMode").value
// 	console.log(sortingMode);
	
// 	switch (sortingMode) {
// 		case "name":
// 			cartas.sort((a, b) => a.name.localeCompare(b.name));
// 			break;
			
// 		case "price":
// 			cartas.sort((a, b) => a.price - b.price);
// 			break;
	
// 		default:
// 			break;
// 	}

// 	addToBody();
// }

// function groupBy() {
// 	grupo = document.getElementById("sortMode").value;
// 	console.log(grupo);

// 	const groups = document.getElementsByClassName("groupMode");
// 	for (let i = 0; i < groups.length; i++) {
// 		groups[i].setAttribute("data-show", 0);
// 	}
	
// 	switch (grupo) {
// 		case "type":
// 			break;
			
// 		case "set":
// 			break;
	
// 		default:
// 			break;
// 	}
	
// 	addToBody();
// }


// function createGroupDiv(group) {
// 	let div = document.createElement("div");
// 	div.setAttribute("data-group-id", group);
// 	div.setAttribute("data-show", 0);
// 	return div;
// }


// <!DOCTYPE html>
// <html lang="en">
// <head>
// 	<meta charset="UTF-8">
// 	<meta name="viewport" content="width=device-width, initial-scale=1.0">
// 	<title>Document</title>

// 	<script src="CardCollection.js"></script>
// 	<script src="Manager.js"></script>

// 	<style>
// 		.group {
// 			border: 5px solid;
// 		}
// 		.group[data-show="0"] {
// 			display: none;
// 		}
// 	</style>
// </head>
// <body>
// 	<button onclick="addToBody()">mostrar lista</button>
// 	<select id="sortMode" onchange="sortBy()">
// 		<option value="name">Nombre</option>
// 		<option value="price">Precio</option>
// 	</select>
// 	<select id="groupMode" onchange="groupBy()">
// 		<option value="none">No agrupar</option>
// 		<option value="type">Tipo</option>
// 		<option value="set">Expansion</option>
// 	</select>
// 	<div id="lista">
// 		<!-- <div class="group" data-group-id="" data-show="0"></div> -->
// 	</div>
// </body>
// </html>