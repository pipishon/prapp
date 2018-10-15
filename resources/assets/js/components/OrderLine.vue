<template>
  <tr class="order-line" >
        <td class="align-middle" style="padding: 7px;">
            <v-checkbox flat class="mt-0" :value="selected.indexOf(data.item) != -1" @change="changeMass"> </v-checkbox>
        </td>

        <td >
          <span class="text-nowrap">{{data.item.prom_id}} <v-icon  v-if="data.item.source == 'mobile_app'">smartphone</v-icon>
            <a :href="'https://my.prom.ua/cabinet/order_v2/edit/' + data.item.prom_id" target="_blank"><v-icon small>open_in_new</v-icon></a>
          </span>
          <div v-html="orderDate(data)" class="text-nowrap"></div>
          <order :order="data.item"><span>Товаров {{data.item.products.length}} шт</span></order>
          <datedelivery :delivery="dictionary.delivery[data.item.delivery_option]" :item="data.item.statuses" :id="data.item.id" />
        </td>

        <td>
          <workout :item="data.item"></workout>
        </td>


        <td :class="{'green lighten-5': data.item.statuses.payment_status == 'Оплачен', 'pink lighten-5': data.item.statuses.payment_status == 'Наложенный'}">
          <paymentstatus :delivery="dictionary.delivery[data.item.delivery_option]" :item="data.item.statuses" />
        </td>

        <td class="comments">
          <div class="content">
            {{data.item.customer.comment}}
            <div v-if="data.item.client_notes" >
              <div><strong><i>Комментарий клиента</i></strong></div>
              <span>
                {{data.item.client_notes}}
              </span>
            </div>
          </div>
        </td>

        <td class="text-nowrap">
          <strong>{{data.item.price}}</strong>
        </td>

        <td>
          <div class="information">
            <div>
              <template v-if="!showDeliverySelect">
                <v-icon class="mr-2 " small>local_shipping</v-icon>
                <u @click="showDeliverySelect = true"><span v-if="data.item.delivery_option != null" > {{mapDelivery}}</span><span v-else>Не указан</span></u>
                </template>
              <v-select @blur="showDeliverySelect = false" v-if="showDeliverySelect" class="m-0 p-0 text-nowrap" :items="Object.values(dictionary.delivery)" :value="mapDelivery" @input="saveDelivery" prepend-icon="local_shipping" small></v-select>
            </div>
            <div class="text-nowrap" >
              <template v-if="!showPaymentSelect">
                <v-icon class="mr-2 " small>credit_card</v-icon><u @click="showPaymentSelect = true"><span v-if="data.item.payment_option != ''">{{mapPayment}}</span><span v-else>Не указан</span></u>
              </template>
              <v-select v-if="showPaymentSelect" class="m-0 p-0 text-nowrap" :items="Object.values(dictionary.payment)" :value="mapPayment"
                  @blur="showPaymentSelect = false" @input="savePayment" prepend-icon="credit_card" small></v-select>
            </div>
            <newpost :item="data.item" v-if="dictionary.delivery[data.item.delivery_option] == 'Новая Почта'"/>
            <div class="my-2" v-else>
              <span @click="showAddressTextarea = true" v-if="!showAddressTextarea">{{data.item.delivery_address}}</span>
              <v-textarea class="m-0" @blur="showAddressTextarea = false; save()" rows="2" auto-grow v-if="showAddressTextarea" v-model="data.item.delivery_address" @focus="checkDeliveryAdress"></v-textarea>
            </div>
            <v-text-field v-if="dictionary.delivery[data.item.delivery_option] != 'Самовывоз'" class="my-0" v-model="data.item.statuses.ttn_string"  label="ТТН" @keyup.enter.native="saveTTN" :class="{blink: ttnSaved}"></v-text-field>
          </div>
        </td>

        <td>
          <customer :item="data.item" :id="data.item.customer.id">{{data.item.client_last_name}} {{data.item.client_first_name}}</customer>
          <div>
            <strong>{{data.item.customer.manual_status}}</strong>
            <v-icon v-if="data.item.customer.stars != null" small :key="i" v-for="i in 5" class="ml-1" :color="(data.item.customer.stars < i) ? 'blue-grey lighten-3' : 'purple lighten-1'">star</v-icon>
          </div>
          <div>
            <div v-if="data.item.statuses.custom_phone">{{data.item.statuses.custom_phone}}</div>
            {{data.item.phone}}
          </div>
          <div>
            <div v-if="data.item.statuses.custom_email">{{data.item.statuses.custom_email}}</div>
            {{data.item.email}}
          </div>
          <div v-if="data.item.customer.statistic">
            <div>
              <strong>{{data.item.customer.statistic.count_orders}}</strong> {{orderString(data.item.customer.statistic.count_orders)}} на <strong>{{data.item.customer.statistic.total_price}}</strong> грн
            </div>
            <div>
              <strong>{
                {{data.item.customer.statistic.count_orders_delivered}}:
                {{data.item.customer.statistic.count_orders_received}}:
                {{data.item.customer.statistic.count_orders_canceled}}
                }</strong>,
              {{data.item.customer.statistic.days_after_last_order}}
              {{daysString(data.item.customer.statistic.days_after_last_order)}}
            </div>
          </div>
          <div>
            <a :href="'https://www.facebook.com/' + data.item.customer.facebook_id" v-if="data.item.customer.facebook_id" target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="17px" height="17px" viewBox="0 0 60.734 60.733" > <path d="M57.378,0.001H3.352C1.502,0.001,0,1.5,0,3.353v54.026c0,1.853,1.502,3.354,3.352,3.354h29.086V37.214h-7.914v-9.167h7.914   v-6.76c0-7.843,4.789-12.116,11.787-12.116c3.355,0,6.232,0.251,7.071,0.36v8.198l-4.854,0.002c-3.805,0-4.539,1.809-4.539,4.462   v5.851h9.078l-1.187,9.166h-7.892v23.52h15.475c1.852,0,3.355-1.503,3.355-3.351V3.351C60.731,1.5,59.23,0.001,57.378,0.001z" style="fill: #4b679d;"></path></svg>
            </a>
            <a :href="'https://www.instagram.com/' + data.item.customer.instagram_id +'/'" v-if="data.item.customer.instagram_id" target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 512 512" width="17px" height="17px"> <g> <path d="M352,0H160C71.648,0,0,71.648,0,160v192c0,88.352,71.648,160,160,160h192c88.352,0,160-71.648,160-160V160    C512,71.648,440.352,0,352,0z M464,352c0,61.76-50.24,112-112,112H160c-61.76,0-112-50.24-112-112V160C48,98.24,98.24,48,160,48    h192c61.76,0,112,50.24,112,112V352z" style="fill: rgb(132, 102, 168);"></path> </g> <g> <path d="M256,128c-70.688,0-128,57.312-128,128s57.312,128,128,128s128-57.312,128-128S326.688,128,256,128z M256,336    c-44.096,0-80-35.904-80-80c0-44.128,35.904-80,80-80s80,35.872,80,80C336,300.096,300.096,336,256,336z" style="fill: rgb(132, 102, 168);"></path> </g> <g> <circle cx="393.6" cy="118.4" r="17.056" style="fill: rgb(132, 102, 168);"></circle> </g> </svg>
              @{{data.item.customer.instagram_id}}
            </a>

          </div>
        </td>

        <td class="ma-0 pa-0">
          <v-textarea style="font-size: 14px;" class="mt-0 pt-0" solo auto-grow @blur="save()" v-model="data.item.comment"></v-textarea>
        </td>

        <td :class="{'green lighten-5': item.statuses.collected_string == 'Собран'}">
          <ordercollected :item="data.item" @change="updateStatuses"></ordercollected>
        </td>

        <td>
          <orderstatus :item="data.item" @change="updateStatuses"></orderstatus>
        </td>
  </tr>
</template>

<script>
    import order from './Order'
    import customer from './Customer'
    import orderstatus from './OrderStatus'
    import datedelivery from './DateDelivery'
    import paymentstatus from './PaymentStatus'
    import ordercollected from './OrderCollected'
    import workout from './Workout'
    import newpost from './NewPost'
    import ClickOutside from 'vue-click-outside'
    import * as moment from 'moment';
    import { mapMutations, mapGetters } from 'vuex'

    export default {
      props: ['item', 'dictionary'],
      directives: {
        ClickOutside
      },
      data() {
        return {
          ttnSaved: false,
          showDeliverySelect: false,
          showPaymentSelect: false,
          showAddressTextarea: false,
          sNotDelivered: false,
          payStatus: '',
          /*delivery: {
            'Пункты самовывоза': 'Самовывоз',
            'Новая Почта': 'Новая Почта',
            'Доставка Укрпочтой (25-55 грн, оплачивается вместе с заказом)': 'Укрпочта',
            'Доставка "Ин тайм" (отправки – по средам)': 'Ин тайм'
          },
          payment: {
            'Покупка без риска': 'Покупка без риска',
            'Наличными при самовывозе из офиса (Киев, Урловская)': 'Наличные',
            'Приват24': 'Приват24',
            'не указан': 'не указан',
            'Платёжный терминал ПриватБанка (комиссия от 2 грн)': 'Терминал ПриватБанка',
            'Платёжный терминал ПриватБанка (комиссия от 2 грн)': 'Терминал ПриватБанка',
            'Платёжные терминалы типа IBox или карты других банков (НЕ рекомендуем!)': 'Терминал IBox',
            'Наложенный платеж (для заказов от 200 грн до 1000 грн, только Новая Почта)': 'Наложенный платеж'
          },*/
        }
      },
     computed: {
        ...mapGetters(['selected']),
        data () {
          return {item: this.item}
        },
        deliveryArr () {
          return Object.values(this.delivery)
        },
        mapDelivery () {
          if (this.data.item.delivery_option != null) {
            return this.dictionary.delivery[this.data.item.delivery_option.trim()]
          } else {
            return 'Не указан'
          }
        },
        mapPayment () {
          if (this.data.item.payment_option != null) {
            return this.dictionary.payment[this.data.item.payment_option.trim()]
          } else {
            return 'Не указан'
          }
        },
      },
      components: {
        order,
        customer,
        orderstatus,
        datedelivery,
        paymentstatus,
        ordercollected,
        workout,
        newpost
      },
      methods: {
        ...mapMutations(['massSelection']),
        changeMass(val) {
          if (val) {
            const massItems = Object.assign([], this.selected)
            massItems.push(this.item)
            this.massSelection(massItems)
          } else {
            const massItems = this.selected.filter(el => el.id != this.item.id)
            this.massSelection(massItems)
          }
        },
        saveTTN() {
          this.ttnSaved = true;
          setTimeout(() => {
            this.ttnSaved = false;
          }, 500)
          this.updateStatuses()
        },
        changeCollected (val) {
          this.updateStatuses()
          if (val == 'Собран') {
            this.showCompleteDialog = true
          }
        },
        checkDeliveryAdress () {
          this.data.item.delivery_address = (this.data.item.delivery_address == 'Не указан') ? '' : this.data.item.delivery_address
          this.showAddressTextarea = true
        },
        saveDelivery (val) {
          for (let name in this.dictionary.delivery) {
            if (this.dictionary.delivery[name] == val) {
              this.item.delivery_option = name
              this.save()
            }
          }
          this.showDeliverySelect = false
        },
        savePayment (val) {
          for (let name in this.dictionary.payment) {
            if (this.dictionary.payment[name] == val) {
              this.item.payment_option = name
              this.save()
            }
          }
          this.showPaymentSelect = false
        },
        showNotDelivered (val) {
          this.searchQuery['status'] = (val) ? 'not-delivered' : 'all';
          this.getList()
        },
        updateStatuses() {
          axios.put('api/orderstatus/' + this.item.statuses.id, this.item.statuses).then((res) => {
            console.log(res.data)
          })
        },
        orderString (n) {
          let r = n%10;
          if (n > 5 && n < 21) { return 'заказов' }
          if (r == 1) { return 'заказ' }
          if (r > 1 && r < 5) { return 'заказа' }
          return 'заказов'
        },
        daysString (n) {
          let r = n%10;
          if (n > 5 && n < 21) { return 'дней' }
          if (r == 1) { return 'день' }
          if (r > 1 && r < 5) { return 'дня' }
          return 'дней'
        },
        customerUpdated () {
        },
        orderDate (order) {
          return moment(order.item.prom_date_created).format('HH:mm <br /> DD.MM.YYYY')
        },
        cellClick (data) {
          switch(data.key) {
            case 'comment':
              this.editeComment = data.item.id
              break
            default:
              this.editeComment = ''
              break
          }
        },
        saveComment(event, order) {
          order.comment = event.target.value
          this.editeComment = -1
          axios.put('api/orders/' + order.id, {comment: order.comment}).then((res) => {
            console.log(res.data)
          })
        },
        save () {
          if (this.item.delivery_address == '') { this.item.delivery_address = 'Не указан' }
          axios.put('api/orders/' + this.item.id, this.item).then((res) => {
            console.log(res.data)
          })
        },
        mapStatus (name) {
          let map = {
            'pending': 'Новый',
            'received': 'Принят',
            'delivered': 'Выполнен',
            'canceled': 'Отменен',
            'draft': 'Черновик',
            'paid': 'Оплаченный',
          }
          return map[name];
        },
      },
      mounted() {
      }

    }
</script>
<style>
.order-line .comments {
  width: 15rem;
  position: relative;
  padding: 0;
}

.modal-lg {
  max-width: 80vw;
}

.order-line .comments .content {
  max-height: 12rem;
  padding: 0.75rem;
  overflow: hidden;
  background-color: #FAFAFA;
  width: 100%;
}

.order-line.pink .comments .content {
  background-color: #fce4ec!important;
}
.order-line.green .comments .content {
  background-color: #e8f5e9;
}

.order-line .comments .content:hover {
  position: absolute;
  z-index: 2;
  min-height: 100%;
  max-height: 100vh;
  border-bottom: 1px solid #dee2e6;
}
.order-line .blink {
  animation: blink 500ms infinite;  /* IE 10+, Fx 29+ */
}

@-webkit-keyframes blink {
  0%, 49% {
    background-color: #e8f5e9;
  }
  50%, 100% {
    background-color: white;
  }
}
.order-line .v-input--selection-controls.v-input--is-disabled.v-input--is-label-active .v-icon {
  color: #82b1ff !important;

}
.v-textarea.v-text-field--box.v-text-field--single-line .v-text-field__prefix, .v-textarea.v-text-field--box.v-text-field--single-line textarea, .v-textarea.v-text-field--enclosed.v-text-field--single-line .v-text-field__prefix, .v-textarea.v-text-field--enclosed.v-text-field--single-line textarea {
  margin-top: 0;
  height: 170px !important;
}
.v-text-field.v-text-field--solo:not(.v-text-field--solo-flat) .v-input__slot {
  height: 170px;
}
</style>
