<template>
  <v-dialog  v-model="showDialog"  fullscreen transition="dialog-bottom-transition" >
    <a href="#" @click.prevent slot="activator"><slot></slot></a>
    <v-card v-if="showDialog && order != null">
      <v-toolbar flat card dense fixed>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn flat @click.native="showDialog = false"><v-icon>close</v-icon></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-container fluid grid-list-lg>

      <div class="row mt-5">
        <div class="col">
          <btable :items="[order]" :fields="orderFields" :notstriped="true">
            <template slot="row" slot-scope="data">
              <tr v-for="(item, index)  in data.items" :key="item.id" :class="{'pink lighten-5': item.on_sale}">
                <td>
                  {{item.prom_id}}
                </td>
                <td>
                  {{item.status}}
                </td>
                <td>
                  {{item.delivery_option}}
                </td>
                <td>
                  {{item.payment_option}}
                </td>
                <td>
                  {{sumPriceWithDiscount.toFixed(2)}} грн. (<span v-if="item.price.indexOf('грн') == -1">{{parseFloat(item.price).toFixed(2)}} грн.</span><span v-else>{{item.price.replace(',','.')}}</span>)
                </td>
                <td>
                  {{item.phone}}
                </td>
                <td>
                  {{item.email}}
                </td>
                <td>
                  {{item.client_first_name}}
                </td>
                <td>
                  {{item.delivery_address}}
                </td>
                <td>
                  {{item.prom_date_created}}
                </td>
              </tr>
            </template>
          </btable>
        </div>
      </div>
      <v-layout class="px-5" row>
        <v-flex xs6 md3 >
          <v-select v-model="order.statuses.custom_phone" label="Приоритетный телефон заказа" :items="phones" @change="updateStatuses"></v-select>
        </v-flex>
        <v-flex xs6 md3 >
          <v-select v-model="order.statuses.custom_email" label="Приоритетный email заказа" :items="emails" @change="updateStatuses"></v-select>
        </v-flex>
        <v-flex xs6 md5 >
          <v-btn icon @click="clear"><v-icon>clear</v-icon></v-btn>
            <v-btn @click="sendFeedback()" :class="{primary: feedbackSent || order.feedbackcount}" flat>
              Запрос на отзыв&nbsp;<span v-if="order.feedbackcount">({{order.feedbackcount}})</span>
            </v-btn>
            <v-progress-circular v-show="onSendFeedback"
              size="20" :width="2" indeterminate color="primary" >
            </v-progress-circular>

        </v-flex>
        <v-flex xs6 md3 >
          <v-btn flat><a :href="'api/pdf/invoice/' + order.id + '?sort=' + sort" target="_blank" @click="updateWithTimeout">Скачать PDF</a></v-btn>
          <v-btn flat><a :href="'api/pdf/invoice/' + order.id + '?with_discount=true&sort=' + sort" @click="updateWithTimeout" target="_blank">Скачать PDF (скидки)</a></v-btn>
        </v-flex>
        <v-flex xs6 md3 >
          <v-btn @click="refreshOrder(false)" flat><v-icon small class="mr-2" >refresh</v-icon>Обновить заказ</v-btn>
          <v-btn @click="refreshOrder(true)" flat><v-icon small class="mr-2" >refresh</v-icon>Обновить заказ со скидками</v-btn>

        </v-flex>
      </v-layout>
      <div class="row order">
        <div class="col">
          <label>Товары</label>
          <btable :items="sortedProducts" :notstriped="true" :fields="productFields">
          <template slot="row" slot-scope="data">
            <tr v-for="(item, index)  in data.items" :key="item.id" :class="{'pink lighten-5': item.on_sale}">
              <td style="width: 40px;">{{index + 1}}</td>
              <td style="width: 63px;"><img width="40" :src="img40(item.image)" /></td>
              <td>{{item.name}}</td>
              <td>{{item.sku}}</td>
              <td>{{item.quantity}}</td>
              <td>{{item.purchase}}</td>
              <td :class="{blink: priceSaved[item.id]}">

                <input
                    :value="oprice(item)"
                   @keypress.enter="savePrice($event, item)"
                />
                <span class="grey--text" v-if="item.prom_price && item.prom_price != item.price">({{item.prom_price}})</span>
              </td>
              <td :class="{blink: discountSaved[item.id]}">
                <input
                   :value="item.discount"
                   @keypress.enter="saveDiscount($event, item)"
                   @focus="if(item.discount == 0) item.discount = ''"
                  @blur="if(item.discount == '') item.discount = 0"
                   ref="discounts"
                />
              </td>
              <td>{{Math.round(item.quantity*oprice(item)*(1-item.discount/100) * 100) / 100}}</td>
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
        <v-btn :class="{primary: sort == 'name'}" flat @click="sort = 'name'" > Сорт. название </v-btn>
        <v-btn :class="{primary: sort == 'sort1'}" flat @click="sort = 'sort1'" > Сорт. сорт1 </v-btn>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
        <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
    export default {
      props: ['orderid', 'name'],
      data() {
        return {
          onSendFeedback: false,
          feedbackSent: false,
          order: null,
          priceSaved: {},
          discountSaved: {},
          customer: null,
          showDialog: false,
          sort: 'name',
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
            //console.log(this.order.id)
            axios.get('api/orders/' + this.orderid).then((res) => {
              this.order = res.data
              this.sort = 'name'
              this.order.products = _.orderBy(this.order.products, 'name')
              axios.get('api/customers/' + this.order.customer_id).then((res) => {
                this.customer = res.data
              })
            })
          }
        },

      },
      computed: {
        sortedProducts () {
          if (this.sort == 'name') {
            return _.orderBy(this.order.products, 'name')
          } else {
            return _.orderBy(this.order.products, ['sort1', 'name'], ['asc', 'asc'])
          }
        },
        sumPrice () {
          let sum = 0
          this.order.products.map((product) => {
            const price = product.order_price || product.price
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
            const price = item.order_price || item.price
            sum += item.quantity*price*(1-item.discount/100)
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
        oprice (product) {
          return product.order_price || product.price
        },
        img40(img) {
          return img.replace(/w\d+/, 'w40').replace(/h\d+/, 'h40')
        },
        setMassDiscount (e) {
          this.order.products.map((item) => {
            if (item.discount == 0) {
              item.discount = e.target.value
            }
          })
          axios.post('api/orderproducts/massdiscount', { items: this.order.products}).then((res) => {
            console.log(res.data)
          })
        },
        savePrice (e, item) {
          item.order_price = e.target.value
          this.$set(this.priceSaved, item.id, true);
          setTimeout(() => {
            this.priceSaved[item.id] = false;
          }, 500)
          axios.put('api/orderproducts/' + item.id, { price: item.order_price}).then((res) => {
            console.log(res.data)
          })
        },
        saveDiscount (e, item) {
          item.discount = e.target.value

          axios.put('api/orderproducts/' + item.id, { discount: item.discount || 0 }).then((res) => {
            this.$set(this.discountSaved, item.id, true);
            setTimeout(() => {
              this.discountSaved[item.id] = false;
            }, 500)
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
        updateWithTimeout ()
        {
          setTimeout(()=>{ this.$emit('update') }, 500)
        },
        sendFeedback () {
          this.onSendFeedback = true
          axios.get('api/orders/sendfeedback/' + this.order.prom_id).then((res) => {
            this.onSendFeedback = false
            this.feedbackSent = true
            console.log(res)
          })
        },
        refreshOrder (withDiscounts) {
          let params = {}
          if (withDiscounts) {
            params.with_discounts = true
          }
          axios.get('api/orders/updatefromprom/' + this.order.prom_id, {params}).then((res) => {
            this.$emit('update')
            axios.get('api/orders/' + this.order.id).then((res) => {
              this.order = res.data
              this.sort = 'name'
              this.order.products = _.orderBy(this.order.products, 'name')
              axios.get('api/customers/' + this.order.customer_id).then((res) => {
                this.customer = res.data
              })
            })
          })
        },
        save() {
          axios.get('api/orders/refresh/' + this.order.id).then((res) => {
            this.$emit('update')
            this.showDialog = false
          })
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
