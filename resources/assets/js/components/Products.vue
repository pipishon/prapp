<template>
  <div>
    <btable :items="list" :fields="fields" :search="['sku', 'name']" @search="onSearch"  >
      <template slot="sku" slot-scope="data">{{data.item.sku}}</template>
    </btable>

    <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
  </div>
</template>

<script>
    export default {
      data() {
        return {
          fields: [
            { key: 'sku', label: 'Sku' },
            { key: 'name', label: 'Name' },
            { key: 'price', label: 'Price' },
            { key: 'category', label: 'Status' },
            { key: 'description', label: 'Description' },
            { key: 'status', label: 'Category' }
          ],
          list: [],
          groups: [],
          curPage: 0,
          lastPage: 0,
          searchQuery: {}
        }
      },
      methods: {
        getList (params) {
          params = Object.assign(this.searchQuery, params)
          axios.get('api/products', {params}).then((res) => {
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
