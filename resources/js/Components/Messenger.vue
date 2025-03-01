<template>
  <div class="messenger mt-2 bg-gray-800 rounded-lg border border-gray-700">
    <!-- Messages list -->
    <div class="messages p-4 space-y-4 max-h-96 overflow-y-auto">
      <div v-for="note in notes" :key="note.id" class="flex items-start">
        <!-- User avatar with improved fallback handling -->
        <div class="flex-shrink-0 mr-3">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-700 flex items-center justify-center border border-gray-700">
            <!-- Show either the avatar or the initials, never both -->
            <template v-if="shouldShowInitialsForNote(note)">
              <span class="text-white text-xl font-bold">
                {{ getUserInitials(note.user_id) }}
              </span>
            </template>
            <template v-else>
              <img 
                :src="getAvatarUrl(note.user_id)" 
                :alt="getUserName(note.user_id)"
                class="h-full w-full object-cover"
                @error="(e) => handleNoteAvatarError(e, note.id)"
              />
            </template>
          </div>
        </div>
        
        <!-- Message content -->
        <div class="flex-1 bg-gray-700 rounded-lg p-3">
          <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-white">{{ getUserName(note.user_id) }}</p>
            <p class="text-xs text-gray-400">{{ formatTimestamp(note.created_at) }}</p>
          </div>
          <p class="text-sm text-gray-300 mt-1 whitespace-pre-wrap">{{ note.text }}</p>
        </div>
      </div>
    </div>
    
    <!-- Input area -->
    <div class="border-t border-gray-700 p-4">
      <div class="flex items-start space-x-3">
        <!-- Current user avatar with improved fallback -->
        <div class="flex-shrink-0">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-700 flex items-center justify-center border border-gray-700">
            <!-- Show either the avatar or the initials, never both -->
            <template v-if="currentUserAvatarError">
              <span class="text-white text-lg font-medium">
                {{ getCurrentUserInitials() }}
              </span>
            </template>
            <template v-else>
              <img 
                :src="currentUserAvatar" 
                :alt="getUserName(userId)"
                class="h-full w-full object-cover"
                @error="handleCurrentUserAvatarError"
              />
            </template>
          </div>
        </div>
        
        <!-- Message input -->
        <div class="flex-1">
          <textarea 
            v-model="newNoteText" 
            placeholder="Add a note..." 
            class="w-full rounded-md bg-gray-700 border-gray-600 shadow-sm focus:border-lime-400 focus:ring-lime-400 text-white text-sm"
            rows="2"
            @keydown.enter.prevent="addNote"
          ></textarea>
          
          <div class="flex justify-end mt-2">
            <button 
              @click="addNote" 
              class="inline-flex items-center rounded-md bg-lime-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-lime-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-400"
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
import { ref, onMounted, reactive } from 'vue';
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
    const noteAvatarErrors = reactive({});
    const currentUserAvatarError = ref(false);
    const userAvatarCache = reactive({}); // Cache to track which users have avatar errors
    
    // Format timestamps for display
    const formatTimestamp = (timestamp) => {
      try {
        return format(new Date(timestamp), 'MMM d, yyyy h:mm a');
      } catch (e) {
        return 'Unknown date';
      }
    };
    
    // Get user initials for avatar fallback
    const getUserInitials = (userId) => {
      const name = props.getUserName(userId);
      if (!name) return '??';
      
      return name
        .split(' ')
        .filter(part => part.length > 0)
        .map(part => part[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    };
    
    // Get current user initials
    const getCurrentUserInitials = () => {
      return getUserInitials(props.userId);
    };
    
    // Process avatar URL to ensure it's complete
    const getAvatarUrl = (userId) => {
      try {
        const avatarUrl = props.getUserAvatar(userId);
        
        // If we have no avatar URL, return default
        if (!avatarUrl) {
          return '/default-avatar.png';
        }
        
        // Check if the avatar URL is already a full URL
        if (avatarUrl.startsWith('http://') || avatarUrl.startsWith('https://')) {
          return avatarUrl;
        }
        
        // Handle leading slashes
        if (avatarUrl.startsWith('/')) {
          return avatarUrl;
        }
        
        // Add leading slash for relative paths
        return `/${avatarUrl}`;
      } catch (error) {
        console.error('Error getting avatar URL:', error);
        return '/default-avatar.png';
      }
    };

    // Determine if we should show initials for a note
    const shouldShowInitialsForNote = (note) => {
      // If we have an explicit note error, show initials
      if (noteAvatarErrors[note.id]) {
        return true;
      }
      
      // If this user's avatar has previously failed, show initials
      if (userAvatarCache[note.user_id] === false) {
        return true;
      }
      
      // For new notes from current user, use the current user avatar error state
      if (note.user_id === props.userId && note.isNew) {
        return currentUserAvatarError.value;
      }
      
      return false;
    };
    
    // Handle individual note avatar loading errors
    const handleNoteAvatarError = (e, noteId) => {
      console.log(`Avatar for note ${noteId} failed to load`);
      
      // Get the user ID for this note
      const note = notes.value.find(n => n.id === noteId);
      if (note) {
        // Mark this user's avatar as failed in our cache
        userAvatarCache[note.user_id] = false;
      }
      
      // Also mark this specific note
      noteAvatarErrors[noteId] = true;
      
      if (e && e.target) {
        e.target.style.display = 'none'; // Hide the broken image
      }
    };
    
    // Handle current user avatar loading errors
    const handleCurrentUserAvatarError = (e) => {
      console.log('Current user avatar failed to load');
      currentUserAvatarError.value = true;
      userAvatarCache[props.userId] = false;
      
      if (e && e.target) {
        e.target.style.display = 'none'; // Hide the broken image
      }
    };
    
    // Pre-check if an avatar URL will load for a user
    const checkAvatarUrl = (userId) => {
      // If we've already checked this user, use cached result
      if (userId in userAvatarCache) {
        return userAvatarCache[userId];
      }
      
      const avatarUrl = getAvatarUrl(userId);
      const img = new Image();
      img.src = avatarUrl;
      
      img.onload = () => {
        userAvatarCache[userId] = true;
      };
      
      img.onerror = () => {
        userAvatarCache[userId] = false;
        
        // Find all notes from this user and mark them as having avatar errors
        notes.value.forEach(note => {
          if (note.user_id === userId) {
            noteAvatarErrors[note.id] = true;
          }
        });
      };
      
      // Default to assuming it works until proven otherwise
      return true;
    };
    
    // Check if avatars failed to load on initial notes
    onMounted(() => {
      // Check current user avatar
      if (props.currentUserAvatar) {
        const img = new Image();
        img.src = props.currentUserAvatar;
        img.onload = () => {
          currentUserAvatarError.value = false;
          userAvatarCache[props.userId] = true;
        };
        img.onerror = () => {
          currentUserAvatarError.value = true;
          userAvatarCache[props.userId] = false;
        };
      } else {
        currentUserAvatarError.value = true;
        userAvatarCache[props.userId] = false;
      }
      
      // Pre-check avatars for all users in notes
      const userIds = new Set();
      notes.value.forEach(note => {
        userIds.add(note.user_id);
      });
      
      userIds.forEach(userId => {
        checkAvatarUrl(userId);
      });
      
      // Scroll to the most recent message
      setTimeout(() => {
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }, 100);
    });
    
    // Add a new note
    const addNote = () => {
      if (!newNoteText.value.trim()) return;
      
      // Create a temporary note with a temporary ID
      const tempId = 'temp-' + Date.now();
      const newNote = {
        id: tempId,
        text: newNoteText.value.trim(),
        user_id: props.userId,
        created_at: new Date().toISOString(),
        isNew: true // Flag to identify newly added notes
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
      
      // Send to server
      axios.post(`/work-orders/${props.workOrderId}/notes`, {
        text: newNote.text,
      })
      .then(response => {
        // Update the note with the real ID from server
        const noteIndex = notes.value.findIndex(n => n.id === tempId);
        if (noteIndex !== -1) {
          notes.value[noteIndex].id = response.data.id || newNote.id;
          
          // The avatar display will be based on the cached user avatar status
          if (userAvatarCache[props.userId] === false) {
            noteAvatarErrors[notes.value[noteIndex].id] = true;
          }
        }
      })
      .catch(error => {
        console.error('Error adding note:', error);
        // Remove the temp note on failure
        notes.value = notes.value.filter(n => n.id !== tempId);
      });
    };
    
    return {
      notes,
      newNoteText,
      noteAvatarErrors,
      currentUserAvatarError,
      formatTimestamp,
      getAvatarUrl,
      getUserInitials,
      getCurrentUserInitials,
      handleNoteAvatarError,
      handleCurrentUserAvatarError,
      shouldShowInitialsForNote,
      addNote
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
</style>