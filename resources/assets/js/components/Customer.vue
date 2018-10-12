<template>
  <v-dialog v-model="showDialog"  >
    <a href="#" slot="activator"><slot></slot></a>
    <v-card v-if="showDialog" class="customer">
      <v-toolbar flat card dense>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn flat @click.native="showDialog = false"><v-icon>close</v-icon></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-container fluid grid-list-lg>
    <div class="row">
      <div class="form-group col">
        <label>Names</label>
        <template v-for="customer in customersToMerge">
          <ul class="list-group not-merged">
            <li class="list-group-item" v-for="name in customer.name">{{name}}</li>
          </ul>
        </template>
        <draggable v-model="customer.name" :element="'ul'" @change="save" class="list-group">
          <li class="list-group-item" v-for="name in customer.name">{{name}}</li>
        </draggable>
      </div>
      <div class="form-group col">
        <div class="row">
          <div class="col-9">
            <v-text-field v-model="phoneToAdd" label="Добавить телефон"></v-text-field>
          </div>
          <div class="col-3">
            <v-btn @click="addPhone" icon color="primary" flat><v-icon>add_box</v-icon></v-btn>
          </div>
        </div>
        <template v-for="customer in customersToMerge">
          <ul class="list-group not-merged" v-if="customer.phones">
            <li class="list-group-item" v-for="phone in customer.phones">{{phone.phone}}</li>
          </ul>
        </template>
        <ul class="list-group " v-if="customer.phones">
          <li class="list-group-item" v-for="phone in customer.phones">{{phone.phone}}</li>
        </ul>
      </div>
      <div class="form-group col">
        <div class="row">
          <div class="col-9">
            <v-text-field v-model="emailToAdd" label="Добавить email"></v-text-field>
          </div>
          <div class="col-3">
            <v-btn @click="addEmail" icon color="primary" flat><v-icon>add_box</v-icon></v-btn>
          </div>
        </div>
        <template v-for="customer in customersToMerge">
          <ul class="list-group not-merged" v-if="customer.phones">
            <li class="list-group-item" v-for="email in customer.emails">{{email.email}}</li>
          </ul>
        </template>
        <ul class="list-group" v-if="customer.phones">
          <li class="list-group-item" v-for="email in customer.emails">{{email.email}}</li>
        </ul>
      </div>
      <div class="form-group col">
        <v-select :items="[' ', 'VIP']" label="Статус" v-model="customer.manual_status" @input="onStatusChange">
        </v-select>
        <div class="mb-1">
            <v-icon  class="ml-1" v-for="i in 5" :key="i" @click="customer.stars = i" :color="(customer.stars < i) ? 'blue-grey lighten-3' : 'purple lighten-1'">star</v-icon>
        </div>
        <div>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="17px" height="17px" viewBox="0 0 60.734 60.733" > <path d="M57.378,0.001H3.352C1.502,0.001,0,1.5,0,3.353v54.026c0,1.853,1.502,3.354,3.352,3.354h29.086V37.214h-7.914v-9.167h7.914   v-6.76c0-7.843,4.789-12.116,11.787-12.116c3.355,0,6.232,0.251,7.071,0.36v8.198l-4.854,0.002c-3.805,0-4.539,1.809-4.539,4.462   v5.851h9.078l-1.187,9.166h-7.892v23.52h15.475c1.852,0,3.355-1.503,3.355-3.351V3.351C60.731,1.5,59.23,0.001,57.378,0.001z" style="fill: #4b679d;"></path> </svg>
            <v-text-field v-model="customer.facebook_id" style="width: 80%; display: inline-block" label="Facebook id"></v-text-field>
        </div>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 512 512" width="17px" height="17px"> <g> <path d="M352,0H160C71.648,0,0,71.648,0,160v192c0,88.352,71.648,160,160,160h192c88.352,0,160-71.648,160-160V160    C512,71.648,440.352,0,352,0z M464,352c0,61.76-50.24,112-112,112H160c-61.76,0-112-50.24-112-112V160C48,98.24,98.24,48,160,48    h192c61.76,0,112,50.24,112,112V352z" style="fill: rgb(132, 102, 168);"></path> </g> <g> <path d="M256,128c-70.688,0-128,57.312-128,128s57.312,128,128,128s128-57.312,128-128S326.688,128,256,128z M256,336    c-44.096,0-80-35.904-80-80c0-44.128,35.904-80,80-80s80,35.872,80,80C336,300.096,300.096,336,256,336z" style="fill: rgb(132, 102, 168);"></path> </g> <g> <circle cx="393.6" cy="118.4" r="17.056" style="fill: rgb(132, 102, 168);"></circle> </g> </svg>
            <v-text-field class="m-0" v-model="customer.instagram_id" style="width: 80%; display: inline-block" label="Instagram id"></v-text-field>
            <v-checkbox class="m-0" v-model="customer.bill_required" label="Счет (обязятельно)"></v-checkbox>
            <v-checkbox class="m-0" v-model="customer.gift_required" label="Подарок (обязятельно)"></v-checkbox>
            <v-textarea class="m-0" row-height="14" v-model="customer.gifts" auto-grow label="Подарки"></v-textarea>
      </div>
    </div>
    <div class="row">
      <div class="form-group col-12">
        <v-textarea row-height="14" v-model="customer.comment" auto-grow label="Комментарий"></v-textarea>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <h3>Orders</h3>
        <btable :items="customer.orders" :fields="orderFields" :rownumber="1">
        <template slot="prom_id" slot-scope="data">{{data.item.prom_id}}<a :href="'https://my.prom.ua/cabinet/order_v2/edit/' + data.item.prom_id" target="_blank"><v-icon small>open_in_new</v-icon></a></template>
        <template slot="status" slot-scope="data">{{$store.state.statuses[data.item.status]}}</template>
        </btable>
        <template v-for="customer in customersToMerge">
          <btable :items="customer.orders" class="not-merged" :fields="orderFields"></btable>
        </template>
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
import draggable from 'vuedraggable'

    export default {
      props: ['id', 'name', 'item'],
      data() {
        return {
          customer: {},
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
          emailToAdd: '',
          phoneToAdd: '',
          customersToMerge: []
        }
      },
      components: {
        draggable
      },
      watch: {
        showDialog (val) {
          if (val) {
            this.getCustomer()
          }
        }
      },
      methods: {
        initValues () {
          this.emailToAdd = ''
          this.phoneToAdd = ''
          this.customersToMerge = []
        },
        onStatusChange (val) {
          axios.put('api/customers/' + this.customer.id, {'manual_status': val}).then((res) => {
            this.$emit('updated')
          })
        },
        addEmail () {
          this.processPhoneEmail({ email: this.emailToAdd })
        },
        addPhone () {
          this.processPhoneEmail({ phone: this.phoneToAdd })
        },
        processPhoneEmail (params) {
          axios.get('api/customers/phoneemail', {params}).then((res) => {
            if (res.data.id != this.customer.id) {
              if (res.data.length != 0) {
                this.customersToMerge.push(res.data)
              } else {
                params.id = this.customer.id
                axios.get('api/customers/addphoneemail', {params}).then((res) => {
                  this.getCustomer()
                })
              }
            }
          })
        },
        save () {
          /*let params = {
            name: this.customer.name,
            comment: this.customer.comment,
            stars: this.customer.stars,
            facebook_id: this.customer.facebook_id,
            instagram_id: this.customer.instagram_id
          }*/
          let params = {...this.customer }

          if (this.customersToMerge.length > 0) {
            let ids = []
            this.customersToMerge.map((customer) => { ids.push(customer.id) })
            params['merge'] = 1
            params['ids'] = ids
          }

          this.item.customer = this.customer
          axios.put('api/customers/' + this.customer.id, params).then((res) => {
            console.log(res.data)
            this.$emit('updated', this.customer.id)
            this.showDialog = false
          })
        },
        getCustomer () {
          axios.get('api/customers/' + this.id).then((res) => {
            this.customer = res.data
            this.initValues()
          })
        },
      },
      mounted() {
      }
    }
</script>
<style>
.customer .modal-lg {
  max-width: 80vw;
}
.customer .not-merged {
  color: gray;
}
.customer label {
  margin-bottom: 0;
}
</style>
