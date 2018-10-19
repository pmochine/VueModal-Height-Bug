import Modal from './Modal.vue'

const defaultComponentName = 'modal'


const Plugin = {
  install (Vue, options = {}) {

    this.event = new Vue()
    this.componentName = options.componentName || defaultComponentName
    /**
     * Plugin API
     */
    Vue.prototype.$modal = {
      show (modal, paramsOrProps, params, events = {}) {
        if (typeof modal === 'string') {
          Plugin.event.$emit('toggle', modal, true, paramsOrProps)
          return
        }
      }
    }
    /**
     * Sets custom component name (if provided)
     */
    Vue.component(this.componentName, Modal)

  }
}

export default Plugin
