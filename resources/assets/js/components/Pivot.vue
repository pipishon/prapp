<template>
  <div>
    <div style="width: 200px">
      <v-select label="Поставщик" v-model="suplier" @change="getProducts" :items="supliers" item-text="name" item-value="name"></v-select>
    </div>
    <template v-for="(products, category) in categories">
      <strong>{{category}}</strong>
      <v-container fluid>
      <v-layout row>
        <v-flex md6 >
      <table>
        <th></th><th></th><th></th><th></th><th></th><th>нал</th><th>пок</th><th>прод</th><th>марж</th><th>есть</th><th>купить</th><th>будет</th>
        <tr v-for="(product, index) in products">
          <td>
            {{index + 1}}
          </td>
          <td>
            <div style=" width: 65px; overflow: hidden; white-space: nowrap;">
              {{product.sku}}
            </div>
          </td>
          <td>
            <div style="width: 200px; overflow: hidden; white-space: nowrap;">
              {{product.name}}
            </div>
          </td>
          <td>
            &nbsp;A
          </td>
          <td >
            <div style="width: 50px; overflow: hidden; white-space: nowrap;">
              <a :href="product.suplierlinks[0]">{{product.sku}}</a>
              <span v-if="product.suplierlinks.length > 1">({{product.suplierlinks.length}})</span>
            </div>
          </td>
          <td class="text-center">
            +
          </td>
          <td>
            &nbsp;{{product.purchase_price}}
          </td>
          <td>
            &nbsp;{{product.price}}
          </td>
          <td>
            &nbsp;
            <span v-if="product.margin">
              {{product.margin.toFixed(2)}}
            </span>
          </td>
          <td>
            &nbsp;
            {{product.quantity}}
          </td>
          <td>
            <input style="width: 35px">
          </td>
          <td>
            &nbsp;
          </td>
        </tr>
      </table>
      </v-flex>
      <v-flex class="pr-2 month-table" md5>
      <table class="">
        <th class="text-nowrap">6 мec</th>
        <th>ПГ</th>
        <th>ППГ</th>
        <th v-for="item in monthHeader">{{item.name}}</th>
        <th></th>
        <tr v-for="(product, index) in products">
          <td :class="{'green lighten-5': getLastYear(product) > 0}">{{getLastMonths(product)}}</td>
          <td :class="{'green lighten-5': getLastYear(product) > 0}">{{getLastYear(product)}}</td>
          <td :class="{'green lighten-5': getPreLastYear(product) > 0}">{{getPreLastYear(product)}}</td>
          <td v-for="item in monthHeader">
            &nbsp;
            <template v-for="morder in product.morders">
              <span v-if="morder.year === item.year && morder.month === item.month">
                {{morder.quantity}}
              </span>
            </template>
          </td>
          <td>{{getSumMonths(product.morders)}}</td>
        </tr>
      </table>
      </v-flex>
    </v-layout>
    </v-container>
    </template>
  </div>
</template>
<script>
  import * as moment from 'moment'
  import { extendMoment } from 'moment-range';
    export default {
      data() {
        return {
          supliers: [],
          categories: [],
          suplier: null,
          monthParams: {
            minYear: 10000,
            maxYear: 0,
            minMonth: 100,
            maxMonth: 0,
          },
          countMonth: 5
        }
      },
      computed: {
        monthHeader() {
          let header = []
          for (let i = this.monthParams.minYear; i <= this.monthParams.maxYear; i++) {
            const startMonth = (i == this.monthParams.minYear) ? this.monthParams.minMonth : 1
            const endMonth = (i == this.monthParams.maxYear) ? this.monthParams.maxMonth : 12
            for (let j = startMonth; j <= endMonth; j++) {
              header.push({
                year: i,
                month: j,
                name: j+'.'+(i - 2000)
              })
            }
          }
          return header
        }
      },
      methods: {
        getLastMonths (product) {
          const morders = product.morders
          const emoment = extendMoment(moment)
          const range = emoment.rangeFromInterval('month', -1*this.countMonth, moment())
          let sum = 0;
          for (let item of range.by('month')) {
            let month = item.format('M');
            let year = item.format('Y');
            morders.map((morder) => {
              if (morder.year == year && morder.month == month) {
                sum += morder.quantity
              }
            })
          }
          return sum - product.quantity
        },
        getLastYear (product) {
          const morders = product.morders
          const emoment = extendMoment(moment)
          const range = emoment.rangeFromInterval('month', this.countMonth, moment().subtract(1, 'year'))
          let sum = 0;
          for (let item of range.by('month')) {
            let month = item.format('M');
            let year = item.format('Y');
            morders.map((morder) => {
              if (morder.year == year && morder.month == month) {
                sum += morder.quantity
              }
            })
          }
          return sum - product.quantity
        },
        getPreLastYear (product) {
          const morders = product.morders
          const emoment = extendMoment(moment)
          const range = emoment.rangeFromInterval('month', this.countMonth, moment().subtract(2, 'year'))
          let sum = 0;
          for (let item of range.by('month')) {
            let month = item.format('M');
            let year = item.format('Y');
            morders.map((morder) => {
              if (morder.year == year && morder.month == month) {
                sum += morder.quantity
              }
            })
          }
          return sum - product.quantity
        },
        getSumMonths (morders) {
          let sum = 0
          morders.map((el) => {
            sum += el.quantity
          })
          return sum
        },
        getSupliers () {
          axios.get('api/suplier').then((res) => {
            this.supliers = res.data
          })
        },
        getMonthParams() {
          for (let cat in this.categories) {
            const products = this.categories[cat]
            products.map((product) => {
              product.morders.map((month) => {
                if (this.monthParams.maxYear < month.year) {
                  this.monthParams.maxYear = month.year
                }
                if (this.monthParams.minYear > month.year) {
                  this.monthParams.minYear = month.year
                }
              })
              product.morders.map((month) => {
                if (this.monthParams.maxMonth < month.month &&
                    this.monthParams.maxYear == month.year
                ) {
                  this.monthParams.maxMonth = month.month
                }
                if (this.monthParams.minMonth > month.month &&
                    this.monthParams.minYear == month.year
                ) {
                  this.monthParams.minMonth = month.month
                }
              })
            })
          }
        },
        getProducts () {
          const params = {suplier: this.suplier}
          axios.get('api/product/suplier', {params}).then((res) => {
            this.categories = res.data
            this.getMonthParams()
          })
        }
      },
      mounted() {
        this.getSupliers()
      }
    }
</script>
<style scoped>
table {
  table-layout: fixed;
}
table th,
table td {
  font-size: 10px;
  padding: 2px;
  border: 1px solid black;
}
.month-table {
  overflow-x: scroll;
}
</style>
