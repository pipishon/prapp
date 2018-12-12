<template>
  <div class="wrap">
      <div class="icons-wrap">
        <div v-for="(val, key) in modes" @click="mode = val; massSelection([]); $emit('change', val)" class="icon-wrap my-3" :class="{active: mode==val}">
          <v-icon :color="(mode==val) ? 'orange' : 'white'" medium>{{key}}</v-icon>
        </div>
      </div>
  </div>
</template>
<script>
    import { mapActions, mapGetters, mapMutations } from 'vuex'
    export default {
      props: ['imode'],
      data() {
        return {
          mode: 'dashboard',
          modes: {
            'dashboard': 'dashboard',
            'people': 'customers',
            'storage': 'products',
            'view_week': 'orders',
            'mode_comment': 'messages',
            'reply_all': 'autoreply',
            'bar_chart': 'statistics',
            'receipt': 'templates',
            'done_outline': 'autoreceive',
            'library_books': 'dictionary',
            'settings': 'settings',
            'directions_car': 'nptrack',
            'table_chart': 'pivot'
          }
        }
      },
      methods: {
        ...mapMutations(['massSelection']),
      },
      mounted() {
        this.mode = this.imode
      }
    }
</script>
<style scoped>
.wrap {
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  width: 3.2rem;
  background: black;
  padding: 0.2rem 0;
}
.icons-wrap{
  margin-top: 6rem;
}
.icon-wrap {
  width: 3rem;
  cursor: pointer;
  text-align: center;
}
.icon {
  margin: 1rem 0;
  fill: white;
}
.active .icon{
  color: #ffc107;
}
</style>
