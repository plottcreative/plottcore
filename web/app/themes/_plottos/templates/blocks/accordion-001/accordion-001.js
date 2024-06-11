export default function accordion001() {

	const accordions = document.querySelectorAll('.accordion-001__accordion');

	const openAccordion = (accordion) => {
		const content = accordion.querySelector('.accordion-001__accordion-content');
		accordion.classList.add('accordion-001__accordion-active');
		content.style.maxHeight = content.scrollHeight + 'px';
	}

	const closeAccordion = (accordion) => {
		const content = accordion.querySelector('.accordion-001__accordion-content');
		accordion.classList.remove('accordion-001__accordion-active');
		content.style.maxHeight = null;
	}

	accordions.forEach((accordion) => {
		const heading = accordion.querySelector('.accordion-001__accordion-heading');
		const content = accordion.querySelector('.accordion-001__accordion-content');

		heading.onclick = () => {
			if (content.style.maxHeight) {
				closeAccordion(accordion);
			} else {
				accordions.forEach((accordion) => closeAccordion(accordion));
				openAccordion(accordion);
			}
		};

	});

}
