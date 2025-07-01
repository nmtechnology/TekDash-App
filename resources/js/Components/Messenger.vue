<template>
  <div class="messenger bg-transparent flex flex-col h-full">
    <!-- Messages list -->
    <div class="messages p-1 space-y-4 overflow-y-auto flex-1">
      <div 
        :class="[
          'chat', 
          note.user_id === userId ? 'chat-end' : 'chat-start',
          note.isNew ? 'chat-new' : '',
        ]" 
        v-for="note in notes" 
        :key="note.id"
      >
        <!-- User avatar - Show note author's initials -->
        <div class="chat-image">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-700 flex items-center justify-center border border-gray-700 mask mask-hexagon">
            <div class="avatar-initials text-white text-lg font-bold" :title="note.user_name || 'Unknown'">
              {{ note.user_initials || getUserInitials(note.user_id) }}
            </div>
          </div>
        </div>
        
        <!-- Message content as chat bubble with varied colors -->
        <div 
          class="chat-bubble" 
          :class="getBubbleClass(note)"
        >
          <div v-if="note.urgent" class="font-bold text-xs mb-1 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            URGENT
          </div>
          {{ note.text }}
        </div>
        <div class="chat-footer text-xs opacity-70 mt-1">
          <span class="font-semibold">{{ note.user_name || 'User' }}</span> • {{ formatTimestamp(note.created_at) }}
        </div>
      </div>
    </div>
    
    <!-- Input area with shadcn components -->
    <div class="border-t border-gray-700 p-2 mt-auto">
      <div class="flex items-start space-x-2">
        <!-- Current user avatar - Always show initials for consistency -->
        <!-- <div class="flex-shrink-0">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-800 flex items-center justify-center border border-gray-900 mask mask-hexagon">
            <div class="avatar-initials text-lime-400 text-lg font-bold">
              {{ getCurrentUserInitials() }}
            </div>
          </div>
        </div> -->
        
        <!-- Message input using shadcn components -->
        <div class="flex-1 grid gap-2">
          <div class="relative">
            <Textarea
              v-model="newNoteText"
              placeholder="Type your message here..."
              rows="1"
              @keydown.enter.prevent="addNote"
              ref="messageInput"
              class="resize-none min-h-[40px] pr-10"
            />
            
            <!-- Urgent message button -->
            <button 
              @click.stop="toggleUrgentMessage" 
              class="absolute bottom-2 right-10 p-1 rounded-full hover:bg-gray-700"
              :class="isUrgent ? 'text-red-500 hover:text-red-400 bg-gray-700' : 'text-red-400 hover:text-red-300'"
              title="Mark as urgent"
              type="button"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </button>
            
            <!-- Emoji button -->
            <button 
              @click.stop="showEmojiPickerModal = true" 
              class="absolute bottom-2 right-2 p-1 rounded-full hover:bg-gray-700 text-yellow-400 hover:text-yellow-300"
              title="Add emoji"
              type="button"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
          </div>
          
          <div class="flex justify-end">
            <button 
              @click="addNote" 
              class="glossy-btn btn font-bold inline-flex justify-center rounded-md border px-4 py-2 text-lime-400 hover:bg-lime-400 hover:text-lime-500 text-base transition-all duration-200 shadow-lg"
              :disabled="!newNoteText.trim()"
              type="button"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Send Message
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
import EmojiPicker from './EmojiPicker.vue';
import { Button } from '@/Components/ui/button';
import { Textarea } from '@/Components/ui/textarea';


export default {
  components: {
    EmojiPicker,
    Button,
    Textarea
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
    const isUrgent = ref(false); // Flag for urgent messages
    
    // Function to fetch notes from the server
    const fetchNotes = () => {
      axios.get(`/work-orders/${props.workOrderId}/notes`)
        .then(response => {
          // Process the notes data to ensure each note has user initials
          notes.value = response.data.map(note => {
            if (!note.user_initials && note.user && note.user.name) {
              const nameParts = note.user.name.split(' ').filter(part => part.trim().length > 0);
              if (nameParts.length >= 2) {
                note.user_initials = (nameParts[0][0] + nameParts[nameParts.length-1][0]).toUpperCase();
              } else if (nameParts.length === 1) {
                note.user_initials = nameParts[0].substring(0, 2).toUpperCase();
              }
            }
            return note;
          });
          
          // Scroll to the most recent message
          setTimeout(() => {
            const messagesContainer = document.querySelector('.messages');
            if (messagesContainer) {
              messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
          }, 100);
        })
        .catch(error => {
          console.error('Error fetching notes:', error);
        });
    };
    
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
    
    // Toggle urgent message flag
    const toggleUrgentMessage = () => {
      isUrgent.value = !isUrgent.value;
      
      // Focus back to textarea after toggling
      nextTick(() => {
        if (messageInput.value) {
          messageInput.value.focus();
        }
      });
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
      // Fetch notes on component mount
      fetchNotes();
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
        isNew: true,
        urgent: isUrgent.value // Add the urgent flag
      };
      
      // Add it to our notes array
      notes.value.push(newNote);
      
      // Clear the input and reset urgent flag
      newNoteText.value = '';
      isUrgent.value = false;
      
      // Scroll to bottom
      setTimeout(() => {
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }, 10);
      
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
        { 
          text: newNote.text,
          urgent: isUrgent.value // Include urgent flag in the API request
        },
        config
      ).then(response => {
        console.log('Note saved successfully:', response.data);
        const noteIndex = notes.value.findIndex(n => n.id === tempId);
        if (noteIndex !== -1 && response.data) {
          // Update the temporary note with the server data
          notes.value[noteIndex] = { ...response.data, isNew: false };
          
          // Fetch all notes to ensure we have the complete updated list
          fetchNotes();
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

    // Function to get bubble classes based on message type and sender
    const getBubbleClass = (note) => {
      // If the message is urgent, always use chat-bubble-error (red)
      if (note.urgent) {
        return 'chat-bubble-error';
      }
      
      // For the current user (messages on the left) - use green
      if (note.user_id === props.userId) {
        return 'chat-bubble-success'; // Green for sender
      } 
      // For other users (messages on the right) - use blue
      else {
        return 'chat-bubble-primary'; // Blue for receiver
      }
    };
    
    return {
      notes,
      newNoteText,
      messageInput,
      formatTimestamp,
      getUserInitials,
      getCurrentUserInitials,
      addNote,
      fetchNotes,
      showEmojiPickerModal,
      insertEmoji,
      toggleUrgentMessage,
      isUrgent,
      getCsrfToken,
      getBubbleClass,
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

/* Make messenger a full-height flex container */
.messenger {
  display: flex;
  flex-direction: column;
  height: 100%;
  min-height: 400px;
}

.messages {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
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

/* DaisyUI chat bubble styling enhancements */
.chat-bubble {
  margin-bottom: 0.5rem;
  word-break: break-word;
  max-width: 90%;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.chat-bubble:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Enhanced styling for urgent messages */
.chat-bubble-error {
  animation: urgentPulse 2s infinite;
  box-shadow: 0 0 8px rgba(255, 0, 0, 0.3);
}

@keyframes urgentPulse {
  0% { box-shadow: 0 0 8px rgba(255, 0, 0, 0.3); }
  50% { box-shadow: 0 0 12px rgba(255, 0, 0, 0.5); }
  100% { box-shadow: 0 0 8px rgba(255, 0, 0, 0.3); }
}

/* Ensure the chat bubble triangle is displayed */
.chat-bubble:before {
  display: block;
}

/* Space between chats for better readability */
.chat + .chat {
  margin-top: 1rem;
}

/* Update chat layout for DaisyUI bubbles */
.chat {
  margin-bottom: 1.5rem;
}

.chat-header {
  position: relative;
  font-size: 0.75rem;
  line-height: 1rem;
  opacity: 0.8;
}

/* Glossy button styles */
.glossy-btn {
  background: linear-gradient(145deg, #394867, #2a2e3d);
 
}

.glossy-btn:disabled {
  background: #2a2e3d;
  box-shadow: none;
}

/* Custom button styles to match footer */
.btn {
  @apply inline-flex items-center justify-center rounded-md border border-transparent font-semibold text-sm transition-all duration-150;
}

.btn:disabled {
  @apply cursor-not-allowed opacity-50;
}
</style>