<template>
  <div class="">
    <div class="loader-overlay" v-if="loader">
      <div class="loader" >
        <img src="imgs/loader.svg">
      </div>
    </div>
    <v-container>
      <v-btn @click="addDiscount" flat>Добавить скидку</v-btn>
      <v-layout row>
      <v-flex xs12 md5 >
      <div v-for="discount in list">
          <v-layout row>
            <v-flex xs12 md8 >
              <v-text-field v-model="discount.name" />
            </v-flex>
            <v-flex xs12 md4>
              <v-btn icon @click="removeDiscount(discount)"><v-icon>delete</v-icon></v-btn>

              {{discount.nums}}
            </v-flex>
          </v-layout>
        <table class="vals-table">
          <tr>
            <td><v-btn icon class="ma-0" @click="addDiscountVal(discount)"><v-icon>add_box</v-icon></v-btn></td>
            <td v-for="val in discount.vals"><input v-model="val.qty" @blur="checkDiscountVal(discount, val)"/></td>
          </tr>
          <tr>
            <td class="px-3 py-2">%</td>
            <td v-for="val in discount.vals"><input v-model="val.percent" @blur="checkDiscountVal(discount, val)"/></td>
          </tr>
        </table>
      </div>
      </v-flex>
      <v-flex xs12 md7 >
        <v-checkbox label="Включить скидки 3" v-model="tableDiscounts.enable" />
        <div style="position: relative;">
          <v-btn icon class="ma-0" @click="addDiscountTableRow()" style="position: absolute; top: 35px; left: -37px;">
            <v-icon>add_box</v-icon>
          </v-btn>
          <v-btn icon class="ma-0" @click="addDiscountTableCol()">
            <v-icon>add_box</v-icon>
          </v-btn>
          <table class="discount-table">
            <tr>
              <td></td>
              <td v-for="(col, idx) in tableDiscounts.vals[0]">
                <input v-model="tableDiscounts.quantities[idx]" @blur="checkTableQty" />
              </td>
            </tr>
            <tr v-for="(row, idx) in tableDiscounts.vals">
              <td>
                <input v-model="tableDiscounts.prices[idx]" @blur="checkTablePrice"/>
              </td>
              <td v-for="(col, idx) in row"><input v-model="row[idx]" /></td>
            </tr>
          </table>
        </div>
      </v-flex>
      </v-layout>
    </v-container>
    <v-footer fixed class="pa-3">
      <v-spacer></v-spacer>
      <v-btn @click="save" flat>Сохранить</v-btn>
      <v-spacer></v-spacer>

    </v-footer>
  </div>
</template>

<script>
import { mapActions, mapGetters, mapMutations } from 'vuex'
    export default {
      data() {
        return {
          list: [],
          showDialog: false,
          loader: false,
          tableDiscounts: {
            vals: [[]],
            prices: [],
            quantities: [],
            enable: false
          }
        }
      },
      computed: {
        ...mapGetters(['settings']),
      },
      methods: {
        ...mapActions(['updateSettings']),
        checkTableQty() {
          let col = null
          this.tableDiscounts.quantities.map((el, idx) => {
            if (el == '') {
              col = idx
            }
          })
          if (col != null) {
            this.tableDiscounts.quantities.splice(col, 1)
            this.tableDiscounts.vals.map((row) => {
              row.splice(col, 1)
            })
          }
        },
        checkTablePrice() {
          let row = null
          this.tableDiscounts.prices.map((el, idx) => {
            if (el == '') {
              row = idx
            }
          })
          if (row != null) {
            this.tableDiscounts.prices.splice(row, 1)
            this.tableDiscounts.vals.splice(row, 1)
          }
        },
        addDiscountTableRow() {
          const row = JSON.parse(JSON.stringify(this.tableDiscounts.vals[0]))
          for (let i in row) {
            row[i] = '';
          }
          this.tableDiscounts.vals.push(row)
          this.tableDiscounts.prices.push('')
        },
        addDiscountTableCol() {
          this.tableDiscounts.vals.map((row) => row.push(''))
          this.tableDiscounts.quantities.push('')
        },
        addDiscount () {
          this.list.push({
            name: '',
            vals: []
          })
        },
        getList () {
          axios.get('api/discounts').then((res) => {
            this.list = res.data
          })
        },
        checkDiscountVal(discount, val) {
          if (val.qty == '' && val.percent == '' && discount.vals.indexOf(val) != -1) {
            discount.vals.splice(discount.vals.indexOf(val), 1)
          }
        },
        removeDiscount(discount) {
          this.list.splice(this.list.indexOf(discount), 1)
        },
        addDiscountVal(discount) {
          discount.vals.push({
            qty: '',
            percent: ''
          })
        },
        save () {
          this.loader = true
          axios.post('api/discounts', {data: this.list}).then((res) => {
            this.loader = false
            console.log(res.data)
          })
          console.log(this.tableDiscountsPrice)
          this.updateSettings({name: 'table_discounts', value: JSON.stringify(this.tableDiscounts)})
        }
      },
      mounted() {
        this.getList()
        if (typeof(this.settings.table_discounts) != 'undefined') {
          this.tableDiscounts = JSON.parse(this.settings.table_discounts)
        }
      }
    }
</script>
<style scoped>
.vals-table input {
    width: 60px;
    padding: 5px;
    text-align: center;
}
.discount-table input {
    width: 50px;
    padding: 5px;
    text-align: center;
}
.vals-table td,
.discount-table td
{
  border: 1px solid lightgray;
  text-align: center;
  padding: 2px;
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
</style>
