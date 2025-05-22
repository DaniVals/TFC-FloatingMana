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
		// console.log(JSON_DATA);
		
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
			// console.log(types);
			if (types[0] == "Legendary") {
				types.shift();
			}
			// console.log(types);
			this.type = types[0];
			
		} else {
			this.type = "other"
		}

		const cardPrices = this.element.querySelector("#card-prices");
		if (cardPrices) {
			let improvement = parseFloat((this.price - this.purchasePrice).toFixed(2));

			// show color only if there is a price to compare
			if (JSON_DATA["prices"] && JSON_DATA["prices"]["eur"]) {
				cardPrices.setAttribute("data-price-improvement", improvement);
				if (0 == improvement) {
					cardPrices.title = "= " + improvement + "€";
				}
				else if (0 < improvement) {
					cardPrices.title = "↑ " + improvement + "€";
				} 
				else {
					cardPrices.title = "↓ " + Math.abs(improvement) + "€";
				}
			}else{
				cardPrices.title = "sin precio";
			}
		}
	}
}