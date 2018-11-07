<template>
  <div class="ttn-track">
    <btable :items="list"
      :fields="fields"
      :search="['prom_id', 'int_doc_number', 'name']"
      :notstriped="true"
      @search="onSearch"
    >

      <template slot="row" slot-scope="data">
        <tr v-for="item in data.items" :class="{'green lighten-5': item.redelivery && item.status_code == 11}">
          <td>

          </td>
          <td>
            {{item.prom_id}}
            <a :href="'https://my.prom.ua/cabinet/order_v2/edit/' + item.prom_id" target="_blank"><v-icon small>open_in_new</v-icon></a>
            <div v-if="item.date_first_day_storage"
              :class="{'yellow lighten-4': !isToday(item.date_first_day_storage), 'pink lighten-5': isToday(item.date_first_day_storage)}"
                 class="pa-2"
              >
              Платно с:
              <div>{{item.date_first_day_storage}}</div>
            </div>
          </td>
          <td>
            {{item.int_doc_number}}
            <a :href="'https://novaposhta.ua/tracking/?cargo_number=' + item.int_doc_number" target="_blank"><v-icon small>open_in_new</v-icon></a>
            <div class="my-2">
              <a target="_blank" @click.stop :href="'https://my.novaposhta.ua/orders/printDocument/orders[]/'+item.int_doc_number+'/type/html/apiKey/b2aa728b253bc10bbb33e79c30d6498d'">
                <v-icon small>description</v-icon>
              </a>
              <a target="_blank" @click.stop :href="'https://my.novaposhta.ua/orders/printMarkings/orders[]/'+item.int_doc_number+'/type/html/apiKey/b2aa728b253bc10bbb33e79c30d6498d'">
                <v-icon small>book</v-icon>
              </a>
            </div>
            <div>
              <v-icon small class="mr-2 grey--text " >local_shipping</v-icon>{{item.estimate_delivery_date}}
            </div>
          </td>
          <td>
            {{item.status}}
          </td>
          <td :class="{'pink lighten-5': item.redelivery && item.status_code != 11}">
            <div v-if="item.redelivery">
              <div>Наложенный: {{item.redelivery_sum}}</div>
              <span class="text-no-wrap">
                Статус:
                <strong v-if="item.status_code == 11">выплачен <v-icon small color="green">check_circle</v-icon></strong>
                <strong v-else class="red--text text--lighten-1 ">не выплачено <v-icon small color="#EF5350">hourglass_empty</v-icon></strong>
              </span>
            </div>
            <div v-else>
              Оплачен
            </div>
          </td>
          <td>
            <customer :id="item.customer_id">{{item.full_name}}</customer>
            <div>{{item.phone}}</div>
          </td>
          <td class="text-no-wrap">
            {{item.city}} <span v-html=getWarehouseNum(item.warehouse)></span>
            <v-tooltip top content-class="white black--text" transition="sss" :open-delay="0" :close-delay="0" style="cursor: default;">

              <span slot="activator">
                <v-icon small>info</v-icon>
              </span>
              {{item.recipient_address}}
            </v-tooltip>
          </td>
          <td>
            <table class="table">
              <tbody>
              <tr>
                <td>
                  Отправка:
                </td>
                <td class="text-no-wrap">
                  {{item.date_created}}
                  <strong>({{item.send_days}})</strong>
                </td>
                <td>
                  <v-icon v-if="item.date_delivered" small color="green">check_circle</v-icon>
                  <v-icon v-else small>check_circle</v-icon>
                </td>
              </tr>
              <tr>
                <td>
                  Доставка:
                </td>
                <td class="text-no-wrap">
                  <span v-if="item.date_delivered" :class="{'red--text text--lighten-1': !item.date_received && item.delivery_days > 3}">
                    {{item.date_delivered}}
                    <strong>({{item.delivery_days}})</strong>
                  </span>
                  <span v-else>
                    &mdash;
                  </span>
                </td>
                <td>
                  <span class="text-no-wrap" v-if="item.date_delivered">
                    <span v-if="!item.date_received && item.delivery_days > 3" >
                        <v-icon small color="#EF5350">check_circle</v-icon>
                    </span>
                    <span v-else>
                      <v-icon v-if="item.date_received" small color="green">check_circle</v-icon>
                      <v-icon v-else small>check_circle</v-icon>
                    </span>
                  </span>
                </td>
              </tr>
              <tr>
                <td>
                  Получено:
                </td>
                <td>
                  <span v-if="item.date_received">
                    {{item.date_received}}
                  </span>
                  <span v-else>
                    &mdash;
                  </span>
                </td>
                <td>
                  <v-icon v-if="item.date_received" small color="green">check_circle</v-icon>
                </td>
              </tr>
              </tbody>
            </table>
          </td>
          <td class="text-no-wrap">
            <div v-if="item.ttn != null && item.ttn.weight != 'NaN' && item.document_weight != item.ttn.weight">{{item.ttn.weight}} кг</div>
            {{item.document_weight}} кг
          </td>
          <td class="text-no-wrap">
            <div v-if="item.ttn != null && item.document_cost != item.ttn.cost_on_site">{{item.ttn.cost_on_site}} грн</div>
            {{item.document_cost}} грн
          </td>
        </tr>
      </template>


    </btable>

    <v-footer fixed class="pa-3">
    <v-btn flat @click="refresh">Отследить</v-btn>
    <v-btn flat @click="getUsualTracks" :class="{primary: footerButtons == 'usual'}">Текущие ТТН</v-btn>
    <v-btn flat @click="getTodayTracks" :class="{primary: footerButtons == 'today'}">Сегодня ТТН</v-btn>
    <v-btn flat @click="getAllTracks" :class="{primary: footerButtons == 'all'}">Все ТТН</v-btn>
    <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
    </v-footer>

  </div>
</template>

<script>
    import customer from './Customer'
    import * as moment from 'moment';
    import { mapGetters } from 'vuex'

    export default {
      data() {
        return {
          footerButtons: 'usual',
          fields: [
            { key: 'valid', label: '' },
            { key: 'prom_id', label: 'Заказ' },
            { key: 'int_doc_number', label: 'ТТН' },
            { key: 'status', label: 'Статус доставки' },
            { key: 'payment_status', label: 'Статус оплаты' },
            { key: 'name', label: 'ФИО' },
            { key: 'address', label: 'Адрес' },
            { key: 'timetable', label: 'График доставки' },
            { key: 'weight', label: 'Вес' },
            { key: 'document_cost', label: 'Цена' },
          ],
          list: [],
          curPage: 0,
          lastPage: 0,
          searchQuery: {},
        }
      },
      components: {
        customer
      },
      computed: {
        ...mapGetters(['settings']),
      },
      methods: {
        getAllTracks () {
          this.footerButtons = 'all'
          this.searchQuery.all = true
          delete this.searchQuery.today
          this.getList()
        },
        getUsualTracks () {
          delete this.searchQuery.all
          delete this.searchQuery.today
          this.footerButtons = 'usual'
          this.getList()
        },
        getTodayTracks () {
          this.footerButtons = 'today'
          this.searchQuery.today = true
          delete this.searchQuery.all
          this.getList()
        },
        refresh () {
          axios.get('api/nptrackcheck').then((res) => {
            console.log(res.data)
            this.getList()
          })
        },
        getWarehouseNum (w) {
          const match = w.match(/№(\d+)/u)
          return (match != null) ? '&mdash; ' + match[1] : ''
        },
        isToday (d) {
          return moment().diff(moment(d), 'days') == 0;
        },
        daysFromNow (d) {
          const from = moment(d);
          const today = moment();
          return today.diff(from, 'days');
        },
        getList (params) {
          params = Object.assign(this.searchQuery, params)
          axios.get('api/nptrack', {params}).then((res) => {
            this.list = res.data.data
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
        this.getList()
      }
    }
</script>
<style scope>
.ttn-track .table .table{
  background: none !important;
}
.ttn-track .table .table th, .ttn-track .table .table td
{
  border: none;
  padding: 2px 6px;
}
</style>
