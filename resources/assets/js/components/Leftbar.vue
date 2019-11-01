<template>
  <div class="wrap">
      <div class="icons-wrap">
        <div v-for="(val, key) in modes" @click="onClick(val)" class="icon-wrap my-3" :class="{active: mode==val}">
          <v-badge overlap color="primary" >
            <span slot="badge" v-if="typeof(leftBadges[val]) != 'undefined' && leftBadges[val] - localBadges[val] > 0">
              {{leftBadges[val] - localBadges[val]}}
            </span>
            <v-icon :color="(mode==val) ? 'orange' : 'white'" medium>{{key}}</v-icon>
          </v-badge>
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
          localBadges: {},
          mode: 'dashboard',
          modes: {
            'dashboard': 'dashboard',
            'people': 'customers',
            'storage': 'products',
            'view_week': 'orders',
            'mode_comment': 'messages',
            'reply_all': 'autoreply',
            'subject': 'statistics',
            'receipt': 'templates',
            'done_outline': 'autoreceive',
            'library_books': 'dictionary',
            'settings': 'settings',
            'directions_car': 'nptrack',
            'table_chart': 'pivot',
            'contacts': 'rfc',
            'offline_bolt': 'discount',
            '3d_rotation': 'votes',
            'hourglass_full': 'cron',
            'directions_walk': 'creturn',
          }
        }
      },
      computed: {
        ...mapGetters(['leftBadges'])
      },
      methods: {
        ...mapMutations(['massSelection']),
        onClick (val) {
          this.mode = val
          this.massSelection([])
          this.$emit('change', val)
          this.localBadges[val] = this.leftBadges[val]
          localStorage.setItem('leftBadges', JSON.stringify(this.localBadges));
        }
      },
      mounted() {
        if (localStorage.getItem('leftBadges')) {
          try {
            this.localBadges = JSON.parse(localStorage.getItem('leftBadges'));
          } catch(e) {
            localStorage.removeItem('leftBadges');
          }
        } else {
          let badges = {}
          for (let n in this.leftBadges) {
            badges[n] = 0
          }
          this.localBadges = badges
          localStorage.setItem('leftBadges', JSON.stringify(badges));
        }
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
