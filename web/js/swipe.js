var element = document.getElementById('slider');
window.mySwipe = new Swipe(element, {
	startSlide: 1,
	// auto: 3000,
	draggable: true,
	autoRestart: false,
	continuous: false,
	disableScroll: true,
	stopPropagation: true,
	callback: function (index, element) {
		// console.log('swipe', index);
		const url = new URL(document.location);
		const urlDate = url.searchParams.get('date');
		// console.log(urlDate);
		let date = urlDate ? new Date(urlDate) : new Date();
		// console.log(url.pathname);
		if (url.pathname.includes('Monthly')) {
			date.setMonth(date.getMonth() + index - 1);
		} else {
			// 0 => -1, +2 => +1
			date.setDate(date.getDate() + index - 1);
		}
		const ymd = date.getFullYear() + '-' + (date.getMonth() + 1).toString().padStart(2, '0') + '-' + date.getDate().toString().padStart(2, '0');
		console.log(ymd);
		url.searchParams.set('date', ymd);
		document.location.href = url.toString();
	},
	transitionEnd: function (index, element) {
		// console.log('transitionend', index);
	}
});
