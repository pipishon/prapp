<template>
  <div class="products">
    <btable
      :items="list"
      :fields="fields"
      :search="['sku', 'name']"
      @search="onSearch"
      :select-all="true"
    >
       <template slot="mass">
            <v-checkbox class="ma-0 pa-0" :input-value="selected.length" @change="massChange"></v-checkbox>
            <v-menu content-class="products" offset-y v-if="selected.length" class="ma-0 mass-menu" >
              <div  slot="activator" class="ma-0 mass-menu-activator"><strong>{{selected.length}} заказов &#8595;</strong></div>
              <v-list>
                <v-list-tile
                  v-for="(action, fnName) in {
                    massLabelAdd: 'Добавить метки',
                    massLabelRemove: 'Удалить метки',
                    massSuplierAdd: 'Добавить поставщиков',
                    massSuplierRemove: 'Удалить поставщиков'
                  }"
                  :key="fnName" @click.prevent.stop>
                  <div style="width: 100%;" @click="massSearch = ''; massSelectedItems=[]">
                    <v-menu content-class="products" offset-x full-width>
                        <div style="width: 100%; padding: 0 16px;" slot="activator">
                          <span  > {{ action }} </span>
                        </div>
                        <v-list>
                          <v-list-tile>
                            <input class="mass-search" v-model="massSearch" label="Поиск" @click.prevent.stop/>
                          </v-list-tile>
                          <v-list-tile
                            v-for="(item, index) in massActionItems(fnName)"
                            :key="index"
                            @click=""
                          >
                            <div @click.prevent.stop class="mx-3">
                              <v-checkbox :label="item.name" :value="item.id" v-model="massSelectedItems"></v-checkbox>
                            </div>
                          </v-list-tile>
                          <v-list-tile>
                          <v-btn @click="processMassAction(fnName)">{{action}} для {{selected.length}}</v-btn>
                          </v-list-tile>
                        </v-list>
                    </v-menu>
                  </div>
                </v-list-tile>
              </v-list>
            </v-menu>
              <v-icon v-if="selected.length && isMassBusy" class="mass-loader">hourglass_empty</v-icon>
       </template>
      <template slot="row" slot-scope="data">
        <tr v-for="(item, key) in data.items" :key="item.id" :class="{'green lighten-5': false}">

          <td>
            <v-checkbox flat class="mt-0" :value="selected.indexOf(item) != -1" @change="changeMass(arguments[0], item)"> </v-checkbox>
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
            <div v-for="item in item.supliers">{{item.name}}</div>
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
            <div v-for="item in item.labels">{{item.name}}</div>
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
          <v-list>
            <template v-for="(item, index) in supliers">
              <v-list-tile
                :key="item.id"
                avatar
                ripple
              >
                <v-list-tile-content>
                  <v-list-tile-title>{{ item.name }}</v-list-tile-title>
                </v-list-tile-content>

                <v-list-tile-action>
                  <v-btn icon @click="removeSuplier(item)"><v-icon color="red">delete</v-icon></v-btn>
                </v-list-tile-action>

              </v-list-tile>
              <v-divider
                v-if="index + 1 < supliers.length"
                :key="index"
              ></v-divider>
            </template>
          </v-list>
        </div>
    </v-navigation-drawer>
    <v-navigation-drawer
          v-model="ShowLabelDrawer"
          fixed
          temporary
          right
        >
        <v-toolbar flat>
          <h3>Метки</h3>
        </v-toolbar>
        <div class="pa-2">
          <v-text-field v-model="LabelToAdd" label="Добавить метку" append-icon="add_box" @click:append="addLabel"></v-text-field>
          <v-list>
            <template v-for="(item, index) in labels">
              <v-list-tile
                :key="item.id"
                avatar
                ripple

              >
                <v-list-tile-content>
                  <v-list-tile-title>{{ item.name }}</v-list-tile-title>
                </v-list-tile-content>

                <v-list-tile-action>
                  <v-btn icon @click="removeLabel(item)"><v-icon color="red">delete</v-icon></v-btn>
                </v-list-tile-action>

              </v-list-tile>
              <v-divider
                v-if="index + 1 < labels.length"
                :key="index"
              ></v-divider>
            </template>
          </v-list>
        </div>
    </v-navigation-drawer>
  </div>
</template>

<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex'
    export default {
      data() {
        return {
          massSelectedItems: [],
          massSearch: '',
          SuplierToAdd: null,
          ShowLabelDrawer: false,
          ShowSuplierDrawer: false,
          LabelToAdd: null,
          supliers: [],
          labels: [],
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
      watch: {
        isMassBusy (val) {
          if (!val) {
            this.getList()
            this.massSelection([])
          }
        }
      },
      computed: {
        ...mapGetters(['settings', 'selected', 'isMassBusy']),
      },
      methods: {
        ...mapMutations(['massSelection']),
        ...mapActions(['massAction']),
        processMassAction (action) {
          this.massAction({fnName: action, selected: this.selected, items: this.massSelectedItems})
        },
        massActionItems (action) {
          if (action.indexOf('Label') != -1) {
            return this.labels.filter((el) => el.name.indexOf(this.massSearch) != -1)
          } else {
            return this.supliers.filter((el) => el.name.indexOf(this.massSearch) != -1)
          }
        },
        massChange(val) {
          const massItems = (val) ? this.list : []
          this.massSelection(massItems)
        },
        addLabel () {
          axios.post('api/labelp', {name: this.LabelToAdd}).then((res) => {
            this.getLabels()
            console.log(res.data)
          });
        },
        removeLabel (item) {
          axios.delete('api/labelp/' + item.id).then((res) => {
            this.getLabels()
            console.log(res.data)
          });
        },
        addSuplier () {
          axios.post('api/suplier', {name: this.SuplierToAdd}).then((res) => {
            this.getSupliers()
            console.log(res.data)
          });
        },
        removeSuplier (item) {
          axios.delete('api/suplier/' + item.id).then((res) => {
            this.getSupliers()
            console.log(res.data)
          });
        },
        changeMass(val, item) {
          if (val) {
            const massItems = Object.assign([], this.selected)
            massItems.push(item)
            this.massSelection(massItems)
          } else {
            const massItems = this.selected.filter(el => el.id != item.id)
            this.massSelection(massItems)
          }
        },
        getLabels () {
          axios.get('api/labelp').then((res) => {
            this.labels = res.data
          })
        },
        getSupliers () {
          axios.get('api/suplier').then((res) => {
            this.supliers = res.data
          })
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
        this.getLabels()
        this.getSupliers()
        this.getList()
      }
    }
</script>
<style>
.products .v-list__tile {
  padding: 0;
}
.products .mass-search {
  border: 1px solid lightgray;
  width: 100%;
  margin: 0 12px;
  padding: 3px 6px;
}
</style>
