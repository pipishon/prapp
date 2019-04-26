<template>
  <div>
    <v-app light class="wrap" data-app>

      <leftbar class="hidden-sm-and-down" :imode="mode" @change="mode = arguments[0]"/>
      <div class="container-fluid">
        <products v-if="mode == 'products'"/>
        <orders v-if="mode == 'orders'"/>
        <customers v-if="mode == 'customers'"/>
        <messages v-if="mode == 'messages'"/>
        <autoreplies v-if="mode == 'autoreply'"/>
        <statistics v-if="mode == 'statistics'"/>
        <templates v-if="mode == 'templates'"/>
        <autoreceive v-if="mode == 'autoreceive'"/>
        <dictionary v-if="mode == 'dictionary'"/>
        <settings v-if="mode == 'settings'"/>
        <nptrack v-if="mode == 'nptrack'"/>
        <pivot v-if="mode == 'pivot'"/>
        <dashboard v-if="mode == 'dashboard'"/>
        <rfc v-if="mode == 'rfc'"/>
        <discount v-if="mode == 'discount'"/>
        <votes v-if="mode == 'votes'"/>
      </div>
    </v-app>
  </div>
</template>

<script>

import 'vuetify/dist/vuetify.min.css'
import leftbar from './components/Leftbar'
import products from './components/Products'
import messages from './components/Messages'
import autoreplies from './components/Autoreplies'
import orders from './components/Orders'
import customers from './components/Customers'
import statistics from './components/Statistics'
import templates from './components/MessageTemplates'
import autoreceive from './components/AutoReceive'
import dictionary from './components/Dictionary'
import settings from './components/Settings'
import nptrack from './components/NpTrack'
import pivot from './components/Pivot'
import dashboard from './components/Dashboard'
import discount from './components/Discounts'
import rfc from './components/Rfc'
import votes from './components/Votes'
    export default {
      data () {
        return {
          mode: 'orders'
        }
      },
      components: {
        templates,
        products,
        orders,
        customers,
        leftbar,
        messages,
        autoreplies,
        statistics,
        autoreceive,
        dictionary,
        settings,
        nptrack,
        pivot,
        dashboard,
        rfc,
        discount,
        votes
      },
      mounted() {
        //this.$store.dispatch('loadDictionary')
        this.$store.dispatch('loadSettings')
        this.$store.dispatch('loadTemplates')
      }
    }
</script>

<style scoped>
.wrap {
  padding-left: 2.1rem;
}
.container {
  margin-bottom: 3rem;
}

@media screen and (max-width: 600px) {
  .wrap {
    padding-left: 0;
  }
}

</style>
