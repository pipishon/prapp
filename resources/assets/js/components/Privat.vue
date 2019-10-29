<template>
  <div class="hidden-sm-and-down ">
    <v-navigation-drawer
          :value="drawer"
          fixed
          right
          width="400"
class="privat-payment"
        >
		<v-toolbar flat>
      <v-list>
        <v-list-tile>
          <v-list-tile-action>
						<v-progress-circular indeterminate v-if="onLoad"  ></v-progress-circular>
						<v-btn @click="getList" icon v-else><v-icon>sync</v-icon></v-btn>
          </v-list-tile-action>
          <v-list-tile-title>
          </v-list-tile-title>
          <v-list-tile-action >
						<v-btn class="pull-right" @click="$emit('onClose')" icon><v-icon>close</v-icon></v-btn>
          </v-list-tile-action >
        </v-list-tile>
      </v-list>
    </v-toolbar>
<div class="wrap">

				<div v-for="payment in payments" :key="payment.id">
					<div :class="{'green lighten-5': payment.processed, 'grey lighten-4': !payment.processed}" class="pa-3 ma-3 payment" @click="payment.processed = !payment.processed; update(payment)">
                <div><strong>{{payment.amount}} грн</strong></div>
                <div>{{ customerName(payment.description) }}</div>
                <div>{{ payment.trandate }}</div>
					</div>
				</div>
</div>
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
        },
				update (payment) {
          axios.put('api/privat/' + payment.id, payment).then((res) => {
						this.getUnchecked()
						console.log(res.data)
          })
				},
				getUnchecked () {
          const params = {last_id: true}
          axios.get('api/privat', {params}).then((res) => {
						this.$emit('newRecords', res.data)
					})
				}
      },
      mounted() {
        this.getList()
				this.getUnchecked()
        setInterval(() => {
					// const maxPayment = this.payments.reduce((prev, cur) => 1*prev.id > 1*cur.id ? prev : cur)
					// const maxId = 1*maxPayment.id
					this.getUnchecked();
        }, 120000) 
      }
    }
</script>
<style scoped>
.payment { 
	border-radius: 15px;
	cursor: pointer;
width: 350px;
}
.wrap {
	height: 100%;
	overflow-y: scroll;
}
.privat-payment {
overflow: hidden;
}
</style>
