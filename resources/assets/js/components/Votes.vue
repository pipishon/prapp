<template>
  <div class="container">
  <v-data-table
    :headers="headers"
    :items="list"
    :disable-initial-sort="true"
    class="elevation-1"
  >
    <template slot="items" slot-scope="props">
      <td>
        {{props.item.updated_at}}
      </td>
      <td>
        <order :orderid="props.item.order.id"><span>{{props.item.order.prom_id}}</span></order>
      </td>
      <td >
        <customer :item="props.item.order" :id="props.item.order.customer_id">{{props.item.order.client_last_name}} {{props.item.order.client_first_name}}</customer>
      </td>
      <td>
        {{props.item.vote}}
      </td>
      <td>
        {{props.item.comment}}
      </td>
      <td>
        {{props.item.ip}}
      </td>
    </template>
  </v-data-table>
  </div>
</template>
<script>
    import order from './Order'
    import customer from './Customer'
  import * as moment from 'moment';
    export default {
      data() {
        return {
          headers: [
            { text: 'Дата', value:'updated_at' },
            { text: 'Заказ', value:'order_id' },
            { text: 'Клиент', value:'customer' },
            { text: 'Голос', value:'vote' },
            { text: 'Комментарий', value:'comment' },
            { text: 'IP', value:'ip' },
          ],
          list: []
        }
      },
      methods: {
        getList () {
          axios.get('api/votes').then((res) => {
            this.list = res.data
          })
        },
      },
      components: {
        order,
        customer,
      },
      mounted() {
        this.getList()
      }
    }
</script>
<style>
</style>
