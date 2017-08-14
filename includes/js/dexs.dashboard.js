/* 
 |	DEXS.COUNTER
 |	@file		./includes/js/dexs.dashboard.js
 |	@author		SamBrishes@pytesNET
 |	@version	0.1.3 [0.1.0] - Alpha
 |
 |	@license	X11 / MIT License
 |	@copyright	Copyright © 2015 - 2016 pytesNET
 */
jQuery(document).ready(function(){
	var dexsCR_chart = function(){
		var element = document.getElementById("dexs-counter-stats-chart");
		if(element === null){
			return false;
		}
		var stats = JSON.parse(element.getAttribute("data-stats"));
		if(!stats instanceof Object){
			return false;
		}
		
		var dexsCR_chart = new Chart(element, {
			type: 	"line",
			data:	{
				labels:		stats.labels,
				datasets: 	[{
					label:					stats.label[0],
					data:					stats.total,
					borderColor:			"#69a8bb",
					backgroundColor: 		"#69a8bb",
					pointRadius:			4, 
					pointHitRadius:			8,
					pointHoverRadius:		6,
					pointBackgroundColor: 	"#69a8bb",
					fill:					false,
					lineTension:			0
				},{
					label:					stats.label[1],
					data:					stats.unique,
					borderColor:			"#ff9500",
					backgroundColor: 		"#ff9500",
					pointRadius:			3, 
					pointHitRadius:			8,
					pointHoverRadius:		6,
					pointBackgroundColor: 	"#ff9500",
					fill:					false,
					lineTension:			0
				}]
			},
			options: {
				animation:	{
					duration:	142
				},
				legend:		{
					labels:		{
						usePointStyle:	true
					}
				},
				tooltips:	{
					cornerRadius:	2,
					displayColors:	false,
					callbacks:		{
						beforeBody:		function(item, data){
							if(item[0].datasetIndex === 1){
								return data.datasets[0].label + ": " + data.datasets[0].data[item[0].index];
							}
						},
						afterBody:		function(item, data){
							if(item[0].datasetIndex === 0){
								return data.datasets[1].label + ": " + data.datasets[1].data[item[0].index];
							}
						}
					}
				},
				scales: 	{
					yAxes: [{
						ticks: {
							beginAtZero: 	true,
							maxTicksLimit: 	5
						}
					}]
				}
			}
		})
	}
	dexsCR_chart();
});