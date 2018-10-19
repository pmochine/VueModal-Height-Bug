<template>
  <transition name="overlay-fade">
    <div
      v-if="visibility.overlay"
      ref="overlay"
      class="v--modal-overlay"
    >
      <div
        class="v--modal-background-click"
        @mousedown.self="handleBackgroundClick"
      >
        <transition
          :name="transition"
          @before-enter="beforeTransitionEnter"
          @after-leave="afterTransitionLeave"
        >
          <div
            v-if="visibility.modal"
            ref="modal"
            class="v--modal v--modal-box"
            :style="modalStyle"
          >
            <slot/>

          </div>
        </transition>
      </div>
    </div>
  </transition>
</template>
<script>
import Modal from './index'
import Guesser from './Guesser';

import { inRange, getMutationObserver } from './util'
import { parseNumber } from './parser'

import Vue from 'vue';

export default {
  name: 'VueJsModal',
  props: {
    name: {
      required: true,
      type: String
    },
    delay: {
      type: Number,
      default: 0
    },
    transition: {
      type: String
    },

    width: {
      type: [Number, String],
      default: 600,
    },
    height: {
      type: [Number, String],
      default: 300,
      validator (value) {
        return value === 'auto' || value
      }
    },
  },
  components:{

  },
  data () {
    return {
      visible: false,

      visibility: {
        modal: false,
        overlay: false
      },

      modal: {
        width: 0,
        widthType: 'px',
        height: 0,
        heightType: 'px',
        renderedHeight: 0
      },

      window: {
        width: 0,
        height: 0
      },

      mutationObserver: null
    }
  },
  created () {
    this.setInitialSize()
  },
  /**
   * Sets global listeners
   */
  beforeMount () {
    Modal.event.$on('toggle', this.handleToggleEvent)

     this.window.width = window.innerWidth
      this.window.height = window.innerHeight

    /**
     * Only observe when using height: 'auto'
     * The callback will be called when modal DOM changes,
     * this is for updating the `top` attribute for height 'auto' modals.
     */
    if (this.isAutoHeight) {

      const MutationObserver = getMutationObserver()

      if (MutationObserver) {
        this.mutationObserver = new MutationObserver(mutations => {
          this.updateRenderedHeight()
        })
      }
    }


    
  },
  /**
   * Removes global listeners
   */
  beforeDestroy () {
    Modal.event.$off('toggle', this.handleToggleEvent)

    
    

  },
  computed: {
    /**
     * Returns true if height is set to "auto"
     */
    isAutoHeight () {
      return this.modal.heightType === 'auto'
    },
    /**
     * Calculates and returns modal position based on the
     * pivots, window size and modal size
     */
    position () {
      const {
        window,
        trueModalHeight
      } = this

      const maxLeft = window.width - this.modal.width
      const maxTop = window.height - trueModalHeight

      const left = 0.5 * maxLeft
      const top = 0.5 * maxTop

      return {
        left: parseInt(inRange(0, maxLeft, left)),
        top: parseInt(inRange(0, maxTop, top))
      }
    },
   
    /**
     * Returns pixel height (if set with %) and makes sure that modal size
     * fits the window.
     *
     * Returns modal.renderedHeight if height set as "auto"
     */
    trueModalHeight () {
      const { modal, isAutoHeight } = this

      const value = modal.height

      if (isAutoHeight) {
        // use renderedHeight when height 'auto'

        //MEIN PROBLEM IST HIER! Es entsteht zu erst eine null
        return this.modal.renderedHeight
      }

      return value
    },

    /**
     * CSS styles for position and size of the modal
     */
    modalStyle () {
      return {
        top: this.position.top + 'px',
        left: this.position.left + 'px',
        width: this.modal.width + 'px',
        height: this.isAutoHeight ? 'auto' : this.trueModalHeight + 'px'
      }
    }
  },
  methods: {
    guessSlotsHeight(){
      console.log('guessing')
      console.log(this.$slots)

      console.log(Guesser)

      let modalDiv = document.createElement('div')

      //Achtung muss hier noch die Classes dynamisch hinzufügen
      modalDiv.className = 'v--modal v--modal-box'

      // console.log(Modal.event)

      let slotComponent = Vue.extend(this.$slots.default)

      console.log(new slotComponent().$mount(modalDiv).$el)

    },
    handleToggleEvent (name, state, params) {
      if (this.name === name) {
        this.toggle(state, params)
      }
    },
    /**
     * Initializes modal's size & position,
     * if "reset" flag is set to true - this function will be called
     * every time "beforeOpen" is triggered
     */
    setInitialSize () {
      const { modal } = this
      const height = parseNumber(this.height)

      modal.width = 600
      modal.height = height.value
      modal.heightType = height.type

      //my code
      if(this.isAutoHeight) this.guessSlotsHeight()
    },



    /**
     * Event handler which is triggered on $modal.show and $modal.hide
     * BeforeEvents: ('before-close' and 'before-open') are `$emit`ed here,
     * but AfterEvents ('opened' and 'closed') are moved to `watch.visible`.
     */
    toggle (nextState, params) {
      const { visible } = this

      if (visible === nextState) {
        return
      }

      const beforeEventName = visible
        ? 'before-close'
        : 'before-open'

      if (beforeEventName === 'before-open') {

        /**
         * Need to unfocus previously focused element, otherwise
         * all keypress events (ESC press, for example) will trigger on that element.
         */
        if (document.activeElement &&
          document.activeElement.tagName !== 'BODY' &&
          document.activeElement.blur) {
          document.activeElement.blur()
        }


      } 

      let stopEventExecution = false

      const stop = () => {
        stopEventExecution = true
      }

  

      if (!stopEventExecution) {
        this.visible = nextState
        this.visible
          ? this.startOpeningModal()
          : this.startClosingModal()
      }
    },

    /**
     * Event handler that is triggered when background overlay is clicked
     */
    handleBackgroundClick () {
      
        this.toggle(false)
      
    },

    startOpeningModal () {
      this.visibility.overlay = true

      setTimeout(() => {
        this.visibility.modal = true
      }, this.delay)
    },

    startClosingModal () {
      this.visibility.modal = false

      setTimeout(() => {
        this.visibility.overlay = false
      }, this.delay)
    },

    
    /**
     * Update $data.modal.renderedHeight using getBoundingClientRect.
     * This method is called when:
     * 1. modal opened
     * 2. MutationObserver's observe callback
     */
    updateRenderedHeight () {
      
      if (this.$refs.modal) {
        
        this.modal.renderedHeight = this.$refs.modal.getBoundingClientRect().height
      }
    },
    /**
     * Start observing modal's DOM, if childList or subtree changes,
     * the callback (registered in beforeMount) will be called.
     */
    connectObserver () {
      if (this.mutationObserver) {
        this.mutationObserver.observe(this.$refs.overlay, {
          childList: true,
          attributes: true,
          subtree: true
        })
      }
    },
    /**
     * Disconnects MutationObserver
     */
    disconnectObserver () {
      if (this.mutationObserver) {
        this.mutationObserver.disconnect()
      }
    },

    beforeTransitionEnter () {
      this.connectObserver()
    },


    afterTransitionLeave () {
      console.log('left')
      this.disconnectObserver()
      
    }
  }
}
</script>
<style>
.v--modal-block-scroll {
  overflow: hidden;
  width: 100vw;
}

.v--modal-overlay {
  position: fixed;
  box-sizing: border-box;
  left: 0;
  top: 0;
  width: 100%;
  height: 100vh;
  background: rgba(0, 0, 0, 0.2);
  z-index: 999;
  opacity: 1;
}

.v--modal-overlay.scrollable {
  height: 100%;
  min-height: 100vh;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
}

.v--modal-overlay .v--modal-background-click {
  min-height: 100%;
  width: 100%;
}

.v--modal-overlay .v--modal-box {
  position: relative;
  overflow: hidden;
  box-sizing: border-box;
}

.v--modal-overlay.scrollable .v--modal-box {
  margin-bottom: 2px;
}

.v--modal {
  background-color: white;
  text-align: left;
  border-radius: 3px;
  box-shadow: 0 20px 60px -2px rgba(27, 33, 58, 0.4);
  padding: 0;
}

.v--modal.v--modal-fullscreen {
  width: 100vw;
  height: 100vh;
  margin: 0;
  left: 0;
  top: 0;
}

.v--modal-top-right {
  display: block;
  position: absolute;
  right: 0;
  top: 0;
}

.overlay-fade-enter-active,
.overlay-fade-leave-active {
  transition: all 0.2s;
}

.overlay-fade-enter,
.overlay-fade-leave-active {
  opacity: 0;
}

.nice-modal-fade-enter-active,
.nice-modal-fade-leave-active {
  transition: all 0.4s;
}

.nice-modal-fade-enter,
.nice-modal-fade-leave-active {
  opacity: 0;
  transform: translateY(-20px);
}
</style>
