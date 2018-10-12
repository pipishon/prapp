<template>
<div class="form-group">
  <label>{{label}}</label>
  <slot></slot>
  <small v-if="typeof(helptext) != 'undefined'" class="form-text text-muted">{{helptext}}</small>
</div>
</template>

<script>
    export default {
      props: ['label', 'helptext'],
      data() {
        return {
        }
      },
      methods: {
      },
      mounted() {
      }
    }
</script>
<style scope>
</style>
