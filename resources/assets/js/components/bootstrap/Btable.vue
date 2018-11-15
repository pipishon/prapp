<template>
  <div>
  <table class="table table-bordered" :class="{'table-striped' : !notstriped, 'fixed' : typeof(widths) != 'undefined'}" >
      <thead class="thead-dark">
        <th v-if="rownumber"></th>
        <th v-if="selectAll" style="width: 40px"></th>
        <th v-for="(field, key) in fields" :class="field.th_class" @click="$set(showWidthAdj, key, true)" :style="widthStyle(key)">
          <span v-if="typeof(widths) == 'undefined' || !showWidthAdj[key]">
            {{field.label}}
          </span>
          <v-text-field v-model="widths[key]" v-if="typeof(widths) != 'undefined' && showWidthAdj[key]" dark @keyup.enter="showWidthAdj[key] = false; $emit('updatewidth')" class="ma-0 pa-0"></v-text-field>
        </th>
      </thead>
      <tfoot>
        <tr>
          <slot name="footer"></slot>
        </tr>
      </tfoot>
      <tbody>
        <tr v-if="typeof(search) != 'undefined'" >
          <td v-if="selectAll" style="position: relative; padding: 15px 0px 0px 7px;">
            <v-checkbox class="ma-0 pa-0" :input-value="this.selected.length" @change="massChange"></v-checkbox>
            <v-menu offset-y v-if="selected.length" class="ma-0 mass-menu" >
              <div  slot="activator" class="ma-0 mass-menu-activator"><strong>{{selected.length}} заказов &#8595;</strong></div>
              <v-list>
                <v-list-tile v-for="(item, fnName) in {massTtn: 'Сформировать ТТН', massSendTtn: 'Разослать ТТН', massDelivered: 'Установить Выполнен'}" :key="fnName" @click="massAction({fnName, selected})" >
                  <v-list-tile-title >{{ item }}</v-list-tile-title>
                </v-list-tile>
              </v-list>
            </v-menu>
              <v-icon v-if="selected.length && isMassBusy" class="mass-loader">hourglass_empty</v-icon>
          </td>

          <td v-for="field in fields" >
            <input v-if="search.indexOf(field.key) != -1" type="text" class="form-control" placeholder="Search" @keyup.enter="onChange($event, field.key)"/>
          </td>
        </tr>
          <slot name="row" :items="items">
        <tr v-for="(item, key) in items" :key="item.id" :class="{'green lighten-5': item.status == 'delivered' && orders}">
          <td v-if="selectAll"><v-checkbox></v-checkbox></td>
          <td v-if="rownumber">{{key + 1}}</td>
          <td :class="field.td_class"  v-for="field in fields" @click="$emit('tdclick', {key: field.key, item})"><slot :name="field.key" :item="item">{{item[field.key]}}</slot></td>
        </tr>
        </slot>
      </tbody>
    </table>
  </div>
</template>

<script>
    import { mapActions, mapMutations, mapGetters } from 'vuex'
    export default {
      props: ['items', 'fields', 'search', 'notstriped', 'rownumber', 'orders', 'widths', 'selectAll'],
      data() {
        return {
          showWidthAdj: {}
        }
      },
      computed: {
        ...mapGetters(['selected', 'isMassBusy'])
      },
      methods: {
        ...mapMutations(['massSelection']),
        ...mapActions(['massAction']),
        massChange(val) {
          const massItems = (val) ? this.items : []
          this.massSelection(massItems)
        },
        widthStyle (key) {
          if (typeof(this.widths) == 'undefined') return {}
          const width = (this.widths[key] < 40) ? 40 : this.widths[key]
          return {width: width + 'px'}
        },
        onChange (event, key) {
          let data = {}
          data[key] = event.target.value
          this.$emit('search', data);
        }
      },
      mounted() {
      }
    }
</script>
<style scope>
.loader-wrap td {
    padding: 0px;
    position: absolute;
    width: 100%;
    height: 7px;
    border: none;
}
.fixed {
  table-layout: fixed;
}
.mass-menu {
  position: absolute;
  right: -160px;
  top: 5px;
  background: white;
  display: block;
}
.mass-menu-activator {
  width: 160px;
  background: white;
  height: 42px;
  padding: 10px 15px;
  border: 1px solid lightgray;
  border-radius: 4px;
}
.mass-loader {
  position: absolute;
  right: -190px;
  top: 14px;
}
.v-input--selection-controls .v-input__slot {
  margin-bottom: 0;
}
</style>
