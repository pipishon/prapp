<template>
          <div class="collected">
            <div>
              <perfselect menu-props="offsetY" :style="{width: '125px'}" @input="changeCollected" :hide-details="true" class="mt-0 pt-0" v-model="collectedString" :items="['Не собран', 'Собран']" :append-icon="(item.statuses.collected_string != 'Собран') ? 'hourglass_empty' : 'check_circle'" ></perfselect>
            </div>
            <div class="mt-1 w-75 text-nowrap">
              <span v-if="['Пункты самовывоза', 'Доставка Укрпочтой (25-55 грн, оплачивается вместе с заказом)'].indexOf(item.delivery_option) == -1">
                Вес: <strong ><u @click="showDialog = true" >{{item.statuses.shipment_weight.replace('-', '&mdash;')}}</u></strong><span class="ml-2"> Место: <strong>{{item.statuses.shipment_place}}</strong></span>
              </span>
              <span v-else><br /></span>
            </div>
            <table class="table mt-1">
              <tbody>
                <tr v-for="(name, key) in {'bill': 'Cчет', 'business_card': 'Визитка', 'present': 'Подарок'}">
                  <td>{{name}}: <span class="red--text ml-1" v-if="(key == 'bill' && item.customer.bill_required) || (key == 'present' && item.customer.gift_required)">(*)</span></td>
                  <td><v-checkbox class="m-0" @change="$emit('change')" :hide-details="true" height="10" flat v-model="item.statuses[key]"></v-checkbox></td>
                </tr>
              </tbody>
            </table>
            <v-text-field class="mt-1 mb-0" :hide-details="true" :class="{blink: giftSaved}" label="Подарок" v-model="gift" @keyup.enter.native="saveGift" />
              <v-dialog v-if="showDialog" v-model="showDialog" width="350" persistent @keydown.esc="showDialog = false">
                <v-card class="p-2">
                  <v-container fluid v-if="['Пункты самовывоза', 'Доставка Укрпочтой (25-55 грн, оплачивается вместе с заказом)'].indexOf(item.delivery_option) == -1">
                      <v-layout row>
                        <v-flex xs12 md6 class="px-2" >
                          <v-select class="m-0" v-model="weight " label="Вес посылки" :items="['0,5 кг', '1 кг', '2 кг', '3 кг', '4 кг', '5 кг', '6 кг', '7 кг', '8 кг', '9 кг', '10 кг', '>10 кг', '0,1 кг', '-']"></v-select>
                        </v-flex>
                        <v-flex xs12 md6 class="px-2">
                          <v-select class="m-0" v-model="place" label="Колличество мест" :items="['1', '2', '3', '-']"></v-select>
                        </v-flex>
                      </v-layout>
                   </v-container>
                      <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
                        <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
                      </v-card-actions>
                </v-card>
              </v-dialog>
            </div>
</template>

<script>
import * as moment from 'moment';
    export default {
      props: ['item'],
      data() {
        return {
          weight: '0,5 кг',
          place: '1',
          gift: null,
          showDialog: false,
          collectedString: null,
          changeCollectedStatus: false,
          giftSaved: false
        }
      },
      watch: {
        item: {
          handler () {
            if (!this.saved) {
              this.onMount()
            }
          },
          deep: true
        },
        showDialog (val) {
          if (!this.saved && !val) {
            this.onMount()
          }
        }
      },
      methods: {
        changeCollected (val) {
          if (val == 'Не собран') {
            this.item.statuses.collected_string = val
            this.item.statuses.collected = 0
            this.$emit('change')
          } else {
            this.changeCollectedStatus = true
            this.showDialog = true
          }
        },
        onMount () {
           this.weight = (this.item.statuses.shipment_weight == '-') ? '0,5 кг' : this.item.statuses.shipment_weight
           this.place = (this.item.statuses.shipment_place == '-') ? '1' : this.item.statuses.shipment_place
           this.collectedString = this.item.statuses.collected_string
           this.gift = this.item.statuses.present_name;
        },
        saveGift () {
          if ( this.item.statuses.present_name == this.gift ) return
          this.saved = true
          this.item.statuses.present_name = this.gift;
          if (this.gift != '') {
            axios.get('api/customers/' + this.item.customer.id).then((res) => {
              let customer = res.data
              if (customer.gifts == null) {
                customer.gifts = this.gift;
              } else {
                customer.gifts += ', ' + this.gift;
              }
              this.item.customer.gifts = customer.gifts
              axios.put('api/customers/' + this.item.customer.id, this.item.customer).then((res) => {
                console.log(res)
              })
            })
          }

          this.giftSaved = true;
          setTimeout(() => {
            this.giftSaved = false;
          }, 500)
          this.saved = false;
          this.$emit('change')
        },
        save () {
          this.saved = true
          this.item.statuses.shipment_weight = this.weight;
          this.item.statuses.shipment_place = this.place;
          if (this.changeCollectedStatus) {
            this.item.statuses.collected_string = 'Собран';
            this.item.statuses.collected = 1
          }
          this.showDialog = false;
          this.saved = false;
          this.$emit('change')
        }
      },
      mounted() {
        this.onMount()
      }
    }
</script>
<style>
.collected {
  width: 10rem;
}
.collected  .table td
{
  border: none;
  padding: 2px 6px;
}
.collected  .table{
  background: none;
}
.blink {
  animation: blink 500ms infinite;  /* IE 10+, Fx 29+ */
}

@-webkit-keyframes blink {
  0%, 49% {
    background-color: #e8f5e9;
  }
  50%, 100% {
    background-color: #fafafa;
  }
}
</style>
