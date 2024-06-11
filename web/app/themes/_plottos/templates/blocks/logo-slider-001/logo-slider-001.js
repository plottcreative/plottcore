export function logoSlider001() {

	const carousel = document.getElementById('clients-list');
	const originalItems = [...carousel.children]; // Spread to clone NodeList to Array
	const cloneCount = 10; // Number of times to clone original items for smooth looping

	// Clone items to fill the carousel
	for (let i = 0; i < cloneCount; i++) {
		originalItems.forEach(item => {
			const clone = item.cloneNode(true);
			carousel.appendChild(clone);
		});
	}

	let currentPosition = 0;
	const scrollSpeed = 2; // Adjust for faster or slower scrolling

	function update() {
		currentPosition += scrollSpeed;

		// Reset position to create an infinite loop effect
		if (currentPosition >= carousel.scrollWidth / cloneCount) {
			currentPosition = 0;
		}

		carousel.style.transform = `translateX(-${currentPosition}px)`;

		requestAnimationFrame(update); // Loop the animation
	}

	// Start the continuous scroll
	update();

}
