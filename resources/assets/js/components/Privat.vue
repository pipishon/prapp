<template>
  <div class="hidden-sm-and-down">
    <v-navigation-drawer
          :value="drawer"
          @input="$emit('onClose')"
          absolute
          temporary
          right
          width="400"
        >
          <v-list class="pa-1" subheader three-line >
            <v-list-tile >
              <v-list-tile-content >
								<v-progress-circular indeterminate v-if="onLoad" class="ml-5" ></v-progress-circular>
                <v-btn @click="getList" v-else>Обновить</v-btn>
              </v-list-tile-content>
            </v-list-tile>
            <v-list-tile v-for="payment in payments" :key="payment.id">
              <v-list-tile-content >
                <v-list-tile-title> {{payment.amount}} грн</v-list-tile-title>
                <v-list-tile-sub-title>{{ customerName(payment.description) }}</v-list-tile-sub-title>
                <v-list-tile-sub-title>{{ payment.trandate }}</v-list-tile-sub-title>
              </v-list-tile-content>
            </v-list-tile>
          </v-list>
      </v-navigation-drawer>
  </div>
</template>

<script>
import { mapActions, mapGetters, mapMutations } from 'vuex'
import * as moment from 'moment';
    export default {
      props: ['drawer'],
      data() {
        return {
          payments: [],
					onLoad: false
        }
      },
      watch: {
        drawer (val) {
          if (val) {
            this.getList()
          }
        }
      },
      computed: {
      },
      methods: {
        customerName (desc) {
          let Y = 'Отправитель:';
          return desc.slice(desc.indexOf(Y) + Y.length)
        },
        getList () {
					this.onLoad = true
          axios.get('api/privat').then((res) => {
            this.payments = res.data
						this.onLoad = false
          })
        }
      },
      mounted() {
        //this.getList()
      }
    }
</script>
<style scoped>
.modal-lg {
  max-width: 80vw;
}
.not-merged {
  color: gray;
}
textarea {
  height: 12rem;
}
</style>
