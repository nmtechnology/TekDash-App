<template>
  <div class="messenger mt-2 bg-gray-800 rounded-lg border border-gray-700">
    <!-- Messages list -->
    <div class="messages p-4 space-y-4 max-h-96 overflow-y-auto">
      <div v-for="note in notes" :key="note.id" class="flex items-start">
        <!-- User avatar - Always show initials for consistent UI -->
        <div class="flex-shrink-0 mr-3">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-700 flex items-center justify-center border border-gray-700">
            <div class="avatar-initials text-white text-lg font-bold">
              {{ getUserInitials(note.user_id) }}
            </div>
          </div>
        </div>
        
        <!-- Message content -->
        <div class="flex-1 bg-gray-700 rounded-lg p-3">
          <!-- Improved header with clearer separation between name and timestamp -->
          <div class="flex items-center justify-between mb-1">
            <p class="text-sm font-medium text-lime-400">{{ getUserName(note.user_id) }}</p>
            <p class="text-xs text-gray-400">{{ formatTimestamp(note.created_at) }}</p>
          </div>
          <p class="text-sm text-gray-300 mt-1 whitespace-pre-wrap">{{ note.text }}</p>
        </div>
      </div>
    </div>
    
    <!-- Input area -->
    <div class="border-t border-gray-700 p-4">
      <div class="flex items-start space-x-3">
        <!-- Current user avatar - Always show initials for consistency -->
        <div class="flex-shrink-0">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-700 flex items-center justify-center border border-gray-700">
            <div class="avatar-initials text-white text-lg font-bold">
              {{ getCurrentUserInitials() }}
            </div>
          </div>
        </div>
        
        <!-- Message input -->
        <div class="flex-1">
          <div class="relative">
            <textarea 
              v-model="newNoteText" 
              placeholder="Add a note..." 
              class="w-full rounded-md bg-gray-700 border-gray-600 shadow-sm focus:border-lime-400 focus:ring-lime-400 text-white text-sm"
              rows="2"
              @keydown.enter.prevent="addNote"
            ></textarea>
            
            <!-- Emoji button -->
            <button 
              @click="toggleEmojiPicker" 
              class="absolute bottom-2 right-2 text-yellow-400 hover:text-yellow-500 p-1 rounded-full"
              title="Add emoji"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
          
          <!-- Emoji picker -->
          <div v-if="showEmojiPicker" class="absolute z-10 mt-1 bg-gray-800 border border-gray-700 rounded-lg shadow-lg p-2" style="width: 280px">
            <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-700">
              <h3 class="text-sm font-medium text-white">Emojis</h3>
              <button @click="showEmojiPicker = false" class="text-gray-400 hover:text-gray-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div class="grid grid-cols-8 gap-1 max-h-40 overflow-y-auto emoji-grid">
              <button 
                v-for="emoji in commonEmojis" 
                :key="emoji" 
                @click="addEmoji(emoji)" 
                class="text-xl hover:bg-gray-700 rounded p-1"
              >
                {{ emoji }}
              </button>
            </div>
            
            <!-- Emoji categories -->
            <div class="mt-2 pt-2 border-t border-gray-700">
              <div class="flex space-x-2 mb-2">
                <button 
                  v-for="(category, index) in emojiCategories" 
                  :key="index"
                  @click="selectEmojiCategory(index)"
                  class="text-sm p-1 rounded"
                  :class="selectedCategory === index ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-gray-200'"
                >
                  {{ category.icon }}
                </button>
              </div>
              
              <div class="grid grid-cols-8 gap-1 max-h-40 overflow-y-auto emoji-grid">
                <button 
                  v-for="emoji in currentCategoryEmojis" 
                  :key="emoji" 
                  @click="addEmoji(emoji)" 
                  class="text-xl hover:bg-gray-700 rounded p-1"
                >
                  {{ emoji }}
                </button>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end mt-2">
            <button 
              @click="addNote" 
              class="inline-flex items-center rounded-md outline px-3 py-2 text-sm font-semibold text-lime-400 shadow-sm hover:bg-lime-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-400"
            >
              Send
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import { format } from 'date-fns';
import axios from 'axios';

export default {
  props: {
    initialNotes: {
      type: Array,
      default: () => []
    },
    workOrderId: {
      type: Number,
      required: true
    },
    userId: {
      type: Number,
      required: true
    },
    getUserName: {
      type: Function,
      required: true
    },
    getUserAvatar: {
      type: Function,
      required: true
    },
    currentUserAvatar: {
      type: String,
      default: null
    }
  },
  
  setup(props) {
    const notes = ref([...props.initialNotes]);
    const newNoteText = ref('');
    
    // Emoji picker state
    const showEmojiPicker = ref(false);
    const selectedCategory = ref(0);
    
    // Common emojis that appear at the top
    const commonEmojis = [
      '👍', '👎', '❤️', '🙏', '👌', '👏', '😊', '😂', 
      '🎉', '🔥', '⭐', '✅', '❌', '⚠️', '❓', '💡'
    ];
    
    // Emoji categories
    const emojiCategories = [
      { icon: '😀', emojis: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '🫠', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '☺️', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🫢', '🫣', '🤫', '🤔', '🫡', '🤐', '🤨', '😐', '😑', '😶', '🫥', '😶‍🌫️', '😏', '😒', '🙄', '😬', '😮‍💨', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤧', '🥵', '🥶', '🥴', '😵', '😵‍💫', '🤯', '🤠', '🥳', '🥸', '😎', '🤓', '🧐', '😕', '🫤', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '🥹', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀', '☠️', '💩', '🤡', '👹', '👺', '👻', '👽', '👾', '🤖', '😺', '😸', '😹', '😻', '😼', '😽', '🙀', '😿', '😾', '🙈', '🙉', '🙊'] },
      { icon: '👍', emojis: ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🫰', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🤝', '🙏', '✍️', '💅', '🤳', '💪', '🦾', '🦵', '🦿', '🦶', '👂', '🦻', '👃', '🧠', '👣', '🫀', '🫁', '🦷', '🦴', '👀', '👁️', '👅', '👄', '🫦', '💋', '🩸'] },
      { icon: '🐱', emojis: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼', '🐻‍❄️', '🐨', '🐯', '🦁', '🐮', '🐷', '🐽', '🐸', '🐵', '🙈', '🙉', '🙊', '🐒', '🐔', '🐧', '🐦', '🐤', '🐣', '🐥', '🦆', '🦅', '🦉', '🦇', '🐺', '🐗', '🐴', '🦄', '🐝', '🪱', '🐛', '🦋', '🐌', '🐞', '🐜', '🪰', '🪲', '🪳', '🦟', '🦗', '🕷️', '🕸️', '🦂', '🐢', '🐍', '🦎', '🦖', '🦕', '🐙', '🦑', '🦐', '🦞', '🦀', '🐡', '🐠', '🐟', '🐬', '🐳', '🐋', '🦈', '🐊', '🐅', '🐆', '🦓', '🦍', '🦧', '🦣', '🐘', '🦛', '🦏', '🐪', '🐫', '🦒', '🦘', '🦬', '🐃', '🐂', '🐄', '🐎', '🐖', '🐏', '🐑', '🦙', '🐐', '🦌', '🐕', '🐩', '🦮', '🐕‍🦺', '🐈', '🐈‍⬛', '🪶', '🐓', '🦃', '🦤', '🦚', '🦜', '🦢', '🦩', '🕊️', '🐇', '🦝', '🦨', '🦡', '🦫', '🦦', '🦥', '🐁', '🐀', '🐿️', '🦔', '🐾', '🐉', '🐲', '🌵', '🎄', '🌲', '🌳', '🌴', '🪵', '🌱', '🌿', '☘️', '🍀', '🎍', '🪴', '🎋', '🍃', '🍂', '🍁', '🍄', '🐚', '🪨', '🌾', '💐', '🌷', '🪷', '🌹', '🥀', '🌺', '🌸', '🌼', '🌻'] },
      { icon: '🏆', emojis: ['🏆', '🥇', '🥈', '🥉', '🏅', '🎖️', '🏵️', '🎗️', '🎫', '🎟️', '🎪', '🎭', '🎨', '🎬', '🎤', '🎧', '🎼', '🎹', '🥁', '🪘', '🎷', '🎺', '🪗', '🎸', '🪕', '🎻', '🪩', '🎲', '🎯', '🎳', '🎮', '🎰', '🧩'] },
      { icon: '💯', emojis: ['💯', '❓', '❔', '❕', '❗', '〽️', '⚠️', '🚸', '🔱', '⚜️', '🔰', '♻️', '✅', '🈯', '💹', '❇️', '✳️', '❎', '🌐', '💠', 'Ⓜ️', '🈂️', '🛂', '🛃', '🛄', '🛅', '♿', '🚾', '🅿️', '🚰', '🚹', '🚺', '🚼', '⚧️', '🚻', '🚮', '🎦', '📶', '🈁', '🔣', 'ℹ️', '🔤', '🔡', '🔠', '🆖', '🆗', '🆙', '🆒', '🆕', '🆓', '0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟', '🔢', '#️⃣', '*️⃣', '⏏️', '▶️', '⏸️', '⏯️', '⏹️', '⏺️', '⏭️', '⏮️', '⏩', '⏪', '⏫', '⏬', '◀️', '🔼', '🔽', '➡️', '⬅️', '⬆️', '⬇️', '↗️', '↘️', '↙️', '↖️', '↕️', '↔️', '↪️', '↩️', '⤴️', '⤵️', '🔀', '🔁', '🔂', '🔄', '🔃', '🎵', '🎶', '➕', '➖', '➗', '✖️', '♾️', '💲', '💱', '™️', '©️', '®️', '👁️‍🗨️', '🔚', '🔙', '🔛', '🔝', '🔜', '〰️', '➰', '➿', '✔️', '☑️', '🔘', '🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '⚫', '⚪', '🟤', '🔺', '🔻', '🔸', '🔹', '🔶', '🔷', '🔳', '🔲', '▪️', '▫️', '◾', '◽', '◼️', '◻️', '🟥', '🟧', '🟨', '🟩', '🟦', '🟪', '⬛', '⬜', '🟫', '🔈', '🔇', '🔉', '🔊', '🔔', '🔕', '📣', '📢'] },
      { icon: '🧠', emojis: ['👁️‍🗨️', '💭', '🗯️', '💬', '🗨️', '🗣️', '👤', '👥', '🫂', '👪', '👨‍👩‍👦', '👨‍👩‍👧', '👨‍👩‍👧‍👦', '👨‍👩‍👦‍👦', '👨‍👩‍👧‍👧', '👨‍👨‍👦', '👨‍👨‍👧', '👨‍👨‍👧‍👦', '👨‍👨‍👦‍👦', '👨‍👨‍👧‍👧', '👩‍👩‍👦', '👩‍👩‍👧', '👩‍👩‍👧‍👦', '👩‍👩‍👦‍👦', '👩‍👩‍👧‍👧', '👨‍👦', '👨‍👦‍👦', '👨‍👧', '👨‍👧‍👦', '👨‍👧‍👧', '👩‍👦', '👩‍👦‍👦', '👩‍👧', '👩‍👧‍👦', '👩‍👧‍👧', '🧑‍🤝‍🧑', '👭', '👫', '👬'] }
    ];
    
    // Computed property to get current category emojis
    const currentCategoryEmojis = computed(() => {
      return emojiCategories[selectedCategory.value]?.emojis || [];
    });

    // Toggle emoji picker
    const toggleEmojiPicker = () => {
      showEmojiPicker.value = !showEmojiPicker.value;
    };
    
    // Select emoji category
    const selectEmojiCategory = (index) => {
      selectedCategory.value = index;
    };
    
    // Add emoji to text input
    const addEmoji = (emoji) => {
      newNoteText.value += emoji;
    };
    
    // Format timestamps for display
    const formatTimestamp = (timestamp) => {
      try {
        return format(new Date(timestamp), 'MMM d, yyyy h:mm a');
      } catch (e) {
        return 'Unknown date';
      }
    };
    
    // Get user initials for avatar - improved to focus on first and last initials
    const getUserInitials = (userId) => {
      try {
        const name = props.getUserName(userId);
        
        // Handle invalid inputs
        if (!name || typeof name !== 'string' || name === 'undefined' || name === 'null') {
          return userId?.toString().substring(0, 2) || '??';
        }
        
        // Split the name and filter out empty parts
        const nameParts = name.split(' ').filter(part => part.trim().length > 0);
        
        // If we couldn't extract any parts, use a fallback
        if (nameParts.length === 0) {
          return userId?.toString().substring(0, 2) || '??';
        }
        
        // If there's only one name part, take the first two letters
        if (nameParts.length === 1) {
          return nameParts[0].substring(0, 2).toUpperCase();
        }
        
        // Take the first letter of the first name and the first letter of the last name
        const firstInitial = nameParts[0][0].toUpperCase();
        const lastInitial = nameParts[nameParts.length - 1][0].toUpperCase();
        
        return firstInitial + lastInitial;
      } catch (error) {
        console.error('Error generating initials:', error);
        return userId?.toString().substring(0, 2) || '??';
      }
    };
    
    // Get current user initials
    const getCurrentUserInitials = () => {
      return getUserInitials(props.userId);
    };
    
    // Add a function to get the current CSRF token
    const getCsrfToken = () => {
      // Try to get from meta tag
      const metaToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (metaToken) return metaToken;
      
      // Try to get from form input
      const inputToken = document.querySelector('input[name="_token"]')?.value;
      if (inputToken) return inputToken;
      
      // Try from cookie
      const cookies = document.cookie.split(';').map(cookie => cookie.trim());
      const xsrfCookie = cookies.find(cookie => cookie.startsWith('XSRF-TOKEN='));
      if (xsrfCookie) {
        return decodeURIComponent(xsrfCookie.split('=')[1]);
      }
      
      return '';
    };

    onMounted(() => {
      // Scroll to the most recent message
      setTimeout(() => {
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }, 100);
    });
    
    // Add a new note with improved CSRF handling
    const addNote = () => {
      if (!newNoteText.value.trim()) return;
      
      // Hide emoji picker when sending
      showEmojiPicker.value = false;
      
      // Create a temporary note with a temporary ID
      const tempId = 'temp-' + Date.now();
      const newNote = {
        id: tempId,
        text: newNoteText.value.trim(),
        user_id: props.userId,
        created_at: new Date().toISOString(),
        isNew: true
      };
      
      // Add it to our notes array immediately for UI feedback
      notes.value.push(newNote);
      
      // Clear the input
      newNoteText.value = '';
      
      // Scroll to bottom to show new message
      setTimeout(() => {
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }, 100);
      
      // Get fresh CSRF token
      const token = getCsrfToken();
      
      // Create FormData with text and token
      const formData = new FormData();
      formData.append('text', newNote.text);
      formData.append('_token', token);
      
      // Send to server with FormData to avoid CSRF issues
      axios.post(`/work-orders/${props.workOrderId}/notes`, formData)
        .then(response => {
          // Update the note with the real ID from server
          const noteIndex = notes.value.findIndex(n => n.id === tempId);
          if (noteIndex !== -1 && response.data && response.data.id) {
            notes.value[noteIndex].id = response.data.id;
            console.log('Note saved successfully with ID:', response.data.id);
          }
        })
        .catch(error => {
          console.error('Error adding note:', error);
          
          // Remove the temp note on failure
          notes.value = notes.value.filter(n => n.id !== tempId);
          
          // Show error to user
          alert('Failed to save your note. Please try again.');
        });
    };
    
    return {
      notes,
      newNoteText,
      formatTimestamp,
      getUserInitials,
      getCurrentUserInitials,
      addNote,
      showEmojiPicker,
      selectedCategory,
      commonEmojis,
      emojiCategories,
      currentCategoryEmojis,
      toggleEmojiPicker,
      selectEmojiCategory,
      addEmoji,
    };
  }
};
</script>

<style scoped>
.messages {
  scrollbar-width: thin;
  scrollbar-color: rgba(115, 115, 115, 0.4) transparent;
}

.messages::-webkit-scrollbar {
  width: 6px;
}

.messages::-webkit-scrollbar-track {
  background: transparent;
}

.messages::-webkit-scrollbar-thumb {
  background-color: rgba(115, 115, 115, 0.4);
  border-radius: 20px;
}

/* For smooth scrolling behavior */
.messages {
  scroll-behavior: smooth;
}

/* Updated focus styles to match lime theme */
:focus {
  outline-color: theme('colors.lime.400');
}

.emoji-grid {
  scrollbar-width: thin;
  scrollbar-color: rgba(115, 115, 115, 0.4) transparent;
}

.emoji-grid::-webkit-scrollbar {
  width: 6px;
}

.emoji-grid::-webkit-scrollbar-track {
  background: transparent;
}

.emoji-grid::-webkit-scrollbar-thumb {
  background-color: rgba(115, 115, 115, 0.4);
  border-radius: 20px;
}

/* Enhanced styling for user initials in avatar */
.avatar-initials {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  font-size: 1rem;
  font-weight: 600;
  background-color: #4B5563;
  color: white;
  user-select: none;
}

/* Make messenger height more adaptable */
.messenger {
  display: flex;
  flex-direction: column;
}

.messages {
  flex: 1;
  min-height: 150px;
  max-height: 300px;
}
</style>