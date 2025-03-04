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
        
        <!-- Message content - Now with glossy style -->
        <div class="flex-1 message-bubble glossy-message">
          <!-- Improved header with clearer separation between name and timestamp -->
          <div class="flex items-center justify-between mb-1">
            <p class="text-sm font-medium text-lime-400">{{ getUserName(note.user_id) }}</p>
            <p class="text-xs text-blue-300">{{ formatTimestamp(note.created_at) }}</p>
          </div>
          <p class="text-sm text-white mt-1 whitespace-pre-wrap message-text">{{ note.text }}</p>
        </div>
      </div>
    </div>
    
    <!-- Input area -->
    <div class="border-t border-gray-800 p-4">
      <div class="flex items-start space-x-3">
        <!-- Current user avatar - Always show initials for consistency -->
        <div class="flex-shrink-0">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-800 flex items-center justify-center border border-gray-700">
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
              placeholder="Add a note..." 
              class="w-full rounded-md bg-gray-900 border-gray-600 shadow-sm text-lime-400 text-sm"
              rows="2"
              @keydown.enter.prevent="addNote"
              ref="messageInput"
            ></textarea>
            
            <!-- Emoji button - Updated styling -->
            <button 
              @click.stop="showEmojiPickerModal = true" 
              class="absolute bottom-2 right-2 text-yellow-400 hover:text-yellow-500 p-1 rounded-full"
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
              class="inline-flex items-center rounded-md outline px-3 py-2 text-sm font-semibold text-lime-400 shadow-sm hover:text-gray-900 hover:bg-lime-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-400"
            >
              Send
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
    
    // Get user initials for avatar - making this more robust
    const getUserInitials = (userId) => {
      try {
        // First try to get name from note.user_name if available (for new/updated notes)
        const noteWithUser = notes.value.find(n => n.user_id === userId && n.user_name);
        const name = noteWithUser?.user_name || props.getUserName(userId);
        
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
      
      // Try to get the current user name for the temporary note
      let currentUserName;
      try {
        currentUserName = props.getUserName(props.userId);
      } catch (e) {
        currentUserName = null;
      }
      
      // Create a temporary note with user_name if available
      const tempId = 'temp-' + Date.now();
      const newNote = {
        id: tempId,
        text: newNoteText.value.trim(),
        user_id: props.userId,
        user_name: currentUserName,
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
          // Preserve the user_name from the response or use our best guess
          const responseName = response.data.user_name || 
                              currentUserName || 
                              props.getUserName(response.data.user_id);
                              
          // Update the temporary note with the server data but keep user_name
          notes.value[noteIndex] = { 
            ...response.data,
            user_name: responseName,
            isNew: false 
          };
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
      // Use the prop's getUserName function instead of a non-existent local variable
      getUserName: props.getUserName
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

/* Glossy message styling */
.message-bubble {
  position: relative;
  padding: 0.75rem;
  border-radius: 0.5rem;
  overflow: hidden;
  background: rgba(30, 41, 59, 0.7);
  box-shadow: 
    0 2px 5px rgba(0, 0, 0, 0.2),
    0 4px 10px rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(148, 163, 184, 0.1);
  z-index: 1;
}

/* Glossy reflection effect */
.glossy-message {
  position: relative;
}

.glossy-message::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 50%;
  background: linear-gradient(to bottom, 
    rgba(255, 255, 255, 0.15), 
    rgba(255, 255, 255, 0.05) 40%, 
    rgba(255, 255, 255, 0) 100%);
  z-index: 0;
  pointer-events: none;
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}

/* Message text positioning relative to the glossy effect */
.message-text {
  position: relative;
  z-index: 1;
  color: rgba(255, 255, 255, 0.9);
  font-weight: 400;
  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
}

/* Custom avatar styling to match the glossy theme */
.avatar-initials {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  width: 100%;
  font-size: 1rem;
  font-weight: 600;
  background: linear-gradient(135deg, #4B5563 0%, #1F2937 100%);
  color: white;
  user-select: none;
  box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.1);
}

/* Input area styling to match the glossy theme */
textarea {
  background: rgba(17, 24, 39, 0.7) !important;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(75, 85, 99, 0.3) !important;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1) !important;
}

/* Enhanced emoji modal styling to match glossy theme */
.emoji-modal-container {
  background: rgba(31, 41, 55, 0.9);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(107, 114, 128, 0.2);
  box-shadow: 
    0 10px 25px -5px rgba(0, 0, 0, 0.5),
    0 8px 10px -6px rgba(0, 0, 0, 0.3);
}

.emoji-modal-header {
  background: rgba(17, 24, 39, 0.6);
  border-bottom: 1px solid rgba(107, 114, 128, 0.2);
}
</style>