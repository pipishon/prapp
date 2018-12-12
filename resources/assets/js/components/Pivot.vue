<template>
  <div>
    <div style="width: 200px">
      <v-select label="Поставщик" v-model="suplier" @change="getProducts" :items="supliers" item-text="name" item-value="id"></v-select>
    </div>
    <template v-for="(products, category) in categories">
      <strong>{{category}}</strong>
      <table>
        <tr v-for="(product, index) in products">
          <td>
            {{index}}
          </td>
          <td>
            {{product.sku}}
          </td>
          <td>
            {{product.name}}
          </td>
          <td>
            {{product.sort2}}
          </td>
          <td>
            {{product.link}}
          </td>
          <td>
            {{product.avaiable}}
          </td>
          <td>
            {{product.purchase_price}}
          </td>
          <td>
            {{product.price}}
          </td>
          <td>
            <span v-if="product.margin">
              {{product.margin.toFixed(2)}}
            </span>
          </td>
          <td>
            {{product.quantity}}
          </td>
          <td>
          </td>
          <td>
          </td>
        </tr>
      </table>
    </template>
  </div>
</template>
<script>
    export default {
      data() {
        return {
          supliers: [],
          categories: [],
          suplier: null
        }
      },
      methods: {
        getSupliers () {
          axios.get('api/suplier').then((res) => {
            this.supliers = res.data
          })
        },
        getProducts () {
          const params = {suplier: this.suplier}
          axios.get('api/product/suplier', {params}).then((res) => {
            this.categories = res.data
          })
        }
      },
      mounted() {
        this.getSupliers()
      }
    }
</script>
<style scoped>
</style>
