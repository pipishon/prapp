<template>
  <v-dialog width="600" v-model="showDialog" persistent @keydown.esc="showDialog = false">
    <div slot="activator" class="my-2">
      <v-icon  :class="{'blue--text text--accent-1': valid}" small>check_circle</v-icon>
    <v-tooltip v-if="isTtnCreated" left content-class="white black--text" transition="sss" :open-delay="0" :close-delay="0" style="cursor: default;">
      <span slot="activator" v-if="item.ttn">{{item.ttn.full_address}}</span><span v-else>{{item.delivery_address}}</span>
      <div class="body-1" style="width: 250px;">
        <div>{{this.item.ttn.name}}</div>
        <div>{{this.item.ttn.phone}}</div>
        <div>{{this.item.ttn.full_address}}</div>
        <div>Вес {{(item.ttn.weight > item.ttn.volume_general) ? item.ttn.weight : item.ttn.volume_general}} кг</div>
        <div>Стоимость доставки {{deliveryCost}}</div>
        <div v-if="this.item.ttn.backdelivery == '1'">Наложенный платеж {{this.item.ttn.backprice}} грн.</div>
      </div>
    </v-tooltip>
    <span v-else>
      <span v-if="item.ttn">{{item.ttn.full_address}}</span><span v-else>{{item.delivery_address}}</span>
    </span>

      <span v-if="isTtnCreated">
        <a target="_blank" @click.stop :href="'https://my.novaposhta.ua/orders/printDocument/orders[]/'+item.ttn.int_doc_number+'/type/html/apiKey/b2aa728b253bc10bbb33e79c30d6498d'">
          <v-icon small>description</v-icon>
        </a>
        <a target="_blank" @click.stop :href="'https://my.novaposhta.ua/orders/printMarkings/orders[]/'+item.ttn.int_doc_number+'/type/html/apiKey/b2aa728b253bc10bbb33e79c30d6498d'">
          <v-icon small>book</v-icon>
        </a>
      </span>
      <v-btn v-if="!isTtnCreated && item.statuses.shipment_weight != '-' && valid" @click.native.stop="setDefaults(); send(false);" flat icon class="mt-0 ml-0"><v-icon small>autorenew</v-icon></v-btn>
    </div>
    <v-card v-if="!editeTtn && isTtnCreated">
      <v-card-title class="primary white--text"><h5>Экспресс накладная</h5></v-card-title>
      <div class="px-4 pt-2">
        <table class="w-100">
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Экспресс накладная</strong></td><td class="body-2 light-green--text text--darken-3">{{item.ttn.int_doc_number}}</td> </tr>
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Получатель</strong></td><td class="body-2">{{item.ttn.name}}</td> </tr>
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Телефон получателя</strong></td><td class="body-2">{{item.ttn.phone}}</td> </tr>
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Адрес</strong></td><td class="body-2">{{item.ttn.full_address}}</td> </tr>
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Дата отправки</strong></td><td class="body-2">{{item.ttn.date}}</td> </tr>
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Плательщик</strong></td><td class="body-2">{{mapPayer[item.ttn.payer]}}</td> </tr>
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Оценочная стоимость</strong></td><td class="body-2">{{item.ttn.price}} грн.</td> </tr>
          <tr v-if="item.ttn.backdelivery == 1" style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Плательщик при обратной доставке</strong></td><td class="body-2">{{mapPayer[item.ttn.backpayer]}}</td> </tr>
          <tr v-if="item.ttn.backdelivery == 1" style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Сумма обратной доставки</strong></td><td class="body-2">{{item.ttn.backprice}} грн.</td> </tr>
          <tr style="border-bottom: 1px solid lightgray"><td class="body-2 py-3 px-2"><strong>Вес</strong></td>
            <td class="body-2">{{(item.ttn.weight > item.ttn.volume_general) ? item.ttn.weight : item.ttn.volume_general}} кг</td> </tr>
          <tr ><td class="body-2 py-3 px-2"><strong>Стоимость доставки</strong></td><td class="body-2">{{deliveryCost}}</td> </tr>
        </table>
      </div>
      <v-alert v-model="editeTtnWarning" outline dismissible type="warning" >
        <h3>Внимание!</h3>
        <div>Сохранение новых условий доставки приведет к аннулированию ранее сгенерированной ЭН.</div>
        <v-btn @click="editeTtn = true" color="secondary" outline  flat >Продолжить</v-btn>
      </v-alert>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
        <v-btn color="primary" flat @click="editeTtn = true"> Редактировать ЕН </v-btn>
      </v-card-actions>
    </v-card >
    <v-card v-if="editeTtn || !isTtnCreated">
      <v-card-title class="primary white--text"><h5>Формирование ТТН</h5></v-card-title>
        <div class="px-5">
          <v-layout row>
            <v-flex xs12 md12 >
              <v-select label="Плательщик"
                        :items="[{text: 'Получатель', value: 'Recipient'}, {text: 'Отправитель', value: 'Sender'}]"
                v-model="data.payer">
              </v-select>
            </v-flex>
          </v-layout>
          <v-layout row>
            <v-flex xs6 md6 >
              <v-text-field label="Фамилия" v-model="data.client_last_name" class="mr-3"></v-text-field>
            </v-flex>
            <v-flex xs6 md6 >
              <v-text-field label="Имя" v-model="data.client_first_name" append-outer-icon="code" @click:append-outer="switchNames"></v-text-field>
            </v-flex>
          </v-layout>
          <v-layout row>
            <v-flex xs6 md6 >
              <v-text-field label="Отчество" v-model="data.client_middle_name" class="mr-3"></v-text-field>
            </v-flex>
            <v-flex xs6 md6 >
              <v-text-field label="Телефон" v-model="data.phone" ></v-text-field>
            </v-flex>
          </v-layout>
          <v-layout row>
            <v-flex xs12 md12 >
              <strong v-if="item.ttn">{{item.ttn.full_address}}</strong><strong v-else>{{item.delivery_address}}</strong>
              <v-autocomplete label="Город" item-text="description" item-value="description" v-model="data.city" :items="newPostCities" @change="loadWarehouses"></v-autocomplete>
              <v-autocomplete label="Отделение" :items="newPostWarehouses" v-model="data.warehouse"></v-autocomplete>
            </v-flex>
          </v-layout>
          <v-layout row>
            <v-flex xs6 md6 >
              <v-text-field label="Объявленная стоимость" v-model="data.price" class="mr-3"></v-text-field>
            </v-flex>
          </v-layout>
          <v-layout row>
            <v-flex xs6 md6 >
              <v-checkbox label="Заказать обратную доставку" v-model="data.backdelivery"></v-checkbox>
            </v-flex>
          </v-layout>
          <div v-if="data.backdelivery">
          <v-layout  row>
            <v-flex xs12 md12 >
                <v-select label="Плательщик"
                  :items="[{text: 'Получатель', value: 'Recipient'}, {text: 'Отправитель', value: 'Sender'}]"
                  v-model="data.backpayer"></v-select>
            </v-flex>
          </v-layout>
          <v-layout  row>
            <v-flex md6 >
              <v-text-field label="Сумма обратной доставки" v-model="data.backprice" class="mr-3"></v-text-field>
            </v-flex>
          </v-layout>
          </div>
          <v-layout v-for="(place, key) in places"  :key="key" row>
            <v-flex md2 class="mr-3">
              <v-text-field label="Вес" v-model="place.weight"></v-text-field>
            </v-flex>
            <v-flex md2 class="mr-3">
              <v-text-field label="Длина" v-model="place.length"></v-text-field>
            </v-flex>
            <v-flex md2 class="mr-3">
              <v-text-field label="Ширина" v-model="place.width"></v-text-field>
            </v-flex>
            <v-flex md2 class="mr-3">
              <v-text-field label="Высота" v-model="place.height"></v-text-field>
            </v-flex>
            <v-flex md3>
              <v-text-field label="Объемный вес" :value="volumeWeight(place)"></v-text-field>
            </v-flex>
          </v-layout>
        </div>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="primary" flat @click="showDialog = false; editeTtn= false" > Отмена </v-btn>
          <v-btn color="primary" flat v-if="isWeightValid" @click="send(false)">Сгенерировать ЭН</v-btn>
          <v-btn color="primary" v-if="!isTtnCreated" flat @click="send(true)"> Сохранить </v-btn>
        </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import { mapGetters } from 'vuex'
    export default {
      props: ['item'],
      data() {
        return {
          editeTtnWarning: false,
          editeTtn: false,
          mapPayer: {
            'Recipient': 'Получатель',
            'Sender': 'Отправитель',
          },
          data: {payer: 'Recipient', client_first_name: '', client_last_name: '',
            client_middle_name: '', phone: '', city: '', warehouse: '',
            price: '', backdelivery: 0, backpayer: 'Recipient', backprice: ''
          },
          places: [],
          showDialog: false,
          newPostCity: '',
          newPostWarehouse: '',
          newPostWarehouses: [],
          newPostCities: [],
          payer: 'Получатель'
        }
      },
      watch: {
        showDialog (val) {
          if (val) {
            this.loadCities()
            this.setDefaults()
          }
        },
        editeTtn (val) {
          if (val) {
            this.loadWarehouses()
            //this.setDefaults()
          }
        }
        /*item: {

          handler () {
            this.isValid()
          },
          deep: true
        }*/
      },
      computed: {
        isWeightValid () {
          let valid = true
          this.places.map((place) => {
            if (isNaN(parseFloat(place.weight.replace(',','.')))) {
              valid = false
            }
          })
          return valid
        },
        valid () {
          return (this.item.is_address_valid != null)
        },
        isTtnCreated () {
          return (this.item.ttn != null && this.item.ttn.int_doc_number != null)
        },
        deliveryCost () {
          if (this.item.ttn == null) return 0
          if (this.item.ttn.backdelivery == 1) {
            const npPrice = 1*(parseFloat(this.item.ttn.cost_on_site).toFixed(2))
            const price = 1*(parseFloat(this.item.ttn.backprice).toFixed(2))
            const backPrice = (0.02 * price + 20).toFixed(2)
            const wholePrice = (npPrice + 1*backPrice).toFixed(2)
            return wholePrice + ' грн. = ' + npPrice + ' грн. + ' + backPrice + ' грн.'
          } else {
            const npPrice = 1*(parseFloat(this.item.ttn.cost_on_site).toFixed(2))
            return npPrice + ' грн.'
          }
        }
       // ...mapGetters(['newPostCities']),
      },
      methods: {
        switchNames () {
          console.log('switch')
          const firstName = this.data.client_first_name
          this.data.client_first_name = this.data.client_last_name
          this.data.client_last_name = firstName
        },
        send (onlySave) {
          this.editeTtn = false
          this.editeTtnWarning = false
          let params = Object.assign({}, this.data)
          if (onlySave) {
            params.only_save = true
          }
          params.name = (params.client_last_name + ' ' + params.client_first_name + ' ' + params.client_middle_name).trim()
          params.order_id = this.item.id
          params.places = this.places.length
          delete params.client_last_name
          delete params.client_first_name
          delete params.client_middle_name
          let volume = 0
          let weight = 0
          this.places.map((place) => {
            weight += 1*(parseFloat(place.weight.replace(',','.'))).toFixed(2)
            volume += 1*this.volumeWeight(place)
          })
          params['crm_places'] = JSON.stringify(this.places)
          params['volume_general'] = volume
          params['weight'] = weight
          params['backdelivery'] = (this.data.backdelivery) ? '1' : '0'

          axios.get('api/newpost/getttn', {params}).then((res) => {
            console.log(res.data)
            this.item.is_address_valid = {city: this.data.city, warehouse: this.data.warehouse}
            if (onlySave) {
              this.showDialog = false
            }
            this.item.ttn = res.data
            this.setDefaults();
            if (this.item.ttn.int_doc_number != null) {
              this.item.statuses.ttn_string = this.item.ttn.int_doc_number
              this.updateStatuses()
            }
          })

        },
        volumeWeight (place) {
          let volume = place.length * place.width * place.height / 4000
          if (volume < 0.1) {
            volume = 0.1
          }
          return (volume).toFixed(2);
        },
        setDefaults () {
          const keys = Object.keys(this.data)
          if (this.item.ttn == null) {
            keys.map((key) => {
              if (typeof(this.item[key]) != 'undefined') {
                this.data[key] = this.item[key]
              }
            })
            this.data.client_middle_name = ''
            this.data.phone = (this.item.statuses.custom_phone != null) ? this.item.statuses.custom_phone : this.item.phone
            this.places = []
            const place = { weight: '0.1', length: '5', width: '5', height: '5' }
            if (this.item.statuses.shipment_place && this.item.statuses.shipment_place > 1) {
              for (let i=0; i < this.item.statuses.shipment_place; i++) {
                let p = Object.assign({}, place)
                this.places.push(p)
              }
            } else {
              this.places.push(place)
            }
            //this.places[0].weight = this.item.statuses.shipment_weight //|| 0.1
            const price = Math.ceil(parseFloat(this.item.statuses.payment_price))
            this.data.backprice = price
            this.data.price = price
            if (this.valid) {
              //const matches = this.item.delivery_address.match(/^([а-яА-ЯёЁ()\s\.-]+),(.*)/)
              this.data.city = this.item.is_address_valid.city
              this.loadWarehouses()
              this.data.warehouse = this.item.is_address_valid.warehouse;
            } else {
              this.data.city = ''
              this.data.warehouse = ''
            }
          } else {
            keys.map((key) => {
              if (typeof(this.item.ttn[key]) != 'undefined') {
                this.data[key] = this.item.ttn[key]
              }
            })
            this.places = JSON.parse(this.item.ttn.crm_places)
            const names = this.item.ttn.name.split(' ')
            // this.data.phone = (this.item.statuses.custom_phone != null) ? this.item.statuses.custom_phone : this.item.ttn.phone
            this.data.client_last_name = names[0] || ''
            this.data.client_first_name = names[1] || ''
            this.data.client_middle_name = names[2] || ''
            //this.places[0].weight = //(this.places[0].weight == '-') ? this.item.statuses.shipment_weight : this.places[0].weight

            //const matches = this.item.ttn.full_address.match(/^([а-яА-ЯёЁ()\s\.-]+),(.*)/)
            this.data.city = this.item.is_address_valid.city
            this.loadWarehouses()
            this.data.warehouse = this.item.is_address_valid.warehouse;
          }
          if (this.item.statuses.payment_status == 'Наложенный') {
            this.data.backdelivery = 1
          } else {
            this.data.backdelivery = 0
          }
          if (this.places == null) {
            this.places = [{ weight: '0.1', length: '5', width: '5', height: '5' }]
          }
          this.places[0].weight = this.item.statuses.shipment_weight
          console.log(this.places)
        },
        loadWarehouses () {
          let params = {
            city: this.data.city
          }
          axios.get('api/newpost/warehouses', {params}).then((res) => {
            this.newPostWarehouses = res.data
          })
        },
        loadCities () {
          axios.get('api/newpost/city').then((res) => {
            this.newPostCities = res.data
          })
        },
        updateStatuses() {
          axios.put('api/orderstatus/' + this.item.statuses.id, this.item.statuses).then((res) => {
            console.log(res.data)
          })
        },
        /*isValid () {
          const params = {
            address: this.item.delivery_address
          }
          axios.get('api/newpost/validate', {params}).then((res) => {
            this.valid = (res.data == 1)
          })
          this.valid = (this.item.is_address_valid == 1)
        }*/
      },
      mounted() {
        //this.isValid()
        //this.newPostCity = city;
        //this.newPostWarehouse = address;
      }
    }
</script>
<style scoped>
</style>
