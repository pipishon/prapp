<template>
  <div class="perf-select-wrap">
  <div :class="classAttrs" v-if="!showSelect" @click="selectClick" class="perf-select v-input v-text-field v-select v-input--is-label-active v-input--is-dirty theme--light">
    <div class="v-input__control">
      <div class="v-input__slot">
        <div class="v-select__slot">
          <div class="v-select__selections">
            <div class="v-select__selection v-select__selection--comma">{{getValue}}</div>
            <input readonly="readonly" type="text" autocomplete="on" aria-readonly="false">
          </div>
          <div class="v-input__append-inner">
            <div class="v-input__icon v-input__icon--append">
              <i aria-hidden="true" class="v-icon material-icons theme--light">{{appendIcon}}</i>
            </div>
          </div>
        </div>
          <div class="v-menu"></div>
      </div>
      <div v-if="!hideDetails" class="v-text-field__details"><div class="v-messages theme--light primary--text"><div class="v-messages__wrapper"></div></div></div>
    </div>
  </div>
  <v-select
    v-if="showSelect"
    :class="classAttrs"
    @input="$emit('input', arguments[0])"
    ref="select"
    v-bind="$props">
  </v-select>
  </div>
</template>
<script>
    export default {
      props: {
        hideDetails: Boolean,
        appendIcon: {
          type: String,
          default: 'arrow_drop_down'
        },
        appendIconCb: Function,
        attach: {
          type: null,
          default: false
        },
        browserAutocomplete: {
          type: String,
          default: 'on'
        },
        cacheItems: Boolean,
        chips: Boolean,
        clearable: Boolean,
        deletableChips: Boolean,
        dense: Boolean,
        hideSelected: Boolean,
        items: {
          type: Array,
          default: () => []
        },
        itemAvatar: {
          type: [String, Array, Function],
          default: 'avatar'
        },
        itemDisabled: {
          type: [String, Array, Function],
          default: 'disabled'
        },
        itemText: {
          type: [String, Array, Function],
          default: 'text'
        },
        itemValue: {
          type: [String, Array, Function],
          default: 'value'
        },
        menuProps: {
          type: [String, Array, Object],
        },
        multiple: Boolean,
        openOnClear: Boolean,
        returnObject: Boolean,
        searchInput: {
          default: null
        },
        smallChips: Boolean,
        value: String
      },
      data() {
        return {
          showSelect: false,
          classAttrs: ''
        }
      },
      computed: {
        getValue () {
          let val = this.value
          if (typeof(this.items[0]) === 'object') {
            val = this.items.filter( el => el[this.itemValue] == val )[0][this.itemText]
          }
          return val
        },
      },
      methods: {
        selectClick () {
          this.showSelect = true
          this.$nextTick(() => {
            console.log(this.$refs.select)
            this.$refs.select.onClick()
          })
        }
      },
      mounted() {
        let attrs = (typeof(this.$el) == 'undefined') ? '' : this.$el.className
        let hideDetails = (this.hideDetails) ? ' v-input--hide-details' : ''
          this.classAttrs = attrs + hideDetails
      }
}
</script>
<style>
.v-messages {
  min-height: 1px !important;
}
</style>
