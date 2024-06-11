export function lottie() {

	const animation = Lottie.loadAnimation({
		container: document.getElementById('rotateDevice'),
		renderer: 'svg',
		loop: true,
		autoplay: true,
		path: 'https://plottcreative.s3.eu-west-2.amazonaws.com/globals/rotatedevice.json'
	});

}
