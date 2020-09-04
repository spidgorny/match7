const ctx = document.getElementById('myChart').getContext('2d');
const myChart = new Chart(ctx, {
	type: 'line',
	data: {
		labels,
		datasets: [
			{
				label: 'Consumption',
				data,
				backgroundColor: 'rgba(255, 99, 132, 0.2)',
				borderColor: 'rgba(255, 99, 132, 1)',
				borderWidth: 1
			},
			{
				label: 'Last day/month',
				data: yesterday,
				fill: false,
				borderDash: [5, 5],
				backgroundColor: 'rgba(255, 132, 99, 0.2)',
				borderColor: 'rgba(255, 132, 99, 1)',
				borderWidth: 1
			},
		]
	},
	options: {
		scales: {
			yAxes: [{
				ticks: {
					beginAtZero: true,
				}
			}]
		},
		onClick: (a, b, c) => {
			// console.log(a, b, c);
			if (!b.length) {
				return;
			}
			const url = new URL(document.location.href);
			if (!url.pathname.includes('Monthly')) {
				return;
			}
			url.pathname = '/web/Overview';
			const ym = document.querySelector('h5#title').innerHTML.trim();
			const day = b[0]._index + 1;	// day 0 => 1
			url.searchParams.set('date', ym + '-' + day.toString().padStart(2, '0'));
			document.location.href = url.toString();
		}
	}
});
