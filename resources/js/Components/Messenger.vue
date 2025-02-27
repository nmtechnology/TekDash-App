<template>
  <div class="messaging-component flex flex-col h-full">
    <!-- Scrollable area for notes -->
    <div class="notes-container flex-1 overflow-y-auto mb-4 pr-1 space-y-4" style="max-height: 250px;">
      <div v-for="(note, index) in notes" :key="index" class="flex items-start space-x-4 pb-3 border-b border-gray-700 last:border-b-0">
        <div class="shrink-0">
          <img class="inline-block size-10 rounded-full object-cover" 
               :src="getUserAvatar(note.user_id)" 
               :alt="getUserName(note.user_id)" />
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-sm text-white">{{ note.text }}</p>
          <p class="text-xs text-gray-400">{{ getUserName(note.user_id) }} - {{ formatDate(note.created_at) }}</p>
        </div>
      </div>
      <!-- Empty state when no notes -->
      <div v-if="notes.length === 0" class="text-center py-4 text-gray-500">
        No notes yet. Be the first to add one!
      </div>
    </div>
    
    <!-- Fixed position note form -->
    <div class="flex items-start space-x-4 mt-2 bg-gray-800 pt-2 sticky bottom-0">
      <div class="shrink-0">
        <img class="inline-block size-10 rounded-full object-cover" 
             :src="currentUserAvatarUrl" 
             alt="Current User" />
      </div>
      <div class="min-w-0 flex-1">
        <form @submit.prevent="addNote" class="relative">
          <div class="rounded-lg bg-slate-800 outline outline-1 -outline-offset-1 outline-gray-600 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-lime-600">
            <label for="comment" class="sr-only">Add your comment</label>
            <textarea v-model="newNoteText" rows="2" name="comment" id="comment" class="block w-full resize-none bg-slate-800 px-3 py-1.5 text-base text-white placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6" placeholder="Add your comment..."></textarea>
            
            <!-- Emoji picker toggle button -->
            <div class="absolute bottom-2 left-2 flex items-center">
              <button 
                type="button" 
                @click="toggleEmojiPicker" 
                class="text-gray-400 hover:text-lime-400 focus:outline-none p-1"
                aria-label="Add emoji">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-7.536 5.879a1 1 0 001.415 0 3 3 0 014.242 0 1 1 0 001.415-1.415 5 5 0 00-7.072 0 1 1 0 000 1.415z" clip-rule="evenodd" />
                </svg>
              </button>
              
              <!-- Quick emoji buttons -->
              <div class="ml-2 flex space-x-2">
                <button 
                  v-for="emoji in quickEmojis" 
                  :key="emoji" 
                  type="button" 
                  @click="addEmoji(emoji)" 
                  class="hover:bg-gray-700 p-1 rounded">
                  {{ emoji }}
                </button>
              </div>
            </div>
          </div>
          
          <!-- Emoji picker panel -->
          <div v-if="showEmojiPicker" class="absolute bottom-full left-0 mb-2 bg-gray-700 rounded-lg shadow-lg p-2 z-10">
            <div class="grid grid-cols-8 gap-1 max-h-48 overflow-y-auto emoji-grid">
              <button 
                v-for="emoji in emojis" 
                :key="emoji" 
                @click="addEmoji(emoji)" 
                class="h-8 w-8 flex items-center justify-center hover:bg-gray-600 rounded cursor-pointer text-lg">
                {{ emoji }}
              </button>
            </div>
          </div>
          
          <div class="mt-2 flex justify-end">
            <button type="submit" class="inline-flex items-center rounded-md bg-lime-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-lime-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-lime-600">Post</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { format } from 'date-fns';
import axios from 'axios';

export default {
  name: 'Messenger',
  props: {
    initialNotes: {
      type: Array,
      default: () => [],
    },
    workOrderId: {
      type: Number,
      required: true,
    },
    userId: {
      type: Number,
      required: true,
    },
    getUserName: {
      type: Function,
      required: true,
    },
    getUserAvatar: {
      type: Function,
      required: true,
    },
    currentUserAvatar: {
      type: String,
      default: null
    },
    currentUserName: {
      type: String,
      default: 'User'
    }
  },
  setup(props) {
    const notes = ref(Array.isArray(props.initialNotes) ? [...props.initialNotes] : []);
    const newNoteText = ref('');
    const showEmojiPicker = ref(false);
    
    // Common emojis for the emoji picker
    const emojis = [
      '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇',
      '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚',
      '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩',
      '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣',
      '😖', '😫', '😩', '🥺', '😢', '😭', '😤', '😠', '😡', '🤬',
      '🤯', '😳', '🥵', '🥶', '😱', '😨', '😰', '😥', '😓', '🤗',
      '🤔', '🤭', '🤫', '🤥', '😶', '😐', '😑', '😬', '🙄', '😯',
      '😦', '😧', '😮', '😲', '🥱', '😴', '🤤', '😪', '😵', '🤐',
      '🥴', '🤢', '🤮', '🤧', '😷', '🤒', '🤕', '🤑', '🤠', '👍',
      '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👋', '🖐️', '✋'
    ];
    
    // Quick access emojis
    const quickEmojis = ['👍', '❤️', '😊', '👏', '🙌', '🎉'];

    // Toggle emoji picker
    const toggleEmojiPicker = () => {
      showEmojiPicker.value = !showEmojiPicker.value;
    };
    
    // Add emoji to text input
    const addEmoji = (emoji) => {
      newNoteText.value += emoji;
      // Optional: Close picker after selection
      // showEmojiPicker.value = false;
    };
    
    // Close emoji picker when clicking outside
    const closeEmojiPicker = (event) => {
      const picker = document.querySelector('.emoji-grid');
      const button = document.querySelector('.emoji-toggle');
      if (picker && !picker.contains(event.target) && !button?.contains(event.target)) {
        showEmojiPicker.value = false;
      }
    };
    
    // Function to generate avatar URL from name
    const getAvatarUrl = (name) => {
      if (!name || name === 'N/A') {
        name = 'Unknown User';
      }
      // Replace spaces with + for the API
      const formattedName = name.replace(/\s+/g, '+');
      return `https://ui-avatars.com/api/?name=${encodeURIComponent(formattedName)}&color=7F9CF5&background=EBF4FF`;
    };
    
    // Current user avatar - prefer the actual avatar URL if available
    const currentUserAvatarUrl = computed(() => {
      return props.currentUserAvatar || getAvatarUrl(props.currentUserName);
    });
    
    const formatDate = (date) => {
      if (!date) return 'Invalid date';
      try {
        const parsedDate = new Date(date);
        if (isNaN(parsedDate)) {
          throw new Error('Invalid date');
        }
        return format(parsedDate, 'MMMM dd, yyyy hh:mm a');
      } catch (error) {
        console.error('Invalid date:', date);
        return 'Invalid date';
      }
    };

    // Fetch notes when component is mounted
    onMounted(() => {
      fetchNotes();
      // Auto-scroll to bottom after mounting
      setTimeout(scrollToBottom, 100);
      
      // Add document click listener to close emoji picker when clicking outside
      document.addEventListener('click', closeEmojiPicker);
    });
    
    // Auto-scroll to bottom when new notes are added
    watch(() => notes.value.length, () => {
      setTimeout(scrollToBottom, 50);
    });
    
    // Function to scroll to the bottom of notes
    const scrollToBottom = () => {
      const container = document.querySelector('.notes-container');
      if (container) {
        container.scrollTop = container.scrollHeight;
      }
    };
    
    // Fetch notes for this specific work order
    const fetchNotes = async () => {
      try {
        const response = await axios.get(`/work-orders/${props.workOrderId}/notes`);
        if (response.data && Array.isArray(response.data)) {
          notes.value = response.data;
        }
      } catch (error) {
        console.error('Error fetching notes:', error);
      }
    };

    const addNote = async () => {
      const noteText = newNoteText.value.trim();
      if (noteText) {
        try {
          const response = await axios.post(`/work-orders/${props.workOrderId}/notes`, {
            text: noteText,
            user_id: props.userId,
            work_order_id: props.workOrderId // Make sure work_order_id is included
          });

          // Get user name for the new note
          const userName = props.getUserName(props.userId);
          
          // Add the new note to our local notes array
          notes.value.push({
            ...response.data,
            user_name: userName
          });
          
          newNoteText.value = ''; // Clear the input
        } catch (error) {
          console.error('Error adding note:', error);
        }
      }
    };

    return {
      notes,
      newNoteText,
      addNote,
      formatDate,
      getAvatarUrl,
      currentUserAvatarUrl,
      fetchNotes,
      emojis,
      quickEmojis,
      showEmojiPicker,
      toggleEmojiPicker,
      addEmoji
    };
  },
};
</script>

<style scoped>
.notes-container {
  scrollbar-width: thin;
  scrollbar-color: rgba(115, 115, 115, 0.4) transparent;
}

.notes-container::-webkit-scrollbar {
  width: 6px;
}

.notes-container::-webkit-scrollbar-track {
  background: transparent;
}

.notes-container::-webkit-scrollbar-thumb {
  background-color: rgba(115, 115, 115, 0.4);
  border-radius: 20px;
}

/* For smooth scrolling behavior */
.notes-container {
  scroll-behavior: smooth;
}

/* Emoji picker styling */
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
</style>