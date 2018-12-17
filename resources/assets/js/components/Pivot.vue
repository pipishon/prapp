<template>
  <div>
    <div style="width: 200px">
      <v-select label="Поставщик" v-model="suplier" @change="getProducts" :items="supliers" item-text="name" item-value="name"></v-select>
    </div>
    <template v-for="(products, category) in categories">
      <strong>{{category}}</strong>
      <v-container fluid>
      <v-layout row>
        <v-flex style="max-width: 600px;" >
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
            <div style="width: 200px; line-height: 0.8; overflow: hidden; white-space: nowrap;">
              <product :product="product" @update="">{{product.name}}</product>
            </div>
          </td>
          <td>
            &nbsp;A
          </td>
          <td >
            <div style="width: 50px; overflow: hidden; white-space: nowrap;">

              <a v-if="product.suplierlinks.length > 0 && product.suplierlinks[0].link != ''" :href="product.suplierlinks[0].link">{{product.sku}}</a>
              <span v-else>{{product.sku}}</span>
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
      <v-flex class="pr-2" style="max-width: 82px;">
      <table class="">
        <th class="text-nowrap">6 мec</th>
        <th>ПГ</th>
        <th>ППГ</th>
        <tr v-for="(product, index) in products">
          <td :class="{'green lighten-5': getLastYear(product) > 0}">{{getLastMonths(product)}}</td>
          <td :class="{'green lighten-5': getLastYear(product) > 0}">{{getLastYear(product)}}</td>
          <td :class="{'green lighten-5': getPreLastYear(product) > 0}">{{getPreLastYear(product)}}</td>
        </tr>
      </table>
      </v-flex>
      <v-flex class="pr-2 month-table" style="max-width: calc(100% - 720);">
      <table class="">
        <th v-for="item in monthHeader">{{item.name}}</th>
        <tr v-for="(product, index) in products">
          <td v-for="item in monthHeader">
            <template v-for="morder in product.morders">
              <span v-if="morder.year === item.year && morder.month === item.month">
                {{morder.quantity}}
              </span>
            </template>
          </td>
        </tr>
      </table>
      </v-flex>
      <v-flex class="pr-2" style="max-width: 40px;">
      <table class="">
        <th>&nbsp;</th>
        <tr v-for="(product, index) in products">
          <td >{{getSumMonths(product.morders)}}</td>
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
  import product from './Product'
    export default {
      components: {
        product
      },
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
          for (let i = this.monthParams.maxYear; i >= this.monthParams.minYear; i--) {
            const startMonth = (i == this.monthParams.minYear) ? this.monthParams.minMonth : 1
            const endMonth = (i == this.monthParams.maxYear) ? this.monthParams.maxMonth : 12
            for (let j = endMonth; j >= startMonth; j--) {
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
  position: relative;
}
table th,
table td {
  font-size: 10px;
  padding: 2px;
  border: 1px solid black;
  white-space: nowrap;
}
.month-table {
  overflow-x: scroll;
}
</style>
