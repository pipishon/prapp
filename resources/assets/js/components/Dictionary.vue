<template>
  <div>
  <v-container fluid>
    <v-layout row wrap>
      <v-flex md12>
      <btable :items="list" :fields="fields" :notstriped="true" >
        <template slot="row" slot-scope="data">
          <tr v-for="(item, key) in list" :item="item" :key="key">
            <td>
              <v-textarea v-model="item.from" @blur="update(item)" rows="2" autogrow></v-textarea>
            </td>
            <td>
              <v-textarea v-model="item.to" @blur="update(item)" rows="2" autogrow></v-textarea>
            </td>
            <td>
              <v-checkbox :hide-details="true" v-model="item.delivery" label="Доставка" @change="update(item)" class="m-0" ></v-checkbox>
              <v-checkbox :hide-details="true" v-model="item.payment" label="Оплата" @change="update(item)" class="m-0"></v-checkbox>
            </td>
            <td>
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
    export default {
      data() {
        return {
          fields: [
            { key: 'from', label: 'Перевод с' },
            { key: 'to', label: 'Перевод на' },
            { key: 'delivery', label: '' },
            { key: 'action', label: '' },
          ],
          list: [],
        }
      },
      methods: {
        getList (params) {
          axios.get('api/dictionary').then((res) => {
            this.list = res.data
          })
        },
        update (item) {
          axios.put('api/dictionary/' + item.id, {...item}).then((res) => {
            this.getList()
          })
        },
        add () {
          axios.post('api/dictionary').then((res) => {
            this.getList()
          })
        },
        remove (item) {
          axios.delete('api/dictionary/' + item.id).then((res) => {
            this.getList()
          })
        },
      },
      computed: {
      },
      mounted() {
        this.getList()
      }
    }
</script>
<style scope>
</style>
