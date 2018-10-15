
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Vuetify from 'vuetify'

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
  },
  getters: {
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
        }
      })
    }
  },
  actions: {
    massAction ({dispatch, commit}, data) {
      const ids = data.selected.map((el) => el = el.id)
      dispatch(data.fnName, ids)
    },
    massDelivered ({commit}, ids) {
      commit('setDelivered', ids)
      axios.get('api/mass/delivered', {params: {ids}}).then((res) => {
        console.log(res)
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
        commit('setSettings', res.data)
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
