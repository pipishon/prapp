<template>
  <div class="p-2" >
    <v-btn @click="switchAutoSms" icon title="Автоприем новых, только поступивших в систему заказов"> <v-icon :color="(autoSmsOn == '1') ? 'primary' : ''">flash_auto</v-icon> </v-btn>
</div>
</template>

<script>

import { mapActions, mapGetters } from 'vuex'

    export default {
      props: ['item'],
      data() {
        return {
        }
      },
      computed: {
        ...mapGetters(['settings']),
        autoSmsOn () {
          return (typeof(this.settings.auto_receive_order) == 'undefined') ? '0' : this.settings.auto_receive_order
        }
      },
      methods: {
        ...mapActions(['updateSettings']),
        switchAutoSms() {
          let newVal = (this.settings.auto_receive_order == '0') ? '1' : '0'
          this.updateSettings({ name: 'auto_receive_order', value: newVal })
        },
        init () {
        },
      },
      mounted() {
        this.init()
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
