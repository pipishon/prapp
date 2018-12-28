<template>
  <div style="overflow-x: scroll; padding-bottom: 400px;">
    <div style="width: 200px">
      <v-select label="Поставщик" v-model="suplier" @change="getProducts" :items="supliers" item-text="name" item-value="name"></v-select>
    </div>
    <template v-for="(products, category) in categories">
      <strong>{{category}}</strong>
      <v-container fluid>
      <v-layout row>
        <v-flex >
      <table>
        <th></th><th></th><th></th><th></th><th></th><th></th><th>sort2</th><th>пок</th><th>прод</th><th>марж</th><th>есть</th><th>купить</th><th>будет</th>
        <th class="text-nowrap">6 мec</th>
        <th>ПГ</th>
        <th>ППГ</th>
        <th>Сумма</th>
        <th></th>
        <th v-for="item in monthHeader">{{item.name}}</th>
        <tr v-for="(product, index) in products">
          <td>
            {{index + 1}}
          </td>
          <td style="position: relative; width: 40px;" class="image-td">
            <img style="width: 40px; height: auto" :src="product.main_image" />
            <img class="big-image" style="width: 400px; left: 100%; height: auto; position: absolute; z-index: 100" :src="product.main_image" />
          </td>
          <td>
            <div style="width: 65px; overflow: hidden; white-space: nowrap;">
              {{product.sku}}
            </div>
          </td>
          <td>
            <div style="width: 300px;" >
              <product :product="product" @update="">{{product.name}}</product>
            </div>
          </td>
          <td>
            <span v-if="product.abc_earn">{{product.abc_earn}}{{product.abc_qty}}</span>
            <span style="white-space: nowrap;" v-else>&nbsp;- -&nbsp;</span>
          </td>
          <td >
            <div style="width: 50px; overflow: hidden; white-space: nowrap;">

              <a v-if="product.suplierlinks.length > 0 && product.suplierlinks[0].link != ''" :href="product.suplierlinks[0].link" target="_blank">{{suplierSku(product)}}</a>
              <span v-else>{{suplierSku(product)}}</span>
              <span v-if="product.suplierlinks.length > 1">({{product.suplierlinks.length}})</span>
            </div>
          </td>
          <td class="text-center">
            <input style="width: 20px" :value="product.sort2" @blur="$event.target.value = product.sort2" @keypress.enter="changeSort($event.target.value, product)" />
          </td>
          <td>
            &nbsp;{{product.purchase_price}}
          </td>
          <td>
            <div style="margin-top: 3px; margin-bottom: 2px;">&nbsp;{{product.price}}</div>
          </td>
          <td>
            <span v-if="product.margin">
             {{product.margin.toFixed(2)}}
            </span>

          </td>
          <td>
            <div>{{product.quantity}}</div>
          </td>
          <td>
            <input :value="product.toBuy" ref="tobuys" @keypress.enter="focusNextInput($event, product)" style="width: 35px">
          </td>
          <td>
            {{calcFeatureQty(product)}}
            &nbsp;
          </td>
          <td >
            <div :class="{'green lighten-4': getLastMonths(product) > 0}" style="margin-top: 3px; margin-bottom: 2px;">{{getLastMonths(product)}}</div>
          </td>
          <td >
            <div :class="{'green lighten-4': getLastYear(product) > 0}">{{getLastYear(product)}}</div>
          </td>
          <td >
            <div :class="{'green lighten-4': getPreLastYear(product) > 0}">{{getPreLastYear(product)}}</div>
          </td>
          <td >
            <div style="margin-top: 3px; margin-bottom: 2px;">
              {{getSumMonths(product.morders)}}
            </div>
          </td>
          <td >
            <v-btn @click="showOrderStatistic(product)" icon flat><v-icon>bar_chart</v-icon></v-btn>
          </td>
          <td v-for="item in monthHeader">
            <div style="margin-top: 3px; margin-bottom: 2px; white-space: nowrap;">
              <template v-for="morder in product.morders">
                <span  v-if="morder.year === item.year && morder.month === item.month">
                  {{morder.quantity}}
                </span>
              </template>
              &nbsp;
            </div>
          </td>
        </tr>
      </table>
      </v-flex>
    </v-layout>
    </v-container>
    </template>
    <v-footer fixed class="pa-3" >
      <v-btn @click="getProducts()" class="primary">Обновить</v-btn>
      <v-btn @click="onDisplay = !onDisplay; getProducts()" :class="{primary: onDisplay}">Опубликованные</v-btn>
      <v-btn @click="sort2 = !sort2; getProducts()" :class="{primary: sort2}">Sort2</v-btn>
      <v-btn @click="sort = 'name'; getProducts()" :class="{primary: sort=='name'}">По наименованию</v-btn>
      <v-btn @click="sort = 'sku'; getProducts()" :class="{primary: sort=='sku'}">По артикулу</v-btn>
      <v-btn @click="sort = 'abc'; getProducts()" :class="{primary: sort=='abc'}">По ABC</v-btn>
    </v-footer>
  <v-dialog width="1100" v-model="showDialogStatistics">

    <v-card>
      <div style="width: 1100px; height: 700px;">
        <barchart
          :width="1100"
          :height="700"
          :chart-data="chartData"
          :options="{responsive: true}"
        ></barchart>
      </div>
    </v-card>
  </v-dialog>
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
          chartData: null,
          showDialogStatistics: false,
          onDisplay: true,
          sort2: true,
          sort: 'name',
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
        suplierSku(product) {
          return product.suplier_sku || product.sku
        },
        focusNextInput (e, product) {
          this.$set(product, 'toBuy', e.target.value)
          const index = this.$refs.tobuys.indexOf(e.target)
          if (typeof(this.$refs.tobuys[index + 1]) != 'undefined') {
            this.$refs.tobuys[index + 1].focus()
          }
        },
        calcFeatureQty(product) {
          const inPack = product.pack_quantity || 1
          const toBuy = product.toBuy || 0
          return product.quantity + toBuy * inPack
        },
        changeSort (val, product) {
          const params = {
            sort2: val
          }
          product.sort2 = val
          axios.put('api/products/' + product.id, params ).then((res) => {
            //this.getProducts()
          })
        },
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
          const params = {suplier: this.suplier, sort: this.sort}
          if (this.onDisplay) {
            params.on_display = true
          }
          if (this.sort2) {
            params.sort2 = true
          }
          axios.get('api/product/suplier', {params}).then((res) => {
            this.categories = res.data
            this.getMonthParams()
          })
        },
        showOrderStatistic (product) {
          this.showDialogStatistics = true
          axios.get('api/product/ordermonth/' + product.id).then((res) => {
            let labels = []
            let datasets = [
                {
                  label: 'Заказано Товаров',
                  data: [],
                  backgroundColor: 'red',
                  borderColor: 'red',
                  fill: false,
                },
              ]

            res.data.map((row) => {
              labels.push(row.month + '.' + row.year)
              datasets.map((dataset) => {
                  dataset.data.push(row.qty)
              })
            })

            this.chartData = {labels, datasets}
            console.log(this.chartData)
          })
        },
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
  font-size: 14px;
  line-height: 1.3;
  padding: 2px;
  border: 1px solid black;
}
table td input {
  background: #fafafa;
  padding: 2px 4px;
  border-radius: 3px;
}
table tr:hover {
  background-color: #FFF9C4;
}

table tr:hover input{
  border: 1px solid lightgray;
}
.month-table {
  overflow-x: scroll;
}
.image-td .big-image {
  display: none;
}
.image-td:hover .big-image {
  display: block;
}
</style>
