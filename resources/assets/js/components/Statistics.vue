<template>
  <div class="container">
    <button class="btn btn-default" @click="recalcCustomerStatistics(1)">Recalc customer statistics</button>
    <div>{{customerRecalcs.to}} / {{customerRecalcs.total}}</div>
  </div>
</template>
<script>

    export default {
      props: ['imode'],
      data() {
        return {
          customerRecalcs: {
            to: 0,
            total:0
          }
        }
      },
      methods: {
        recalcCustomerStatistics (page) {
          let params = { page }
          axios.get('api/statistics/recalc/customers', {params}).then((res) => {
            if (res.data.data.length > 0) {
              this.customerRecalcs.to = res.data.to
              this.customerRecalcs.total = res.data.total
              this.recalcCustomerStatistics(res.data.current_page + 1)
            }
          })
        }
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
  width: 3rem;
  background: black;
  padding: 0.2rem 0;
}
.icons-wrap{
  margin-top: 3rem;
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
  fill: #ffc107;
}
</style>
