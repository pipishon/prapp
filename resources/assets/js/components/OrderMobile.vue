<template>
  <v-dialog class="mobile-order"  persistent v-model="showDialog" fullscreen transition="dialog-left-transition" >
    <v-list-tile
        slot="activator"
        avatar
        @click=""
      >
        <v-list-tile-avatar :size="70">
          <img :src="maxPrice.product.main_image">
        </v-list-tile-avatar>

        <v-list-tile-content class="ml-3">
          <div class="caption" style="width: 155px;">
            <div>
              <span class="body-2">№ {{order.prom_id}}</span>
            </div>
            <div>
              {{order.products.length}} товаров
            </div>
            <div>
              <span class="body-2">
                <strong>{{order.statuses.payment_price}} грн</strong>
              </span>
              &nbsp;|&nbsp;
              <span
                :class="{'green--text': order.statuses.payment_status == 'Оплачен',
                  'red--text text--darken-4': order.statuses.payment_status == 'Наложенный'
                }"
                >{{order.statuses.payment_status}}</span>
            </div>
            <div>
              <v-icon small>person</v-icon><span>{{fullName}}</span>
            </div>
            <div>
              <v-icon small>directions_car</v-icon><span>{{order.delivery_option}}</span>
            </div>
          </div>
        </v-list-tile-content>
        <v-list-tile-action class="ml-3" >
          <div class="caption text-no-wrap">
            <div v-if="order.statuses.shipment_date">
              <v-icon small>calendar_today</v-icon><span>&nbsp;{{deliveryDateString}}</span>
            </div>
            <div class="mt-2">
              <v-icon small v-if="order.statuses.collected_string == 'Собран'">check_circle_outline</v-icon>
              <v-icon small v-else>history</v-icon>
              <span>{{order.statuses.collected_string}}</span>
            </div>
          </div>
        </v-list-tile-action>
    </v-list-tile>
    <v-card v-if="showDialog" >
      <v-toolbar flat card dense class="grey darken-1 mobile-order">
        <v-toolbar-items @click="showDialog = false">
          <v-icon color="white">keyboard_arrow_left</v-icon><span  class="white--text " style="padding-top: 11px; font-size: 18px;" >  Назад </span>
        </v-toolbar-items>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-icon @click="refreshOrder" color="white">refresh</v-icon>
        </v-toolbar-items>
      </v-toolbar>
      <div class="px-2 mt-1">
        <div >
          <strong>Заказ № {{order.prom_id}}</strong>
          <span class="ml-2">на {{order.statuses.payment_price}} грн</span>
        </div>
        <div class="mt-1">
          <span>от {{order.prom_date_created}}</span>
        </div>
        <div class="mt-1">
          <div class="grey--text">Клиент:</div>
          <span>{{order.client_first_name}} {{order.client_last_name}} | {{order.customer.statistic.count_orders}} {{orderString(order.customer.statistic.count_orders)}} на {{order.customer.statistic.total_price}} грн
            </span>
        </div>
        <div v-if="order.client_notes" class="mt-1">
          <div class="grey--text">Комментарий:</div>
          <span>{{order.client_notes}}</span>
        </div>
        <div class="mt-1">
          <div class="grey--text">Доставка:</div>
          <span>{{order.delivery_option}}</span>
        </div>
      </div>
      <div class="pa-2 mt-2" style="border-top: 1px solid lightgray; border-bottom: 1px solid lightgray;">
        <strong>Товары в заказе (<span class="body-1"><strong>{{order.products.length}}</strong></span> шт):</strong>
      </div>
      <v-list class="mobile-order px-2">
        <div class="my-1" v-for="(item, index) in orderedProducts"
          style="border-bottom: 1px solid #F5F5F5;"
          >
            <v-list-tile
                :key="item.id"
                avatar
                @click=""
              three-lines
              >
                <v-list-tile-avatar :size="70">
                  <img :src="item.product.main_image">
                </v-list-tile-avatar>

                <v-list-tile-content class="ml-2 mr-2 caption">
                  <div>
                    <div class="body-1" style="line-height: 1.4;">{{item.name}}</div>
                    <div class="grey--text" style="font-size: 13px;">Код: {{item.sku}}</div>
                    <div class="body-1">{{convertPrice(item.product.price)}}&nbsp;грн&nbsp;|&nbsp;<strong>{{item.quantity}} шт</strong>&nbsp;|&nbsp;{{convertPrice(item.product.price * item.quantity)}} грн</div>
                  </div>
                </v-list-tile-content>
              </v-list-tile>
        </div>
      </v-list>
    </v-card>
  </v-dialog>
</template>

<script>
import * as moment from 'moment';
    export default {
      props: ['order' ],
      data() {
        return {
          customer: null,
          showDialog: false,
        }
      },
      computed: {
        fullName () {
          let names = this.order.client_first_name.split(' ')
          names = [...names, ...this.order.client_last_name.split(' ')]
          return names[0] + ' ' + names[1]
        },
        deliveryDateString() {
          let today = moment()
          let tomorrow = moment().add(1, 'd')
          if (today.isSame(this.order.statuses.shipment_date, 'day')) return 'Сегодня'
          if (tomorrow.isSame(this.order.statuses.shipment_date, 'day')) return 'Завтра'
          return this.order.statuses.shipment_date
        },
        orderedProducts: function () {
          return _.orderBy(this.order.products, [ 'product.sort1', 'product.name'])
        },
        maxPrice () {
          if (this.order.products.length == 0) return {product: {}}
          return this.order.products.reduce((acc, curr) => {
            if (typeof(acc.product) == 'undefined') return curr
            return (acc.product.price > curr.product.price) ? acc : curr
          })
        }
      },
      methods: {
        convertPrice (price) {
          return price//.toString().replace('.', ',')
        },
        orderString (n) {
          let r = n%10;
          if (n > 5 && n < 21) { return 'заказов' }
          if (r == 1) { return 'заказ' }
          if (r > 1 && r < 5) { return 'заказа' }
          return 'заказов'
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
      },
      mounted() {
      }
    }
</script>
<style>
.mobile-order .v-avatar,
.mobile-order .v-avatar img
{
  border-radius: 0;
}
.mobile-order .v-list__tile {
  padding: 0;
}
.mobile-order .v-list__tile--avatar {
  height: 90px;
}
.mobile-order .v-list__tile__avatar {
  min-width: 70px;
}
.mobile-order .v-list__tile__action {
  align-items: start;
}
.mobile-order .v-toolbar__content {
  padding-left: 0 !important;
}
</style>
