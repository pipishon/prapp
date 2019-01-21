<template>
  <div class="pivot" >
    <div class="loader-overlay" v-if="listLoading">
      <div class="loader" >
        <img src="imgs/loader.svg">
      </div>
    </div>
    <div style="width: 200px">
      <v-select label="Поставщик" v-model="suplier" @change="getProducts" :items="supliers" item-text="name" item-value="name"></v-select>
    </div>
    <template v-for="(products, category) in categories">
      <strong>{{category}}</strong>
      <v-container fluid>
      <v-layout row>
        <v-flex >
      <table>
        <th>№</th><th>Изобр.</th><th>Артикул</th><th>Название</th><th>ABC</th><th>Пост. артикул</th><th>sort2</th><th>пок</th><th>прод</th><th>марж</th><th>есть</th><th>купить</th><th>будет</th>
        <th class="text-nowrap">6 мec</th>
        <th>ПГ</th>
        <th>ППГ</th>
        <th>Сумма</th>
        <th>Граф.</th>
        <th v-for="item in monthHeader">{{item.name}}</th>
        <tr v-for="(product, index) in products" :class="{part: product.prom_id != product.part_id, 'pink lighten-5': product.on_sale}">
          <td>
            {{index + 1}}
          </td>
          <td style="position: relative; width: 40px; opacity: 1;" class="image-td">
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
              <product :product="product" @update="" :inline="true" >
                {{product.name}}
                <span v-if="product.pack_quantity" class="grey--text text--darken-2">
                  ({{product.pack_quantity}})
                </span>
              </product>

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
          <td class="text-center" :class="{blink: sortSaved[product.id]}">
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
          <td :class="{'pink lighten-5': product.quantity <= 0}">
            <div>
              {{sumQuantity(products.filter((el) => el.part_id == product.part_id))}}
              <div v-if="products.filter((el) => el.part_id == product.part_id).length > 1" class="grey--text">{{Math.round(product.quantity * product.part_koef * 10) / 10}}</div>
            </div>
          </td>
          <td>
            <input :value="product.toBuy" ref="tobuys" @keypress.enter="focusNextInput($event, product)" style="width: 35px">
          </td>
          <td>
            {{calcFeatureQty(product)}}
            &nbsp;
          </td>
          <template v-if="product.prom_id == product.part_id">
            <td >
              <div :class="{'green lighten-4': getLastMonths(products.filter((el) => el.part_id == product.part_id)) > 0}" style="margin-top: 3px; margin-bottom: 2px;">{{getLastMonths(products.filter((el) => el.part_id == product.part_id))}}</div>
            </td>
            <td >
              <div :class="{'green lighten-4': getLastYear(products.filter((el) => el.part_id == product.part_id)) > 0}">{{getLastYear(products.filter((el) => el.part_id == product.part_id))}}</div>
            </td>
            <td >
              <div :class="{'green lighten-4': getPreLastYear(products.filter((el) => el.part_id == product.part_id)) > 0}">{{getPreLastYear(products.filter((el) => el.part_id == product.part_id))}}</div>
            </td>
            <td >
              <div style="margin-top: 3px; margin-bottom: 2px;">
                {{getSumMonths(product.morders)}}
              </div>
            </td>
          </template>
          <template v-else>
            <td colspan="4">
            </td>
          </template>
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
        <template v-if="typeof(newProducts[category]) != 'undefined'">
        <tr v-for="(product, index) in newProducts[category]">
          <td></td>
          <td></td>
          <td></td>
          <td>{{product.name}}</td>
          <td></td>
          <td><a :href="product.suplier_link" target="_black">{{product.suplier_sku}}</a></td>
          <td></td>
          <td>{{product.purchase_price}}</td>
          <td></td>
          <td></td>
          <td></td>
          <td>{{product.buy}}</td>
          <td></td>
        </tr>
        </template>
      </table>
      </v-flex>
    </v-layout>
    <v-layout row>
      <v-flex >
        <v-btn @click="newProductDialogShow = true; newProductCategory = category" >Добавить товар</v-btn>
      </v-flex>
    </v-layout>
    </v-container>
    </template>
    <v-footer fixed class="pa-3" v-if="suplier != null" >
      <v-btn @click="getProducts()" class="primary">Обновить</v-btn>
      <v-btn @click="onDisplay = !onDisplay; getProducts()" :class="{primary: onDisplay}">Опубликованные</v-btn>
      <v-btn @click="sort2 = !sort2; getProducts()" :class="{primary: sort2}">Sort2</v-btn>
      <v-btn @click="sort = 'name'; getProducts()" :class="{primary: sort=='name'}">По наименованию</v-btn>
      <v-btn @click="sort = 'sku'; getProducts()" :class="{primary: sort=='sku'}">По артикулу</v-btn>
      <v-btn @click="sort = 'abc'; getProducts()" :class="{primary: sort=='abc'}">По ABC</v-btn>
      <v-btn color="primary" flat @click="savePurchase" > Сохранить </v-btn>
      <v-menu
        :close-on-content-click="false"
        v-model="savedDatesMenu"
        :nudge-right="40"
        offset-y
        full-width
        min-width="290px"
      >
        <v-btn slot="activator" icon><v-icon>event</v-icon></v-btn>
        <v-date-picker v-model="savedDate" locale="ru-Ru" first-day-of-week="1" no-title scrollable :allowed-dates="allowedDates">
          <v-spacer></v-spacer>
          <v-btn flat color="primary" @click="savedDatesMenu = false">Cancel</v-btn>
          <v-btn flat color="primary" @click="loadSavedPurchase">OK</v-btn>
        </v-date-picker>
      </v-menu>

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
  <v-dialog width="400" v-model="newProductDialogShow">
    <v-card class="pa-3">
      <strong>{{newProductCategory}}</strong>
      <v-text-field v-model="newProduct.name" label="Название"></v-text-field>
      <v-text-field v-model="newProduct.suplier_sku" label="Артикул"></v-text-field>
      <v-text-field v-model="newProduct.purchase_price" label="Закупочная цена"></v-text-field>
      <v-text-field v-model="newProduct.buy" label="Купить"></v-text-field>
      <v-text-field v-model="newProduct.suplier_link" label="Ссылка"></v-text-field>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="cancelNewProduct" > Отмена </v-btn>
        <v-btn color="primary" flat @click="saveNewProduct" > Сохранить </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
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
          listLoading: false,
          savedDatesMenu: false,
          savedDate: '',
          savedDates: [],
          newProductDialogShow: false,
          newProductCategory: null,
          newProducts: {},
          newProduct: {name: '', suplier_sku: '', purchase_price: '', suplier_link: '', buy: ''},
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
          countMonth: 5,
          sortSaved: {},
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
        sumQuantity (products) {
          let sum = 0;
          products.map((product) => {
            sum += Math.round(product.quantity * product.part_koef * 10) / 10
          })
          return sum
        },
        loadSavedPurchase () {
          const params = {
            suplier: this.suplier,
            date: this.savedDate
          }
          this.listLoading = true
          axios.get('api/purchase', {params}).then((res) => {
            this.newProducts = []
            if (typeof(res.data[0].products) != 'undefined') {
              this.newProducts = JSON.parse(res.data[0].products)
            }

            let id_qty_buy = []
            if (typeof(res.data[0].id_qty_buy) != 'undefined') {
              id_qty_buy = JSON.parse(res.data[0].id_qty_buy)
            }

            for (let category in this.categories) {
              this.categories[category].map((product) => {
                if (typeof(id_qty_buy[category]) != 'undefined') {
                  id_qty_buy[category].map((savedProduct) => {
                    if (product.id == savedProduct.id) {
                      product.quantity = savedProduct.qty
                      product.toBuy = savedProduct.buy
                      //this.$set(product, 'toBuy', savedProduct.buy)
                    }
                  })
                }
              })
            }
            this.savedDatesMenu = false
            this.listLoading = false
            console.log(res.data)
          })
        },
        allowedDates (val) {
          return this.savedDates.indexOf(val) != -1
        },
        savePurchase () {
          let id_qty_buy = {}
          for (let category in this.categories) {
            if (typeof(id_qty_buy[category]) == 'undefined') {
              id_qty_buy[category] = []
            }
            this.categories[category].map((product) => {
              id_qty_buy[category].push({
                id: product.id,
                qty: product.quantity,
                buy: 1*product.toBuy || 0,
              })
            })

          }
          id_qty_buy = JSON.stringify(id_qty_buy)
          const params = {
            suplier: this.suplier,
            id_qty_buy,
            products: JSON.stringify(this.newProducts)
          }

          this.listLoading = true
          axios.post('api/purchase/save', {...params}).then((res) => {
            this.listLoading = false
            console.log(res.data)
          })
        },
        cancelNewProduct () {
          this.newProductDialogShow = false
          for (let i in this.newProduct) {
            this.newProduct[i] = ''
          }
        },
        saveNewProduct () {
          if (typeof(this.newProducts[this.newProductCategory]) == 'undefined') {
            this.newProducts[this.newProductCategory] = []
          }
          this.newProducts[this.newProductCategory].push(JSON.parse(JSON.stringify(this.newProduct)))
          this.cancelNewProduct()
        },
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
            this.$set(this.sortSaved, product.id, true);
            setTimeout(() => {
              this.sortSaved[product.id] = false;
            }, 500)
            //this.getProducts()
          })
        },
        getOrdersInRange(products, range) {
          let packs = []
          products.map((product) => {
            product.packitems.map((pack) => {
              for (let cat in this.categories) {
                const allProducts = this.categories[cat]
                if (allProducts.filter((el) => el.id == pack.product_id).length > 0) {
                  let pck = JSON.parse(JSON.stringify(allProducts.filter((el) => el.id == pack.product_id)[0]))
                  pck.morders.map((el) => el.quantity =  1 * el.quantity * pack.koef)
                  packs.push(pck)
                }
              }
            })
          })
          //products = [...products, ...packs]

          let qty = 0
          packs.map((product) => {
            const morders = product.morders
            let sum = 0;
            for (let item of range.by('month')) {
              let month = item.format('M');
              let year = item.format('Y');
              morders.map((morder) => {
                if (morder.year == year && morder.month == month) {
                  sum += 1*morder.quantity
                }
              })
            }
            qty += (sum) * product.part_koef
          })
          products.map((product) => {
            const morders = product.morders
            let sum = 0;
            for (let item of range.by('month')) {
              let month = item.format('M');
              let year = item.format('Y');
              morders.map((morder) => {
                if (morder.year == year && morder.month == month) {
                  sum += 1*morder.quantity
                }
              })
            }
            qty += (sum - product.quantity) * product.part_koef
          })
          return Math.round(qty * 10) / 10
        },
        getLastMonths (products) {
          const emoment = extendMoment(moment)
          const range = emoment.rangeFromInterval('month', -1*this.countMonth, moment().subtract(1, 'month'))
          return this.getOrdersInRange(products, range)
        },
        getLastYear (products) {
          const emoment = extendMoment(moment)
          const range = emoment.rangeFromInterval('month', this.countMonth, moment().subtract(1, 'year').add(1, 'month'))
          return this.getOrdersInRange(products, range)
        },
        getPreLastYear (products) {
          const emoment = extendMoment(moment)
          const range = emoment.rangeFromInterval('month', this.countMonth, moment().subtract(2, 'year').add(1, 'month'))
          return this.getOrdersInRange(products, range)
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
          this.monthParams  = {
            minYear: 10000,
            maxYear: 0,
            minMonth: 100,
            maxMonth: 0,
          }
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
                  console.log(month.month, month.year, this.monthParams)
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

          this.listLoading = true
          axios.get('api/product/suplier', {params}).then((res) => {
            this.listLoading = false
            this.categories = res.data
            this.getMonthParams()
            this.getSavedDates()
          })
        },
        getSavedDates () {
          const params = {suplier: this.suplier}
          this.listLoading = true
          axios.get('api/purchase/getsaveddates', {params}).then((res) => {
            this.listLoading = false
            this.savedDates = res.data
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
        //this.suplier = 'Aliexpress (мобили)'
        //this.getProducts()
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
  border: 1px solid #dee2e6;
}
table td input {
  background: #fafafa;
  padding: 2px 4px;
  border-radius: 3px;
}
table tr.part td{
  opacity: 0.5;
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
.loader-overlay {
  left: -30px;
  right: 0;
  width: 100vw;
  height: 100%;
  z-index: 100;
  position: fixed;
  z-index: 10000;
}
.loader {
  width: 200px;
  height: 200px;
  position: fixed;
  left: calc(50vw - 100px);
  top: calc(50vh - 100px);
}
.pivot {
 overflow-x: scroll;
 padding-bottom: 400px;
}

.pivot .blink {
  animation: blink 500ms infinite;  /* IE 10+, Fx 29+ */
}

@-webkit-keyframes blink {
  0%, 49% {
    background-color: #e8f5e9;
  }
  50%, 100% {
    background-color: #fafafa;
  }
}
</style>
