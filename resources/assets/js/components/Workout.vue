<template>
  <div class="text-nowrap workout">
      <table class="table">
        <thead>
          <tr>
            <th ></th>
            <th ></th>
            <th class="text-center"><v-icon small>smartphone</v-icon></th>
            <th v-if="item.email!='' || item.statuses.custom_email != null" class="text-center"><v-icon small>email</v-icon></th>
            <th v-else></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(name, key) in names" >
            <td class="">{{name}}:</td>
            <td class="" @click="onClick(key, item)">
              <v-checkbox height="10" color="primary" :class="{'drop-phone': key == 'drop_phone'}" flat v-model="item.statuses[key]" :hide-details="true" class="mt-0" disabled v-if="key != 'ttn'" ></v-checkbox>
              <v-checkbox v-else height="10" color="primary" flat v-model="item.statuses['ttn_status']" :hide-details="true" class="mt-0" disabled ></v-checkbox>
            </td>
            <td style="line-height: 1;">
              <v-tooltip v-if="!isObjEmpty(smsStatuses[key])" top content-class="white black--text" transition="sss" :open-delay="0" :close-delay="0" style="cursor: default;">
              <v-icon slot="activator" v-if="typeof(item.statuses[key + '_phone']) != 'undefined'" :color="statusColor[item.statuses[key + '_phone']]" small class="">check_circle</v-icon>
              <table>
                <tr v-for="status in smsStatuses[key]"><td class="pr-2">{{status.status}}</td><td>{{formatedTime(status.time)}}</td></tr>
              </table>
              </v-tooltip>
              <div v-else style="cursor: default;">
                <v-icon slot="activator" v-if="typeof(item.statuses[key + '_phone']) != 'undefined'" :color="statusColor[item.statuses[key + '_phone']]" small class="">check_circle</v-icon>
              </div>
            </td>
            <td style="line-height: 1;" v-if="item.email!='' || item.statuses.custom_email != null">
              <v-tooltip v-if="!isObjEmpty(emailStatuses[key])" top content-class="white black--text" transition="sss" :open-delay="0" :close-delay="0" style="cursor: default;">
                <span  slot="activator" v-if="typeof(item.statuses[key + '_email']) != 'undefined'">
                  <v-icon v-if="item.statuses[key + '_email'] == '4'" color="#82b1ff" small class="">offline_pin</v-icon>
                  <v-icon v-else :color="statusColor[item.statuses[key + '_email']]" small class="">check_circle</v-icon>
                </span>
                <table>
                  <tr v-for="status in emailStatuses[key]"><td class="pr-2">{{status.status}}</td><td>{{formatedTime(status.time)}}</td></tr>
                </table>
              </v-tooltip>
              <div v-else style="cursor: default;">
                <div   v-if="typeof(item.statuses[key + '_email']) != 'undefined'">
                  <v-icon v-if="item.statuses[key + '_email'] == '4'" color="#82b1ff" small class="">offline_pin</v-icon>
                  <v-icon v-else :color="statusColor[item.statuses[key + '_email']]" small class="">check_circle</v-icon>
                </div>
              </div>
            </td>
            <td v-else>
                  <v-icon style="color: transparent; background-color=transparent;" small class="">check_circle</v-icon>
            </td>
          </tr>
        </tbody>
      </table>
      <v-dialog v-if="showDialog" v-model="showDialog" width="400" persistent @keydown.esc="showDialog = false">
        <v-card >
          <v-card-title class="primary white--text"><h5>{{names[type]}}</h5></v-card-title>
          <div class="px-3">
            <span class="mr-4" :class="{'red--text': !isPhoneValid}">
              <strong v-if="item.statuses.custom_phone" >{{item.statuses.custom_phone}}</strong><strong v-else>{{item.phone}}</strong>
            </span>
            <span :class="{'red--text': !isEmailValid}">
              <strong v-if="item.statuses.custom_email">{{item.statuses.custom_email}}</strong><strong v-else>{{item.email}}</strong>
            </span>
            <div>
                <v-textarea :value="message(type + '_sms')" @input="msgs['sms'] = arguments[0]" counter label="Смс"></v-textarea>
            </div>
            <div v-if="item.email!='' || item.statuses.custom_email != null">
              <div v-if="type=='requisites'">
                Email триггер <strong>api-send-requisites</strong>
                <v-text-field v-model="paymentPrice" label="К оплате" style="width: 70px;"></v-text-field>
              </div>
              <div v-if="type=='ttn'">
                Email триггер <strong v-if="deliverer == 'Укрпочта'">api-send-ttn-ukrpost</strong><strong v-else>api-send-ttn-newpost</strong>
                <v-text-field v-model="ttn" label="ТТН" style="width: 140px;"></v-text-field>
              </div>
            </div>
            <div>

              <v-layout class="px-5" row>
                <v-flex xs6 md6 >
                  <v-checkbox :disabled="!isPhoneValid" v-model="sendSms" label="СМС"></v-checkbox>
                </v-flex>
                <v-flex xs6 md6 >
                  <v-checkbox :disabled="!isEmailValid" v-if="(item.email!='' || item.statuses.custom_email != null)" v-model="sendEmail" label="Email"></v-checkbox>
                </v-flex>
              </v-layout>
            </div>
          </div>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
              <v-btn color="primary" flat @click="send" > Отправить </v-btn>
            </v-card-actions>
        </v-card>
      </v-dialog>
  </div>
</template>

<script>
import { mapActions, mapGetters, mapMutations } from 'vuex'
import * as moment from 'moment';
    export default {
      props: ['item', 'delivery'],
      data() {
        return {
          showDialog: false,
          dialogData: {},
          statusColor: ['gray', 'green', '#82b1ff', 'red'],
          sendSms: true,
          sendEmail: true,
          msgs: {},
          type: null,
          tmplts: {},
          names: {'requisites': 'Реквизиты', 'phoned': 'Звонил', 'drop_phone': 'Не дозвон', 'payed': 'Оплачено', 'collected': 'Собрано', 'ttn': 'ТТН'},
          mapStatuses: {'1': 'Отправлено', '2': 'Доставлено', '3': 'Ошибка', '4': 'Прочтено'},
          payemntPrice: null,
          isPhoneValid: true,
          isEmailValid: true,
          ttn: null,
          deliverer: null,
        }
      },
      watch: {
        showDialog (val) {
          if (val) {
            this.dialogData = JSON.parse(JSON.stringify(this.item))
            this.deliverer = this.delivery
            this.paymentPrice = parseFloat(this.dialogData.statuses.payment_price).toFixed(2)
            this.ttn = this.dialogData.statuses.ttn_string
            let smsId = this.settings['template_' + this.type + '_sms']
            //let emailId = this.settings['template_' + this.type + '_email']
            this.tmplts[this.type + '_sms'] = this.templates.filter( el => el.id == smsId )[0]
            //this.tmplts[this.type + '_email'] = this.templates.filter( el => el.id == emailId )[0]
            if (typeof(this.tmplts[this.type + '_sms']) != 'undefined') {
              this.msgs.sms = this.replaceSpecWords(this.tmplts[this.type + '_sms'].template)
            }
            /*if (typeof(this.tmplts[this.type + '_email']) != 'undefined') {
              this.msgs.email = this.replaceSpecWords(this.tmplts[this.type + '_email'].template)
            }*/
            let phone = this.dialogData.statuses.custom_phone || this.dialogData.phone
            const email = this.dialogData.statuses.custom_email || this.dialogData.email
            let rx = /^\+380\d{9}$/
            this.isPhoneValid = (phone.match(rx) != null)
            this.isEmailValid = this.validateEmail(email)
            this.sendSms = this.isPhoneValid
            this.sendEmail = this.isEmailValid
          }
        }
      },
      computed: {
        ...mapGetters(['settings', 'templates']),
        emailStatuses () {
          let map = {'send_at': 'Отправлено', 'delivered_at': 'Доставлено', 'read_at': 'Прочтено'}
          let result = {requisites: {}, payed: {}, ttn: {}}
          this.item.email_statuses.map((el) => {
            for (name in map) {
              if (el[name] != null) {
                result[el.type][name] = {status: map[name], time: el[name]}
              }
            }
          })
          /*for (let type in result) {
            if (Object.keys(result[type]).length === 0) {
              result[type]['not_send'] = {status: 'Не отправлено'}
            }
          }*/
          return result
        },
        smsStatuses () {
          let map = {'send_at': 'Отправлено', 'delivered_at': 'Доставлено', 'error_at': 'Ошибка'}
          let result = {requisites: {}, payed: {}, ttn: {}}
          this.item.sms_statuses.map((el) => {
            for (name in map) {
              if (el[name] != null) {
                result[el.type][name] = {status: map[name], time: el[name]}
              }
            }
          })
          /*for (let type in result) {
            if (Object.keys(result[type]).length === 0) {
              result[type]['not_send'] = {status: 'Не отправлено'}
            }
          }*/
          return result
        },
        dictionary () {
          return this.$store.state.dictionary
        },
        settings () {
          return this.$store.state.settings
        },
        message () {
          return (type) => {
            if (this.type == null) return ''
            let id = this.settings['template_' + type]
            if (typeof(id) == 'undefined') return ''
            let template = this.templates.filter( el => el.id == id )[0]
            if (typeof(template) == 'undefined') return ''
            return this.replaceSpecWords(template.template)
            //let str = template.template.replace('$name$', this.item.client_first_name + ' ' + this.item.client_last_name)
            //return str.replace('$id$', this.item.prom_id).replace('$price$', this.item.price)
          }
        },
      },
      methods: {
        ...mapMutations(['updateOrder']),
        ...mapActions(['updateSettings']),
        validateEmail (email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        },
        isObjEmpty (obj) {
          if (!obj) return true
          return Object.keys(obj).length == 0
        },
        formatedTime (time) {
          if (!time) return ''
          return moment(time).format('HH:mm, DD.MM.YYYY')
        },
        onClick (key, item) {
          switch (key) {
            case 'payed':
            case 'ttn':
            case 'requisites':
              this.type = key;
              this.showDialog = true
              break
            case 'phoned':
            case 'drop_phone':
              item.statuses[key] = !item.statuses[key]
              this.updateStatuses(item)
              break
          }
          return true
        },
        send() {
          if (this.sendSms) {
            let phone = (this.dialogData.statuses.custom_phone) ? this.dialogData.statuses.custom_phone : this.dialogData.phone //'+380679325925'//'380683223527'
            let params = {
              'order_id': this.dialogData.prom_id,
              type: this.type,
              message: this.msgs.sms,
              phone
            }
            axios.get('api/sendsms', {params}).then((res) => {
              console.log(res)
            })
            let type = (this.type == 'ttn') ? 'ttn_status' : this.type
            this.dialogData.statuses[type] = 1
            this.dialogData.statuses[this.type + '_phone'] = 1
          }
          if (this.sendEmail && (this.dialogData.email || this.dialogData.statuses.custom_email)) {
            let email = (this.dialogData.statuses.custom_email) ? this.dialogData.statuses.custom_email : this.dialogData.email
            if (this.type == 'requisites') {
              let params = {
                email,
                'order_id': this.dialogData.prom_id,
                type: 'requisites',
                price: this.paymentPrice
              }
              axios.get('api/sendemail', {params}).then((res) => {
                console.log(res)
              })
            }
            if (this.type == 'ttn') {
              let params = {
                email,
                'order_id': this.dialogData.prom_id,
                type: 'ttn',
                ttn: this.ttn,
                deliverer: this.deliverer
              }
              axios.get('api/sendemail', {params}).then((res) => {
                console.log(res.data)
              })
            }
            this.dialogData.statuses[this.type + '_email'] = 1
          }
          this.updateOrder(this.dialogData)
          this.updateStatuses(this.dialogData)
          this.showDialog = false
        },
        replaceSpecWords (template) {
          let price = parseFloat(this.item.statuses.payment_price).toFixed(2) + ' '
          price = price.trim().replace('.', ',')
          let str = template.replace('$name$', this.item.client_first_name + ' ' + this.item.client_last_name)
          str = str.replace('$id$', this.item.prom_id).replace('$price$', price)
          return str.replace('$ttn$', this.item.statuses.ttn_string).replace('$private$', this.settings.private_card)
        },
        init () {
        },
        updateStatuses(order) {
          axios.put('api/orderstatus/' + order.statuses.id, order.statuses).then((res) => {
            console.log(res.data)
          })
        },
      },
      mounted() {
        this.init()
      }
    }
</script>
<style scoped>
.modal-lg {
  max-width: 80vw;
}
.not-merged {
  color: gray;
}
textarea {
  height: 12rem;
}
</style>
