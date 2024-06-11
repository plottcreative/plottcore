export function burger(){
	let burgerMenu = document.getElementById('burger-menu'),
		mobileMenu = document.getElementById('mobile-menu'),
		header = document.querySelector('header'),
		body = document.querySelector('body');

	mobileMenu.style.marginTop = header.offsetHeight + 'px';

	window.onresize = () => {
		mobileMenu.style.marginTop = header.offsetHeight + 'px';
	}

	burgerMenu.onclick = () => {
		burgerMenu.classList.toggle('active');
		mobileMenu.classList.toggle('active');
		body.classList.toggle('scroll-lock');
	}

}
