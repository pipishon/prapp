<template>
  <div class="container">
    <div>
      <h4>Статистика продаж</h4>
      <strong>{{dateRange[0]}} - {{dateRange[1]}}</strong>
      <v-btn @click="rangeDialog = true">Установить промежуток</v-btn>
    </div>
    <div style="width: 650px;">
      <stackedchart
        :width="650"
        :height="400"
        :chart-data="chartBarData"
        :options="{responsive: true}"
      ></stackedchart>
    </div>
    <table class="table text-center">
      <tr>
        <th>Заказы Принято, шт</th><th>Сумма Принято, грн</th><th>Заказы Выполнено, шт</th>
        <th>Сумма Выполнено, грн</th><th>Прибыль Выполнено, грн</th><th>Маржа Выполнено, %</th>
      </tr>
      <tr>
        <td>{{sumOrderDayStatistic.quantity}}</td>
          <td>{{sumOrderDayStatistic.price_pending}}</td>
          <td>{{sumOrderDayStatistic.quantity_delivered}}</td>
          <td>{{sumOrderDayStatistic.price_delivered}}</td>
          <td>{{sumOrderDayStatistic.earn_delivered}}</td>
          <td>{{(sumOrderDayStatistic.earn_delivered * 100 / sumOrderDayStatistic.price_delivered).toFixed(0)}}</td>
      </tr>
    </table>
    <v-dialog v-model="rangeDialog" width="640">
      <v-card>
        <v-daterange no-presets :first-day-of-week="1" locale="ru-Ru" :options="dateRangeOptions" @input="dateRangeTmp = arguments[0]"></v-daterange>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click=" dateRangeTmp=dateRange.slice();rangeDialog = false" > Закрыть </v-btn>
            <v-btn color="primary" flat @click="setOrderStatRange" > Установить </v-btn>
          </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import * as moment from 'moment';
    export default {
      data() {
        return {
          rangeDialog: false,
          dateRange: [moment().subtract(7, 'days').format('Y-MM-DD'), moment().format('Y-MM-DD')],
          dateRangeTmp: [],
          sumOrderDayStatistic: {
            'quantity': 0,
            'price_pending': 0,
            'quantity_delivered': 0,
            'price_delivered': 0,
            'earn_delivered': 0,
            'margin_delivered': 0,
          },
          chartBarData: null
        }
      },
      computed: {
        dateRangeOptions () {
          return {
            startDate: this.dateRange[0],
            endDate: this.dateRange[1],
            format: 'YYYY-MM-DD'
          }
        }
      },
      methods: {
        setOrderStatRange () {
          this.dateRange = this.dateRangeTmp.slice();
          this.getSumOrderDayStatistic();
          this.rangeDialog = false
        },
        getSumOrderDayStatistic () {
          const params = {
            from: this.dateRange[0],
            to: this.dateRange[1],
          }
          axios.get('api/orderdaystatistic', {params}).then((res) => {
            const mapVals = {
              'Сумма': 'price_delivered',
              'Прибыль': 'earn_delivered',
            }
            let labels = []
            let datasets = [
                {
                  label: 'Сумма',
                  data: [],
                  backgroundColor: '#ff6384',
                  borderColor: '#ff6384',
                  fill: false,
                },
                {
                  label: 'Прибыль',
                  data: [],
                  backgroundColor: '#36a2eb',
                  borderColor: '#36a2eb',
                  fill: false,
                }
              ]
            for (name in this.sumOrderDayStatistic) {
              this.sumOrderDayStatistic[name] = 0
            }
            res.data.map((row) => {
              for (name in this.sumOrderDayStatistic) {
                this.sumOrderDayStatistic[name] += row[name]
              }
              labels.push(row.date)
              datasets.map((dataset) => {
                dataset.data.push(row[mapVals[dataset.label]])
              })
            })
            this.chartBarData = {labels, datasets}
          })
        }
      },
      mounted() {
        this.getSumOrderDayStatistic()
      }
    }
</script>
<style>
.date-range__pickers label{
  display: none;
}
</style>
