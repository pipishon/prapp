<template>
  <v-dialog class="mobile-order"  v-model="showDialog" fullscreen transition="dialog-left-transition" >
    <v-list-tile
        slot="activator"
        avatar
        @click=""
      >
        <v-list-tile-avatar :size="70">
          <img :src="maxPrice.product.main_image">
        </v-list-tile-avatar>

        <v-list-tile-content class="ml-3">
          <div class="caption">
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
              <v-icon small>person</v-icon><span>{{order.client_first_name}} {{order.client_last_name}}</span>
            </div>
            <div>
              <v-icon small>directions_car</v-icon><span>{{order.delivery_option}}</span>
            </div>
          </div>
        </v-list-tile-content>
    </v-list-tile>
    <v-card v-if="showDialog" >
      <v-toolbar flat card dense >
        <v-toolbar-items>
          <v-btn flat @click.native="showDialog = false"> < Назад </v-btn>
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
          <span>{{order.client_first_name}} {{order.client_last_name}}</span>
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
        <strong>Товары в заказе (<span class="subheading"><strong>{{order.products.length}}</strong></span> шт):</strong>
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
                    <div>{{item.name}}</div>
                    <div class="grey--text">Код: {{item.sku}}</div>
                    <div>{{item.product.price}}&nbsp;грн&nbsp;|&nbsp;{{item.quantity}} шт&nbsp;|&nbsp;{{item.product.price * item.quantity}} грн</div>
                  </div>
                </v-list-tile-content>
              </v-list-tile>
        </div>
      </v-list>
    </v-card>
  </v-dialog>
</template>

<script>
    export default {
      props: ['order' ],
      data() {
        return {
          customer: null,
          showDialog: false,
        }
      },
      computed: {
        orderedProducts: function () {
          return _.orderBy(this.order.products, 'product.name')
        },
        maxPrice () {
          return this.order.products.reduce((acc, curr) => {
            if (typeof(acc.product) == 'undefined') return curr
            return (acc.product.price > curr.product.price) ? acc : curr
          })
        }
      },
      methods: {
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
</style>
