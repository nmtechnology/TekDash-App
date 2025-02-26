<template>
  <div class="messaging-component">
    <!-- Display existing notes with their user avatars -->
    <div v-for="(note, index) in notes" :key="index" class="flex items-start space-x-4 mb-4">
      <div class="shrink-0">
        <img class="inline-block size-10 rounded-full object-cover" 
             :src="getAvatarUrl(note.user_name || 'User')" 
             :alt="note.user_name || 'User'" />
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-sm text-white">{{ note.text }}</p>
        <p class="text-xs text-gray-400">{{ note.user_name || 'User' }} - {{ formatDate(note.created_at) }}</p>
      </div>
    </div>
    
    <!-- New note form with current user avatar -->
    <div class="flex items-start space-x-4 mt-4">
      <div class="shrink-0">
        <img class="inline-block size-10 rounded-full object-cover" 
             :src="currentUserAvatarUrl" 
             alt="Current User" />
      </div>
      <div class="min-w-0 flex-1">
        <form @submit.prevent="addNote" class="relative">
          <div class="rounded-lg bg-slate-800 outline outline-1 -outline-offset-1 outline-gray-600 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-lime-600">
            <label for="comment" class="sr-only">Add your comment</label>
            <textarea v-model="newNoteText" rows="3" name="comment" id="comment" class="block w-full resize-none bg-slate-800 px-3 py-1.5 text-base text-white placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6" placeholder="Add your comment..."></textarea>
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
import { ref, computed } from 'vue';
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

    const addNote = async () => {
      const noteText = newNoteText.value.trim();
      if (noteText) {
        try {
          const response = await axios.post(`/work-orders/${props.workOrderId}/notes`, {
            text: noteText,
            user_id: props.userId
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
      currentUserAvatarUrl
    };
  },
};
</script>

<style scoped>
.messaging-component {
  margin-top: 1rem;
}

.notes-list {
  max-height: 200px;
  overflow-y: auto;
  margin-bottom: 1rem;
}
</style>