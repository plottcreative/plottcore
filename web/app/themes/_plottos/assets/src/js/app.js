// Node Module Imports
import Lottie from "lottie-web";
import Swiper from "swiper";
import AOS from "aos";

//Src JS Imports
import {burger} from "./burger";
import {dropdownMenu} from "./dropdownMenu";
import {lottie} from "./lottie";
import {aosInit} from "./aos";

//Block Imports
import faqs001 from "../../../templates/blocks/faqs-001/faqs-001";
import accordion001 from "../../../templates/blocks/accordion-001/accordion-001";
import {logoSlider001} from "../../../templates/blocks/logo-slider-001/logo-slider-001";

// This will make lottie available globally.
window.Lottie = Lottie;
window.AOS = AOS;
window.Swiper = Swiper;

window.onload = () => {

	lottie();
	burger();
	dropdownMenu();
	aosInit();
	faqs001();
	accordion001();
	//logoSlider001();

	document.querySelectorAll('input, textarea, select').forEach(function(input) {
		// Add a 'change' event listener
		input.addEventListener('change', function() {
			let isRequired = input.closest('.gfield') && input.closest('.gfield').classList.contains('gfield_contains_required');
			let isValid = input.validity.valid;

			if (isRequired && isValid) {
				input.closest('.gfield').classList.remove('gfield_error');
				if (input.parentElement.nextElementSibling && input.parentElement.nextElementSibling.classList.contains('validation_message')) {
					input.parentElement.nextElementSibling.style.display = 'none';
				}
			}
		});

		// Add a 'blur' event listener
		input.addEventListener('blur', function() {
			let isRequired = input.closest('.gfield') && input.closest('.gfield').classList.contains('gfield_contains_required');
			let isInValid = !input.validity.valid;
			let isEmpty = input.value === '';

			if (isRequired && (isInValid || isEmpty)) {
				input.closest('.gfield').classList.add('gfield_error');
				if (input.parentElement.nextElementSibling && input.parentElement.nextElementSibling.classList.contains('validation_message')) {
					input.parentElement.nextElementSibling.style.display = 'block';
				}
			}
		});
	});

};
