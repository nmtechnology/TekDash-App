<template>
  <div class="glossy-section timeline-container">
    <h3 class="text-xl text-lime-400 font-semibold mb-4">Activity Timeline</h3>
    
    <div v-if="loading" class="flex justify-center my-4">
      <svg class="animate-spin h-8 w-8 text-lime-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
    
    <div v-else-if="activities.length === 0" class="text-gray-400 text-center py-4">
      No activity history found for this work order.
    </div>
    
    <div v-else class="timeline">
      <div v-for="(activity, index) in activities" :key="activity.id" class="timeline-item">
        <div class="timeline-marker relative">
          <div class="timeline-dot"></div>
          <div v-if="index !== activities.length - 1" class="timeline-line"></div>
        </div>
        
        <div class="timeline-content">
          <div class="flex justify-between items-start mb-1">
            <div class="text-lime-400 font-semibold">{{ getActivityTitle(activity) }}</div>
            <div class="text-xs text-gray-400">{{ formatDate(activity.created_at) }}</div>
          </div>
          
          <div class="text-gray-300 mb-1">
            {{ getActivityDescription(activity) }}
          </div>
          
          <div v-if="showValues(activity)" class="text-sm">
            <div v-if="activity.old_value" class="text-red-400">
              Old: {{ formatValue(activity.old_value, activity.field_name) }}
            </div>
            <div v-if="activity.new_value" class="text-green-400">
              New: {{ formatValue(activity.new_value, activity.field_name) }}
            </div>
          </div>
          
          <div class="text-xs text-gray-400 mt-1">
            {{ activity.user ? `By ${activity.user.name}` : 'System update' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { format } from 'date-fns';

export default {
  props: {
    workOrderId: {
      type: [Number, String],
      required: true
    }
  },
  
  setup(props) {
    const activities = ref([]);
    const loading = ref(true);
    
    const fetchActivities = async () => {
      try {
        loading.value = true;
        const response = await axios.get(`/work-orders/${props.workOrderId}/activities`);
        
        if (response.data.success) {
          activities.value = response.data.activities;
        } else {
          console.error('Failed to fetch activities:', response.data.error);
        }
      } catch (error) {
        console.error('Error fetching activities:', error);
      } finally {
        loading.value = false;
      }
    };
    
    const formatDate = (dateString) => {
      if (!dateString) return '';
      
      try {
        const date = new Date(dateString);
        return format(date, 'MMM d, yyyy h:mm a');
      } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
      }
    };
    
    const getActivityTitle = (activity) => {
      switch (activity.action_type) {
        case 'create':
          return 'Created';
        case 'update':
          return `Updated ${activity.field_name}`;
        case 'delete':
          return 'Deleted';
        default:
          return activity.action_type || 'Unknown action';
      }
    };
    
    const getActivityDescription = (activity) => {
      return activity.description || `${activity.action_type} ${activity.field_name}`;
    };
    
    const showValues = (activity) => {
      return activity.action_type === 'update' && (activity.old_value || activity.new_value);
    };
    
    const formatValue = (value, fieldName) => {
      if (fieldName === 'date_time' || fieldName === 'created_at' || fieldName === 'updated_at') {
        try {
          return formatDate(value);
        } catch (e) {
          return value;
        }
      }
      return value;
    };
    
    onMounted(() => {
      fetchActivities();
    });
    
    return {
      activities,
      loading,
      formatDate,
      getActivityTitle,
      getActivityDescription,
      showValues,
      formatValue
    };
  }
};
</script>

<style scoped>
.timeline-container {
  padding: 1.25rem;
  margin-top: 1.5rem;
  background: rgba(17, 24, 39, 0.7);
  border: 1px solid rgba(75, 85, 99, 0.2);
  border-radius: 0.5rem;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  position: relative;
}

.timeline-item {
  display: flex;
  position: relative;
  margin-bottom: 1rem;
}

.timeline-marker {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-right: 1rem;
}

.timeline-dot {
  height: 16px;
  width: 16px;
  background-color: rgb(132, 204, 22);
  border-radius: 50%;
  position: relative;
  z-index: 2;
  box-shadow: 0 0 0 4px rgba(132, 204, 22, 0.2);
}

.timeline-line {
  position: absolute;
  top: 16px;
  left: 7px; /* Centers the line with the dot */
  width: 2px;
  background-color: rgba(132, 204, 22, 0.4);
  height: calc(100% + 1.5rem); /* Extends the line to the next dot */
  z-index: 1;
}

.timeline-content {
  flex: 1;
  padding: 0.75rem 1rem;
  background: rgba(31, 41, 55, 0.5);
  border-radius: 0.375rem;
  border: 1px solid rgba(75, 85, 99, 0.2);
  position: relative;
  z-index: 2;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}
</style>
