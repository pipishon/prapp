<template>
  <div>
    <v-dialog v-if="showDialog" v-model="showDialog" width="350" persistent @keydown.esc="showDialog = false">
      <v-card class="p-2">
        <v-container fluid>
              <v-select class="m-0" v-model="reason" label="Причина отмены заказа" :items="reasons"></v-select>
              <v-textarea v-model="reasonMessage" label="Дополнительный комментарий"></v-textarea>
         </v-container>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
              <v-btn color="primary" flat @click="saveAfterModal" > Сохранить </v-btn>
            </v-card-actions>
      </v-card>
    </v-dialog>
      <perfselect class="mt-0 pt-0" v-model="item.status" :items="statuses" @input="changeStatus"></perfselect>
      <div class="mt-5" v-if="item.statuses.delivered != null">
        Выполнен:
        <v-menu :close-on-content-click="false" v-model="menuDate" offset-y full-width >
          <strong slot="activator">{{delivered}}<v-icon small class="ml-1">event</v-icon></strong>
          <v-date-picker locale="ru-Ru" first-day-of-week="1" :value="delivered"  @input="changeDate"  no-title scrollable></v-date-picker>
        </v-menu>
      </div>

  </div>
</template>

<script>

import * as moment from 'moment-timezone';
    export default {
      props: ['item'],
      data() {
        return {
          menuDate: false,
          showDialog: false,
          stat: '',
          reason: '',
          reasonMessage: '',
          reasons: [
          {value: 'not_available', text: 'Нет в наличии'},
          {value: 'price_changed', text: 'Изменилась цена'},
          {value: 'buyers_request', text: 'По просьбе покупателя'},
          {value: 'not_enough_fields', text: 'Недостаточно данных'},
          {value: 'duplicate', text: 'Заказ-дубликат'},
          {value: 'invalid_phone_number', text: 'Не получается дозвонится'},
          {value: 'less_than_minimal_price', text: 'Нет минимальной суммы заказа'},
          {value: 'another', text: 'Другое'}
          ],
          statuses: [
            {'value': 'pending', 'text': 'Новый'},
            {'value': 'received', 'text': 'Принят'},
            {'value': 'delivered', 'text': 'Выполнен'},
            {'value': 'canceled', 'text': 'Отменен'},
          ],

        }
      },
      computed: {
        delivered () {
          return moment(this.item.statuses.delivered).format('YYYY-MM-DD')
        }
      },
      methods: {
        changeDate(val) {
          this.item.statuses.delivered = moment(val).format('YYYY-MM-DD HH:mm')
          this.menuDate = false
          this.$emit('change')
        },
        saveAfterModal () {
          let params = {
            id: this.item.id,
            status: this.item.status,
            reason: this.reason
          }
          if (this.reasonMessage != '') {
            params['reason_message'] = this.reasonMessage
          }
          axios.get('api/orders/changestatus', {params}).then((res) => {
            console.log(res.data)
          })
          this.item.statuses.delivered = null
          this.$emit('change')
          this.showDialog = false
        },
        changeStatus (stat) {
          if (stat == 'canceled')  {
            this.showDialog = true
            return
          }
          if (stat == 'delivered')  {
            this.item.statuses.delivered = moment().tz('Europe/Kiev').format('YYYY-MM-DD HH:mm')
          } else {
            this.item.statuses.delivered = null
          }

          let params = {
            id: this.item.id,
            status: this.item.status
          }
          axios.get('api/orders/changestatus', {params}).then((res) => {
            console.log(res.data)
          })
          this.$emit('change')
        }
      },
      mounted() {
      }
    }
</script>
<style scoped>
.status-select {
  width: 7.5rem;
}
</style>
