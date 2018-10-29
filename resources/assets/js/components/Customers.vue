<template>
  <div>
    <btable :items="list"
      :fields="fields"
      :search="['name', 'phone', 'email']"
      @search="onSearch"
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


    </btable>


    <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
  </div>
</template>

<script>
    import customer from './Customer'
    import * as moment from 'moment';
    import { mapGetters } from 'vuex'

    export default {
      data() {
        return {
          fields: [
            { key: 'name', label: 'ФИО' },
            { key: 'phone', label: 'Телефон' },
            { key: 'email', label: 'email' },
            { key: 'manual_status', label: 'Статус' },
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
        customer
      },
      computed: {
        ...mapGetters(['settings']),
      },
      methods: {
        averOrderDays (item) {
          return Math.round(this.daysFromNow(item.statistic.first_order) / item.statistic.count_orders)
        },
        daysFromNow (d) {
          const from = moment(d);
          const today = moment();
          return today.diff(from, 'days');
        },
        customerUpdated (id) {
          axios.get('api/customers/' + id).then((res) => {
            this.curCustomer = res.data.data
          })
        },
        getList (params) {
          params = Object.assign(this.searchQuery, params)
          axios.get('api/customers', {params}).then((res) => {
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
</style>
