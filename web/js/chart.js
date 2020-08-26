var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
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
				label: 'Yesterday',
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
		}
	}
});
