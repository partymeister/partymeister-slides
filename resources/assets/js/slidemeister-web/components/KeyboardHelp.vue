<template>
  <div class="keyboard-help" @click="$emit('close')">
    <div class="keyboard-help-content" @click.stop>
      <h3>Keyboard Shortcuts</h3>
      <table>
        <tbody>
          <tr><td><kbd>&rarr;</kbd></td><td>Next slide (soft)</td></tr>
          <tr><td><kbd>Shift + &rarr;</kbd></td><td>Next slide (hard cut)</td></tr>
          <tr><td><kbd>&larr;</kbd></td><td>Previous slide (soft)</td></tr>
          <tr><td><kbd>Shift + &larr;</kbd></td><td>Previous slide (hard cut)</td></tr>
          <tr><td><kbd>Space</kbd></td><td>Trigger prizegiving bars</td></tr>
          <tr><td><kbd>F1</kbd> - <kbd>F4</kbd></td><td>Play jingle + light</td></tr>
          <tr><td><kbd>F5</kbd> - <kbd>F10</kbd></td><td>MIDI trigger only</td></tr>
          <tr><td><kbd>Esc</kbd></td><td>Stop jingle + MIDI stop</td></tr>
          <tr><td><kbd>m</kbd></td><td>Toggle video mute</td></tr>
          <tr><td><kbd>d</kbd></td><td>Toggle status bar</td></tr>
          <tr><td><kbd>Shift + d</kbd></td><td>Toggle full debug panel</td></tr>
          <tr><td><kbd>?</kbd></td><td>Toggle this help</td></tr>
        </tbody>
      </table>
      <p class="dismiss">Click outside to close</p>
    </div>
  </div>
</template>

<script setup lang="ts">
defineEmits<{
  close: []
}>()
</script>

<style scoped>
.keyboard-help {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.85);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.keyboard-help-content {
  background: #1a1a1a;
  border: 1px solid #333;
  border-radius: 8px;
  padding: 24px 32px;
  color: #eee;
  font-family: monospace;
  cursor: default;
  min-width: 360px;
}

h3 {
  margin: 0 0 16px 0;
  font-size: 18px;
  color: #fff;
}

table {
  width: 100%;
  border-collapse: collapse;
}

td {
  padding: 6px 0;
}

td:first-child {
  width: 160px;
  text-align: left;
}

kbd {
  background: #333;
  border: 1px solid #555;
  border-radius: 3px;
  padding: 2px 6px;
  font-size: 12px;
}

.dismiss {
  margin: 16px 0 0 0;
  color: #666;
  font-size: 12px;
  text-align: center;
}
</style>
