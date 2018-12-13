<template>
  <div>
    <div style="width: 200px">
      <v-select label="Поставщик" v-model="suplier" @change="getProducts" :items="supliers" item-text="name" item-value="name"></v-select>
    </div>
    <template v-for="(products, category) in categories">
      <strong>{{category}}</strong>
      <table>
        <tr v-for="(product, index) in products">
          <td>
            {{index}}
          </td>
          <td>
            {{product.sku}}
          </td>
          <td>
            {{product.name}}
          </td>
          <td>
            {{product.sort2}}
          </td>
          <td>
            {{product.link}}
          </td>
          <td>
            {{product.avaiable}}
          </td>
          <td>
            {{product.purchase_price}}
          </td>
          <td>
            {{product.price}}
          </td>
          <td>
            <span v-if="product.margin">
              {{product.margin.toFixed(2)}}
            </span>
          </td>
          <td>
            {{product.quantity}}
          </td>
          <td>
          </td>
        </tr>
      </table>
      <table>
        <th v-for="item in monthHeader">{{item.name}}</th>
        <tr v-for="(product, index) in products">
          <td v-for="item in monthHeader">
          </td>
        </tr>
      </table>
    </template>
  </div>
</template>
<script>
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
          }
        }
      },
      computed: {
        monthHeader() {
          let header = []
          for (let i = this.monthParams.minYear; i < this.monthParams.maxYear; i++) {
            for (let j = 1; j < 13; j++) {
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
</style>
