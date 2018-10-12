<template>
  <tr>
    <td v-for="n in fields.length">
      <input v-if="typeof(fields[n - 1]) != 'undefined'" type="text" class="form-control" :placeholder="'Search '+fields[n - 1]" @keyup.enter="onChange($event, fields[n - 1])"/>
    </td>
  </tr>
</template>

<script>
    export default {
      props: ['fields'],
      data() {
        return {
        }
      },
      methods: {
        onChange (event, field) {
          if (field == '') return
          let data = {}
          data[field] = event.target.value
          this.$emit('change', data);
        }
      }
    }
</script>
<style scoped>
</style>
