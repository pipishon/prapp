<template>
  <div>
    <btable :items="list" :fields="fields"  >

        <template slot="message" slot-scope="data">
          <a href="#" @click="autoreplyId = data.item.id" data-toggle="modal" data-target="#autoreplyModal">{{data.item.message}}</a>
        </template>

        <template slot="active" slot-scope="data">
          <input type="checkbox" v-model="data.item.active" @change="toggleActive($event, data.item.id)" class="form-controll"/>
        </template>

    </btable>
    <autoreply :id="autoreplyId" @updated="getList"/>
    <div class="footer"><button class="btn btn-primary" data-toggle="modal" @click="autoreplyId = ''" data-target="#autoreplyModal">Add new</button>
</div>
  </div>
</template>

<script>
    import autoreply from './Autoreply'
    export default {
      data() {
        return {
          fields: [
            { key: 'message', label: 'Message' },
            { key: 'from', label: 'From' },
            { key: 'to', label: 'To' },
            { key: 'active', label: 'Active' },
          ],
          list: [],
          autoreplyId: ''
        }
      },
      components: {
        autoreply
      },
      methods: {
        getList (params) {
          axios.get('api/autoreply').then((res) => {
            this.list = res.data
          })
        },
        toggleActive(event, id) {
          let active = event.target.checked
          axios.put('api/autoreply/' + id, {active}).then((res) => {
            console.log(res.data)
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
</style>
