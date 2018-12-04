<template>
  <div>
  <v-dialog  v-model="showDialog" fullscreen transition="dialog-bottom-transition" >
    <a href="#" slot="activator"><slot></slot></a>
    <v-card v-if="showDialog">
      <v-toolbar flat card dense fixed>
        <h3>Редактирование товара</h3>
        <v-spacer></v-spacer>
        <v-toolbar-items>
          <v-btn flat @click.native="showDialog = false"><v-icon>close</v-icon></v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-container fluid class="mt-5">
        <v-layout row align-center class="px-4">
          <v-flex xs12 md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Наименование</span>
          </v-flex>
          <v-flex xs12 md9 >
            <input :value="product.name" readonly append-icon="open_in_new">
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Aртикул</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.sku" readonly>
          </v-flex>
          <v-flex offset-md6 md-2>
            <div class="ttl" style="padding-top: 0; padding-bottom: 5px;">Изображение</div>
            <img style="position: absolute" width="130" :src="product.main_image" />
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >ID товара</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.prom_id" readonly>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Группа</span>
          </v-flex>
          <v-flex md5 >
            <input  :value="product.category" readonly>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Ед. измерения</span>
          </v-flex>
          <v-flex md1 >
            <input :value="product.units" readonly>
          </v-flex>
          <v-flex offset-md6 md-3>
            <div class="ttl" style="padding-bottom: 0; padding-top: 30px;">Наличие</div>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Мин. остаток</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.min_quantity" readonly>
          </v-flex>
          <v-flex md2 offset-md4  class="text-right pr-4">
            <span class="subheading font-weight-medium" >Остаток</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.quantity" readonly>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Поставщик</span>
          </v-flex>
          <v-flex md4 >
            <input  :value="0" readonly>
          </v-flex>
          <v-flex md2 offset-md1  class="text-right pr-4">
            <span class="subheading font-weight-medium" >Статус</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.status" readonly>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 offset-md7  class="text-right pr-4">
            <span class="subheading font-weight-medium" >Наличие</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.presence" readonly>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <div class="ttl" >Цены</div>
          </v-flex>
          <v-flex offset-md7 md-3>
            <div class="ttl" >Метки</div>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Закуп. цена</span>
          </v-flex>
          <v-flex md1 >
            <input v-model="product.purchase_price">
          </v-flex>
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Маржа</span>
          </v-flex>
          <v-flex md1 >
            <input :value="(product.margin != null) ? product.margin.toFixed(2) : ''" readonly>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Цена продажи</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.price" readonly>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <div class="ttl" >Продажи</div>
          </v-flex>
          <v-flex offset-md7 md-3>
            <div class="ttl" >Сортировка</div>
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Заказы, всего</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="product.orders" readonly>
          </v-flex>
          <v-flex md1 >
            <v-btn @click="showOrderStatistic" icon flat><v-icon>bar_chart</v-icon></v-btn>
          </v-flex>
          <v-flex offset-md3 md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Сорт 1</span>
          </v-flex>
          <v-flex md1 >
            <input  v-model="product.sort1">
          </v-flex>
        </v-layout>
        <v-layout row align-center class="px-4">
          <v-flex md2 class="text-right pr-4">
            <div class="ttl" >URL поставщика</div>
          </v-flex>
          <v-flex offset-md5 md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Сорт 2</span>
          </v-flex>
          <v-flex md1 >
            <input  v-model="product.sort2">
          </v-flex>
        </v-layout>
        <v-layout row class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Ссылка</span>
          </v-flex>
          <v-flex md5 >
            <div>
              <div v-for="(item, index) in product.suplierlinks">
                <input style="width:80%; display: inline-block;" v-model="item.link" />
                <span v-if="item.link != ''">
                  <v-btn icon class="ma-0 pa-0">
                    <v-icon @click="goToLink(item.link)">link</v-icon>
                  </v-btn>
                  <v-btn v-if="index > 0" class="ma-0 pa-0" icon @click="deleteSuplierLink(item)">
                    <v-icon>delete</v-icon>
                  </v-btn>
                </span>
              </div>
              <div>
              <v-btn class="ma-0 pa-0" @click="addSuplierLink" icon ><v-icon class="grey--text" >add_box</v-icon></v-btn>
              </div>
            </div>
          </v-flex>
        </v-layout>
      </v-container>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="cancel" > Отмена </v-btn>
        <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-dialog width="600" v-model="showDialogStatistics">

    <v-card>
      <div style="width: 600px; height: 600px;">
        <linechart
          :chart-data="chartData"
          :options="{responsive: true}"
        ></linechart>
      </div>
    </v-card>
  </v-dialog>
  </div>
</template>
<script>
    export default {
      props: ['product'],
      data() {
        return {
          chartData: null,
          showDialogStatistics: false,
          showDialog: false,
          suplierLink: '',
        }
      },
      watch: {
        product: {
          handler (val) {
            if (this.product.suplierlinks.length == 0) {
              this.addSuplierLink()
            }
            this.product.margin = (this.product.purchase_price) ? (this.product.price - this.product.purchase_price) * 100 / this.product.price : null
          },
          deep: true
        },
        showDialog(val) {
          if (val) {
            if (this.product.suplierlinks.length == 0) {
              this.addSuplierLink()
            }
          }
        }
      },
      computed: {
      },
      methods: {
        showOrderStatistic () {
          this.showDialogStatistics = true
          axios.get('api/product/ordermonth/' + this.product.id).then((res) => {
            let labels = []
            let datasets = [
                {
                  label: 'Заказано Товаров',
                  data: [],
                  backgroundColor: 'red',
                  borderColor: 'red',
                  fill: false,
                  pointRadius: 0
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
        goToLink (link) {
          const win = window.open(link, '_blank');
          win.focus();
        },
        deleteSuplierLink (item) {
          const index = this.product.suplierlinks.indexOf(item)
          this.product.suplierlinks.splice(index, 1)// = this.product.suplierlinks.filter((el) => el.id != item.id)
        },
        addSuplierLink () {
          const params = {product_id: this.product.id, link: this.suplierLink}
          this.product.suplierlinks.push(params)
        },
        cancel () {
          this.$emit('update')
          this.showDialog = false
        },
        save () {
          axios.put('api/products/' + this.product.id, {...this.product}).then((res) => {
            this.$emit('update')
            this.product.magrin = res.data.margin
            this.showDialog = false
          })
        }
      },
      mounted() {
      }
    }
</script>
<style scoped>
.ttl {
  font-size: 16px;
  padding-top: 25px;
  color: red;
}
input {
  width: 100%;
  border: 1px solid lightgray;
  padding: 3px 10px;
  border-radius: 0.25rem;
  margin: 10px 0;
}
</style>
