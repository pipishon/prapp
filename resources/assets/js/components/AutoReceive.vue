<template>
  <div>
  <v-container fluid>
    <v-layout row wrap>
      <v-flex md12>
      <btable :items="list" :fields="fields" :notstriped="true" >
        <template slot="row" slot-scope="data">
          <tr v-for="(item, key) in list" :item="item" :key="key">
            <td>
              <v-select menu-props="offsetY" :items="days" v-model="item.day_from" @change="update(item)"></v-select>
            </td>
            <td>
              <v-select menu-props="offsetY" :items="days" v-model="item.day_to" @change="update(item)"></v-select>
            </td>
            <td>
              <v-menu :close-on-content-click="false" v-model="menus.timeFrom[key]" offset-y full-width>
                <v-text-field slot="activator" :value="minutes(item.time_from)" readonly label="Время с" prepend-icon="access_time"></v-text-field>
              <v-time-picker v-if="menus.timeFrom[key]" v-model="item.time_from"  @change="menus.timeFrom[key] = false; update(item)" format="24hr"></v-time-picker>
              </v-menu>
            </td>
            <td>
              <v-menu :close-on-content-click="false" v-model="menus.timeTo[key]" offset-y full-width>
                <v-text-field slot="activator" :value="minutes(item.time_to)" readonly label="Время до" prepend-icon="access_time"></v-text-field>
              <v-time-picker v-if="menus.timeTo[key]" v-model="item.time_to"  @change="menus.timeTo[key] = false; update(item)" format="24hr"></v-time-picker>
              </v-menu>
            </td>
            <td>
              <v-select menu-props="offsetY" :value="template('sms', item)" :items="tmplts"  @input="updateTemplate('sms', item, arguments[0])"></v-select>
            </td>
            <td>
              <v-select menu-props="offsetY" :value="template('email', item)" :items="tmplts"  @input="updateTemplate('email', item, arguments[0])"></v-select>
            </td>
            <td>
              <v-checkbox v-model="item.active" @change="update(item)"></v-checkbox>
            </td>
            <td>
              <v-btn @click="clear(item)"   icon><v-icon color="primary">cancel</v-icon></v-btn>
              <v-btn @click="remove(item)"  icon><v-icon color="error">delete</v-icon></v-btn>
            </td>
          </tr>
        </template>
      </btable>
      </v-flex>
    </v-layout>
  </v-container>
  <v-footer fixed class="pa-3">
      <v-spacer></v-spacer>
      <v-btn @click="add">Add</v-btn>
      <v-spacer></v-spacer>
  </v-footer>
  </div>
</template>

<script>
import * as moment from 'moment';
    export default {
      data() {
        return {
          menus: {
            timeFrom: {},
            timeTo: {},
            dateTo: {},
            dateFrom: {},
          },
          fields: [
            { key: 'day_from', label: 'День с' },
            { key: 'day_to', label: 'День до' },
            { key: 'time_from', label: 'Время с' },
            { key: 'time_to', label: 'Время до' },
            { key: 'sms_template_id', label: 'Смс шаблон' },
            { key: 'sms_template_id', label: 'Email шаблон' },
            { key: 'active', label: 'Включен' },
            { key: 'action', label: '' },
          ],
          list: [],
          templates: [],
        }
      },
      methods: {
        minutes (val) {
          return (val) ? moment(val, 'HH:mm:ss').format('HH:mm') : null
        },
        getList (params) {
          axios.get('api/autoreceive').then((res) => {
            this.list = res.data
          })
        },
        update (item) {
          axios.put('api/autoreceive/' + item.id, {...item}).then((res) => {
            this.getList()
          })
        },
        add () {
          axios.post('api/autoreceive').then((res) => {
            this.getList()
          })
        },
        clear (item) {
          Object.keys(item).forEach(name => {
            if (name != 'id') item[name] = null
          })
          this.update(item)
        },
        remove (item) {
          axios.delete('api/autoreceive/' + item.id).then((res) => {
            this.getList()
          })
        },
        updateTemplate (type, item, val) {
          item[type + '_template_id'] = val.id
          this.update(item)
        },
        template (type, item) {
          if (item[type + '_template_id'] == null ||
              this.templates.length == 0 ||
              typeof(this.templates.filter(el => el.id == item[type + '_template_id'])[0]) == 'undefined'
          ) return {}
          let tpl = this.templates.filter(el => el.id == item[type + '_template_id'])[0]
          return {text: tpl.name, value: tpl}
        }
      },
      computed: {
        days () {
          let arr = moment.weekdays()
          arr.push(arr.shift())
          arr.map((el, i) => arr[i] = {text: el, value: i.toString()})
          return arr
        },
        tmplts () {
          let tpls = []
          this.templates.map( (a) => {tpls.push({text: a.name, value: a}) })
          return tpls
        }
      },
      mounted() {
        moment.locale('ru')
        moment.updateLocale('ru', {
          week: {
            dow: 1
          }
        })
        this.getList()
        axios.get('api/messagetpl').then((res) => {
          this.templates = res.data
        })
      }
    }
</script>
<style scope>
</style>
