<template>
  <div id="app">
    <div class="content-container">
      <app-frame>
        <router-view></router-view>
      </app-frame>
    </div>
  </div>
</template>

<script>
import appFrame from '@/components/partials/Frame.vue'

export default {
  name: 'app',
  components: {
    appFrame
  },
  created() {
    this.$store.dispatch('getContent');
  },


}
</script>

<style lang="scss">
@import "~vue-material/dist/theme/engine";
@import "~vue-material/dist/theme/all";

.md-toolbar.md-theme-default.md-primary,
.md-field.md-theme-default:before {
  background-color: #244C5A !important;
}

.md-field.md-theme-default.md-focused label, {
  color: #244C5A;
}

#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
}

body {
  background-color: #819db1;
}

.content-container {
  max-width: 1500px;
  margin: auto;
}

a {
  color: black;
}

.md-app:not(.md-overlap).md-theme-default {
    height: 100vh;
    overflow-x: hidden;
    margin-top: -1px;
    margin-left: -1px;
    margin-right: -1px;
}
</style>
