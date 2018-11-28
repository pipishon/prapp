<template>
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
            <input v-if="product.margin" :value="product.margin.toFixed(2)" readonly>
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
            <input  :value="product.orders_count" readonly>
          </v-flex>
          <v-flex offset-md4 md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Сорт 1</span>
          </v-flex>
          <v-flex md1 >
            <input  :value="0" readonly>
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
            <input  :value="0" readonly>
          </v-flex>
        </v-layout>
        <v-layout row class="px-4">
          <v-flex md2 class="text-right pr-4">
            <span class="subheading font-weight-medium" >Ссылка</span>
          </v-flex>
          <v-flex md5 >
            <div>
              <v-text-field style="width:80%; display: inline-block;" :hide-details="true" class="ma-0 pa-0" v-model="suplierLink" ></v-text-field><v-btn icon @click="addSuplierLink"><v-icon>add</v-icon></v-btn>
              <div v-for="item in product.suplierlinks">
                <v-text-field style="width:80%; display: inline-block;" :hide-details="true" class="ma-0 pa-0" v-model="item.link" append-icon="link" @click:append="goToLink(item.link)"></v-text-field><v-btn icon @click="item.link = ''; updateSuplierLink(item)"><v-icon>remove</v-icon></v-btn>
              </div>
            </div>
          </v-flex>
        </v-layout>
      </v-container>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="showDialog = false" > Отмена </v-btn>
        <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script>
    export default {
      props: ['product'],
      data() {
        return {
          showDialog: false,
          suplierLink: '',
        }
      },
      computed: {
      },
      methods: {
        goToLink (link) {
          const win = window.open(link, '_blank');
          win.focus();
        },
        updateSuplierLink (item) {
          const params = {id: this.product.id, link: item.link, link_id: item.id}
          axios.get('api/product/updatesuplierlink', {params}).then((res) => {
            console.log(res.data)
            if (item.link == '') {
              this.product.suplierlinks = this.product.suplierlinks.filter((el) => el.id != item.id)
            }
          })
        },
        addSuplierLink () {
          const params = {id: this.product.id, link: this.suplierLink}
          axios.get('api/product/addsuplierlink', {params}).then((res) => {
            console.log(res.data)
            this.product.suplierlinks.push(res.data)
            this.suplierLink = ''
          })
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
  border-radius: 5px;
  margin: 10px 0;
}
</style>
