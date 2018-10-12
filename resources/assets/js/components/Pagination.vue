<template>
  <div class="wrap">
      <nav class="container">
        <ul class="pagination">
          <li class="page-item"><a @click.prevent="$emit('change', (current == 1) ? 1 : current - 1)" class="page-link" href="#"><span aria-hidden="true">&laquo;</span></a></li>
          <template v-if="false && current > 2">
            <li class="page-item" :class="{active: current == 1}">
              <a class="page-link" href="#" @click.prevent="$emit('change', 1)">
                1
              </a>
            </li>
            <li>...</li>
          </template>
          <li class="page-item" :class="{active: current == page}" v-for="page in pages">
            <a class="page-link" href="#" @click.prevent="$emit('change', page)">
              {{page}}
            </a>
          </li>
          <template v-if="false && (last - current) > 2">
            <li>...</li>
            <li class="page-item" :class="{active: current == last}">
              <a class="page-link" href="#" @click.prevent="$emit('change', last)">
                {{last}}
              </a>
            </li>
          </template>
          <li class="page-item"><a @click.prevent="$emit('change', (current == last) ? current : current + 1)" class="page-link" href="#"><span aria-hidden="true">&raquo;</span></a></li>
        </ul>
      </nav>
  </div>
</template>

<script>
    export default {
      props: ['current', 'last'],
      data() {
        return {
          npages: 2,
          pages: [],
        }
      },
      watch: {
        current () {
          this.getPages()
        },
        last () {
          this.getPages()
        }
      },
      methods: {
        getPages (params) {
          let cur = this.current
          let last = this.last
          let npages = this.npages
          this.pages = [cur]
          if (last > cur) {
            this.pages.push(cur + 1)
          }
          /*let decada = parseInt(cur / npages)
          if (last < npages) {
            for (let i = 1; i <= last; i++) {
              this.pages.push(i)
            }
            return
          }
          if (cur <= (last - npages)) {
            for (let i = 0; i < npages + 2; i++) {
              if ( i - 1 + (decada * npages) < 1 ) continue
              console.log(i - 1 + (decada * npages), npages, decada, this.pages)
              this.pages.push(i - 1 + (decada * npages))
            }
          } else {
            for (let i = last - npages; i <= last; i++) {
              this.pages.push(i)
            }
          }*/
        }
      },
      mounted() {
        this.getPages()
      }
    }
</script>
<style scoped>
.wrap {
}
ul {
  margin-bottom: 0;
}
</style>
