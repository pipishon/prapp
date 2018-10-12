<template>
  <div class="mt-4 p-2" :class="{'yellow lighten-4': is_today}">

  <div >
  <div class="text-nowrap">Отгрузка:
  <v-dialog v-model="showDialog" width="300" persistent @keydown.esc="showDialog = false">
      <v-icon slot="activator" small>event</v-icon>
    <v-card v-if="showDialog">
      <v-container fluid>
          <v-layout row>
            <v-flex xs12 md12 >
              <v-menu :close-on-content-click="false" v-model="menuDate" offset-y full-width >
                <v-text-field slot="activator" v-model="date"  readonly label="Дата отгрузки" prepend-icon="event" append-icon="close" @click:append="date = null"></v-text-field>
                <v-date-picker locale="ru-Ru" v-model="date" @input="menuDate = false"  no-title scrollable></v-date-picker>
              </v-menu>
              <v-btn small flat @click="setDate(0)" class="my-0">Сегодня</v-btn> <v-btn class="my-0" small flat @click="setDate(1)">Завтра</v-btn>
            </v-flex>
          </v-layout>
          <div v-if="delivery == 'Самовывоз'">
          <v-layout row>
            <v-flex xs12 md6 >
              <v-menu :close-on-content-click="false" v-model="menuFrom" offset-y full-width>
                <v-text-field slot="activator" v-model="from" readonly label="с" prepend-icon="access_time"></v-text-field>
              <v-time-picker v-if="menuFrom" v-model="from"  @input="menuFrom = false" format="24hr"></v-time-picker>
              </v-menu>
            </v-flex>
            <v-flex xs12 md6>
              <v-menu :close-on-content-click="false" v-model="menuTo" offset-y full-width>
                <v-text-field slot="activator" v-model="to" readonly label="до" prepend-icon="access_time" append-icon="close" @click:append="from = '00:00'; to = '00:00'"></v-text-field>
                <v-time-picker v-if="menuTo" v-model="to" @input="menuTo = false" format="24hr"></v-time-picker>
              </v-menu>
            </v-flex>
          </v-layout>
          </div>
       </v-container>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
            <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
          </v-card-actions>
    </v-card>
  </v-dialog>
  </div>
    <div v-if="item.shipment_date != null">
      <strong>
      {{item.shipment_date}}
      <span v-if="item.shipment_from != null && item.shipment_to == null"> с </span>
      <span v-if="item.shipment_from == null && item.shipment_to != null"> до </span>
      <span v-if="item.shipment_from != null">
      {{item.shipment_from}}
      </span>
      <span v-if="item.shipment_from != null && item.shipment_to != null"> - </span>
      <span v-if="item.shipment_to != null">
      {{item.shipment_to}}
      </span>
      </strong>


    </div>
    <div v-else>
      не указано
    </div>
  </div>
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
          saved: false
        }
      },
      computed: {
        is_today () {
          if (this.item.shipment_date == null) return false
          let today = new Date();
          let pieces = this.item.shipment_date.split('-')
          if (today.getDate() == parseInt(pieces[2]) && today.getMonth() + 1 == parseInt(pieces[1])) {
            return true
          }
          return false
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
        }
      },
      methods: {
        setDate (n) {
          this.date = moment().add(n, 'days').format('YYYY-MM-DD')
        },
        save () {
          this.saved = true
          this.showDialog = false;
          this.item.shipment_date = this.date
          this.item.shipment_to = (this.to == '00:00') ? null : this.to
          this.item.shipment_from = (this.from == '00:00') ? null : this.from
          axios.put('api/orderstatus/' + this.item.id, this.item).then((res) => {
            console.log(res.data)
            this.saved = false
          })
        },
        onMount () {
         this.date = this.item.shipment_date
         this.to = this.item.shipment_to || '00:00'
         this.from = this.item.shipment_from || '00:00'
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
