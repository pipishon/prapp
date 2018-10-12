<template>
<select class="form-control" :value="value" @change="$emit('input', $event.target.value)" >
  <option v-for="(val, key) in options" :value="key">{{val}}</option>
</select>
</template>

<script>
    export default {
      props: ['options', 'value'],
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
