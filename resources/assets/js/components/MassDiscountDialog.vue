<template>
  <div>
    <div @click.stop="showDialog = true" style="cursor: pointer;"><slot></slot></div>
    <v-dialog  v-model="showDialog" width="300" persistent @keydown.esc="showDialog = false">
      <v-card v-if="showDialog">
        <v-container fluid>
          <v-select :items="discounts" v-model="discountId" item-text="name" item-value="id" label="Скидка">
          </v-select>
        </v-container>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="primary" flat @click.stop="showDialog = false" > Отмена </v-btn>
            <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapActions, mapGetters, mapMutations } from 'vuex'
    export default {
      props: ['selected'],
      data() {
        return {
          showDialog: false,
          discounts: [],
          discountId: 0,
        }
      },
watch: {
showDialog(val) {
console.log(val)
}
},
      methods: {
        ...mapActions(['massAction']),
        save() {
          this.massAction({
            fnName: 'massDiscount',
            selected: this.selected,
            discountId: this.discountId
          })
        },
        getDiscounts () {
          axios.get('api/discounts').then((res) => {
            this.discounts = res.data
          })
        }
      },
      mounted() {
        this.getDiscounts()
      }
    }
</script>
<style>
.collected {
  width: 10rem;
}
.collected  .table td
{
  border: none;
  padding: 2px 6px;
}
.collected  .table{
  background: none;
}
.blink {
  animation: blink 500ms infinite;  /* IE 10+, Fx 29+ */
}

@-webkit-keyframes blink {
  0%, 49% {
    background-color: #e8f5e9;
  }
  50%, 100% {
    background-color: #fafafa;
  }
}
</style>
