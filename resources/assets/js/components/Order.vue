<template>
  <v-dialog  v-model="showDialog" fullscreen transition="dialog-bottom-transition" >
    <a href="#" @click.prevent slot="activator"><slot></slot></a>
    <v-card v-if="showDialog">
      <v-toolbar flat card dense fixed>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn flat @click.native="showDialog = false"><v-icon>close</v-icon></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-container fluid grid-list-lg>

      <div class="row mt-5">
        <div class="col">
          <btable :items="[order]" :fields="orderFields"></btable>
        </div>
      </div>
      <v-layout class="px-5" row>
        <v-flex xs6 md3 >
          <v-select v-model="order.statuses.custom_phone" label="Приоритетный телефон заказа" :items="phones" @change="updateStatuses"></v-select>
        </v-flex>
        <v-flex xs6 md3 >
          <v-select v-model="order.statuses.custom_email" label="Приоритетный email заказа" :items="emails" @change="updateStatuses"></v-select>
        </v-flex>
        <v-flex xs6 md3 >
          <v-btn icon @click="clear"><v-icon>clear</v-icon></v-btn>
        </v-flex>
        <v-flex xs6 md3 >
          <v-btn flat><a :href="'api/pdf/invoice/' + order.id" target="_blank">Скачать PDF</a></v-btn>
          <v-btn flat><a :href="'api/pdf/invoice/' + order.id + '?with_discount=true'" target="_blank">Скачать PDF (скидки)</a></v-btn>
        </v-flex>
        <v-flex xs6 md3 >
          <v-btn @click="refreshOrder" flat><v-icon small class="mr-2" >refresh</v-icon>Обновить заказ</v-btn>

        </v-flex>
      </v-layout>
      <div class="row">
        <div class="col">
          <label>Товары</label>
          <btable :items="order.products" :notstriped="true" :fields="productFields">
          <template slot="row" slot-scope="data">
            <tr v-for="(item, index)  in data.items" :key="item.id" :class="{'pink lighten-5': item.on_sale}">
              <td style="width: 40px;">{{index + 1}}</td>
              <td style="width: 63px;"><img width="40" :src="img40(item.image)" /></td>
              <td>{{item.name}}</td>
              <td>{{item.sku}}</td>
              <td>{{item.quantity}}</td>
              <td>{{item.purchase}}</td>
              <td>
                {{item.price}}
                <span class="grey--text" v-if="item.prom_price && item.prom_price != item.price">({{item.prom_price}})</span>
              </td>
              <td>
                <input
                   :value="item.discount"
                   @keypress.enter="saveDiscount($event, item)"
                   @focus="if(item.discount == 0) item.discount = ''"
                  @blur="if(item.discount == '') item.discount = 0"
                   ref="discounts"
                />
              </td>
              <td>{{Math.round(item.quantity*item.price*(1-item.discount/100) * 100) / 100}}</td>
            </tr>
          </template>

          <template slot="footer">
            <td colspan="5"></td>
            <td>{{sumPurchase.toFixed(2)}} грн.</td>
            <td>{{sumPrice.toFixed(2)}} грн.</td>
            <td><input class="mass-discount" @keypress.enter="setMassDiscount" /></td>
            <td>{{sumPriceWithDiscount.toFixed(2)}} грн.</td>
          </template>
          </btable>
        </div>
      </div>
      </v-container>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
        <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
    export default {
      props: ['order', 'name'],
      data() {
        return {
          customer: null,
          showDialog: false,
          orderFields: [
            { key: 'prom_id', label: 'Id' },
            { key: 'status', label: 'Статус' },
            { key: 'delivery_option', label: 'Доставка' },
            { key: 'payment_option', label: 'Оплата' },
            { key: 'price', label: 'Цена' },
            { key: 'phone', label: 'Телефон' },
            { key: 'email', label: 'Email' },
            { key: 'client_first_name', label: 'Клиент' },
            { key: 'delivery_address', label: 'Адрес' },
            { key: 'prom_date_created', label: 'Дата' },
          ],
          productFields: [
            { key: 'idx', label: '' },
            { key: 'image', label: '' },
            { key: 'name', label: 'Название' },
            { key: 'sku', label: 'Артикул' },
            { key: 'quantity', label: 'Кол-во' },
            { key: 'purchase', label: 'Закуп. цена' },
            { key: 'price', label: 'Цена' },
            { key: 'discount', label: 'Скидка' },
            { key: 'sum', label: 'Сумма' },
          ]
        }
      },
      watch: {
        showDialog (val) {
          if (val) {
            axios.get('api/customers/' + this.order.customer_id).then((res) => {
              this.customer = res.data
            })
          }
        }
      },
      computed: {
        sumPrice () {
          let sum = 0
          this.order.products.map((product) => {
            sum += product.price * product.quantity
          })
          return sum
        },
        sumPurchase () {
          let sum = 0
          this.order.products.map((product) => {
            sum += product.purchase * product.quantity
          })
          return sum
        },
        sumPriceWithDiscount () {
          let sum = 0
          this.order.products.map((item) => {
            sum += item.quantity*item.price*(1-item.discount/100)
          })
          return sum
        },
        phones () {
          if (this.customer == null) return []
          let a = []
          this.customer.phones.map((el) => a.push(el.phone))
          return a
        },
        emails () {
          if (this.customer == null) return []
          let a = []
          this.customer.emails.map((el) => a.push(el.email))
          return a
        }
      },
      methods: {
        img40(img) {
          return img.replace(/w\d+/, 'w40').replace(/h\d+/, 'h40')
        },
        setMassDiscount (e) {
          this.order.products.map((item) => {
            item.discount = e.target.value
          })
          axios.post('api/orderproducts/massdiscount', { items: this.order.products}).then((res) => {
            console.log(res.data)
          })
        },
        saveDiscount (e, item) {
          item.discount = e.target.value
          axios.put('api/orderproducts/' + item.id, { discount: item.discount || 0 }).then((res) => {
            console.log(res.data)
          })
          const index = this.$refs.discounts.indexOf(e.target)
          if (typeof(this.$refs.discounts[index + 1]) != 'undefined') {
            const next = this.$refs.discounts[index + 1]
            if (next.value == 0) {
              next.value = ''
            }
            next.focus()
          }
        },
        refreshOrder () {
          axios.get('api/orders/updatefromprom/' + this.order.prom_id).then((res) => {
            this.$emit('update')
            console.log(res.data)
          })
        },
        save() {
          this.showDialog = false
        },
        clear () {
          this.order.statuses.custom_email = null
          this.order.statuses.custom_phone = null
          this.updateStatuses()
        },
        updateStatuses() {
          axios.put('api/orderstatus/' + this.order.statuses.id, this.order.statuses).then((res) => {
            console.log(res.data)
          })
        },
      },
      mounted() {
      }
    }
</script>
<style scoped>

table tr:hover {
  background-color: #FFF9C4;
}

table tr:hover input{
  border: 1px solid lightgray;
}


table td input {
  background-color: #fff;
  padding: 2px 4px;
  border-radius: 3px;
  width: 50px;
}
.mass-discount {
  border: 1px solid lightgray;
}
</style>
