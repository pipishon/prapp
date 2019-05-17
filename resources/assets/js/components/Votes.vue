<template>
  <div class="container">
    <input placeholder="Поиск" v-model="searchEmailsWord" @keypress.enter="getEmails"/>
  <v-data-table
    :headers="emailHeaders"
    :items="emails"
    :disable-initial-sort="true"
    :loading="onEmailsLoad"
    class="elevation-1"
  >
  <v-progress-linear v-slot:progress color="blue" indeterminate></v-progress-linear>
    <template slot="items" slot-scope="props">
      <td>
        <strong>{{props.item.send_at}}<span v-if="props.item.send_by == 'manual'">(Р)</span></strong>
        <div>
          <v-icon small>local_shipping</v-icon>
          <span v-if="props.item.delivered">{{props.item.delivered}}</span>
          <span v-else>{{props.item.np_received}}</span>
        </div>
        <div>
          <v-icon small>event</v-icon>
          <span>{{props.item.prom_date_created}}</span>
        </div>

      </td>
      <td>
        <order :orderid="props.item.order_id"><span>{{props.item.prom_id}}</span></order>
      </td>
      <td >
        <customer :id="props.item.customer_id">{{props.item.client_last_name}} {{props.item.client_first_name}}</customer>
        <div>
          <span>{{props.item.count_orders}}</span> {{orderString(props.item.count_orders)}} на <span>{{props.item.total_price}}</span> грн
        </div>
          <strong v-if="props.item.manual_status">{{props.item.manual_status}} |</strong> <strong>{{mapAuto[props.item.auto_status]}}</strong>
      </td>
      <td>
        {{props.item.delivery_option}}
      </td>
      <td>
        {{props.item.email}}
      </td>
      <td>
        <table class="delivery-table">
          <tr>
            <td>Отправлено:</td>
            <td>
              <v-tooltip top content-class="white black--text" transition="sss" :open-delay="0" :close-delay="0" style="cursor: default;">
                <v-icon small slot="activator" color="green">check_circle</v-icon>
                {{props.item.send_at}}
              </v-tooltip>
            </td>
          </tr>
          <tr>
            <td>Доставлено:</td>
            <td>

              <v-icon v-if="props.item.status == 'UNDELIVERED'" small color="red">check_circle</v-icon>
              <span v-else>
                <v-tooltip v-if="props.item.delivered_at" top content-class="white black--text" transition="sss" :open-delay="0" :close-delay="0" style="cursor: default;">
                  <v-icon  small slot="activator" color="green">check_circle</v-icon>
                  {{props.item.delivered_at}}
                </v-tooltip>
                <v-icon v-else small color="gray">check_circle</v-icon>
              </span>
            </td>
          </tr>
          <tr>
            <td>Прочитано:</td>
            <td>
              <v-tooltip v-if="props.item.read_at" top content-class="white black--text" transition="sss" :open-delay="0" :close-delay="0" style="cursor: default;">
                <v-icon  small slot="activator" color="green">check_circle</v-icon>
                {{props.item.read_at}}
              </v-tooltip>
              <v-icon v-else small slot="activator" color="gray">check_circle</v-icon>
            </td>
          </tr>
        </table>
      </td>
      <td>
        <v-btn icon
          @click="removeId = props.item.prom_id; removeType = 'email'; removeDialog = true"
          ><v-icon>delete</v-icon></v-btn>
      </td>
    </template>
  </v-data-table>

  <v-data-table
    :headers="headers"
    :items="list"
    :disable-initial-sort="true"
    class="elevation-1 mt-5"
    :loading="onVotesLoad"
  >
    <v-progress-linear v-slot:progress color="blue" indeterminate></v-progress-linear>
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

        <div class="text-center">{{mapDevice[props.item.device]}}</div>
      </td>
      <td>
        Отзыв <v-icon v-if="props.item.is_prom_comment" small color="green">check_circle</v-icon>
              <v-icon v-else small color="gray">check_circle</v-icon>
      </td>
      <td>
        <v-btn icon
          @click="removeId = props.item.order.prom_id; removeType = 'vote'; removeDialog = true"
          ><v-icon>delete</v-icon></v-btn>
      </td>
    </template>
  </v-data-table>

    <v-dialog width="300" v-model="removeDialog" persistent @keydown.esc="showDialog = false">
      <v-card>
        <v-container fluid>
          Подтверждаете удаление?
         </v-container>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="removeItem" > Удалить </v-btn>
            <v-btn color="primary" flat @click="removeDialog = false" > Отмена </v-btn>
          </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
    import order from './Order'
    import customer from './Customer'
  import * as moment from 'moment';
    export default {
      data() {
        return {
          removeDialog: false,
          removeId: null,
          removeType: null,
          onEmailsLoad: false,
          onVotesLoad: false,
          searchEmailsWord: '',
          mapDevice: {
            'desctop': 'ПК',
            'tablet': 'Планшет',
            'mobile': 'Moб',
          },
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
          headers: [
            { text: 'Дата', value:'updated_at' },
            { text: 'Заказ', value:'order_id' },
            { text: 'Клиент', value:'customer' },
            { text: 'Голос', value:'vote' },
            { text: 'Комментарий', value:'comment' },
            { text: 'IP', value:'ip' },
            { text: '', value:'is_prom_comment' },
            { text: '', value:'_' },
          ],
          list: [],
          emailHeaders: [
            { text: 'Дата', value:'send_at' },
            { text: 'Заказ', value:'order_id' },
            { text: 'Клиент', value:'customer_id' },
            { text: 'Доставка', value:'delivery_option' },
            { text: 'Email', value:'email' },
            { text: 'Отправка', value:'send_at' },
            { text: '', value:'_' },
          ],
          emails: [],
        }
      },
      methods: {
        removeItem () {
          const id = this.removeId
          switch(this.removeType) {
            case 'email':
              this.emails = this.emails.filter((el) => el.prom_id != id)
              axios.post('api/voteremoveemail', {id}).then((res) => {
                console.log(res.data)
              })
            break
            case 'vote':
              this.list = this.list.filter((el) => el.order.prom_id != id)
              axios.post('api/voteremove', {id}).then((res) => {
                console.log(res.data)
              })
            break
          }
          this.removeDialog = false
        },
        removeEmail (id) {
        },
        removeVote (id) {
        },
        orderString (n) {
          let r = n%10;
          if (n > 5 && n < 21) { return 'заказов' }
          if (r == 1) { return 'заказ' }
          if (r > 1 && r < 5) { return 'заказа' }
          return 'заказов'
        },
        getList () {
          this.onVotesLoad = true
          axios.get('api/votes').then((res) => {
            this.onVotesLoad = false
            console.log(res.data)
            this.list = res.data
          })
        },
        getEmails () {
          this.emails = []
          this.onEmailsLoad = true
          let params = {
            search_query: this.searchEmailsWord
          }
          axios.get('api/votesemail', {params}).then((res) => {
            this.onEmailsLoad = false
            console.log(res.data)
            this.emails = res.data
          })
        },
      },
      components: {
        order,
        customer,
      },
      mounted() {
        this.getList()
        this.getEmails()
      }
    }
</script>
<style scoped>
.delivery-table {
  width: 105px;
  text-align: right;
}
.delivery-table td{
  padding: 3px 0 !important;
  height: auto !important;
  border: none !important;
}
.delivery-table tr{
  border: none !important;
}
input {
  padding: 4px 8px;
  border: 1px solid lightgray;
  border-radius: 3px;
  margin-bottom: 1rem;
}
</style>
