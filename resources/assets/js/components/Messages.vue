<template>
  <div>
      <btable :items="list" :fields="fields" :search="['prom_id', 'phone']" @search="onSearch"  >
        <template slot="prom_id" slot-scope="data">
          <a data-toggle="modal" data-target="#messageModal" @click.prevent="curMessageId = data.item.id" href="#">{{data.item.prom_id}}</a>
        </template>
      </btable>
      <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
      <message :id="curMessageId" @updated="getList"/>
  </div>
</template>

<script>
    import message from './Message'

    export default {
      data() {
        return {
          list: [],
          curPage: 0,
          lastPage: 0,
          searchQuery: {},
          curMessageId: 0,
          fields: [
            { key: 'prom_id', label: 'Номер' },
            { key: 'prom_date_created', label: 'Дата' },
            { key: 'client_full_name', label: 'ФИО' },
            { key: 'phone', label: 'Телефон' },
            { key: 'subject', label: 'Тема' },
            { key: 'prom_status', label: 'Статус ПРОМ' },
            { key: 'crm_status', label: 'Статус CRM' },
            { key: '', label: 'Действие' },
          ]
        }
      },
      components: {
        message
      },
      methods: {
        getList (params) {
          params = Object.assign(this.searchQuery, params)
          axios.get('api/messages', {params}).then((res) => {
            this.list = res.data.data
            this.curPage = res.data.current_page
            this.lastPage = res.data.last_page
          })
        },
        loadPage(page) {
          this.getList({page})
        },
        onSearch (data) {
          let key = Object.keys(data)[0]
          this.searchQuery[key] = data[key]
          this.getList({page: 1})
        }
      },
      mounted() {
        this.getList()
      }
    }
</script>
