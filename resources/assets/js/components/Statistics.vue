<template>
  <div class="container">
    <v-btn @click="importOrderFromApi()">Импорт всех заказов по апи</v-btn>
      <v-progress-circular :size="60" :color="(imprt.done) ? 'green' : 'black'" :value="imprt.imported * 100 / imprt.total">
        {{imprt.imported}}
      </v-progress-circular>
    <v-btn @click="importProdcutMonthOrders()">Актуализация продаж по месяцам</v-btn>
    <button class="btn btn-default" @click="recalcCustomerStatistics(1)">Recalc customer statistics</button>
    <div>{{customerRecalcs.to}} / {{customerRecalcs.total}}</div>
    <button class="btn btn-default" @click="recalcOrderDayStatistics">Recalc order day statistics</button>
    <div>
      <strong>{{orderDayRecalcs.curDay}}</strong>
      <table>
        <tr v-for="(avr, date) in orderDayRecalcs.avr">
          <td>{{date}}</td>
          <td>{{avr}}</td>
        </tr>
      </table>
    </div>
    <linechart
      :chart-data="chartData"
      :width="1000"
      :height="800"
      :options="{responsive: false}"
    ></linechart>
  </div>
</template>
<script>
    export default {
      props: ['imode'],
      data() {
        return {
          imprt: {
            imported: 0,
            total: 1,
            done: false
          },
          customerRecalcs: {
            to: 0,
            total:0
          },
          orderDayRecalcs: {
            curDay: null,
            avr: null
          },
          orderDayStatistics: [],
          chartData: null
        }
      },
      methods: {
        importProdcutMonthOrders(page) {
          if (typeof(page) == 'undefined') {
            this.imprt.imported = 0
            this.imprt.done = false
          }
          const params = { page }
          axios.get('api/product/morders', {params}).then((res) => {
            if (res.data.data.length > 0) {
              this.imprt.imported = res.data.to
              this.imprt.total = res.data.total
              this.importProdcutMonthOrders(res.data.current_page + 1)
            } else {
              this.imprt.done = true
            }
          })
        },
        importOrderFromApi (lastId) {
          if (typeof(lastId) == 'undefined') {
            this.imprt.imported = 0
            this.imprt.done = false
          }
          const params = {last_id: lastId}
          axios.get('api/orders/importfromapi', {params}).then((res) => {
            console.log(res.data)
            if (typeof(res.data.last_id) != 'undefined') {
              this.imprt.imported += res.data.imported
              this.imprt.total = res.data.total
              this.importOrderFromApi(res.data.last_id)
            } else {
              this.imprt.imported += res.data.imported
              this.imprt.done = true
              this.getList()
            }
          })
        },
        recalcOrderDayStatistics () {
          let params = { day:  this.orderDayRecalcs.curDay }
          axios.get('api/statistics/recalc/orders', {params}).then((res) => {
            console.log(res.data)
            if (res.data.day) {
              this.orderDayRecalcs.curDay = res.data.day
              this.orderDayRecalcs.avr = res.data.avr
              this.recalcOrderDayStatistics()
            }
          })
        },
        recalcCustomerStatistics (page) {
          let params = { page }
          axios.get('api/statistics/recalc/customers', {params}).then((res) => {
            if (res.data.data.length > 0) {
              this.customerRecalcs.to = res.data.to
              this.customerRecalcs.total = res.data.total
              this.recalcCustomerStatistics(res.data.current_page + 1)
            }
          })
        }
      },
      mounted() {
        this.mode = this.imode
        axios.get('api/orderdaystatistic').then((res) => {
          let labels = []
          let datasets = [
              {
                label: 'second',
                data: [],
                backgroundColor: 'red',
                borderColor: 'red',
                fill: false,
                pointRadius: 0
              },
              {
                label: 'third',
                data: [],
                backgroundColor: 'green',
                borderColor: 'green',
                fill: false,
                pointRadius: 0
              },
              {
                label: 'fourth',
                data: [],
                backgroundColor: 'blue',
                borderColor: 'blue',
                fill: false,
                pointRadius: 0
              },
            ]
          this.orderDayStatistics = res.data
          this.orderDayStatistics.map((row) => {
            labels.push(row.date)
            datasets.map((dataset) => {
              dataset.data.push(row[dataset.label])
            })
          })
          this.chartData = {labels, datasets}
          console.log(this.chartData)
        })
      }
    }
</script>
<style scoped>
.wrap {
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  width: 3rem;
  background: black;
  padding: 0.2rem 0;
}
.icons-wrap{
  margin-top: 3rem;
}
.icon-wrap {
  width: 3rem;
  cursor: pointer;
  text-align: center;
}
.icon {
  margin: 1rem 0;
  fill: white;
}
.active .icon{
  fill: #ffc107;
}
</style>
