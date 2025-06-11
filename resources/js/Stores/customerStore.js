// Customer store using reactive state to share customer data across components
import { reactive, ref } from 'vue';
import axios from 'axios';

// Create a reactive state to store customers
const state = reactive({
  customers: [],
  isLoading: false,
  error: null,
  initialized: false
});

// Create a function to load customers
const loadCustomers = async () => {
  console.log('customerStore: loadCustomers called. Current state:', { 
    customersCount: state.customers.length, 
    initialized: state.initialized,
    isLoading: state.isLoading
  });
  
  if (state.customers.length > 0 && state.initialized) {
    // If customers are already loaded, no need to fetch them again
    console.log('customerStore: Returning cached customers:', state.customers.length);
    return state.customers;
  }

  state.isLoading = true;
  state.error = null;

  try {
    console.log('customerStore: Fetching customers from API');
    const response = await axios.get('/api/customers', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });
    
    // Check if we have a valid response
    if (response.data && typeof response.data === 'object') {
      let customersData;
      
      // Case 1: Array of customers directly
      if (Array.isArray(response.data)) {
        customersData = response.data;
        console.log('customerStore: API returned customers array:', customersData.length);
      }
      // Case 2: Object with data property containing array
      else if (response.data.data && Array.isArray(response.data.data)) {
        customersData = response.data.data;
        console.log('customerStore: API returned customers in data property:', customersData.length);
      }
      // No valid customers array found
      else {
        console.error('customerStore: Invalid response format, no customers array:', response.data);
        throw new Error('Invalid response format from API');
      }
      
      state.customers = customersData;
      state.initialized = true;
      return state.customers;
    } else {
      console.error('customerStore: Invalid response type:', typeof response.data);
      throw new Error('Invalid response type from API');
    }
  } catch (error) {
    console.error('customerStore: Failed to load customers:', error);
    state.error = error;
    throw error; // Re-throw so calling components can handle it
  } finally {
    state.isLoading = false;
  }
};

// Function to reset the store (useful for testing or when user logs out)
const resetStore = () => {
  state.customers = [];
  state.isLoading = false;
  state.error = null;
  state.initialized = false;
};

// Function to add a new customer to the store (useful when creating a new customer)
const addCustomer = (customer) => {
  state.customers.push(customer);
};

// Export the store
export default {
  state,
  loadCustomers,
  resetStore,
  addCustomer
};
