<template>
  <div class="container">
    <v-container fluid class="my-0 py-0">
        <v-layout row>
        </v-layout>
    <btable
      :items="list"
      :fields="fields"
      :notstriped="true"
      class="mb-5"
    >
      <template slot="row" slot-scope="data">
        <tr v-for="item  in data.items" :key="item.id" >
          <td >
          </td>
          <td >
            <v-flex md3 >
              <v-select :menu-props="{maxHeight: 400}"  :hide-details="true" label="Фильтр пользователей" :items="Object.keys(filterMap)" v-model="selectedFilter" @input="onSelectFilter(item.id)" ></v-select>
            </v-flex>
              <v-chip v-model="filterChips[item.id][filter]" v-for="chip in filters[item.id]" :key="chip.filter" close>{{chip.filter}}
                <span v-if="item.from">&nbsp;от {{chip.from}}</span>
                <span v-if="item.to">&nbsp;до {{chip.to}}</span>
              </v-chip>
          </td>
          <td >
          </td>
          <td >
          </td>
          <td >
          </td>
          <td >
          </td>
        </tr>
      </template>
    </btable>
    </v-container>
    <v-dialog  v-model="showAddFilterDialog" :width="(selectedFilter != 'Авто статус') ? 300 : 1000" persistent @keydown.esc="showAddFilterDialog = false">
      <v-card v-if="showAddFilterDialog" >

          <v-card-title class="primary white--text"><h5>{{selectedFilter}}</h5></v-card-title>
          <div class="px-3">
            <v-text-field label="От" v-model="filterFrom"></v-text-field>
            <v-text-field label="До" v-model="filterTo"></v-text-field>
          </div>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="showAddFilterDialog = false" > Отмена </v-btn>
            <v-btn color="primary" flat @click="setFilter(activeTaskId)" > Установить </v-btn>
          </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
  import * as moment from 'moment';
    export default {
      data() {
        return {
          list: [{
            id: 0,
            name: 'test',
            filters: [],
            time: '00-00',
          }],
          fields: [
            { key: 'name', label: 'Название' },
            { key: 'filters', label: 'Фильтры' },
            { key: 'time', label: 'Время рассылки' },
          ],
          activeTaskId: null,
          showAddFilterDialog: false,
          selectedFilter: null,
          filterFrom: null,
          filterTo: null,
          filters: [],
          filterChips: {},
          filterMap: {
             'Дата первой покупки': 'first_order',
             'Дата последней покупки': 'last_order',
             'Кол-во заказов': 'count_orders',
             'Всего денег': 'total_price',
             'Средний чек': 'aver_price',
             'Авто статус': 'auto_status',
          },
        }
      },
      methods: {
        setFilter (id) {
          if (typeof(this.filters[id]) == 'undefined') {
            this.filters[id] = []
          }
          this.filters[id].push({
            filter: this.selectedFilter,
            from: this.filterFrom,
            to: this.filterTo
          })

          this.filterFrom = null
          this.filterTo = null
          this.showAddFilterDialog = false
        },
        onSelectFilter (id) {
          this.activeTaskId = id
          this.showAddFilterDialog = true
        }
      },
      mounted() {
      }
    }
</script>
<style scoped>
</style>
