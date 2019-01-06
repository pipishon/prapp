<template>
  <div>
  <v-dialog  v-model="showDialog" fullscreen transition="dialog-bottom-transition" >
    <span slot="activator"><slot></slot></span>
    <v-card v-if="showDialog">
      <v-toolbar flat card dense fixed>
        <h3>Cоставной товар</h3>
        <v-toolbar-items>
          <v-btn flat @click.native="showDialog = false"><v-icon>close</v-icon></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-container fluid class="mt-5">
        <table class="table">
          <tr>
            <th style="width: 50px;">Id товара</th>
            <th style="width: 220px;">Наименование товара</th>
            <th style="width: 50px;">Коэффициент</th>
            <th style="width: 50px;"></th>
          <tr>
          <tr v-for="item in items">
            <td :class="{error: errors[item.item_promid]}"><v-text-field @keypress.enter="loadItem(item)" v-model="item.item_promid"></v-text-field></td>
            <td >{{item.item_name}}</td>
            <td ><v-text-field v-model="item.koef"></v-text-field></td>
            <td ><v-btn icon @click="removeItem(item)"><v-icon>delete</v-icon></v-btn></td>
          </tr>
        </table>
        <v-btn @click="addItem">Добавить товар</v-btn>
      </v-container>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="cancel" > Отмена </v-btn>
        <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
  </v-dialog>
  </div>
</template>
<script>
    export default {
      props: ['productpromid'],
      data() {
        return {
          showDialog: false,
          product: null,
          items: [],
          errors: {}
        }
      },
      watch: {
        showDialog(val) {
          if (val) {
            this.loadItems()
          }
        }
      },
      computed: {
      },
      methods: {
        loadItem (item) {
          if (item.item_pormid != '') {
            axios.get('api/products/' + item.item_promid).then((res) => {
              if (typeof(res.data.name) == 'undefined') {
                this.$set(this.errors, item.item_promid, true)
              } else {
                this.$set(this.errors, item.item_promid, false)
              }
              item.item_name = res.data.name
              item.item_id = res.data.id
              console.log(item, this.errors)
            })
          }
        },
        addItem () {
          this.items.push({
            product_id: this.product.id,
            product_name: this.product.name,
            product_promid: this.product.prom_id,
            item_id: '',
            item_promid: '',
            item_name: '',
            koef: 1
          })
        },
        removeItem(item) {
          this.items = this.items.filter((el) => el.item_id != item.item_id)
        },
        cancel () {
          this.$emit('update')
          this.showDialog = false
        },
        loadItems () {
          axios.get('api/pack/' + this.product.id).then((res) => {
            this.items = res.data || []
          })
        },
        save () {
          axios.put('api/pack/' + this.product.id, {items: this.items}).then((res) => {
            this.$emit('update')
            this.showDialog = false
          })
        }
      },
      mounted() {
        axios.get('api/products/' + this.productpromid).then((res) => {
          this.product = res.data
          this.loadItems()
        })
      }
    }
</script>
<style scoped>
.table {
  table-layout: fixed;
  position: relative;
}
.table td {
  vertical-align: middle;
}
</style>
