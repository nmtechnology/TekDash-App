<template>
  <div class="emoji-picker">
    <!-- Tabs for emoji categories -->
    <div class="emoji-picker-tabs">
      <button
        v-for="(tab, index) in tabs"
        :key="index"
        @click="activeTab = index"
        class="emoji-tab"
        :class="{ 'active': activeTab === index }"
      >
        {{ tab.icon }}
      </button>
    </div>
    
    <!-- Emoji grid -->
    <div class="emoji-picker-content">
      <div class="emoji-grid">
        <button
          v-for="emoji in currentEmojis"
          :key="emoji"
          @click="selectEmoji(emoji)"
          class="emoji-btn"
        >
          {{ emoji }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'EmojiPicker',
  emits: ['select'],
  
  setup(props, { emit }) {
    const activeTab = ref(0);
    
    // Simplified emoji tabs with fewer categories for reliability
    const tabs = [
      { icon: '😀', label: 'Smileys', emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚', '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩', '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣', '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬', '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗', '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯', '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐', '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕'] },
      { icon: '👍', label: 'Gestures', emojis: ['👋', '🤚', '✋', '🖐️', '👌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️'] },
      { icon: '❤️', label: 'Love', emojis: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝', '💟', '♥️', '💌', '💋', '👩‍❤️‍💋‍👨', '👨‍❤️‍💋‍👨', '👩‍❤️‍💋‍👩', '👩‍❤️‍👨', '👨‍❤️‍👨', '👩‍❤️‍👩'] },
      { icon: '🐱', label: 'Animals', emojis: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵', '🐔', '🐧', '🐦', '🐤', '🦆', '🦅', '🦉', '🐺', '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞', '🐜', '🕷️', '🦂', '🦗', '🦟', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🐘', '🦛', '🦏', '🦒'] },
      { icon: '🍎', label: 'Food', emojis: ['🍏', '🍎', '🍐', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈', '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🥑', '🍆', '🥦', '🥬', '🥒', '🌶️', '🌽', '🥕', '🧄', '🧅', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨', '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🥩', '🍗', '🍖', '🦴', '🌭', '🍔', '🍟', '🍕', '🥪', '🥙', '🧆', '🌮', '🌯', '🥗', '🥘', '🥫', '🍝', '🍜', '🍲', '🍛', '🍣', '🍱', '🥟', '🦪', '🍤', '🍙', '🍚', '🍘', '🍥'] },
      { icon: '⚽', label: 'Activities', emojis: ['⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉', '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍', '🏏', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊', '🥋', '🎽', '🛹', '🛼', '🛷', '⛸️', '🥌', '🎿', '⛷️', '🏂', '🪂', '🏋️', '🤼', '🤸', '🤽', '🤾', '🤺', '🏄', '🏊', '🚣', '🧗', '🚴', '🚵'] },
      { icon: '🔥', label: 'Popular', emojis: ['👍', '👎', '❤️', '🙏', '👌', '👏', '😊', '😂', '🎉', '🔥', '⭐', '✅', '❌', '⚠️', '❓', '💡', '💯', '🎯', '🙌', '🚀', '💪', '👀', '🔍', '💰', '💤', '💩', '😱', '👻', '👋', '🎂', '🍻', '🎮', '📱', '👑', '🌈', '🌟', '🌱', '🍀', '🏆', '🏅', '🥇'] }
    ];
    
    // Get current emojis based on active tab
    const currentEmojis = computed(() => {
      return tabs[activeTab.value].emojis || [];
    });
    
    // When an emoji is selected
    const selectEmoji = (emoji) => {
      emit('select', emoji);
    };
    
    return {
      tabs,
      activeTab,
      currentEmojis,
      selectEmoji
    };
  }
};
</script>

<style scoped>
.emoji-picker {
  width: 100%;
  max-width: 320px;
  background: rgba(31, 41, 55, 0.8);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  user-select: none;
  border: 1px solid rgba(107, 114, 128, 0.2);
}

.emoji-picker-tabs {
  display: flex;
  overflow-x: auto;
  background: rgba(17, 24, 39, 0.7);
  padding: 8px 4px;
  scrollbar-width: none; /* For Firefox */
  border-bottom: 1px solid rgba(75, 85, 99, 0.2);
}

.emoji-picker-tabs::-webkit-scrollbar {
  display: none; /* For Chrome and others */
}

.emoji-tab {
  flex: 0 0 auto;
  font-size: 16px;
  padding: 6px 8px;
  background: none;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #9CA3AF;
}

.emoji-tab:hover {
  background-color: rgba(55, 65, 81, 0.7);
  color: #F3F4F6;
  transform: translateY(-1px);
}

.emoji-tab.active {
  background: linear-gradient(to bottom, rgba(55, 65, 81, 0.9), rgba(55, 65, 81, 0.7));
  color: #F3F4F6;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.emoji-picker-content {
  padding: 8px;
  max-height: 240px;
  overflow-y: auto;
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 6px;
}

.emoji-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  font-size: 20px;
  background: none;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.emoji-btn:hover {
  background: rgba(55, 65, 81, 0.5);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.emoji-btn:active {
  transform: scale(0.95) translateY(0);
}

/* Scrollbar styling */
.emoji-picker-content {
  scrollbar-width: thin;
  scrollbar-color: #4B5563 transparent;
}

.emoji-picker-content::-webkit-scrollbar {
  width: 6px;
}

.emoji-picker-content::-webkit-scrollbar-track {
  background: transparent;
}

.emoji-picker-content::-webkit-scrollbar-thumb {
  background-color: #4B5563;
  border-radius: 20px;
}
</style>
