
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Vuetify from 'vuetify'

import * as moment from 'moment';

Vue.use(Vuetify)

import Vuex from 'vuex'
Vue.use(Vuex)

const store = new Vuex.Store({
  state: {
    dictionary: {
      payment: {},
      delivery: {}
    },
    settings: {},
    statuses: {
      'pending': 'Новый',
      'received': 'Принят',
      'delivered': 'Выполнен',
      'canceled': 'Отменен',
    },
    selected: [],
    templates: [],
    orders: [],
    isMassBusy: false
  },
  getters: {
    isMassBusy: state => {
      return state.isMassBusy
    },
    selected: state => {
      return state.selected
    },
    settings: state => {
      return state.settings
    },
    tplForSelect: state => {
      let tpls = []
      state.templates.map( (a) => {tpls.push({text: a.name, value: a}) })
      return tpls
    },
    templates: state => {
      return state.templates
    }
  },
  mutations: {
    setMassBusy (state, val) {
      state.isMassBusy = val
    },
    massSelection (state, data) {
      state.selected = data
    },
    setSettings(state, data) {
      state.settings = data
    },
    setDictPayment(state, data) {
      state.dictionary.payment = data
    },
    setDictDelivery(state, data) {
      state.dictionary.delivery = data
    },
    setTemplates(state, data) {
      state.templates = data
    },
    updateOrder(state, data) {
      let order = state.orders.filter( order => order.id == data.id )[0]
      console.log(state.orders, order, data)
      for (var k in data){
        if (data.hasOwnProperty(k)) {
          order[k] = data[k]
        }
      }
    },
    updateSettings(state, data) {
      state.settings[data.name] = data.value
    },
    setOrders(state, data) {
      state.orders = data
    },
    setDelivered(state, ids) {
      state.orders.map((order) => {
        if (ids.indexOf(order.id) != -1) {
          order.status = 'delivered'
          order.statuses.delivered = moment().format('YYYY-MM-DD HH:mm')
        }
      })
    },
    setTtn(state, ttns) {
      state.orders.map((order) => {
        if (typeof(ttns[order.id]) != 'undefined') {
          order.ttn = ttns[order.id]
          order.statuses.ttn_string = ttns[order.id].int_doc_number
          console.log(order)
        }
      })
    },
    setSendTtn(state, ids) {
      state.orders.map((order) => {
        if (ids.phone.indexOf(order.id) != -1 || ids.email.indexOf(order.id) != -1) {
          order.statuses.ttn_status = 1
          if (ids.phone.indexOf(order.id) != -1) {
            order.statuses.ttn_phone = 1
          }
          if (ids.email.indexOf(order.id) != -1) {
            order.statuses.ttn_email = 1
          }
        }
      })
    }
  },
  actions: {
    massAction ({dispatch, commit}, data) {
      commit('setMassBusy', true)
      dispatch(data.fnName, data)
    },
    massTtn ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      console.log(ids)
      axios.get('api/mass/createttn', {params: {ids}}).then((res) => {
        let ttns = res.data
        commit('setTtn', ttns)
        commit('setMassBusy', false)
        console.log(res.data)
      })
    },
    massUpdateProduct ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      const name = data.name
      const value = data.value
      axios.get('api/product/massupdate', {params: {ids, name, value}}).then((res) => {
        commit('setMassBusy', false)
        console.log(res.data)
      })
    },
    massLabelAdd ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      const label_ids = data.items
      axios.get('api/product/addlabel', {params: {ids, label_ids}}).then((res) => {
        commit('setMassBusy', false)
        console.log(res.data)
      })
    },
    massLabelRemove ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      const label_ids = data.items
      axios.get('api/product/removelabel', {params: {ids, label_ids}}).then((res) => {
        commit('setMassBusy', false)
        console.log(res.data)
      })
    },
    massSuplierAdd ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      const suplier_ids = data.items
      axios.get('api/product/addsuplier', {params: {ids, suplier_ids}}).then((res) => {
        commit('setMassBusy', false)
        console.log(res.data)
      })
    },
    massSuplierRemove ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      const suplier_ids = data.items
      axios.get('api/product/removesuplier', {params: {ids, suplier_ids}}).then((res) => {
        commit('setMassBusy', false)
        console.log(res.data)
      })
    },
    massSendTtn ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      console.log(ids)
      axios.get('api/mass/sendttn', {params: {ids}}).then((res) => {
        let phone = res.data.phone
        let email = res.data.email
        commit('setSendTtn', {phone, email})
        commit('setMassBusy', false)
        console.log(res.data)
      })
    },
    massDelivered ({commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      commit('setDelivered', ids)
      console.log(ids)
      axios.get('api/mass/delivered', {params: {ids}}).then((res) => {
        console.log(res)
        commit('setMassBusy', false)
      })
    },
    loadDictionary ({commit}) {
      axios.get('api/dictionary', {params: {type: 'payment'}}).then((res) => {
        commit('setDictPayment', res.data)
      })
      axios.get('api/dictionary', {params: {type: 'delivery'}}).then((res) => {
        commit('setDictDelivery', res.data)
      })
    },
    loadSettings ({commit}) {
      axios.get('api/settings').then((res) => {
        const val = (Array.isArray(res.data)) ? {} : res.data
        commit('setSettings', val)
      })
    },
    loadTemplates ({commit}) {
      axios.get('api/messagetpl').then((res) => {
        commit('setTemplates', res.data)
      })
    },
    updateSettings ({commit, state}, data) {
      commit('updateSettings', data)
      axios.post('api/settings', state.settings).then((res) => {
        console.log(res.data)
      })
    }
  }
})

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
//import BootstrapVue from 'bootstrap-vue'
//import 'bootstrap-vue/dist/bootstrap-vue.css'

import * as components from './components/bootstrap'

for (let component in components) {
  Vue.component(component,  components[component])
}

import * as elements from './components/elements'

for (let element in elements) {
  Vue.component(element,  elements[element])
}
//Vue.use(BootstrapVue);

Vue.component('pagination', require('./components/Pagination.vue'));
Vue.component('search', require('./components/Search.vue'));

Vue.component('icon', require('vue-awesome/components/Icon'));

Vue.component('App', require('./App.vue'));

const app = new Vue({
    el: '#app',
    store: store,
    template: '<App/>'
});
