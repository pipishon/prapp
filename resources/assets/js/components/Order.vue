<template>
  <v-dialog  v-model="showDialog" fullscreen transition="dialog-bottom-transition" >
    <a href="#" slot="activator"><slot></slot></a>
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
        <v-flex xs6 offset-md3 md3 >
          <v-btn @click="refreshOrder" flat><v-icon small class="mr-2" >refresh</v-icon>Обновить заказ</v-btn>

        </v-flex>
      </v-layout>
      <div class="row">
        <div class="col">
          <label>Products</label>
          <btable :items="order.products" :fields="productFields"></btable>
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
            { key: 'prom_id', label: 'Prom id' },
            { key: 'status', label: 'Status' },
            { key: 'delivery_option', label: 'Delivery' },
            { key: 'payment_option', label: 'Payment' },
            { key: 'price', label: 'Price' },
            { key: 'phone', label: 'Phone' },
            { key: 'email', label: 'Email' },
            { key: 'client_first_name', label: 'Client' },
            { key: 'delivery_address', label: 'Adress' },
            { key: 'prom_date_created', label: 'Date' },
          ],
          productFields: [
            { key: 'sku', label: 'SKU' },
            { key: 'name', label: 'Name' },
            { key: 'price', label: 'Price' },
            { key: 'quantity', label: 'Quatity' },
            { key: 'prom_id', label: 'Prom id' },
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
</style>
