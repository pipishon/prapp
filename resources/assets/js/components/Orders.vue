<template>
  <div class="orders">
    <btable
       :items="list"
       :select-all="true"
       :fields="fields"
       :notstriped="true"
       :search="['prom_id', 'phone']"
       @search="onSearch"
       :orders="true"
       :widths="tableWidths"
       @updatewidth="updateWidths"
       >

      <template slot="row" slot-scope="data">
        <tr is="orderline" v-for="(item, key) in data.items" :dictionary="dictionary" :item="item" :key="key" :class="{'green lighten-5': item.status == 'delivered', 'pink lighten-5': item.status == 'canceled'}"></tr>
      </template>
    </btable>

    <v-footer fixed class="pa-3">
      <v-btn flat @click="refresh">Обновить заказы</v-btn>
      <v-btn flat @click="getAllOrders" :class="{primary: footerButton == 'all'}">Все заказы ({{globalStats.pending || 0}} | {{globalStats.received || 0}}) ({{allCollected.total}} / {{allCollected.collected}})</v-btn>

      <v-btn v-for="name in ['Новая Почта', 'Укрпочта', 'Самовывоз']" :key="name" flat @click="getSpecDeliveryOrders(name)" :class="{primary: footerButton == name}">{{name}} {{getDeliveryCollectedString(name)}}</v-btn>
      <v-btn flat @click="getNalogenii" :class="{primary: footerButton == 'nalogenii'}">Наложенный</v-btn>
      <v-btn @click="showNotDelivered" icon title="Показывать только не закрытые заказы"> <v-icon :color="(sNotDelivered) ? 'primary' : ''">no_sim</v-icon> </v-btn>
      <v-btn @click="showTodayDelivered" icon title="Показывать только с сегодняшней отгрузкой"> <v-icon :color="(sTodayDelivered == '1') ? 'primary' : ''">local_shipping</v-icon> </v-btn>
      <v-btn @click="showNotPayed" icon title="Показывать только неоплаченные заказы"> <v-icon :color="(sNotPayed == '1') ? 'primary' : ''">monetization_on</v-icon> </v-btn>
      <autosms />
      <v-tooltip top content-class="white black--text" >
        <v-icon slot="activator" color="success">info</v-icon>
        <div v-for="(stat, name) in globalStats" v-if="typeof(orderStatuses[name]) !='undefined'" class="p-2" :class="{'yellow darken-2': name=='pending'}">
            {{orderStatuses[name]}} : {{stat}}
          </div>
      </v-tooltip>

      <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
    </v-footer>
  </div>
</template>

<script>
    import orderline from './OrderLine'
    import autosms from './AutoSms'
    import { mapActions, mapGetters, mapMutations } from 'vuex'

    export default {
      data() {
        return {
          allCollected: {},
          deliveryCollected: {},
          globalStats: {},
          footerButton: 'all',
          sNotPayed: false,
          sNotDelivered: false,
          sTodayDelivered: false,
          payStatus: '',
          massAction: {},
          fields: [
            { key: 'prom_id', label: 'Заказ' },
            { key: 'workout', label: 'Обработка' },
            { key: 'payment', label: 'Оплата' },
            { key: 'comments', label: 'Комментарии' },
            { key: 'price', label: 'Цена', 'td_class': ['text-nowrap', 'text-center'] },
            { key: 'info', label: 'Информация' },
            { key: 'phone', label: 'Клиент'  },
            { key: 'comment', label: 'Рабочие комментарии', 'th_class': 'text-nowrap' },
            { key: 'complete', label: 'Сборка'},
            { key: 'status', label: 'Статус'},
          ],
          orderStatuses: {
            'pending': 'Новый',
            'received': 'Принят',
            'delivered': 'Выполнен',
            'canceled': 'Отменен',
            'all': 'Всего',
          },
          list: [],
          curPage: 0,
          lastPage: 0,
          searchQuery: {},
          tableWidths: {}
        }
      },
      computed: {
        ...mapGetters(['settings']),
        dictionary () {
          return this.$store.state.dictionary
        }
      },
      watch: {
        settings: {
          handler () {
            this.tableWidths = (typeof(this.settings.order_table_widths) != 'undefined') ? JSON.parse(this.settings.order_table_widths) : {}
          },
          deep: true
        }
      },
      components: {
        orderline,
        autosms
      },
      methods: {
        ...mapMutations(['setOrders']),
        ...mapActions(['updateSettings']),
        updateWidths () {
          this.updateSettings({name: 'order_table_widths', value: JSON.stringify(this.tableWidths)})
        },
        getDeliveryCollectedString(name) {
           let n = Object.keys(this.dictionary.delivery).reduce((obj,key) => {
               obj[ this.dictionary.delivery[key] ] = key;
               return obj;
            },{});
           let val = n[name]
           if (this.deliveryCollected[val] != 'undefined') {
             let m = this.deliveryCollected[val]
             if (typeof(m) != 'undefined') {
               return '(' + m.total + ' / ' + m.collected + ')'
             }
           }
           return ''
        },
        showNotPayed (val) {
          this.searchQuery['not_payed'] = (typeof(this.searchQuery['not_payed']) == 'undefined' || this.searchQuery['not_payed'] == '0' ) ? '1' : '0';
          this.sNotPayed = this.searchQuery['not_payed']
          this.getList()
        },
        showTodayDelivered (val) {
          this.searchQuery['today_delivery'] = (typeof(this.searchQuery['today_delivery']) == 'undefined' || this.searchQuery['today_delivery'] == '0' ) ? '1' : '0';
          this.sTodayDelivered = this.searchQuery['today_delivery']
          this.getList()
        },
        showNotDelivered (val) {
          this.searchQuery['status'] = (typeof(this.searchQuery['status']) == 'undefined' || this.searchQuery['status'] == 'all' ) ? 'not-delivered' : 'all';
          this.sNotDelivered = (this.searchQuery['status'] == 'not-delivered')
          this.getList()
        },
        getAllOrders () {
          this.footerButton = 'all'
          this.sNotDelivered = false
          delete this.searchQuery.delivery_option
          delete this.searchQuery.status
          this.getList()
        },
        getNalogenii () {
          this.footerButton = 'nalogenii'
          this.sNotDelivered = false
          this.searchQuery = {}
          this.searchQuery['payment_status'] = 'Наложенный'
          this.getList()
        },
        getSpecDeliveryOrders (delivery) {
          delete this.searchQuery['payment_status']
          for (let d in this.dictionary.delivery) {
            if (this.dictionary.delivery[d] == delivery) {
              this.footerButton = delivery
              this.sNotDelivered = true
              this.searchQuery['status'] = 'not-delivered'
              this.searchQuery['delivery_option'] = d
              this.getList()
            }
          }
        },
        refresh () {
          axios.get('api/sync/orders').then((res) => {
            this.getList()
          })
        },
        getList (params) {
          params = Object.assign(this.searchQuery, params)
          axios.get('api/orders', {params}).then((res) => {
            this.list = res.data.data
            this.setOrders(this.list)
            this.curPage = res.data.current_page
            this.lastPage = res.data.last_page
            this.deliveryCollected = res.data.delivery_collected
            this.globalStats = res.data.stats
            this.allCollected = res.data.all_collected
          })
        },
        loadPage(page) {
          this.getList({page})
        },
        onSearch (data) {
          let key = Object.keys(data)[0]
          this.searchQuery[key] = data[key]
          this.getList({page: 1})
        }
      },
      mounted() {
        this.tableWidths = (typeof(this.settings.order_table_widths) != 'undefined') ? JSON.parse(this.settings.order_table_widths) : {}
        axios.get('api/dictionary', {params: {type: 'payment'}}).then((res) => {
          this.dictionary.payment = res.data
        })
        axios.get('api/dictionary', {params: {type: 'delivery'}}).then((res) => {
          this.dictionary.delivery = res.data
        })
        this.getList()
        setInterval(() => {
          this.getList()
          console.log('iterval')
        }, 120000)
      }
    }
</script>
<style>
.vip {
  fill: darkred;
}
.comment {
  position: relative;
  width: 100%;
  height: 100%;
}
.products-link {
  text-decoration: underline;
}
.check-circle {
  color: #4d7954;
}
.hourglass {
  color: #de834f;
  margin: 0 0.3rem;
}
.payment {
  width: 10rem;
}

.payment .status, .complete .status{
  width: 6rem;
  display: inline-block;
}
.workout .table th, .workout .table td
{
  border: none;
  padding: 2px 6px;
}

.workout .table,
.complete .table {
  background: none;
}

.workout .table td svg {
  margin-top: -8px;
}
.workout .table th svg,
.information svg
{
  color: gray;
}

.orders .information svg {
  margin-top: -5px;
  cursor: pointer;
}
.star-icon {
  margin-top: -5px;
  color: #5e5792;
}
.orders .comment {
  height: 13rem;
}
#app .v-input--selection-controls__ripple {
cursor: pointer;
    height: 20px;
    position: absolute;
    transition: inherit;
    width: 20px;
    left: 1px;
    top: calc(50% - 10px);
}
.v-input--checkbox * {
  transition: none !important;
  animation: none !important;
}
.v-ripple__animation {
  transition: none;
  display: none;
}

</style>
