<template>
  <div>
    <bmodal id="messageModal" title="Message" @save="sendMessage" :savetext="sendButtonText">
      <div class="container-fluid">
        <div class="form-group row">
          <label class="col-2 col-form-label">От кого</label>
          <span class="form-control col-10" >
            <a v-if="typeof(customer) != 'undefined'" href="#" data-toggle="modal" data-target="#customerModal" >{{message.client_full_name}}</a>
            <span v-else>{{message.client_full_name}}</span>
          </span>
        </div>
        <div class="form-group row">
          <label class="col-2 col-form-label">Контакты</label>
          <span type="text" class="form-control col-10" id="name"><strong>{{message.phone}}</strong>
            <span v-if="typeof(customer) != 'undefined'">
              <span v-if="typeof(customer.phones) != 'undefined' && customer.phones.length > 0 && customer.phones[0].phone != message.phone">{{customer.phones[0].phone}}</span>
              <span v-if="typeof(customer.emails) != 'undefined' && customer.emails.length > 0">{{customer.emails[0].email}}</span>
            </span>
          </span>
        </div>
        <div class="form-group row">
          <label class="col-2 col-form-label">Тема</label>
          <span type="text" class="form-control col-10" id="name">{{message.subject}}</span>
        </div>
        <div class="form-group row">
          <label class="col-2 col-form-label">Сообщение</label>
          <span type="text" class="form-control col-10" id="name">{{message.message}}</span>
        </div>
        <div class="form-group row">
          <label >Сообщение</label>
          <textarea class="form-control" v-model="messageToSend"></textarea>
        </div>
        <div v-for="m in message.replies" >
          <strong>{{m.created_at}}</strong>
          <p>{{m.message}}</p>
        </div>
      </div>
    </bmodal>
  <customer :customer="customer" v-if="typeof(customer) != 'undefined'"/>
  </div>
</template>

<script>

    import customer from './Customer'
    export default {
      props: ['id'],
      data() {
        return {
          message: {},
          customer: {},
          messages: [],
          messageToSend: '',
          sendButtonText: 'Отправить сообщение',
          messageSent: false
        }
      },
      components: {
        customer
      },
      watch: {
        id (val) {
          this.message = {}
          this.customer = {}
          this.messageToSend = ''
          this.getMessage()
        }
      },
      methods: {
        getMessage () {
          axios.get('api/messages/' + this.id).then((res) => {
            this.message = res.data.message
            this.messages = res.data.messages
            this.customer = res.data.customer
            console.log(res.data)
          })
        },
        getCustomer (id) {
          axios.get('api/customers/' + id).then((res) => {
            this.customer = res.data
          })
        },
        sendMessage () {
          if (this.messageSent || typeof(this.message.id) == 'undefined') return
          let params = {
            id: this.message.prom_id,
            message: this.messageToSend
          }
          axios.get('api/messages/send', {params}).then((res) => {
            console.log(res.data)
            this.messageSent = true
            this.sendButtonText = 'Отправлено'
            this.getMessage()
          })
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
.modal-body {
  padding: 3rem;
}
.not-merged {
  color: gray;
}
</style>
