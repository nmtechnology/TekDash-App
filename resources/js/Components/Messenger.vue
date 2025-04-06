<template>
  <div class="messenger mt-2 bg-gray-800 rounded-lg border border-gray-700">
    <!-- Messages list -->
    <div class="messages p-4 space-y-4 max-h-96 overflow-y-auto">
      <div 
        class="chat chat-start" 
        v-for="note in notes" 
        :key="note.id"
      >
        <!-- User avatar - Always show initials for consistent UI -->
        <div class="chat-image">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-700 flex items-center justify-center border border-gray-700 mask mask-hexagon">
            <div class="avatar-initials text-white text-lg font-bold">
              {{ getCurrentUserInitials() }}
            </div>
          </div>
        </div>
        
        <!-- Message content as post-it note -->
        <div 
          class="chat-bubble post-it-note" 
          :class="note.user_id === userId ? 'bg-blue-400' : 'bg-yellow-300'"
        >
          <!-- Fake pin/tack for post-it note -->
          <div class="post-it-pin"></div>
          <p class="message-text">{{ note.text }}</p>
        </div>
        <div class="chat-header text-xs text-blue-300 mb-1">
          {{ formatTimestamp(note.created_at) }}
        </div>
      </div>
    </div>
    
    <!-- Input area -->
    <div class="border-t border-gray-800 p-4">
      <div class="flex items-start space-x-1">
        <!-- Current user avatar - Always show initials for consistency -->
        <div class="flex-shrink-0">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-800 flex items-center justify-center border border-gray-900 mask mask-hexagon">
            <div class="avatar-initials text-lime-400 text-lg font-bold">
              {{ getCurrentUserInitials() }}
            </div>
          </div>
        </div>
        
        <!-- Message input -->
        <div class="flex-1">
          <div class="relative">
            <textarea 
              v-model="newNoteText" 
              placeholder="Add a notation..." 
              class="rounded-md bg-gray-900 border-gray-800 shadow-sm text-lime-400 text-sm"
              rows="1"
              @keydown.enter.prevent="addNote"
              ref="messageInput"
            ></textarea>
            
            <!-- Emoji button - Updated styling -->
            <button 
              @click.stop="showEmojiPickerModal = true" 
              class="btn bottom-2 right-2 text-yellow-400 hover:text-yellow-500 p-1 mask mask-hexagon"
              title="Add emoji"
              type="button"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
          
          <div class="flex justify-end mt-2">
            <button 
              @click="addNote" 
              class="inline-flex items-center rounded-md btn px-3 py-2 text-sm font-semibold text-lime-400 shadow-sm hover:text-gray-900 hover:bg-lime-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-400"
            >
              Post
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Emoji Picker Modal - Add this section -->
    <div v-if="showEmojiPickerModal" class="emoji-modal-backdrop" @click="showEmojiPickerModal = false">
      <div class="emoji-modal-container" @click.stop>
        <div class="emoji-modal-header">
          <h3 class="emoji-modal-title">Select Emoji</h3>
          <button 
            @click="showEmojiPickerModal = false" 
            class="emoji-modal-close"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <EmojiPicker @select="insertEmoji" />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, nextTick } from 'vue';
import { format } from 'date-fns';
import axios from 'axios';
// Remove the Inertia import
import EmojiPicker from './EmojiPicker.vue';


export default {
  components: {
    EmojiPicker
  },
  
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
      type: [Number, String],  // Allow both number and string types
      required: true
    },
    getUserName: {
      type: [String, Function],  // Allow both string and function types
      required: true
    },
    getUserAvatar: {
      type: Function,
      required: true
    },
    currentUserAvatar: {
      type: String,
      default: null
    },
    team: {
      type: Object,
      required: false,
      default: () => ({})
    },
    users: { // Modified users prop to be optional
      type: Array,
      required: false,
      default: () => []
    }
  },
  
  setup(props) {
    const notes = ref([...props.initialNotes]);
    const newNoteText = ref('');
    const messageInput = ref(null);
    const showEmojiPickerModal = ref(false);
    
    // Format timestamps for display
    const formatTimestamp = (timestamp) => {
      try {
        return format(new Date(timestamp), 'MMM d, yyyy h:mm a');
      } catch (e) {
        return 'Unknown date';
      }
    };
    
    // Get user initials for avatar
    const getUserInitials = (userId) => {
      try {
        // Handle both function and string prop types
        const name = typeof props.getUserName === 'function' 
          ? props.getUserName(userId)
          : props.getUserName;

        if (!name || typeof name !== 'string' || name === 'undefined' || name === 'null') {
          return userId?.toString().substring(0, 2) || '??';
        }
        
        const nameParts = name.split(' ').filter(part => part.trim().length > 0);
        
        if (nameParts.length === 0) {
          return userId?.toString().substring(0, 2) || '??';
        }
        
        if (nameParts.length === 1) {
          return nameParts[0].substring(0, 2).toUpperCase();
        }
        
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
    
    // Insert emoji at cursor position or append to end
    const insertEmoji = (emoji) => {
      if (messageInput.value) {
        const textarea = messageInput.value;
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        
        // Insert emoji at cursor position or append to end
        newNoteText.value = newNoteText.value.substring(0, start) + 
                           emoji + 
                           newNoteText.value.substring(end);
        
        // Close the modal
        showEmojiPickerModal.value = false;
        
        // Focus back to textarea and place cursor after the inserted emoji
        nextTick(() => {
          textarea.focus();
          const newCursorPos = start + emoji.length;
          textarea.setSelectionRange(newCursorPos, newCursorPos);
        });
      } else {
        // Fallback if ref is not available
        newNoteText.value += emoji;
        showEmojiPickerModal.value = false;
      }
    };
    
    // Improved CSRF token retrieval
    const getCsrfToken = () => {
      // Get from the meta tag (most reliable in Laravel)
      const metaToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      if (metaToken) return metaToken;
      
      // Get from cookie (decode it properly)
      const cookies = document.cookie.split(';').map(cookie => cookie.trim());
      const xsrfCookie = cookies.find(cookie => cookie.startsWith('XSRF-TOKEN='));
      if (xsrfCookie) {
        return decodeURIComponent(xsrfCookie.split('=')[1]);
      }
      
      // Last resort - try from form input
      const inputToken = document.querySelector('input[name="_token"]')?.value;
      if (inputToken) return inputToken;
      
      console.error('CSRF token not found');
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
    
    // Improved add note functionality
    const addNote = () => {
      if (!newNoteText.value.trim()) return;
      
      // Close emoji picker if open
      showEmojiPickerModal.value = false;
      
      // Create a temporary note
      const tempId = 'temp-' + Date.now();
      const newNote = {
        id: tempId,
        text: newNoteText.value.trim(),
        user_id: props.userId,
        created_at: new Date().toISOString(),
        isNew: true
      };
      
      // Add it to our notes array
      notes.value.push(newNote);
      
      // Clear the input
      newNoteText.value = '';
      
      // Scroll to bottom
      setTimeout(() => {
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }, 100);
      
      // Get CSRF token
      const token = getCsrfToken();
      
      // Set up axios with proper headers
      const config = {
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'Accept': 'application/json'
        }
      };
      
      // Use axios.post instead of Inertia.post
      axios.post(`/work-orders/${props.workOrderId}/notes`, 
        { text: newNote.text },
        config
      ).then(response => {
        console.log('Note saved successfully:', response.data);
        const noteIndex = notes.value.findIndex(n => n.id === tempId);
        if (noteIndex !== -1 && response.data) {
          // Update the temporary note with the server data
          notes.value[noteIndex] = { ...response.data, isNew: false };
        }
      }).catch(error => {
        console.error('Error adding note:', error);
        // Show error message
        let errorMessage = 'Failed to save your note. ';
        if (error.response && error.response.data) {
          errorMessage += Object.values(error.response.data).join(', ');
        } else {
          errorMessage += 'Please try again.';
        }
        alert(errorMessage);
        notes.value = notes.value.filter(n => n.id !== tempId);
      });
    };

    return {
      notes,
      newNoteText,
      messageInput,
      formatTimestamp,
      getUserInitials,
      getCurrentUserInitials,
      addNote,
      showEmojiPickerModal,
      insertEmoji,
      getCsrfToken,
      getUserName: computed(() => typeof props.getUserName === 'function' 
        ? props.getUserName 
        : () => props.getUserName)
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
  outline-color: theme('colors.blue.400');
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
  background-color: rgba(238, 255, 0, 0.4);
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

/* Enhanced emoji picker styling */
.emoji-picker-container {
  position: absolute;
  z-index: 50;
  bottom: calc(100% + 5px);
  left: 0;
  width: 320px;
  max-height: 350px;
  background-color: #1F2937;
  border: 1px solid #4B5563;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -4px rgba(0, 0, 0, 0.2);
  padding: 0.75rem;
  overflow-y: auto;
}

.emoji-grid {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 0.25rem;
  max-height: 160px;
  overflow-y: auto;
  padding: 0.25rem;
  margin-bottom: 0.5rem;
  scrollbar-width: thin;
  scrollbar-color: rgba(115, 115, 115, 0.4) transparent;
}

.emoji-grid::-webkit-scrollbar {
  width: 5px;
}

.emoji-grid::-webkit-scrollbar-track {
  background: transparent;
}

.emoji-grid::-webkit-scrollbar-thumb {
  background-color: rgba(115, 115, 115, 0.4);
  border-radius: 10px;
}

.emoji-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  padding: 0.375rem;
  border-radius: 0.25rem;
  cursor: pointer;
  transition: background-color 0.15s ease;
  height: 36px;
  width: 36px;
}

.emoji-btn:hover {
  background-color: #374151;
}

.emoji-btn:active {
  background-color: #4B5563;
}

.category-tabs {
  display: flex;
  gap: 0.5rem;
  padding-bottom: 0.5rem;
  margin-bottom: 0.5rem;
}

.category-tab {
  padding: 0.375rem;
  border-radius: 0.25rem;
  transition: all 0.15s ease;
}

.category-tab:hover {
  background-color: #374151;
}

/* New emoji modal styling */
.emoji-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.emoji-modal-container {
  background-color: #1F2937;
  border-radius: 8px;
  width: 350px;
  max-width: 90%;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.emoji-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #4B5563;
}

.emoji-modal-title {
  font-size: 16px;
  font-weight: 600;
  color: #F3F4F6;
}

.emoji-modal-close {
  background: none;
  border: none;
  color: #9CA3AF;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
}

.emoji-modal-close:hover {
  background-color: #374151;
  color: #F3F4F6;
}

/* Remove glossy message styles and replace with post-it note styling */
.post-it-note {
  position: relative;
  padding: 1rem 1.25rem;
  background: #FFFA99; /* Classic post-it yellow */
  color: #333; /* Darker text for better readability */
  border: none !important;
  border-radius: 2px !important;
  box-shadow: 
    0 4px 8px rgba(0, 0, 0, 0.3),
    0 1px 3px rgba(0, 0, 0, 0.2);
  transform: rotate(-2deg); /* Slight rotation for post-it effect */
  transition: transform 0.2s ease;
  margin-bottom: 0.75rem;
  z-index: 1;
}

/* Every other post-it rotated differently for variety */
.chat:nth-child(even) .post-it-note {
  transform: rotate(1deg);
  background: #FFDD99; /* Slightly different shade */
}

.chat:nth-child(3n) .post-it-note {
  transform: rotate(-0.5deg);
  background: #FFF5AB; /* Another post-it shade */
}

/* Hover effect */
.post-it-note:hover {
  transform: rotate(0deg) scale(1.02);
  z-index: 2;
}

/* Post-it texture */
.post-it-note::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23000000' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
  opacity: 0.3;
  pointer-events: none;
}

/* Pin/tack styling */
.post-it-pin {
  position: absolute;
  top: 5px;
  left: 50%;
  transform: translateX(-50%);
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: radial-gradient(circle at 30% 30%, #F5F5F5 0%, #999 100%);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  z-index: 3;
}

/* Message text styling for post-its */
.post-it-note .message-text {
  position: relative;
  z-index: 1;
  color: #333;
  font-family: 'Comic Sans MS', 'Comic Sans', cursive, sans-serif;
  font-size: 0.95rem;
  line-height: 1.4;
  word-break: break-word;
}

/* Remove default Daisy UI chat bubble triangle */
.chat-bubble:before {
  display: none;
}

/* Update chat layout to accommodate post-it notes better */
.chat {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.75rem;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.chat-image {
  grid-row: span 2;
  align-self: center;
}

.chat-header {
  grid-column: 2;
  margin-top: -0.5rem;
}

/* ... rest of existing styles ... */
</style>