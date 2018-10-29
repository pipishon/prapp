<template>
  <div class="p-2" >

    <div class="payment">
      <div>
        <span class="status">
          <perfselect :style="{width: '135px'}" class="mt-0 pt-0" menu-props="offsetY" v-model="payment_status" :items="['Не оплачен', 'Частично', 'Наложенный', 'Оплачен']"  :append-icon="(item.payment_status != 'Оплачен') ? 'hourglass_empty' : 'check_circle'" @input="setStatus"></perfselect>
        </span>
      </div>
      <div @click="showDialog = true">
        <div class="mt-1">
          <span>К оплате:</span><u class="float-right"><strong>{{parseFloat(item.payment_price).toFixed(2)}} грн</strong></u>
        </div>
        <div class="mt-1">
          <span>Оплачено:</span><u class="float-right"><strong>{{parseFloat(item.payment_partialy).toFixed(2)}} грн</strong></u>
        </div>
        <div class="mt-1">
          <span>Остаток:</span><u class="float-right"><strong>{{parseFloat(item.payment_price - item.payment_partialy).toFixed(2)}} грн</strong></u>
        </div>
        <div class="mt-4" v-if="item.payment_date != null">
          <span v-if="this.payment_status == 'Оплачен'">Оплачено:</span>
          <span v-else>Обработан:</span><br />
          <span>{{payment_date_string}}</span>
        </div>
      </div>
    </div>
  <v-dialog width="400" v-if="showDialog" v-model="showDialog" persistent @keydown.esc="showDialog = false" >
    <v-card>
      <v-container fluid>
          <v-layout row>
            <v-flex xs12 justify-center >
              <div style="width: 300px; margin-left: 40px;"  >
                <v-text-field class="xs-8" v-model="payment_price"  label="К оплате" append-icon="play_for_work" @click:append="payment_partialy = payment_price"></v-text-field>
                <v-text-field v-model="payment_partialy"  label="Оплачено"></v-text-field>
                <v-text-field v-model="payment_leave" disabled label="Остаток"></v-text-field>
                <v-menu :close-on-content-click="false" v-model="menuDate" menu-props="offsetY" full-width >
                  <v-text-field slot="activator" v-model="date"  readonly label="Дата отгрузки" prepend-icon="event" append-icon="close" @click:append="date = null"></v-text-field>
                  <v-date-picker locale="ru-Ru" v-model="date" @input="menuDate = false"  no-title scrollable></v-date-picker>
                </v-menu>
                <v-btn small flat @click="setDate(0)" class="my-0">Сегодня</v-btn> <v-btn class="my-0" small flat @click="setDate(1)">Завтра</v-btn>
              </div>
            </v-flex>
          </v-layout>
          <div v-if="delivery == 'Самовывоз'">
          <v-layout row>
            <v-flex xs12 md6 >
              <div style="width: 100px; margin-left: 40px;"  >
                <v-menu :close-on-content-click="false" v-model="menuFrom" menu-props="offsetY" full-width>
                  <v-text-field slot="activator" v-model="from" readonly label="с" prepend-icon="access_time"></v-text-field>
                <v-time-picker v-if="menuFrom" v-model="from"  @input="menuFrom = false" format="24hr"></v-time-picker>
                </v-menu>
              </div>
            </v-flex>
            <v-flex xs12 md6>
              <div style="width: 100px; display: inline-block;"  >
                <v-menu :close-on-content-click="false" v-model="menuTo" menu-props="offsetY" full-width>
                  <v-text-field slot="activator" v-model="to" readonly label="до" prepend-icon="access_time" ></v-text-field>
                  <v-time-picker v-if="menuTo" v-model="to" @input="menuTo = false" format="24hr"></v-time-picker>
                </v-menu>
              </div>
              <div style="width: 45px; display: inline-block; text-align: right"  >
                <v-icon class="mb-1" @click="from = '00:00'; to = '00:00'">close</v-icon>
              </div>
            </v-flex>
          </v-layout>
          </div>
       </v-container>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="dismiss" > Отмена </v-btn>
            <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
          </v-card-actions>
    </v-card>
  </v-dialog>
</div>
</template>

<script>
import * as moment from 'moment';
    export default {
      props: ['delivery', 'item'],
      data() {
        return {
          showDialog: false,
          menuDate: false,
          menuFrom: false,
          menuTo: false,
          date: null,
          to: null,
          from: null,
          payment_price: null,
          payment_partialy: null,
          payment_status: 'Не оплачен',
          saved: false,
          changeStatus: false,
        }
      },
      computed: {
        payment_leave () {
          return  (this.payment_price - this.payment_partialy).toFixed(2)
        },
        payment_date_string () {
          return moment(this.item.payment_date).format('YYYY-MM-DD HH:mm')
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
          if (!val && !this.saved) {
            this.onMount()
          }
        }
      },
      methods: {
        setDate (n) {
          this.date = moment().add(n, 'days').format('YYYY-MM-DD')
        },
        dismiss () {
          this.showDialog = false
        },
        onMount () {
           this.date = this.item.shipment_date
           this.to = this.item.shipment_to || '00:00'
           this.from = this.item.shipment_from || '00:00'
           this.payment_price = parseFloat(this.item.payment_price).toFixed(2)
           this.payment_partialy = parseFloat(this.item.payment_partialy).toFixed(2)
           this.payment_status = this.item.payment_status
           this.changeStatus = false
        },
        setStatus (val) {
          if (this.payment_status != 'Не оплачен') {
            this.changeStatus = true
            this.showDialog = true
          } else {
            this.payment_partialy = 0
            this.save()
          }
        },
        save () {
          this.saved = true
          this.showDialog = false;
          this.item.shipment_date = this.date
          this.item.shipment_to = (this.to == '00:00') ? null : this.to
          this.item.shipment_from = (this.from == '00:00') ? null : this.from
          this.item.payment_price = this.payment_price
          this.item.payment_partialy = this.payment_partialy
          this.item.payment_status = this.payment_status

          if (['Оплачен', 'Наложенный'].indexOf(this.payment_status) != -1) {
            this.item.payment_date = moment().format('YYYY-MM-DD HH:mm:ss')
            this.payment_date = this.item.payment_date
          } else {
            this.item.payment_date = null
            this.payment_date = null
            this.item.payed = 0
          }

          axios.put('api/orderstatus/' + this.item.id, this.item).then((res) => {
            console.log(res.data)
            this.changeStatus = false
          })
          this.saved = false;

        }
      },
      mounted() {
        this.onMount()
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
