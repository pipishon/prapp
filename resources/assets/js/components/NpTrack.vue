<template>
  <div class="ttn-track">
    <div class="loader-overlay" v-if="listLoading">
      <div class="loader" >
        <img src="imgs/loader.svg">
      </div>
    </div>
    <btable :items="list"
      :fields="fields"
      :search="['prom_id', 'int_doc_number', 'name']"
      :notstriped="true"
      :widths="tableWidths"
      @updatewidth="updateWidths"
      @search="onSearch"
             v-show="mode == 'np'"
    >

      <template slot="row" slot-scope="data">
        <tr v-for="item in data.items" :class="{
          'green lighten-5': (item.redelivery && item.status_code == 11) || (!item.redelivery && item.status_code == 9),
          'pink lighten-5': item.status_code > 100
          }">
          <td>

          </td>
          <td>
            {{item.prom_id}}
            <a :href="'https://my.prom.ua/cms/order/edit/' + item.prom_id" target="_blank"><v-icon small>open_in_new</v-icon></a>
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
            <div v-if="item.ttn != null && item.document_cost != item.ttn.cost_on_site">{{deliveryCost(item, item.ttn.cost_on_site)}}</div>
            {{deliveryCost(item, item.document_cost)}}
          </td>
          <td>
            <v-checkbox v-model="item.current" @change="updateTrack(item)"></v-checkbox>
          </td>
        </tr>
      </template>


    </btable>


    <btable :items="ukrlist"
      :fields="ukrfields"
      :search="['prom_id', 'ttn']"
      :notstriped="true"
      :widths="['0', '80', '120', '250', '100', '230', '200', '60']"
      @search="onSearch"
             v-show="mode == 'ukr'"
    >

      <template slot="row" slot-scope="data">
        <tr v-for="item in data.items" :class="{'green lighten-5': [41002, 41003, 41004, 41022].indexOf(item.status_code) != -1
          }">
          <td>

          </td>
          <td>
            {{item.prom_id}}
            <a :href="'https://my.prom.ua/cms/order/edit/' + item.prom_id" target="_blank"><v-icon small>open_in_new</v-icon></a>
          </td>
          <td>
            {{item.ttn}}
            <a :href="'https://a.ukrposhta.ua/vidslidkuvati-forma-poshuku_UA.html?barcode=' + item.ttn" target="_blank"><v-icon small>open_in_new</v-icon></a>
          </td>
          <td>
            {{item.status}}
          </td>
          <td>
            <customer :id="item.order.customer_id">{{item.order.client_first_name}} {{item.order.client_last_name}}</customer>
            <div>{{item.order.phone}}</div>
          </td>
          <td>
            {{item.order.delivery_address}}
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
          <td>
            <v-checkbox v-model="item.current" @change="updateTrack(item)"></v-checkbox>
          </td>
        </tr>
      </template>


    </btable>



    <v-footer fixed class="pa-3">
    <v-btn flat @click="refresh">Отследить</v-btn>
    <v-btn flat @click="getUsualTracks" :class="{primary: footerButtons == 'usual'}">Текущие ТТН ({{nums.usual}})</v-btn>
    <v-btn flat @click="getTodayTracks" :class="{primary: footerButtons == 'today'}">Сегодня ТТН ({{nums.today}})</v-btn>
    <v-btn flat @click="getAllTracks" :class="{primary: footerButtons == 'all'}">Все ТТН ({{nums.all}})</v-btn>
    <v-btn flat @click="mode = 'np'" :class="{primary: mode == 'np'}">НП</v-btn>
    <v-btn flat @click="mode = 'ukr'" :class="{primary: mode == 'ukr'}">Укр</v-btn>
    <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
    </v-footer>

  </div>
</template>

<script>
    import customer from './Customer'
    import * as moment from 'moment-timezone';
    import { mapGetters, mapActions } from 'vuex'

    export default {
      data() {
        return {
          mode: 'np',
          tableWidths: {},
          nums: {},
          ukrnums: {},
          listLoading: false,
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
            { key: 'current', label: 'Тек.' },
          ],
          ukrfields: [
            { key: 'valid', label: '' },
            { key: 'prom_id', label: 'Заказ' },
            { key: 'ttn', label: 'ТТН' },
            { key: 'status', label: 'Статус доставки' },
            { key: 'name', label: 'ФИО' },
            { key: 'address', label: 'Адрес' },
            { key: 'timetable', label: 'График доставки' },
            { key: 'current', label: 'Тек.' },
            //{ key: 'weight', label: 'Вес/Длина' },
          ],
          list: [],
          ukrlist: [],
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
      watch: {
        settings: {
          handler () {
            this.tableWidths = (typeof(this.settings.nptrack_table_widths) != 'undefined') ? JSON.parse(this.settings.nptrack_table_widths) : {}
          },
          deep: true
        }
      },
      methods: {
        ...mapActions(['updateSettings']),
        updateTrack (item) {
          axios.put('api/nptrack/' + item.id, {...item}).then((res) => {
            console.log(res.data)
            this.getList()
          })
        },
        updateWidths () {
          this.updateSettings({name: 'nptrack_table_widths', value: JSON.stringify(this.tableWidths)})
        },
        deliveryCost (item, p) {
          if (item.redelivery == 1) {
            const npPrice = 1*(parseFloat(p).toFixed(2))
            const price = 1*(parseFloat(item.redelivery_sum).toFixed(2))
            const backPrice = (0.02 * price + 20).toFixed(2)
            const wholePrice = (npPrice + 1*backPrice).toFixed(2)
            return wholePrice + ' грн.'// + npPrice + ' грн. + ' + backPrice + ' грн.'
          } else {
            const npPrice = 1*(parseFloat(p).toFixed(2))
            return npPrice + ' грн.'
          }
        },
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
          this.listLoading = true
          axios.get('api/nptrackcheck').then((res) => {
            axios.get('api/checkukrtrack').then((res) => {
              console.log(res.data)
              this.getList()
            })
          })
        },
        getWarehouseNum (w) {
          if (w == null) return ''
          const match = w.match(/№(\d+)/u)
          return (match != null) ? '&mdash; ' + match[1] : ''
        },
        isToday (d) {
          return moment().tz('Europe/Kiev').diff(moment(d), 'days') == 0;
        },
        daysFromNow (d) {
          const from = moment(d);
          const today = moment().tz('Europe/Kiev');
          return today.diff(from, 'days');
        },
        getList (params) {
          this.listLoading = true
          params = Object.assign(this.searchQuery, params)
          axios.get('api/nptrack', {params}).then((res) => {
            this.listLoading = false
            this.list = res.data.data
            this.nums = res.data.nums
            if (this.list.length > 0) {
              this.curPage = res.data.current_page
              this.lastPage = res.data.last_page
            } else {
              this.curPage = 1
              this.lastPage = 1
            }
          })
          axios.get('api/ukrtrack', {params}).then((res) => {
            this.ukrlist = res.data.data
            this.ukrnums = res.data.nums
            console.log(res.data)
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
        this.tableWidths = (typeof(this.settings.nptrack_table_widths) != 'undefined') ? JSON.parse(this.settings.nptrack_table_widths) : {}
        this.getList()
      }
    }
</script>
<style scope>
.loader-overlay {
  left: -30px;
  right: 0;
  width: 100vw;
  height: 100%;
  z-index: 100;
  position: fixed;
  z-index: 10000;
}
.loader {
  width: 200px;
  height: 200px;
  position: fixed;
  left: calc(50vw - 100px);
  top: calc(50vh - 100px);
}
.ttn-track .table .table{
  background: none !important;
}
.ttn-track .table .table th, .ttn-track .table .table td
{
  border: none;
  padding: 2px 6px;
}
</style>
