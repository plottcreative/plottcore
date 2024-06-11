import AOS from "aos";
export function aosInit(){
	AOS.init({
		once: true,
		offset: 0,
		disable: window.innerWidth < 1200
	});
}
