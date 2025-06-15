<template>
    <AppLayout title="Customers">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-lime-400">
                Customers
            </h2>
            <AddCustomerButton @customer-added="reloadCustomers" />
        </template>

        <!-- Dashboard at the top -->
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 mt-4">
            <Dashboard />
        </div>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-gray-900/50 shadow-xl backdrop-blur-xl sm:rounded-lg">
                    <!-- Search and Add Customer Section -->
                    <div class="p-6 flex justify-between items-center border-b border-gray-700">
                        <div class="relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search customers..."
                                class="pl-10 pr-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700 text-white placeholder-gray-400 focus:outline-none focus:border-lime-500"
                                @input="performSearch"
                            />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                    </div>

                    <!-- Customers Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Business Name</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Contact Person</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Net Terms</th>
                                    <th class="px-6 py-3 text-left text-sm font-medium text-lime-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-800/30 cursor-pointer">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-white">{{ customer.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ customer.business_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ customer.poc_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ customer.poc_email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-white">{{ customer.net_terms }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-xs text-lime-400 hover:underline" @click.stop="showCustomerProfile(customer)">View Details</span>
                                    </td>
                                </tr>
                                <tr v-if="customers.data.length === 0">
                                    <td colspan="6" class="text-center py-4 text-gray-400">No customers found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <Pagination :links="customers.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Customer Modal -->
        <Modal :show="showAddModal" @close="closeModal">
            <div class="p-6 bg-gray-800 text-white">
                <h3 class="text-lg font-medium text-lime-400 mb-4">
                    Add New Customer
                </h3>
                <form @submit.prevent="submitForm">
                    <div class="space-y-4">
                        <div>
                            <Label for="business_name" value="Business Name" />
                            <Input
                                id="business_name"
                                v-model="form.business_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <div>
                            <Label for="address" value="Address" />
                            <Textarea
                                id="address"
                                v-model="form.address"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <div>
                            <Label for="poc_name" value="Contact Person" />
                            <Input
                                id="poc_name"
                                v-model="form.poc_name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <div>
                            <Label for="poc_email" value="Contact Email" />
                            <Input
                                id="poc_email"
                                v-model="form.poc_email"
                                type="email"
                                class="mt-1 block w-full"
                                required
                            />
                        </div>

                        <div>
                            <Label for="fax" value="Fax (Optional)" />
                            <Input
                                id="fax"
                                v-model="form.fax"
                                type="text"
                                class="mt-1 block w-full"
                            />
                        </div>

                        <div>
                            <Label for="net_terms" value="Net Terms" />
                            <Select
                                id="net_terms"
                                v-model="form.net_terms"
                                class="mt-1 block w-full"
                                required
                            >
                                <option value="Net 7">Net 7</option>
                                <option value="Net 15">Net 15</option>
                                <option value="Net 30">Net 30</option>
                                <option value="Net 60">Net 60</option>
                            </Select>
                        </div>

                        <div>
                            <Label for="pay_rate" value="Pay Rate (Optional)" />
                            <Input
                                id="pay_rate"
                                v-model="form.pay_rate"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                        <PrimaryButton type="submit">Create</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
        
        <!-- Customer Profile Modal -->
        <Modal :show="showProfileModal" @close="closeProfileModal" max-width="4xl">
            <div class="p-6 bg-gray-800 text-white">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-lime-400">
                        Customer Profile
                    </h3>
                    <div class="flex space-x-2">
                        <SecondaryButton @click="closeProfileModal">Close</SecondaryButton>
                        <DangerButton @click="confirmDelete(currentCustomer)" v-if="currentCustomer">Delete</DangerButton>
                    </div>
                </div>
                
                <div v-if="currentCustomer" class="space-y-6">
                    <!-- Customer Info Card -->
                    <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Business Name -->
                            <div class="col-span-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-400">Business Name</h4>
                                    <button 
                                        v-if="!editingFields.business_name" 
                                        @click="startEditing('business_name')" 
                                        class="text-xs text-lime-400 hover:text-lime-300">
                                        Edit
                                    </button>
                                </div>
                                <div v-if="editingFields.business_name" class="mt-1">
                                    <Input
                                        v-model="editForm.business_name"
                                        class="w-full"
                                        @keyup.enter="saveField('business_name')"
                                    />
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button @click="cancelEdit('business_name')" class="text-xs text-gray-400 hover:text-white">Cancel</button>
                                        <button @click="saveField('business_name')" class="text-xs text-lime-400 hover:text-lime-300">Save</button>
                                    </div>
                                </div>
                                <p v-else class="text-lg font-semibold text-white mt-1">{{ currentCustomer.business_name }}</p>
                            </div>

                            <!-- Address -->
                            <div class="col-span-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-400">Address</h4>
                                    <button 
                                        v-if="!editingFields.address" 
                                        @click="startEditing('address')" 
                                        class="text-xs text-lime-400 hover:text-lime-300">
                                        Edit
                                    </button>
                                </div>
                                <div v-if="editingFields.address" class="mt-1">
                                    <Textarea
                                        v-model="editForm.address"
                                        class="w-full"
                                    />
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button @click="cancelEdit('address')" class="text-xs text-gray-400 hover:text-white">Cancel</button>
                                        <button @click="saveField('address')" class="text-xs text-lime-400 hover:text-lime-300">Save</button>
                                    </div>
                                </div>
                                <p v-else class="text-white mt-1 whitespace-pre-line">{{ currentCustomer.address }}</p>
                            </div>

                            <!-- Contact Person -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-400">Contact Person</h4>
                                    <button 
                                        v-if="!editingFields.poc_name" 
                                        @click="startEditing('poc_name')" 
                                        class="text-xs text-lime-400 hover:text-lime-300">
                                        Edit
                                    </button>
                                </div>
                                <div v-if="editingFields.poc_name" class="mt-1">
                                    <Input
                                        v-model="editForm.poc_name"
                                        class="w-full"
                                        @keyup.enter="saveField('poc_name')"
                                    />
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button @click="cancelEdit('poc_name')" class="text-xs text-gray-400 hover:text-white">Cancel</button>
                                        <button @click="saveField('poc_name')" class="text-xs text-lime-400 hover:text-lime-300">Save</button>
                                    </div>
                                </div>
                                <p v-else class="text-white mt-1">{{ currentCustomer.poc_name }}</p>
                            </div>

                            <!-- Contact Email -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-400">Contact Email</h4>
                                    <button 
                                        v-if="!editingFields.poc_email" 
                                        @click="startEditing('poc_email')" 
                                        class="text-xs text-lime-400 hover:text-lime-300">
                                        Edit
                                    </button>
                                </div>
                                <div v-if="editingFields.poc_email" class="mt-1">
                                    <Input
                                        v-model="editForm.poc_email"
                                        type="email"
                                        class="w-full"
                                        @keyup.enter="saveField('poc_email')"
                                    />
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button @click="cancelEdit('poc_email')" class="text-xs text-gray-400 hover:text-white">Cancel</button>
                                        <button @click="saveField('poc_email')" class="text-xs text-lime-400 hover:text-lime-300">Save</button>
                                    </div>
                                </div>
                                <p v-else class="text-white mt-1">{{ currentCustomer.poc_email }}</p>
                            </div>

                            <!-- Fax -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-400">Fax</h4>
                                    <button 
                                        v-if="!editingFields.fax" 
                                        @click="startEditing('fax')" 
                                        class="text-xs text-lime-400 hover:text-lime-300">
                                        Edit
                                    </button>
                                </div>
                                <div v-if="editingFields.fax" class="mt-1">
                                    <Input
                                        v-model="editForm.fax"
                                        class="w-full"
                                        @keyup.enter="saveField('fax')"
                                    />
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button @click="cancelEdit('fax')" class="text-xs text-gray-400 hover:text-white">Cancel</button>
                                        <button @click="saveField('fax')" class="text-xs text-lime-400 hover:text-lime-300">Save</button>
                                    </div>
                                </div>
                                <p v-else class="text-white mt-1">{{ currentCustomer.fax || 'None' }}</p>
                            </div>

                            <!-- Net Terms -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-400">Net Terms</h4>
                                    <button 
                                        v-if="!editingFields.net_terms" 
                                        @click="startEditing('net_terms')" 
                                        class="text-xs text-lime-400 hover:text-lime-300">
                                        Edit
                                    </button>
                                </div>
                                <div v-if="editingFields.net_terms" class="mt-1">
                                    <Select
                                        v-model="editForm.net_terms"
                                        class="w-full"
                                    >
                                        <option value="Net 7">Net 7</option>
                                        <option value="Net 15">Net 15</option>
                                        <option value="Net 30">Net 30</option>
                                        <option value="Net 60">Net 60</option>
                                    </Select>
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button @click="cancelEdit('net_terms')" class="text-xs text-gray-400 hover:text-white">Cancel</button>
                                        <button @click="saveField('net_terms')" class="text-xs text-lime-400 hover:text-lime-300">Save</button>
                                    </div>
                                </div>
                                <p v-else class="text-white mt-1">{{ currentCustomer.net_terms }}</p>
                            </div>

                            <!-- Pay Rate -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-400">Pay Rate</h4>
                                    <button 
                                        v-if="!editingFields.pay_rate" 
                                        @click="startEditing('pay_rate')" 
                                        class="text-xs text-lime-400 hover:text-lime-300">
                                        Edit
                                    </button>
                                </div>
                                <div v-if="editingFields.pay_rate" class="mt-1">
                                    <Input
                                        v-model="editForm.pay_rate"
                                        type="number"
                                        step="0.01"
                                        class="w-full"
                                        @keyup.enter="saveField('pay_rate')"
                                    />
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button @click="cancelEdit('pay_rate')" class="text-xs text-gray-400 hover:text-white">Cancel</button>
                                        <button @click="saveField('pay_rate')" class="text-xs text-lime-400 hover:text-lime-300">Save</button>
                                    </div>
                                </div>
                                <p v-else class="text-white mt-1">${{ currentCustomer.pay_rate }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Work Orders Section -->
                    <div class="bg-gray-900 rounded-lg p-6 border border-gray-700">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-lime-400">Work Orders</h3>
                            <AddWorkOrder :customer-id="currentCustomer.id" :customer-name="currentCustomer.business_name" />
                        </div>
                        
                        <div v-if="currentCustomer.recent_work_orders && currentCustomer.recent_work_orders.length > 0" class="overflow-x-auto">
                            <table class="min-w-full bg-gray-800 text-white rounded-lg overflow-hidden mt-2">
                                <thead class="bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-lime-400">Title</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-lime-400">Status</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-lime-400">Date</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-lime-400">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in currentCustomer.recent_work_orders" :key="order.id" class="border-t border-gray-700">
                                        <td class="px-4 py-2">{{ order.title }}</td>
                                        <td class="px-4 py-2">
                                            <span 
                                                :class="{
                                                    'bg-green-800 text-green-100': order.status === 'Complete',
                                                    'bg-blue-800 text-blue-100': order.status === 'Scheduled',
                                                    'bg-yellow-800 text-yellow-100': order.status === 'In Progress',
                                                    'bg-red-800 text-red-100': order.status === 'Cancelled',
                                                    'bg-purple-800 text-purple-100': order.status === 'Part Needed'
                                                }"
                                                class="px-2 py-1 rounded-full text-xs">
                                                {{ order.status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">{{ formatDate(order.date_time || order.created_at) }}</td>
                                        <td class="px-4 py-2">
                                            <Link :href="route('work-orders.show', order.id)" class="text-blue-400 hover:underline">View</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="mt-3 text-right">
                                <Link 
                                    :href="`/customers/${currentCustomer.id}/work-orders`" 
                                    class="text-lime-400 hover:text-lime-300 text-sm"
                                >
                                    View all work orders →
                                </Link>
                            </div>
                        </div>
                        <p v-else class="text-gray-400">No recent work orders for this customer.</p>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 bg-gray-800 text-white">
                <h3 class="text-lg font-medium text-lime-400 mb-4">Confirm Delete</h3>
                <p class="mb-4">Are you sure you want to delete this customer? This action cannot be undone.</p>
                <div class="flex justify-end space-x-4">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteCustomer">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>

<script>
import { defineComponent } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'
import Textarea from '@/Components/Textarea.vue'
import Select from '@/Components/Select.vue'
import Pagination from '@/Components/Pagination.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import AddCustomerButton from '@/Components/AddCustomerButton.vue'
import AddWorkOrder from '@/Pages/WorkOrders/AddWorkOrder.vue'
import Dashboard from '@/Pages/Dashboard.vue';
import customerStore from '@/Stores/customerStore';

export default defineComponent({
    components: {
        AppLayout,
        Modal,
        Label,
        Input,
        Textarea,
        Select,
        Pagination,
        SecondaryButton,
        DangerButton,
        AddWorkOrder,
        AddCustomerButton,
        Link
    },

    props: {
        customers: {
            type: Object,
            required: true,
        },
    },

    setup() {
        const toast = useToast()
        return { toast }
    },

    data() {
        return {
            search: '',
            showAddModal: false,
            showProfileModal: false,
            showDeleteModal: false,
            currentCustomer: null,
            customerToDelete: null,
            form: this.getEmptyForm(),
            editForm: {},
            editingFields: {
                business_name: false,
                address: false,
                poc_name: false,
                poc_email: false,
                fax: false,
                net_terms: false,
                pay_rate: false
            }
        }
    },

    mounted() {
        // Initialize store with the current customers data
        if (this.customers && this.customers.data) {
            customerStore.state.customers = this.customers.data;
            customerStore.state.initialized = true;
        }
    },

    methods: {
        getEmptyForm() {
            return {
                business_name: '',
                address: '',
                poc_name: '',
                poc_email: '',
                fax: '',
                net_terms: 'Net 30',
                pay_rate: '',
                attachable_files: [],
            }
        },
        
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            if (isNaN(date)) return dateString;
            
            const options = { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            return date.toLocaleDateString('en-US', options);
        },

        async performSearch() {
            if (!this.search.trim()) {
                // Reload the original list if search is cleared
                await this.$inertia.get(route('customers.index'))
                return
            }

            try {
                const response = await axios.get(route('api.customers.search'), {
                    params: { query: this.search }
                })
                this.customers.data = response.data
                // Update the store with the search results
                customerStore.state.customers = response.data
            } catch (error) {
                this.toast.error('Error performing search')
            }
        },

        showCustomerProfile(customer) {
            this.currentCustomer = customer
            this.editForm = { ...customer }
            this.showProfileModal = true
            // Reset editing fields
            Object.keys(this.editingFields).forEach(key => {
                this.editingFields[key] = false
            })
        },
        
        closeProfileModal() {
            this.showProfileModal = false
            this.currentCustomer = null
            // Reset editing fields
            Object.keys(this.editingFields).forEach(key => {
                this.editingFields[key] = false
            })
        },
        
        startEditing(field) {
            this.editingFields[field] = true
        },
        
        cancelEdit(field) {
            this.editingFields[field] = false
            this.editForm[field] = this.currentCustomer[field]
        },
        
        async saveField(field) {
            try {
                const updateData = { [field]: this.editForm[field] }
                await axios.put(route('api.customers.update', this.currentCustomer.id), updateData)
                this.currentCustomer[field] = this.editForm[field]
                this.editingFields[field] = false
                this.toast.success(`${field.replace('_', ' ')} updated successfully`)
                this.$inertia.reload({ only: ['customers'] })
            } catch (error) {
                this.toast.error(error.response?.data?.message || `Failed to update ${field.replace('_', ' ')}`)
            }
        },

        confirmDelete(customer) {
            this.customerToDelete = customer
            this.showDeleteModal = true
        },

        async submitForm() {
            try {
                let response;
                if (this.editingCustomer) {
                    response = await axios.put(route('api.customers.update', this.editingCustomer.id), this.form)
                    this.toast.success('Customer updated successfully')
                } else {
                    response = await axios.post(route('api.customers.store'), this.form)
                    this.toast.success('Customer created successfully')
                    // Add the newly created customer to the store
                    if (response.data && response.data.id) {
                        customerStore.addCustomer(response.data);
                    }
                }
                this.closeModal()
                this.$inertia.reload()
            } catch (error) {
                this.toast.error(error.response?.data?.message || 'An error occurred')
            }
        },

        async deleteCustomer() {
            try {
                await axios.delete(route('api.customers.destroy', this.customerToDelete.id))
                this.toast.success('Customer deleted successfully')
                this.showDeleteModal = false
                this.$inertia.reload()
            } catch (error) {
                this.toast.error(error.response?.data?.message || 'An error occurred')
            }
        },

        closeModal() {
            this.showAddModal = false
            this.editingCustomer = null
            this.form = this.getEmptyForm()
        },

        async reloadCustomers() {
            try {
                const response = await axios.get('/api/customers');
                this.customers = {
                    data: response.data
                };
                this.toast.success('Customer list refreshed');
            } catch (error) {
                console.error('Error loading customers:', error);
                this.toast.error('Error refreshing customer list');
            }
            this.$inertia.reload({ only: ['customers'] });
            // Also reload the customer store to keep it in sync
            customerStore.resetStore();
            customerStore.loadCustomers();
        },

        createWorkOrder() {
            // Logic to create a new work order
            this.toast.info('Create Work Order feature is not yet implemented')
        }
    },
})
</script>
