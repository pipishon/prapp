<template>
  <div>
    <v-container grid-list-md >
     <v-layout row wrap>
      <v-flex d-flex md3>
        <v-layout row wrap>
          <v-flex d-flex xs12>
            <v-card color="red darken-1" dark @click.native="$emit('clickstatus', 'lost_vip')" >
              <v-card-text >
                Потери VIP {{formated('lost_vip')}}
                <template v-if="arrowVal('lost_vip') != 0">
                  <v-icon v-if="arrowVal('lost_vip') == -1" color="red accent-4">arrow_drop_down</v-icon>
                  <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                </template>

                <v-btn @click="showStatistic('lost_vip')" icon flat><v-icon>bar_chart</v-icon></v-btn>
              </v-card-text>
            </v-card>
          </v-flex>
          <v-flex d-flex xs12>
            <v-card color="pink darken-1" dark @click.native="$emit('clickstatus', 'lost')">
              <v-card-text >Потери {{formated('lost')}}

                <template v-if="arrowVal('lost') != 0">
                  <v-icon v-if="arrowVal('lost') == -1" color="red accent-4">arrow_drop_down</v-icon>
                  <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                </template>
                <v-btn @click="showStatistic('lost')" icon flat><v-icon>bar_chart</v-icon></v-btn>
              </v-card-text>
            </v-card>
          </v-flex>
        </v-layout>
      </v-flex>
      <v-flex d-flex md3>
            <v-card color="orange darken-1" dark @click.native="$emit('clickstatus', 'risk')">
              <v-card-text >В зоне риска {{formated('risk')}}
              <template v-if="arrowVal('risk') != 0">
                <v-icon v-if="arrowVal('risk') == -1" color="red accent-4">arrow_drop_down</v-icon>
                <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
              </template>
                <v-btn @click="showStatistic('risk')" icon flat><v-icon>bar_chart</v-icon></v-btn>
              </v-card-text>
            </v-card>
      </v-flex>
      <v-flex d-flex md6>
            <v-layout row wrap>
              <v-flex d-flex xs12>
                <v-card color="green darken-1" dark @click.native="$emit('clickstatus', 'vip')">
                  <v-card-text >VIP {{formated('vip')}}
                  <template v-if="arrowVal('vip') != 0">
                    <v-icon v-if="arrowVal('vip') == -1" color="red accent-4">arrow_drop_down</v-icon>
                    <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                  </template>
                  </v-card-text>
                <v-btn @click="showStatistic('vip')" icon flat><v-icon>bar_chart</v-icon></v-btn>
                </v-card>
              </v-flex>
              <v-flex d-flex xs12>
                <v-card color="green lighten-1" dark @click.native="$emit('clickstatus', 'loyal')">
                  <v-card-text >Лояльные {{formated('loyal')}}
                  <template v-if="arrowVal('loyal') != 0">
                    <v-icon v-if="arrowVal('loyal') == -1" color="red accent-4">arrow_drop_down</v-icon>
                    <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                  </template>
                  </v-card-text>
                <v-btn @click="showStatistic('loyal')" icon flat><v-icon>bar_chart</v-icon></v-btn>
                </v-card>
              </v-flex>
            </v-layout>
      </v-flex>
    </v-layout>
     <v-layout row wrap>
        <v-flex d-flex md3>
          <v-layout row wrap>
            <v-flex d-flex xs12>
              <v-card color="brown" dark >
                <v-card-text>&nbsp;</v-card-text>
              </v-card>
            </v-flex>
            <v-flex d-flex xs12>
              <v-card color="brown lighten-2" dark @click.native="$emit('clickstatus', 'one_time')">
                <v-card-text >Одноразовые {{formated('one_time')}}
                  <template v-if="arrowVal('one_time') != 0">
                    <v-icon v-if="arrowVal('one_time') == -1" color="red accent-4">arrow_drop_down</v-icon>
                    <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                  </template>
                </v-card-text>
                <v-btn @click="showStatistic('one_time')" icon flat><v-icon>bar_chart</v-icon></v-btn>
              </v-card>
            </v-flex>
          </v-layout>
        </v-flex>
        <v-flex d-flex md3>
            <v-card color="brown" dark @click.native="$emit('clickstatus', 'sleep')">
              <v-card-text >Cпящие {{formated('sleep')}}
                  <template v-if="arrowVal('sleep') != 0">
                    <v-icon v-if="arrowVal('sleep') == -1" color="red accent-4">arrow_drop_down</v-icon>
                    <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                  </template>
              </v-card-text>
                <v-btn @click="showStatistic('sleep')" icon flat><v-icon>bar_chart</v-icon></v-btn>
            </v-card>
        </v-flex>
        <v-flex d-flex md3>
            <v-card color="yellow darken-1" dark @click.native="$emit('clickstatus', 'suspended')" >
              <v-card-text >Подвисшие {{formated('suspended')}}
                  <template v-if="arrowVal('suspended') != 0">
                    <v-icon v-if="arrowVal('suspended') == -1" color="red accent-4">arrow_drop_down</v-icon>
                    <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                  </template>
              </v-card-text>
                <v-btn @click="showStatistic('suspended')" icon flat><v-icon>bar_chart</v-icon></v-btn>
            </v-card>
        </v-flex>
        <v-flex d-flex md3>
          <v-layout row wrap>
            <v-flex d-flex xs12>
              <v-card color="light-blue lighten-1" dark @click.native="$emit('clickstatus', 'perspective')">
                <v-card-text >Перспективные {{formated('perspective')}}
                  <template v-if="arrowVal('perspective') != 0">
                    <v-icon v-if="arrowVal('perspective') == -1" color="red accent-4">arrow_drop_down</v-icon>
                    <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                  </template>
                <v-btn @click="showStatistic('perspective')" icon flat><v-icon>bar_chart</v-icon></v-btn>
                </v-card-text>
              </v-card>
            </v-flex>
            <v-flex d-flex xs12>
              <v-card color="purple" dark @click.native="$emit('clickstatus', 'new')">
                <v-card-text >Новые {{formated('new')}}
                  <template v-if="arrowVal('new') != 0">
                    <v-icon v-if="arrowVal('new') == -1" color="red accent-4">arrow_drop_down</v-icon>
                    <v-icon v-else color="green darken-4">arrow_drop_up</v-icon>
                  </template>
                <v-btn @click="showStatistic('new')" icon flat><v-icon>bar_chart</v-icon></v-btn>
                </v-card-text>
              </v-card>
            </v-flex>
          </v-layout>
        </v-flex>
     </v-layout>
     <div @click="$emit('clickstatus', '')">Неопределенных: {{undef}}</div>
     <h2>Всего: {{total}}</h2>
     <span v-if="isToday"> Текущая ситуация </span>
     <span v-else> {{dateRange[0]}} - {{dateRange[1]}}</span>
      <v-dialog v-model="rangeDialog" width="640">
        <v-card>
          <v-daterange no-presets :first-day-of-week="1" locale="ru-Ru" :options="dateRangeOptions" @input="dateRangeTmp = arguments[0]" ></v-daterange>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="primary" flat @click=" dateRangeTmp=dateRange.slice();rangeDialog = false" > Закрыть </v-btn>
              <v-btn color="primary" flat @click="setOrderStatRange" > Показать </v-btn>
            </v-card-actions>
        </v-card>
      </v-dialog>
  </v-container>
      <v-footer fixed>
        <v-btn @click="getYesterday" >Вчера</v-btn>
        <v-btn @click="getToday" >Сегодня</v-btn>
        <v-btn @click="rangeDialog = true" >Показать предыдущие дни</v-btn>
      </v-footer>

    <v-dialog width="1100" v-model="showDialogStatistics">
      <v-card>
        <div style="width: 1100px; height: 700px;">
          <barchart
            :width="1100"
            :height="700"
            :chart-data="chartData"
            :options="{responsive: true}"
          ></barchart>
        </div>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
import * as moment from 'moment';
    export default {
      props: ['today'],
      data() {
        return {
          chartData: null,
          showDialogStatistics: false,
          isToday: false,
          undef: '',
          statuses: {},
          savedDates: [],
          prevStatuses: {},
          availableRange: {
            minDate: moment().format('Y-MM-DD'),
            maxDate: moment().format('Y-MM-DD'),
          },
          map: {},
          total: 1,
          rangeDialog: false,
          dateRange: [moment().subtract(2, 'days').format('Y-MM-DD'), moment().subtract(1, 'days').format('Y-MM-DD')],
          dateRangeTmp: [],
        }
      },
      computed: {
        dateRangeOptions () {
          return {
            startDate: this.dateRange[0],
            endDate: this.dateRange[1],
            minDate: this.availableRange.minDate,
            maxDate: this.availableRange.maxDate,
            format: 'YYYY-MM-DD'
          }
        }
      },
      methods: {
        showStatistic (name) {
          this.showDialogStatistics = true
          axios.get('api/rfc/statistic/' + name).then((res) => {
            let labels = []
            let datasets = [
                {
                  label: name,
                  data: [],
                  backgroundColor: 'red',
                  borderColor: 'red',
                  fill: false,
                },
              ]

            res.data.map((row) => {
              labels.push(row.date)
              datasets.map((dataset) => {
                  dataset.data.push(row[name])
              })
            })

            this.chartData = {labels, datasets}
            console.log(this.chartData)
          })
        },
        getToday () {
          axios.get('api/rfc/gettoday').then((res) => {
            this.isToday = true
            this.statuses = res.data.end[0]
            this.prevStatuses = res.data.start[0]
            this.undef = res.data.undef
          })
        },
        getYesterday () {
          this.dateRange = [moment().subtract(2, 'days').format('Y-MM-DD'), moment().subtract(1, 'days').format('Y-MM-DD')]
          this.getSavedRfc()
        },
        setOrderStatRange () {
          if (this.dateRangeTmp.length > 0) {
            this.dateRange = this.dateRangeTmp.slice();
          }
          this.getSavedRfc();
          this.rangeDialog = false
        },
        getSavedRfc () {
          const params = {
            range: this.dateRange
          }
          axios.get('api/rfc/saved', {params}).then((res) => {
            if (res.data.end.length) {
              this.statuses = res.data.end[0]
              this.prevStatuses = res.data.start[0]
              this.undef = res.data.undef
              this.isToday = false
            }
            console.log(res)
          })
        },
        percent (val) {
          return '(' + (val * 100 / this.total).toFixed(2) + '%)'
        },
        formated (name) {
          const val = this.statuses[name]
          let prev = ''
          if (typeof(this.prevStatuses.vip) != 'undefined') {
            prev = '[' + (this.statuses[name] - this.prevStatuses[name]) + ']'
          }
          return  val + ' ' + this.percent(val) + ' ' + prev
        },
        arrowVal (name) {
          if (typeof(this.prevStatuses.vip) == 'undefined' ||
              this.statuses[name] == this.prevStatuses[name]
          ) {
            return 0
          }
          return (this.statuses[name] < this.prevStatuses[name]) ? -1 : 1
        }
      },
      mounted() {
        axios.get('api/rfc/getdates').then((res) => {
          if (res.data.length != 0) {
            this.availableRange.minDate = res.data[0]
            this.availableRange.maxDate = res.data[res.data.length - 1]
          }
        })
        if (typeof(this.today) != 'undefined' && this.today) {
          this.getToday();
        } else {
          this.getSavedRfc();
        }
        axios.get('api/rfc').then((res) => {
         // this.statuses = res.data.statuses
          this.map = res.data.map
          this.total = res.data.total
        })
        axios.get('api/rfc/statistic/vip').then((res) => {
          console.log(res.data)
        })
      }
    }
</script>
<style>
.date-range__pickers label{
  display: none;
}
.v-icon {
  line-height: 0.8;
}
</style>
