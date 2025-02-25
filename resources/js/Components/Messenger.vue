<template>
    <div class="flex items-start space-x-4">
      <div class="shrink-0">
        <img class="inline-block size-10 rounded-full" src="https://images.unsplash.com/photo-1550525811-e5869dd03032?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" />
      </div>
      <div class="min-w-0 flex-1">
        <form action="#" class="relative">
          <div class="rounded-lg bg-white outline outline-1 -outline-offset-1 outline-gray-300 focus-within:outline focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
            <label for="comment" class="sr-only">Add your comment</label>
            <textarea rows="3" name="comment" id="comment" class="block w-full resize-none bg-transparent px-3 py-1.5 text-base text-gray-900 placeholder:text-gray-400 focus:outline focus:outline-0 sm:text-sm/6" placeholder="Add your comment..." />
  
            <!-- Spacer element to match the height of the toolbar -->
            <div class="py-2" aria-hidden="true">
              <!-- Matches height of button in toolbar (1px border + 36px content height) -->
              <div class="py-px">
                <div class="h-9" />
              </div>
            </div>
          </div>
  
          <div class="absolute inset-x-0 bottom-0 flex justify-between py-2 pl-3 pr-2">
            <div class="flex items-center space-x-5">
              <div class="flex items-center">
                <button type="button" class="-m-2.5 flex size-10 items-center justify-center rounded-full text-gray-400 hover:text-gray-500">
                  <PaperClipIcon class="size-5" aria-hidden="true" />
                  <span class="sr-only">Attach a file</span>
                </button>
              </div>
              <div class="flex items-center">
                <Listbox as="div" v-model="selected">
                  <ListboxLabel class="sr-only">Your mood</ListboxLabel>
                  <div class="relative">
                    <ListboxButton class="relative -m-2.5 flex size-10 items-center justify-center rounded-full text-gray-400 hover:text-gray-500">
                      <span class="flex items-center justify-center">
                        <span v-if="selected.value === null">
                          <FaceSmileIcon class="size-5 shrink-0" aria-hidden="true" />
                          <span class="sr-only">Add your mood</span>
                        </span>
                        <span v-if="!(selected.value === null)">
                          <span :class="[selected.bgColor, 'flex size-8 items-center justify-center rounded-full']">
                            <component :is="selected.icon" class="size-5 shrink-0 text-white" aria-hidden="true" />
                          </span>
                          <span class="sr-only">{{ selected.name }}</span>
                        </span>
                      </span>
                    </ListboxButton>
  
                    <transition leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
                      <ListboxOptions class="absolute z-10 -ml-6 mt-1 w-60 rounded-lg bg-white py-3 text-base shadow outline outline-1 outline-black/5 sm:ml-auto sm:w-64 sm:text-sm">
                        <ListboxOption as="template" v-for="mood in moods" :key="mood.value" :value="mood" v-slot="{ active }">
                          <li :class="[active ? 'relative bg-gray-100 outline-none' : 'bg-white', 'cursor-default select-none px-3 py-2']">
                            <div class="flex items-center">
                              <div :class="[mood.bgColor, 'flex size-8 items-center justify-center rounded-full']">
                                <component :is="mood.icon" :class="[mood.iconColor, 'size-5 shrink-0']" aria-hidden="true" />
                              </div>
                              <span class="ml-3 block truncate font-medium">{{ mood.name }}</span>
                            </div>
                          </li>
                        </ListboxOption>
                      </ListboxOptions>
                    </transition>
                  </div>
                </Listbox>
              </div>
            </div>
            <div class="shrink-0">
              <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Post</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script>
  import { ref } from 'vue';
  import { format } from 'date-fns';
import {
  FaceFrownIcon,
  FaceSmileIcon,
  FireIcon,
  HandThumbUpIcon,
  HeartIcon,
  PaperClipIcon,
  XMarkIcon,
} from '@heroicons/vue/20/solid'
import { Listbox, ListboxButton, ListboxLabel, ListboxOption, ListboxOptions } from '@headlessui/vue'

const moods = [
  { name: 'Excited', value: 'excited', icon: FireIcon, iconColor: 'text-white', bgColor: 'bg-red-500' },
  { name: 'Loved', value: 'loved', icon: HeartIcon, iconColor: 'text-white', bgColor: 'bg-pink-400' },
  { name: 'Happy', value: 'happy', icon: FaceSmileIcon, iconColor: 'text-white', bgColor: 'bg-green-400' },
  { name: 'Sad', value: 'sad', icon: FaceFrownIcon, iconColor: 'text-white', bgColor: 'bg-yellow-400' },
  { name: 'Thumbsy', value: 'thumbsy', icon: HandThumbUpIcon, iconColor: 'text-white', bgColor: 'bg-blue-500' },
  { name: 'I feel nothing', value: null, icon: XMarkIcon, iconColor: 'text-gray-400', bgColor: 'bg-transparent' },
]

const selected = ref(moods[5])
  
  export default {
    name: 'MessagingComponent',
    props: {
      initialNotes: {
        type: Array,
        required: true,
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
    },
    setup(props) {
      const notes = ref([...props.initialNotes]);
      const newNoteText = ref('');
  
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
          });

          const newNote = response.data;
          newNote.user = props.getUserName(props.userId);
          notes.value.push(newNote);
          newNoteText.value = ''; // Clear the input
        } catch (error) {
          console.error('Error adding note:', error);
        }
      }
    };
  
    //   const addNote = () => {
    //     const noteText = newNoteText.value.trim();
    //     if (noteText) {
    //       const newNote = {
    //         text: noteText,
    //         user: props.getUserName(props.userId),
    //         timestamp: new Date().toISOString(),
    //       };
    //       notes.value.push(newNote);
    //       newNoteText.value = ''; // Clear the input
    //     }
    //   };
  
      return {
        notes,
        newNoteText,
        addNote,
        formatDate,
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
  
  .note-item {
    padding: 0.5rem;
    border-bottom: 1px solid #ccc;
  }
  
  .new-note {
    display: flex;
    flex-direction: column;
  }
  
  .note-input {
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 0.375rem;
  }
  
  .btn {
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
  }
  
  .btn-primary {
    background-color: hsl(90, 100%, 50%);
    color: white;
  }
  </style>