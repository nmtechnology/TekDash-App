<template>
  <div class="messenger mt-2 bg-gray-800 rounded-lg border border-gray-700">
    <!-- Messages list -->
    <div class="messages p-4 space-y-4 max-h-96 overflow-y-auto">
      <div v-for="note in notes" :key="note.id" class="flex items-start">
        <!-- User avatar with improved error handling -->
        <div class="flex-shrink-0 mr-3">
          <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-700 flex items-center justify-center border border-gray-700">
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
                @error="(e) => handleNoteAvatarError(e, note.id, note.user_id)"
                @load="markAvatarAsLoaded(note.id, note.user_id)"
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
import { ref, onMounted, reactive, computed, nextTick } from 'vue';
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
    const avatarLoadedStatus = reactive({});
    
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
        // If we have this user's avatar URL cached, return it immediately
        if (userAvatarCache[userId] && userAvatarCache[userId].url) {
          return userAvatarCache[userId].url;
        }
        
        const avatarUrl = props.getUserAvatar(userId);
        
        if (!avatarUrl || avatarUrl === 'undefined') {
          if (!userAvatarCache[userId]) {
            userAvatarCache[userId] = { status: 'error', url: '/default-avatar.png' };
          }
          return '/default-avatar.png';
        }
        
        let processedUrl;
        
        if (avatarUrl.startsWith('http://') || avatarUrl.startsWith('https://')) {
          processedUrl = avatarUrl;
        } else if (avatarUrl.startsWith('/')) {
          processedUrl = avatarUrl;
        } else {
          processedUrl = `/${avatarUrl}`;
        }
        
        // Cache the URL
        if (!userAvatarCache[userId]) {
          userAvatarCache[userId] = { status: 'pending', url: processedUrl };
        }
        
        return processedUrl;
      } catch (error) {
        console.error('Error getting avatar URL for user', userId, error);
        return '/default-avatar.png';
      }
    };

    // Enhanced check to determine if we should show initials
    const shouldShowInitialsForNote = (note) => {
      // Safety check for note with missing ID or user_id
      if (!note || !note.id || !note.user_id) {
        console.log('Invalid note object', note);
        return true;
      }
      
      // If we have a specific note avatar error
      if (noteAvatarErrors[note.id]) {
        return true;
      }
      
      // If user's avatar has failed to load
      if (userAvatarCache[note.user_id] && userAvatarCache[note.user_id].status === 'error') {
        return true;
      }
      
      // For new notes from current user, use the current user avatar error state
      if (note.user_id === props.userId && note.isNew) {
        return currentUserAvatarError.value;
      }
      
      return false;
    };
    
    // Handle avatar successful load
    const markAvatarAsLoaded = (noteId, userId) => {
      if (userId) {
        if (!userAvatarCache[userId]) {
          userAvatarCache[userId] = { status: 'loaded' };
        } else {
          userAvatarCache[userId].status = 'loaded';
        }
      }
      
      // Mark this specific note's avatar as successfully loaded
      if (noteId) {
        avatarLoadedStatus[noteId] = true;
        
        // Make sure there's no error for this note
        if (noteAvatarErrors[noteId]) {
          delete noteAvatarErrors[noteId];
        }
      }
    };
    
    // Improved error handler with more detailed logging
    const handleNoteAvatarError = (e, noteId, userId) => {
      console.log(`Avatar for note ${noteId} failed to load for user ${userId}`);
      
      // Mark this specific note as having an avatar error
      if (noteId) {
        noteAvatarErrors[noteId] = true;
      }
      
      // Also mark the user's avatar status as error
      if (userId) {
        if (!userAvatarCache[userId]) {
          userAvatarCache[userId] = { status: 'error' };
        } else {
          userAvatarCache[userId].status = 'error';
        }
        
        // Find all notes from this user and mark them for avatar failure
        nextTick(() => {
          notes.value.forEach(note => {
            if (note.user_id === userId && !avatarLoadedStatus[note.id]) {
              noteAvatarErrors[note.id] = true;
            }
          });
        });
      }
      
      // Hide the broken image
      if (e && e.target) {
        e.target.style.display = 'none';
      }
    };
    
    // Handle current user avatar loading errors with improved logic
    const handleCurrentUserAvatarError = (e) => {
      console.log('Current user avatar failed to load');
      currentUserAvatarError.value = true;
      
      if (props.userId) {
        if (!userAvatarCache[props.userId]) {
          userAvatarCache[props.userId] = { status: 'error' };
        } else {
          userAvatarCache[props.userId].status = 'error';
        }
      }
      
      if (e && e.target) {
        e.target.style.display = 'none';
      }
    };
    
    // More thorough preloading of avatars
    const preloadAvatarForUser = (userId) => {
      if (!userId || userAvatarCache[userId]) return;
      
      const avatarUrl = getAvatarUrl(userId);
      if (!avatarUrl) return;
      
      const img = new Image();
      
      img.onload = () => {
        if (!userAvatarCache[userId]) {
          userAvatarCache[userId] = { status: 'loaded', url: avatarUrl };
        } else {
          userAvatarCache[userId].status = 'loaded';
          userAvatarCache[userId].url = avatarUrl;
        }
      };
      
      img.onerror = () => {
        if (!userAvatarCache[userId]) {
          userAvatarCache[userId] = { status: 'error', url: avatarUrl };
        } else {
          userAvatarCache[userId].status = 'error';
        }
        
        // Find all notes from this user and mark them
        nextTick(() => {
          notes.value.forEach(note => {
            if (note.user_id === userId) {
              noteAvatarErrors[note.id] = true;
            }
          });
        });
      };
      
      img.src = avatarUrl;
    };
    
    onMounted(() => {
      // Preload current user avatar first
      if (props.currentUserAvatar) {
        const img = new Image();
        img.src = props.currentUserAvatar;
        
        img.onload = () => {
          currentUserAvatarError.value = false;
          if (!userAvatarCache[props.userId]) {
            userAvatarCache[props.userId] = { status: 'loaded', url: props.currentUserAvatar };
          } else {
            userAvatarCache[props.userId].status = 'loaded';
            userAvatarCache[props.userId].url = props.currentUserAvatar;
          }
        };
        
        img.onerror = () => {
          currentUserAvatarError.value = true;
          if (!userAvatarCache[props.userId]) {
            userAvatarCache[props.userId] = { status: 'error' };
          } else {
            userAvatarCache[props.userId].status = 'error';
          }
        };
      } else {
        currentUserAvatarError.value = true;
        if (props.userId) {
          userAvatarCache[props.userId] = { status: 'error' };
        }
      }
      
      // Collect all unique user IDs from notes
      const userIds = new Set();
      if (notes.value && notes.value.length > 0) {
        notes.value.forEach(note => {
          if (note && note.user_id) {
            userIds.add(note.user_id);
          }
        });
      }
      
      // Preload avatars for all users
      userIds.forEach(userId => {
        if (userId !== props.userId) { // We already handled the current user
          preloadAvatarForUser(userId);
        }
      });
      
      // Scroll to the most recent message
      setTimeout(() => {
        const messagesContainer = document.querySelector('.messages');
        if (messagesContainer) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
      }, 100);
    });
    
    // Add a new note with improved handling
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
      
      // For current user avatar, apply cached status
      if (userAvatarCache[props.userId] && userAvatarCache[props.userId].status === 'error') {
        noteAvatarErrors[tempId] = true;
      }
      
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
          const newId = response.data.id || tempId;
          
          // Update the note ID
          notes.value[noteIndex].id = newId;
          
          // Transfer any avatar error status to the new ID
          if (noteAvatarErrors[tempId]) {
            noteAvatarErrors[newId] = true;
            delete noteAvatarErrors[tempId];
          }
          
          // Transfer any loaded status
          if (avatarLoadedStatus[tempId]) {
            avatarLoadedStatus[newId] = true;
            delete avatarLoadedStatus[tempId];
          }
        }
      })
      .catch(error => {
        console.error('Error adding note:', error);
        // Remove the temp note on failure
        notes.value = notes.value.filter(n => n.id !== tempId);
        
        // Also clean up any avatar status
        if (noteAvatarErrors[tempId]) {
          delete noteAvatarErrors[tempId];
        }
        if (avatarLoadedStatus[tempId]) {
          delete avatarLoadedStatus[tempId];
        }
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
      addNote,
      showEmojiPicker,
      selectedCategory,
      commonEmojis,
      emojiCategories,
      currentCategoryEmojis,
      toggleEmojiPicker,
      selectEmojiCategory,
      addEmoji,
      markAvatarAsLoaded,
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
</style>