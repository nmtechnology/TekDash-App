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
  if (state.customers.length > 0 && state.initialized) {
    // If customers are already loaded, no need to fetch them again
    return state.customers;
  }

  state.isLoading = true;
  state.error = null;

  try {
    const response = await axios.get('/api/customers');
    state.customers = response.data;
    state.initialized = true;
    return state.customers;
  } catch (error) {
    console.error('Failed to load customers:', error);
    state.error = error;
    return [];
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
