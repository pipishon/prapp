<template>
  <div class="customers">

    <v-container fluid class="my-0 py-0">
        <v-layout row>
            <v-flex md3 >
              <v-select :menu-props="{maxHeight: 400}"  :hide-details="true" label="Фильтр пользователей" :items="Object.keys(filterMap)" v-model="selectedFilter" @input="showAddFilterDialog = true" ></v-select>
            </v-flex>
              <v-chip v-model="filterChips[item.filter]" v-for="item in filters" :key="item.filter" close>{{item.filter}}
                <span v-if="item.from">&nbsp;<template v-if="item.filter != 'Авто статус'">от</template> {{item.from}}</span>
                <span v-if="item.to">&nbsp;до {{item.to}}</span>
              </v-chip>
        </v-layout>
    </v-container>
    <btable :items="list"
      :fields="fields"
      :search="['name', 'phone', 'email', 'manual_status']"
      :widths="tableWidths"
      @updatewidth="updateWidths"
      @search="onSearch"
      class="mb-5"
    >

      <template slot="row" slot-scope="data">
        <tr v-for="item in data.items">
          <td>
            <customer :item="item" :id="item.id">{{item.name[0]}}</customer>
          </td>
          <td>
            <span v-if="typeof(item.phones[0]) != 'undefined'">{{item.phones[0].phone}}</span>
          </td>
          <td>
            <span v-if="typeof(item.emails[0]) != 'undefined'">{{item.emails[0].email}}</span>
          </td>
          <td>
            {{item.manual_status}}
          </td>
          <td>
            {{mapAuto[item.auto_status]}}
          </td>
          <td>
            {{item.comment}}
          </td>
          <td>
            {{daysFromNow(item.statistic.first_order)}}
          </td>
          <td>
            {{daysFromNow(item.statistic.last_order)}}
          </td>
          <td>
            {{item.statistic.count_orders}}
          </td>
          <td>
            {{item.statistic.total_price}}
          </td>
          <td>
            {{item.statistic.aver_price}}
          </td>
          <td>
            {{averOrderDays(item)}}
          </td>
        </tr>
      </template>
      <template slot="footer">
        <td colspan="5">Всего: {{stats.total}} шт ({{(stats.total * 100/stats.all).toFixed(2)}} %)</td>
        <td></td>
        <td></td>
        <td>{{parseFloat(stats.aver_quantity).toFixed(2)}}</td>
        <td>{{parseFloat(stats.aver_price).toFixed(2)}}</td>
        <td>{{parseFloat(stats.aver_aver).toFixed(2)}}</td>
        <td></td>
      </template>


    </btable>

    <v-dialog  v-model="showAddFilterDialog" :width="(selectedFilter != 'Авто статус') ? 300 : 1000" persistent @keydown.esc="showAddFilterDialog = false">


      <v-card v-if="showAddFilterDialog" >

          <rfc :today="true" v-if="(selectedFilter == 'Авто статус')" @clickstatus="setAutostatusFilter"></rfc>
          <template v-else>
          <v-card-title class="primary white--text"><h5>{{selectedFilter}}</h5></v-card-title>
          <div class="px-3">
            <v-text-field label="От" v-model="filterFrom"></v-text-field>
            <v-text-field label="До" v-model="filterTo"></v-text-field>
          </div>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="showAddFilterDialog = false" > Отмена </v-btn>
            <v-btn color="primary" flat @click="setFilter" > Установить </v-btn>
          </v-card-actions>
          </template>
      </v-card>
    </v-dialog>
    <v-footer fixed class="pa-3">
      <span>Всего клиентов: {{stats.all}}  шт</span>
      <v-spacer></v-spacer>
    <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
    </v-footer>

  </div>
</template>

<script>
    import customer from './Customer'
    import rfc from './Rfc'
    import * as moment from 'moment-timezone';
    import { mapGetters, mapActions } from 'vuex'

    export default {
      data() {
        return {
          autoDialog: false,
          mapAuto: {
            'new' : 'Новые',
            'perspective' : 'Перспективные',
            'suspended' : 'Подвисшие',
            'sleep' : 'Спящие',
            'one_time' : 'Одноразовые',
            'loyal' : 'Лояльные',
            'vip' : 'VIP',
            'risk' : 'В зоне риска',
            'lost' : 'Потери',
            'lost_vip' : 'Потери VIP',
          },
          stats: {},
          total: null,
          tableWidths: {},
          showAddFilterDialog: false,
          selectedFilter: null,
          filterFrom: null,
          filterTo: null,
          filters: [],
          filterChips: {},
          filterMap: {
             'Дата первой покупки': 'first_order',
             'Дата последней покупки': 'last_order',
             'Кол-во заказов': 'count_orders',
             'Всего денег': 'total_price',
             'Средний чек': 'aver_price',
             'Авто статус': 'auto_status',
          },
          fields: [
            { key: 'name', label: 'ФИО' },
            { key: 'phone', label: 'Телефон' },
            { key: 'email', label: 'email' },
            { key: 'manual_status', label: 'Статус' },
            { key: 'auto_status', label: 'Авто cтатус' },
            { key: 'comment', label: 'Комментарий' },
            { key: 'first_order', label: 'Дата первой покупки' },
            { key: 'last_order', label: 'Дата последней покупки' },
            { key: 'count_orders', label: 'Кол-во заказов' },
            { key: 'total_price', label: 'Всего денег' },
            { key: 'aver_price', label: 'Средний чек' },
            { key: 'aver_days', label: 'Период заказов' },
          ],
          list: [],
          groups: [],
          curPage: 0,
          lastPage: 0,
          searchQuery: {},
          curCustomer: {}
        }
      },
      components: {
        customer,
        rfc
      },
      computed: {
        ...mapGetters(['settings']),
      },
      watch: {
        filterChips: {
          handler: 'sendFilter',
          deep: true
        },
        settings: {
          handler () {
            this.tableWidths = (typeof(this.settings.customer_table_widths) != 'undefined') ? JSON.parse(this.settings.customer_table_widths) : {}
          },
          deep: true
        }
      },
      methods: {
        ...mapActions(['updateSettings']),
        updateWidths () {
          this.updateSettings({name: 'customer_table_widths', value: JSON.stringify(this.tableWidths)})
        },
        setAutostatusFilter (name) {
          this.selectedFilter = 'Авто статус'
          this.filterFrom = name
          this.autoDialog = false
          this.setFilter()
        },
        setFilter () {
          let oldFilter = this.filters.filter( el => el.filter == this.selectedFilter )[0]
          if (typeof(oldFilter) != 'undefined') {
            oldFilter.from = this.filterFrom
            oldFilter.to = this.filterTo
            this.filterChips[oldFilter.filter] = true
          } else {
            this.filters.push({
              filter: this.selectedFilter,
              from: this.filterFrom,
              to: this.filterTo
            })
          }
          console.log(this.selectedFilter)
          this.filterFrom = null
          this.filterTo = null
          this.sendFilter()
          this.showAddFilterDialog = false
        },
        sendFilter () {
          let toFilter = []
          for (let f of this.filters) {
            if (typeof(this.filterChips[f.filter]) == 'undefined' || this.filterChips[f.filter]) {
              const m = {
                to: f.to,
                from: f.from,
                name: this.filterMap[f.filter]
              }
              toFilter.push(m)
            }
          }
          this.getList({filter: toFilter})
        },
        averOrderDays (item) {
          return Math.round(this.daysFromNow(item.statistic.first_order) / item.statistic.count_orders)
        },
        daysFromNow (d) {
          const from = moment(d);
          const today = moment().tz('Europe/Kiev');
          return today.diff(from, 'days');
        },
        customerUpdated (id) {
          axios.get('api/customers/' + id).then((res) => {
            this.curCustomer = res.data.data
          })
        },
        getList (params) {
          params = Object.assign(this.searchQuery, params)
          console.log(params)
          axios.get('api/customers', {params}).then((res) => {
            this.list = res.data.data
            this.total = res.data.total
            this.stats = res.data.stats
            if (this.list.length > 0) {
              this.curCustomer = this.list[0]
              this.curPage = res.data.current_page
              this.lastPage = res.data.last_page
            } else {
              this.curCustomer = {}
              this.curPage = 1
              this.lastPage = 1
            }
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
        this.tableWidths = (typeof(this.settings.customer_table_widths) != 'undefined') ? JSON.parse(this.settings.customer_table_widths) : {}
        this.getList()
      }
    }
</script>
<style>
</style>
