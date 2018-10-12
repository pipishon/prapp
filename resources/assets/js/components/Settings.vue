<template>
  <div class="text-nowrap workout">
    <v-container>
      <v-layout row v-for="(label, name) in messageNames" :key="name">
          <v-flex xs12 md6 v-for="type in ['sms', 'email']" :key="type" class="px-5">
            <v-select
              :label="label + ' ' + type"
              :value="getTemplate(settings['template_' + name + '_' + type])"
              @input="selectTemplate(name + '_' + type, arguments[0])"
              :items="tplForSelect">
            </v-select>
          </v-flex>
      </v-layout>
      <v-layout row >
        <v-flex xs12 md6 class="px-5">
          <v-text-field :value="settings.private_card" @input="localSettings.private_card = arguments[0]" label="Номер карты Приват Банка" @blur="updateSetting('private_card')"></v-text-field>
        </v-flex>
      </v-layout>

    </v-container>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
    export default {
      data() {
        return {
          showDialog: false,
          localSettings: {},
          messageNames: {'requisites': 'Реквизиты', 'payed': 'Оплачено', 'ttn': 'ТТН'}
        }
      },
      computed: {
        ...mapGetters(['settings', 'tplForSelect', 'templates'])
      },
      methods: {
        ...mapActions(['updateSettings']),
        getTemplate (id) {
          return this.templates.filter( (el) => el.id == id )[0]
        },
        selectTemplate (type, template) {
          this.updateSettings({ name: 'template_' + type, value: template.id })
          this.$forceUpdate()
        },
        updateSetting (name) {
          this.updateSettings({ name, value: this.localSettings[name] })
        }
      },
      mounted() {
      }
    }
</script>
<style scoped>
</style>
