<script>
import { Bar, mixins } from 'vue-chartjs'
const { reactiveProp } = mixins

export default {
  extends: Bar,
  mixins: [reactiveProp],
  data () {
    return {
      options: {
				title: {
					display: true,
				},
        legend: {
          display: false
        },
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
						},
            barThickness: 10,
					}],
					yAxes: [{
            ticks: {
              beginAtZero:true,
              min: 0,
              stepSize: 1
            },
						display: true,
						scaleLabel: {
							display: true,
						}
					}]
				}
      }
    }
  },
  mounted () {
    this.renderChart(this.chartData, this.options)
    this.addPlugin({
      id: 'top-label',
			afterDatasetsDraw: function(chart) {
				var ctx = chart.ctx;

				chart.data.datasets.forEach(function(dataset, i) {
					var meta = chart.getDatasetMeta(i);
					if (!meta.hidden) {
						meta.data.forEach(function(element, index) {
							// Draw the text in black, with the specified font
							ctx.fillStyle = 'rgb(0, 0, 0)';

							var fontSize = 16;
							var fontStyle = 'normal';
							var fontFamily = 'Helvetica Neue';
							ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

							// Just naively convert to string for now
							var dataString = dataset.data[index].toString();

							// Make sure alignment settings are correct
							ctx.textAlign = 'center';
							ctx.textBaseline = 'middle';

							var padding = 5;
							var position = element.tooltipPosition();
              if (dataString != '0')  {
                ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
              }
						});
					}
				});
			}
		});
  }
}
</script>
