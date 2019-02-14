<template>
  <div>
    <v-list v-if="groupstep == 0">
      <div
        v-for="item in groups"
        @click="getByGroup(item.category)"
        class="pa-2"
        style="border-bottom: 1px solid #F5F5F5;"
      >
        {{item.category}}
      </div>
    </v-list>
    <v-list v-if="groupstep == 1">
      <v-list-tile
        v-for="item in products"
        :key="item.name"
        @click="getByGroup(item.category)"
        >
        <v-list-tile-content>
          <span style="font-size: 12px;"> {{item.name}}</span>
        </v-list-tile-content>
        <v-list-tile-avatar>
          <span style="font-size: 12px;">
            <span>{{item.sum}}</span>/<span class="grey--text">{{item.qty}}</span>
          </span>
        </v-list-tile-avatar>
      </v-list-tile>
    </v-list>
    <v-footer fixed v-if="groupstep == 0">
      <v-layout row>
          <v-flex xs6 sm6 >
            <v-btn flat style="width: 100%;" :class="{primary: orders == 'payed'}" @click="orders = 'payed'; getOrderProductGroups()">Оплаченные</v-btn>
          </v-flex>
          <v-flex xs6 sm6 >
            <v-btn flat style="width: 100%;" :class="{primary: orders == 'all'}" @click="orders = 'all'; getOrderProductGroups()">Все</v-btn>
          </v-flex>
      </v-layout>
    </v-footer>
  </div>
</template>

<script>
    export default {
      props: ['groupstep'],
      data() {
        return {
          groups: null,
          products: null,
          orders: 'payed'
        }
      },
      methods: {
        getOrderProductGroups () {
          const params = {
            orders: this.orders
          }
          axios.get('api/orders/getgroups', params).then((res) => {
            this.groups = res.data
          })
        },
        getByGroup (group) {
          const params = {
            orders: this.orders,
            group: group
          }
          axios.get('api/orders/getbygroup', {params}).then((res) => {
            this.$emit('changegroupstep')
            this.products = res.data
          })
        }
      },
      mounted() {
        this.getOrderProductGroups()
      }
    }
</script>
<style>
</style>
