<template>
  <div class="container">
    <div class="loader-overlay" v-show="onSave">
      <div class="loader" >
        <img src="imgs/loader.svg">
      </div>
    </div>
    <table class="cron-table">
      <tr>
        <th v-for="header in headers">{{header}}</th>
      </tr>
      <tr v-for="row in list" @click="openDialog(row)">
        <td class="text-left">{{row.name}}</td>
        <td class="text-left">{{row.period}}</td>
        <td class="align-middle" @click.stop="save()"><v-checkbox v-model="row.active"></v-checkbox></td>
        <td :class="{'red--text text--lighten-1': !row.success}">{{formatDate(row.last_job)}}</td>
        <td>{{getNextCron(row.period, row.last_job)}}</td>
        <td>
          {{row.description}}
        </td>
      </tr>
    </table>
    <v-dialog width="500" v-model="showDialog" persistent @keydown.esc="showDialog = false">
      <v-card>
        <v-container fluid>
          <v-textarea v-model="tmpRow.name" label="Название"></v-textarea>
          <v-text-field v-model="tmpRow.period"  label="Период"></v-text-field>
          <v-textarea v-model="tmpRow.description" label="Комментарий"></v-textarea>
         </v-container>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
            <v-btn color="primary" flat @click="saveDialog" > Сохранить </v-btn>
          </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>

  import * as moment from 'moment';
  import * as parser from 'cron-parser';
    export default {
      data() {
        return {
          showDialog: false,
          activeRow: {},
          tmpRow: {},
          onSave: false,
          headers: [
            'Название',
            'Периодичность',
            'Активность',
            'Последняя отработка',
            'Следующая отработка',
            'Комментарий'
          ],
          list: [],
        }
      },
      methods: {
        formatDate (time) {
          return moment(time).format('DD-MM-YYYY HH:mm ')
        },
        openDialog (row) {
          this.activeRow = row
          this.tmpRow = JSON.parse(JSON.stringify(row))
          this.showDialog = true
        },
        saveDialog() {
          for (let n in this.tmpRow) {
            this.activeRow[n] = this.tmpRow[n]
          }
          this.showDialog = false
          this.save()
        },
        getList () {
          axios.get('api/crons').then((res) => {
            console.log(res.data)
            this.list = res.data
          })
        },
        getNextCron (cronString, lastJob) {
          try {
            let opts = {
              currentDate: moment(lastJob),
            }
            var interval = parser.parseExpression(cronString, opts);
            return moment(interval.next().toDate()).format('DD-MM-YYYY HH:mm ')
          } catch (err) {
          }
        },
        save() {
          this.onSave = true
          axios.post('api/crons', {items: this.list} ).then((res) => {
            this.onSave = false
            console.log(this.onSave)
            console.log(res.data)
          })
        }
      },
      mounted() {
        this.getList()
      }
    }
</script>
<style scoped>
.cron-table {
  text-align: right;
}
.cron-table td,
.cron-table th
{
  text-align: center;
  padding: 3px 15px !important;
  height: auto !important;
  border: 1px solid lightgray !important;
}
.cron-table th {
  text-align: left;
}
.cron-table td input{
  display: block;
  border: 1px solid lightgray;
  border-radius: 3px;
  padding: 3px 10px;
  margin: auto;
}
.cron-table td .v-input{
  display: inline-block !important;
}

.cron-table tr{
  border: 1px solid lightgray !important;
}
.loader-overlay {
  left: -30px;
  right: 0;
  width: 100vw;
  height: 100%;
  z-index: 100;
  position: fixed;
  z-index: 10000;
}
.loader {
  width: 200px;
  height: 200px;
  position: fixed;
  left: calc(50vw - 100px);
  top: calc(50vh - 100px);
}
</style>
