<template>
  <v-container grid-list-md>
     <v-layout row wrap>
      <v-flex d-flex md3>
        <v-layout row wrap>
          <v-flex d-flex xs12>
            <v-card color="red darken-1" dark >
              <v-card-text>Потери VIP {{formated('lost_vip')}}</v-card-text>
            </v-card>
          </v-flex>
          <v-flex d-flex xs12>
            <v-card color="pink darken-1" dark >
              <v-card-text>Потери {{formated('lost')}}</v-card-text>
            </v-card>
          </v-flex>
        </v-layout>
      </v-flex>
      <v-flex d-flex md3>
            <v-card color="orange darken-1" dark >
              <v-card-text>В зоне риска {{formated('risk')}}</v-card-text>
            </v-card>
      </v-flex>
      <v-flex d-flex md6>
            <v-layout row wrap>
              <v-flex d-flex xs12>
                <v-card color="green darken-1" dark >
                  <v-card-text>VIP {{formated('vip')}}</v-card-text>
                </v-card>
              </v-flex>
              <v-flex d-flex xs12>
                <v-card color="green lighten-1" dark >
                  <v-card-text>Лояльные {{formated('loyal')}}</v-card-text>
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
              <v-card color="brown lighten-2" dark >
                <v-card-text>Одноразовые {{formated('one_time')}}</v-card-text>
              </v-card>
            </v-flex>
          </v-layout>
        </v-flex>
        <v-flex d-flex md3>
            <v-card color="brown" dark >
              <v-card-text>Cпящие {{formated('sleep')}}</v-card-text>
            </v-card>
        </v-flex>
        <v-flex d-flex md3>
            <v-card color="yellow darken-1" dark >
              <v-card-text>Подвисшие {{formated('suspended')}}</v-card-text>
            </v-card>
        </v-flex>
        <v-flex d-flex md3>
          <v-layout row wrap>
            <v-flex d-flex xs12>
              <v-card color="light-blue lighten-1" dark >
                <v-card-text>Перспективные {{formated('perspective')}}</v-card-text>
              </v-card>
            </v-flex>
            <v-flex d-flex xs12>
              <v-card color="purple" dark >
                <v-card-text>Новые {{formated('new')}}</v-card-text>
              </v-card>
            </v-flex>
          </v-layout>
        </v-flex>
     </v-layout>
     <div>Неопределенных: {{statuses['']}}</div>
     <h2>Всего: {{total}}</h2>
  </v-container>
</template>
<script>
import * as moment from 'moment';
    export default {
      data() {
        return {
          statuses: {},
          map: {},
          total: 1
        }
      },
      methods: {
        percent (val) {
          return '(' + (val * 100 / this.total).toFixed(2) + '%)'
        },
        formated (name) {
          const val = this.statuses[name]
          return  val + ' ' + this.percent(val)
        }
      },
      mounted() {
        axios.get('api/rfc').then((res) => {
          this.statuses = res.data.statuses
          this.map = res.data.map
          this.total = res.data.total
        })
      }
    }
</script>
<style>
</style>
