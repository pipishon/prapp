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
          position: 'bottom',
          display: true
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
            stacked: true,
					}],
					yAxes: [{
            ticks: {
              beginAtZero:true,
            },
            stacked: true,
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
  }
}
</script>
