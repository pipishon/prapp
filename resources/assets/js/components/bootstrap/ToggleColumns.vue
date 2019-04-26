<template>
  <v-dialog  v-model="showDialog"  width="300" >
    <v-btn class="ma-0" small slot="activator" icon><v-icon color="white">settings</v-icon></v-btn>
    <v-card v-if="showDialog">
      <v-container fluid class="mt-2">
        <div v-for="field in fields">
          <v-checkbox :label="field.label" v-model="field.enable"></v-checkbox>
        </div>
      </v-container>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" flat @click="save" > Сохранить </v-btn>
        <v-spacer></v-spacer>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex'
    export default {
      props: ['fields'],
      data() {
        return {
          showDialog: false
        }
      },
      computed: {
        ...mapGetters(['settings'])
      },
      methods: {
        ...mapActions(['updateSettings']),
        save() {
          this.updateSettings({
            name: 'product_table_cols',
            value: JSON.stringify(this.fields.map(({enable}) => enable))
          })
          this.showDialog = false
        }
      },
      mounted() {
      }
    }
</script>
<style scope>
</style>
