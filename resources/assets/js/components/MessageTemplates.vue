<template>
  <div>
  <v-container fluid>
    <v-layout row wrap>
      <v-flex md3 class="mr-5">
        <v-card flat class="p-3">
          <v-card-title primary class="title" >В шаблоне будет заменено:</v-card-title>
          <v-card-text >
            <div><strong>$id$</strong> - <span>Идентификатор заказа</span></div>
            <div><strong>$name$</strong> - <span>Имя фамилия клиента, указанные в заказе</span></div>
            <div><strong>$price$</strong> - <span>Сумма по заказу</span></div>
            <div><strong>$ttn$</strong> - <span>ТТН заказа</span></div>
            <div><strong>$private$</strong> - <span>Номер карты привата</span></div>
          </v-card-text>
        </v-card>
      </v-flex>
      <v-flex md6>
      <btable :items="list" :fields="fields" :notstriped="true" >
        <template slot="row" slot-scope="data">
          <tr v-for="(item, key) in list" :item="item" :key="key">
            <td>{{item.name}}</td>
            <td><v-textarea counter v-model="item.template" @blur="save(item)" ></v-textarea></td>
            <td><v-btn @click="remove(item)"  icon><v-icon color="error">cancel</v-icon></v-btn></td>
          </tr>
        </template>
      </btable>
      </v-flex>
    </v-layout>
  </v-container>
  <v-footer fixed class="pa-3">
      <v-spacer></v-spacer>
      <v-text-field v-model="newName" label="Добавить шаблон" append-icon="add" @click:append="add"></v-text-field>
      <v-spacer></v-spacer>
  </v-footer>
  </div>
</template>

<script>
import autosms from './AutoSms'
    export default {
      components: {
        autosms
      },
      data() {
        return {
          fields: [
            { key: 'name', label: 'Имя' },
            { key: 'template', label: 'Шаблон' },
            { key: 'action', label: '' },
          ],
          list: [],
          saved: false,
          newName: '',
        }
      },
      methods: {
        getList () {
          axios.get('api/messagetpl').then((res) => {
            this.list = res.data
          })
        },
        remove (item) {
          axios.delete('api/messagetpl/' + item.id).then((res) => {
            this.getList()
          })
        },
        add () {
          axios.post('api/messagetpl', {name: this.newName}).then((res) => {
            this.getList()
          })
        },
        save (item) {
          axios.put('api/messagetpl/' + item.id, item).then((res) => {
            this.getList()
          })
        }
      },
      mounted() {
        this.getList()
      }
    }
</script>
<style scope>
.footer {
  position: fixed;
  bottom: 0;
  width: 100%;
}

.blink {
  animation: blink 500ms infinite;  /* IE 10+, Fx 29+ */
}

@-webkit-keyframes blink {
  0%, 49% {
    background-color: #e8f5e9;
  }
  50%, 100% {
    background-color: white;
  }
}
</style>
