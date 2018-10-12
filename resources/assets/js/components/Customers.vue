<template>
  <div>
    <btable :items="list" :fields="fields" :search="['name', 'phone', 'email']" @search="onSearch"  >

        <template slot="name" slot-scope="data">
          <a href="#" data-toggle="modal" data-target="#customerModal" @click.prevet="curCustomer = data.item">{{data.item.name[0]}}</a>
        </template>

        <template slot="phone" slot-scope="data">
          <span v-if="typeof(data.item.phones[0]) != 'undefined'">{{data.item.phones[0].phone}}</span>
        </template>

        <template slot="email" slot-scope="data">
          <span v-if="typeof(data.item.emails[0]) != 'undefined'">{{data.item.emails[0].email}}</span>
        </template>

    </btable>

    <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
    <customer :customer="curCustomer" @updated="customerUpdated"/>
  </div>
</template>

<script>
    import customer from './Customer'

    export default {
      data() {
        return {
          fields: [
            { key: 'name', label: 'ФИО' },
            { key: 'phone', label: 'Телефон Дата' },
            { key: 'email', label: 'email' },
            { key: 'manual_status', label: 'Статус' },
            { key: 'auto_status', label: 'Авто статус' },
            { key: 'comment', label: 'Комментарий' },
            { key: 'first_order', label: 'Дата первой покупки' },
            { key: 'last_order', label: 'Дата последней покупки' },
            { key: 'count_orders', label: 'Кол-во заказов' },
            { key: 'total_price', label: 'Всего денег' },
            { key: 'aver_price', label: 'Средний чек' },
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
      methods: {
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
