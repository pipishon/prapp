<template>
  <div>
    <btable :items="list"
      :fields="fields"
      :search="['prom_id', 'int_doc_number', 'name']"
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
</style>
