<template>
  <div class="modal" :class="{show: shown}" :aria-hidden="!shown" :style="{display}" :id="id">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{title}}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="hide">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body container-fluid">
            <slot></slot>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" @click="onSave">{{saveText}}</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="hide">Close</button>
          </div>
        </div>
    </div>
  </div>
</template>

<script>
    export default {
      props: ['id', 'title', 'savetext'],
      data() {
        return {
          shown: false
        }
      },
      computed: {
        saveText () {
          return (this.savetext) ? this.savetext : 'Save changes'
        },
        display () {
          return (this.shown) ? 'block' : 'none';
        }
      },
      methods: {
        show () {
          this.shown = true
        },
        hide () {
          this.shown = false
        },
        onSave() {
          this.$emit('save')
          this.shown = false
        }
      },
      mounted() {
      }
    }
</script>
<style scope>
.modal-lg {
  max-width: 80vw;
}
</style>
