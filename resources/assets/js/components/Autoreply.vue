<template>
<bmodal id="autoreplyModal" title="Autoreply" @save="save">
  <div class="container-fluid">
    <div class="form-group">
      <label>Message</label>
      <textarea v-model="autoreply.message" class="form-control" ></textarea>
    </div>
    <div class="row">
      <div class="form-group col-6">
        <label>Date from</label>
        <datepicker v-model="autoreply.from" type="datetime" lang="ru" format="YYYY-MM-DD HH:mm:ss" :time-picker-options="{ start: '00:00', step: '00:30', end: '23:30' }"></datepicker>
      </div>
      <div class="form-group col-6">
        <label>Date to</label>
        <datepicker v-model="autoreply.to" type="datetime" lang="ru" format="YYYY-MM-DD HH:mm:ss" :time-picker-options="{ start: '00:00', step: '00:30', end: '23:30' }"></datepicker>
      </div>
    </div>
  </div>
</bmodal>
</template>

<script>
import datepicker from 'vue2-datepicker'
    export default {
      props: ['id'],
      data() {
        return {
          autoreply: {
            id: '',
            message: '',
            to: '',
            from: '',
            active: true
          },
          datetime: ''
        }
      },
      components: {
        datepicker
      },
      watch: {
        id (val) {
          if (val != '') {
            axios.get('/api/autoreply/' + val).then((res) => {
              this.autoreply.message = res.data.message
              this.autoreply.to = res.data.to
              this.autoreply.from = res.data.from
              this.autoreply.active = res.data.active
              this.autoreply.id = res.data.id
            })
          } else {
              this.autoreply.message = ''
              this.autoreply.to = ''
              this.autoreply.from = ''
              this.autoreply.active = true
          }
        }
      },
      methods: {
        save () {
          if (this.autoreply.id != '') {
            console.log(this.autoreply.id)
            axios.put('api/autoreply/' + this.autoreply.id, this.autoreply).then((res) => {
              console.log(res.data)
              this.$emit('updated')
            })
          } else {
            console.log(this.autoreply)
            axios.post('api/autoreply', this.autoreply).then((res) => {
              console.log(res.data)
              this.$emit('updated')
            })
          }
        }
      },
      mounted() {
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
