<template>
  <div class="products">
    <v-container fluid class="my-0 py-0">
        <v-layout row>
            <v-flex md3 >
          <v-select  :hide-details="true" label="Фильтр пользователей" :items="Object.keys(filterMap)" v-model="selectedFilter" @input="showAddFilterDialog = true" ></v-select>
            </v-flex>
              <v-chip v-model="filterChips[item.filter]" v-for="item in filters" :key="item.filter" close>{{item.filter}}
                <span v-if="item.from">&nbsp;от {{item.from}}</span>
                <span v-if="item.to">&nbsp;до {{item.to}}</span>
              </v-chip>
        </v-layout>
    </v-container>
    <btable
      :items="list"
      :fields="fields"
      :notstriped="true"
      :search="['sku', 'abc_earn', 'abc_qty', 'name', 'category', 'suplier']"
      @search="onSearch"
      :select-all="true"
      class="mb-5"
      :widths="tableWidths"
      @updatewidth="updateWidths"
    >
       <template slot="mass">
            <v-checkbox class="ma-0 pa-0" :input-value="selected.length" @change="massChange"></v-checkbox>
            <v-menu content-class="products" offset-y v-if="selected.length" class="ma-0 mass-menu" >
              <div  slot="activator" class="ma-0 mass-menu-activator"><strong>{{selected.length}} товаров &#8595;</strong></div>
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
                            :key="item.id"
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
                <v-list-tile
                    v-for="(label, name) in {
                    'purchase_price': 'Закупочная цена',
                    'sort1': 'Сортировка 1',
                    'sort2': 'Сортировка 2',
                    }"
                    :key="name"
                  >
                  <div
                    style="width: 100%; padding: 0 16px; cursor: pointer;"
                    @click="mass.name = name; mass.label = label; showMassDialog = true"
                    >
                    <span >{{label}}</span>
                    </div>
                </v-list-tile>
                <v-list-tile>
                  <v-list-tile-title >
                    <div style="width: 100%; padding: 0 16px; cursor: pointer;">
                      <massdiscount :selected="selected" @click.native="massMenu = false">Назначить скидку</massdiscount>
                    </div>
                  </v-list-tile-title>
                </v-list-tile>
                <v-list-tile>
                  <v-list-tile-title >
                    <div style="width: 100%; padding: 0 16px; cursor: pointer;">
                      <span @click="massAction({fnName: 'massRemoveDiscount', selected: selected})">Удалить скидку</span>
                    </div>
                  </v-list-tile-title>
                </v-list-tile>
              </v-list>
            </v-menu>
              <v-icon v-if="selected.length && isMassBusy" class="mass-loader">hourglass_empty</v-icon>
       </template>
      <template slot="row" slot-scope="data">
        <tr v-for="item  in data.items" :key="item.id" :class="{'pink lighten-5': item.on_sale}">

          <td >
            <v-checkbox flat class="mt-0" :value="selected.indexOf(item) != -1" @change="changeMass(arguments[0], item)"> </v-checkbox>
          </td>
          <td v-show="fields[0].enable == true">
            <img width="50" :src="item.main_image" />
          </td>
          <td v-show="fields[1].enable == true">
            <product @update="getList" :product="item">{{item.name}}</product>
          </td>
          <td v-show="fields[2].enable == true">
            {{item.sku}}
          </td>
          <td v-show="fields[3].enable == true">
            <div :class="{'red--text text--lighten-1': item.presence != 'available'}">
              <div>{{mapPresence[item.presence]}}</div>
              <div>{{item.quantity}}</div>
              <v-icon style="font-size: 18px;color: #616161;" v-if="item.status == 'on_display'">visibility</v-icon>
              <v-icon style="font-size: 18px;color: #E57373;" v-else>visibility_off</v-icon>
            </div>
          </td>
          <td v-show="fields[4].enable == true">
            {{item.category}}
          </td>
          <td v-show="fields[5].enable == true">
            <div v-for="item in item.supliers">{{item.name}}</div>
          </td>
          <td v-show="fields[6].enable == true">
          </td>
          <td v-show="fields[7].enable == true">
            <a href="#" v-if="item.morders" @click.prevent="showOrderStatistic(item)">
              {{item.morders.reduce((a, b) => {
                return {quantity: a.quantity + b.quantity}
                }, {quantity: 0}).quantity}}
            </a>
          </td>
          <td v-show="fields[8].enable == true">
            {{item.purchase_price}}
          </td>
          <td v-show="fields[9].enable == true">
            {{item.price}}
          </td>
          <td v-show="fields[10].enable == true">
            <span v-if="item.margin">{{item.margin.toFixed(2)}}</span>
          </td>
          <td v-show="fields[11].enable == true">
            <div v-for="item in item.labels">{{item.name}}</div>
          </td>
          <td v-show="fields[12].enable == true">
            {{item.sort1}}
          </td>
          <td v-show="fields[13].enable == true">
            {{item.sort2}}
          </td>
          <td v-show="fields[14].enable == true">
            {{item.abc_earn}}
          </td>
          <td v-show="fields[15].enable == true">
            {{item.abc_qty}}
          </td>
          <td v-show="fields[16].enable == true">
            <span v-if="item.discount">
              {{item.discount.name}}
            </span>
          </td>
        </tr>
      </template>
      <template slot="footer">
        <td colspan="2">Всего: {{stats.total}} шт ({{(stats.total * 100/stats.all).toFixed(2)}} %)</td>
          <td v-show="fields[1].enable == true"></td>
          <td v-show="fields[2].enable == true"></td>
          <td v-show="fields[3].enable == true"></td>
          <td v-show="fields[4].enable == true"></td>
        <td>{{(stats.supliers * 100/stats.all).toFixed(2)}} %</td>
          <td v-show="fields[6].enable == true"></td>
          <td v-show="fields[7].enable == true"></td>
        <td>{{((stats.all - stats.purchase_price) * 100/stats.all).toFixed(2)}} %</td>
      </template>
    </btable>

    <v-footer fixed class="pa-3" >
      <span>Всего товаров: {{stats.all}}  шт</span>
      <v-btn  flat @click="showAvailable" :class="{primary: footerAvailable}">B наличии</v-btn>
      <v-btn  flat @click="showNotAvailable" :class="{primary: footerNotAvailable}">Нет в наличии</v-btn>
      <v-btn  flat @click="showOnDisplay" :class="{primary: footerOnDisplay}">Опубликованные</v-btn>
      <v-btn  flat @click="showOnSale" :class="{primary: footerOnSale}">Выводим</v-btn>
      <v-spacer></v-spacer>
      <pagination :current="curPage" :last="lastPage" @change="loadPage"/>
      <v-btn @click="ShowSuplierDrawer = !ShowSuplierDrawer">Поставщики</v-btn>
      <v-btn @click="ShowLabelDrawer = !ShowLabelDrawer">Метки</v-btn>
      <v-btn @click="showImportDialog = true">Импорт</v-btn>

      <span style="width: 50px;">
        <v-select  v-model="perPage" :items="[30, 50, 100, 200]" @input="searchQuery['per_page'] = arguments[0]; getList()"></v-select>
      </span>
    </v-footer>

    <v-dialog  v-model="showImportDialog" width="300" persistent @keydown.esc="showImportDialog = false">
      <v-card class="pa-4">
        <input type="file" @change="imprt.file = arguments[0].target.files" />
        <v-btn @click="importProcess(0)">Импорт csv</v-btn>
        <v-btn @click="importFromApiProcess()">Импорт api</v-btn>
        <v-btn @click="calcABC()">Расчет ABC</v-btn>
        <v-progress-circular :size="60" :color="(imprt.done) ? 'green' : 'black'" :value="imprt.imported * 100 / imprt.total">
          {{imprt.imported}}
        </v-progress-circular>
      </v-card>
    </v-dialog>
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
                  <v-list-tile-title>{{ item.name }} ({{item.products_count}})</v-list-tile-title>
                </v-list-tile-content>

                <v-list-tile-action>
                  <v-btn icon @click="removeSuplier(item)"><v-icon color="red">delete</v-icon></v-btn>
                </v-list-tile-action>

              </v-list-tile>
              <v-divider
                v-if="index + 1 < supliers.length"
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
              ></v-divider>
            </template>
          </v-list>
        </div>
    </v-navigation-drawer>
    <v-dialog  v-model="showMassDialog" width="300" persistent @keydown.esc="showMassDialog = false">
      <v-card class="pa-4">
        <v-text-field v-model="mass.value" :label="mass.label"></v-text-field>
        <v-card-actions>
          <v-btn flat @click="processMass" > Установить для {{selected.length}} </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog  v-model="showAddFilterDialog" width="300" persistent @keydown.esc="showAddFilterDialog = false">

      <v-card v-if="showAddFilterDialog" >
          <v-card-title class="primary white--text"><h5>{{selectedFilter}}</h5></v-card-title>
          <div class="px-3">
            <v-text-field label="От" v-model="filterFrom"></v-text-field>
            <v-text-field label="До" v-model="filterTo"></v-text-field>
          </div>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click="showAddFilterDialog = false" > Отмена </v-btn>
            <v-btn color="primary" flat @click="setFilter" > Установить </v-btn>
          </v-card-actions>
      </v-card>
    </v-dialog>
  <v-dialog width="1100" v-model="showDialogStatistics">

    <v-card>
      <div style="width: 1100px; height: 700px;">
        <barchart
          :width="1100"
          :height="700"
          :chart-data="chartData"
          :options="{responsive: true}"
        ></barchart>
      </div>
    </v-card>
  </v-dialog>
  </div>
</template>

<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex'
    import product from './Product'
    import massdiscount from './MassDiscountDialog'
    export default {
      components: {
        product,
        massdiscount
      },
      data() {
        return {
          chartData: null,
          showDialogStatistics: false,
          showAddFilterDialog: false,
          selectedFilter: null,
          filterFrom: null,
          filterTo: null,
          filters: [],
          filterChips: {},
          filterMap: {
             'Закуп цена': 'purchase_price',
             'Маржа': 'margin',
          },
          imprt: {file: null, imported: 0, total: 1, done: false},
          mass: {label: '', name: '', value: ''},
          footerAvailable: false,
          footerNotAvailable: false,
          footerOnDisplay: true,
          footerOnSale: false,
          mapStatus: {
            'on_display': 'Опубликован',
            'draft': '',
            'deleted': 'Удален',
            'not_on_display': 'Не опубликован',
            'editing_required': 'Необходимо редактирование',
            'approval_pending': 'Ожидается подтверждение',
            'deleted_by_moderator': 'Удален модератором'
          },
          mapPresence: {
            'order': 'Заказ',
            'service': 'Сервис',
            'not_available': 'Нет в наличии',
            'available': 'В наличии'
          },
          purchasePrice: 0,
          showImportDialog: false,
          showMassDialog: false,
          stats: {},
          tableWidths: {},
          perPage: 30,
          massSelectedItems: [],
          massSearch: '',
          SuplierToAdd: null,
          ShowLabelDrawer: false,
          ShowSuplierDrawer: false,
          LabelToAdd: null,
          supliers: [],
          labels: [],
          fields: [
            { key: 'main_image', label: 'Фото', enable: true },
            { key: 'name', label: 'Название', enable: true },
            { key: 'sku', label: 'Артикул', enable: true },
            { key: 'availability', label: 'Наличие', enable: true },
            { key: 'category', label: 'Группа', enable: true },
            { key: 'suplier', label: 'Поставщик', enable: true },
            { key: 'min_quantity', label: 'Мин. остаток', enable: true },
            { key: 'orders', label: 'Заказы', enable: true },
            { key: 'purchase_price', label: 'Закуп цена', enable: true },
            { key: 'price', label: 'Цена продажи', enable: true },
            { key: 'marga', label: 'Маржа', enable: true },
            { key: 'label', label: 'Метки товара', enable: true },
            { key: 'sort1', label: 'Сорт1', enable: true },
            { key: 'sort2', label: 'Сорт2', enable: true },
            { key: 'abc_earn', label: 'ABC приб', enable: true },
            { key: 'abc_qty', label: 'ABC кол', enable: true },
            { key: 'discount', label: 'Скидка', enable: true },
          ],
          list: [],
          groups: [],
          curPage: 0,
          lastPage: 0,
          searchQuery: {}
        }
      },
      watch: {
        filterChips: {
          handler: 'sendFilter',
          deep: true
        },
        isMassBusy (val) {
          if (!val) {
            this.getList()
            this.massSelection([])
          }
        },
        settings: {
          handler () {
            this.tableWidths = (typeof(this.settings.product_table_widths) != 'undefined') ? JSON.parse(this.settings.product_table_widths) : {}
            if (typeof(this.settings.product_table_cols) != 'undefined') {
              JSON.parse(this.settings.product_table_cols).map((val, idx) => {
                this.fields[idx].enable = val
              })
            }
          },
          deep: true
        }
      },
      computed: {
        ...mapGetters(['settings', 'selected', 'isMassBusy']),
      },
      methods: {
        ...mapMutations(['massSelection']),
        ...mapActions(['massAction', 'updateSettings']),
        showOrderStatistic (product) {
          this.showDialogStatistics = true
          axios.get('api/product/ordermonth/' + product.id).then((res) => {
            let labels = []
            let datasets = [
                {
                  label: 'Заказано Товаров',
                  data: [],
                  backgroundColor: 'red',
                  borderColor: 'red',
                  fill: false,
                },
              ]

            res.data.map((row) => {
              labels.push(row.month + '.' + row.year)
              datasets.map((dataset) => {
                  dataset.data.push(row.qty)
              })
            })

            this.chartData = {labels, datasets}
            console.log(this.chartData)
          })
        },
        setFilter () {
          let oldFilter = this.filters.filter( el => el.filter == this.selectedFilter )[0]
          if (typeof(oldFilter) != 'undefined') {
            oldFilter.from = this.filterFrom
            oldFilter.to = this.filterTo
            this.filterChips[oldFilter.filter] = true
          } else {
            this.filters.push({
              filter: this.selectedFilter,
              from: this.filterFrom,
              to: this.filterTo
            })
          }
          this.filterFrom = null
          this.filterTo = null
          this.sendFilter()
          this.showAddFilterDialog = false
        },
        sendFilter () {
          let toFilter = []
          for (let f of this.filters) {
            if (typeof(this.filterChips[f.filter]) == 'undefined' || this.filterChips[f.filter]) {
              const m = {
                to: f.to,
                from: f.from,
                name: this.filterMap[f.filter]
              }
              toFilter.push(m)
            }
          }
          this.getList({filter: toFilter})
        },
        calcABC () {
          axios.get('api/product/calcabc').then((res) => {
            console.log(res.data)
          })
        },
        importFromApiProcess (lastId) {
          const params = {}
          if (typeof(lastId) != 'undefined') {
            params['last_id'] = lastId
          } else {
            this.imprt.imported = 0
            this.imprt.done = false
          }
          axios.get('api/product/importfromapi', {params}).then((res) => {
            console.log(res.data)
            this.imprt.total = res.data.total
            this.imprt.imported += res.data.imported
            if (typeof(res.data.last_id) != 'undefined') {
              this.importFromApiProcess(res.data.last_id)
            } else {
              this.imprt.done = true
              this.getList()
            }
          })
        },
        importProcess (startRow) {
          if (startRow == 0) {
            const file = this.imprt.file[0]
            const formData = new FormData();
            formData.append('importfile', file)
            this.imprt.imported = 0
            this.imprt.done = false
            axios({
                method: 'post',
                url: 'api/product/import',
                data: formData,
                config: { headers: {'Content-Type': 'multipart/form-data' }}
            }).then((res) => {
              if (res.data.imported != 0) {
                this.imprt.imported += res.data.imported
                this.imprt.total += res.data.total
                this.importProcess(res.data.last_row)
              } else {
                this.imprt.done = true
                this.getList()
              }
            })
          } else {
            axios.post('api/product/import', {start_row: startRow}).then((res) => {
              if (res.data.imported != 0) {
                this.imprt.imported += res.data.imported
                this.importProcess(res.data.last_row)
                console.log(res.data)
              } else {
                this.imprt.done = true
                this.getList()
              }
            })
          }
        },
        showNotAvailable () {
          this.footerNotAvailable  = !this.footerNotAvailable
          this.footerAvailable  = !this.footerNotAvailable
          this.searchQuery.not_available = this.footerNotAvailable
          this.searchQuery.available = !this.footerNotAvailable
          this.getList()
        },
        showAvailable () {
          this.footerAvailable  = !this.footerAvailable
          this.footerNotAvailable  = !this.footerAvailable
          this.searchQuery.available = this.footerAvailable
          this.searchQuery.not_available = !this.footerAvailable
          this.getList()
        },
        showOnDisplay () {
          this.footerOnDisplay  = !this.footerOnDisplay
          this.searchQuery.on_display = this.footerOnDisplay
          this.getList()
        },
        showOnSale () {
          this.footerOnSale  = !this.footerOnSale
          this.searchQuery.on_sale = this.footerOnSale
          this.getList()
        },
        processMass () {
          this.massAction({
            fnName: 'massUpdateProduct',
            selected: this.selected,
            name: this.mass.name,
            value: this.mass.value,
          })
          this.mass.label = ''
          this.mass.value = ''
          this.mass.name = ''
          this.showMassDialog = false
        },
        updateWidths () {
          this.updateSettings({name: 'product_table_widths', value: JSON.stringify(this.tableWidths)})
        },
        processMassAction (action) {
          this.massAction({fnName: action, selected: this.selected, items: this.massSelectedItems})
        },
        massActionItems (action) {
          if (action.indexOf('Label') != -1) {
            return this.labels.filter((el) => el.name.toLowerCase().indexOf(this.massSearch) != -1)
          } else {
            return this.supliers.filter((el) => el.name.toLowerCase().indexOf(this.massSearch) != -1)
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
            console.log(res.data)
            this.stats = res.data.stats
            this.curPage = res.data.current_page
            this.lastPage = res.data.last_page
            this.massSelection([])
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

        this.searchQuery.on_display = true
        this.tableWidths = (typeof(this.settings.product_table_widths) != 'undefined') ? JSON.parse(this.settings.product_table_widths) : {}
        if (typeof(this.settings.product_table_cols) != 'undefined') {
          JSON.parse(this.settings.product_table_cols).map((val, idx) => {
            this.fields[idx].enable = val
          })
        }
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
