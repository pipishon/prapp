<template>
    <div class="box" :class="{'unchecked': !value}" @click="change">
      <icon v-if="value" scale="1"  class="check-icon" name="check-square" />
    </div>
</template>

<script>
import 'vue-awesome/icons/check-square'

    export default {
      props: ['value'],
      data() {
        return {
        }
      },
      methods: {
        change () {
          console.log('checkbox')
          this.$emit('input', !this.value)
        }
      },
      mounted() {
      }
    }
</script>
<style scope>
.wrap-box {
  height: 100%;
}
.box {
  display: inline-block;
  width: 16px;
  line-height: 1;
  height: 16px;
  border-radius: 0;
}
.unchecked {
  border: 1px solid lightgray;
  border-radius: 2px;
}
.check-icon {
  fill: #44afd5;
  margin-top: -5px;
  margin-left: 1px;
}
</style>
