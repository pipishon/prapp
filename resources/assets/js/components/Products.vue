<template>
  <div>
    <btable
      :items="list"
      :fields="fields"
      :search="['sku', 'name']"
      @search="onSearch"
      :select-all="true"
    >
      <template slot="row" slot-scope="data">
        <tr v-for="(item, key) in data.items" :key="item.id" :class="{'green lighten-5': false}">

          <td>
            <v-checkbox flat class="mt-0" :value="selected.indexOf(data.item) != -1" @change="changeMass"> </v-checkbox>
          </td>
          <td>
            <img width="100" height="100" :src="item.main_image" />
          </td>
          <td>
            {{item.name}}
          </td>
          <td>
            {{item.sku}}
          </td>
          <td>
          </td>
          <td>
            {{item.category}}
          </td>
          <td>
            {{item.units}}
          </td>
          <td>
            {{item.supliers}}
          </td>
          <td>
          </td>
          <td>
          </td>
          <td>
          </td>
          <td>
            {{item.price}}
          </td>
          <td>
          </td>
          <td>
            {{item.labels}}
          </td>
        </tr>
      </template>
    </btable>

    <v-footer fixed class="pa-3" >
      <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
      <v-btn @click="ShowSuplierDrawer = !ShowSuplierDrawer">Поставщики</v-btn>
      <v-btn @click="ShowLabelDrawer = !ShowLabelDrawer">Метки</v-btn>
    </v-footer>
    <v-navigation-drawer
          v-model="ShowSuplierDrawer"
          fixed
          temporary
          right
        >
        <v-toolbar flat>
          <h3>Поставщики</h3>
        </v-toolbar>
        <div class="pa-2">
          <v-text-field v-model="SuplierToAdd" label="Добавить поставщика" append-icon="add_box" @click:append="addSuplier"></v-text-field>
          {{this.supliers}}
        </div>
    </v-navigation-drawer>
  </div>
</template>

<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex'
    export default {
      data() {
        return {
          SuplierToAdd: null,
          ShowLabelDrawer: false,
          ShowSuplierDrawer: false,
          supliers: [],
          fields: [
            { key: 'main_image', label: 'Фото' },
            { key: 'name', label: 'Название' },
            { key: 'sku', label: 'Артикул' },
            { key: 'availability', label: 'Наличие' },
            { key: 'category', label: 'Группа' },
            { key: 'units', label: 'Ед. изм.' },
            { key: 'suplier', label: 'Поставщик' },
            { key: 'min_quantity', label: 'Мин. остаток' },
            { key: 'orders', label: 'Заказы' },
            { key: 'purchase_price', label: 'Закуп цена' },
            { key: 'price', label: 'Цена продажи' },
            { key: 'marga', label: 'Маржа' },
            { key: 'label', label: 'Метки товара' },
          ],
          list: [],
          groups: [],
          curPage: 0,
          lastPage: 0,
          searchQuery: {}
        }
      },
      computed: {
        ...mapGetters(['settings', 'selected']),
      },
      methods: {
        ...mapMutations(['massSelection']),
        addSuplier () {
          axios.post('api/suplier', {name: this.SuplierToAdd}).then((res) => {
            console.log(res.data)
          });
        },
        changeMass(val) {
          if (val) {
            const massItems = Object.assign([], this.selected)
            massItems.push(this.item)
            this.massSelection(massItems)
          } else {
            const massItems = this.selected.filter(el => el.id != this.item.id)
            this.massSelection(massItems)
          }
        },
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
        axios.get('api/suplier').then((res) => {
          this.supliers = res.data
        })
        this.getList()
      }
    }
</script>
