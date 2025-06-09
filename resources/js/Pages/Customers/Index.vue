<template>
    <AppLayout title="Customers">
        <template #header>
            <h2 class="font-semibold text-xl text-lime-400 leading-tight">
                Customers
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-800 bg-opacity-80 backdrop-blur-md shadow-xl border border-gray-700 rounded-xl p-6">
                    <!-- Search and Add Customer Section -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="w-1/3">
                            <input
                                type="text"
                                v-model="search"
                                @input="performSearch"
                                placeholder="Search customers..."
                                class="w-full px-4 py-2 border border-gray-700 bg-gray-900 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                            />
                        </div>
                        <AddCustomerButton @customer-added="reloadCustomers" />
                    </div>

                    <!-- DaisyUI Customers Table -->
                    <div class="overflow-x-auto rounded-lg">
                        <table class="table table-zebra">
                            <!-- head -->
                            <thead class="text-lime-400 bg-gray-900">
                                <tr>
                                    <th class="bg-transparent">ID</th>
                                    <th class="bg-transparent">Business Name</th>
                                    <th class="bg-transparent">Contact Person</th>
                                    <th class="bg-transparent">Email</th>
                                    <th class="bg-transparent">Net Terms</th>
                                    <th class="bg-transparent text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="customer in customers.data" 
                                    :key="customer.id" 
                                    @click="showCustomerProfile(customer)"
                                    class="bg-gray-800 hover:bg-gray-700 transition-colors text-white border-b border-gray-700 cursor-pointer">
                                    <th class="font-medium">{{ customer.id }}</th>
                                    <td>{{ customer.business_name }}</td>
                                    <td>{{ customer.poc_name }}</td>
                                    <td>{{ customer.poc_email }}</td>
                                    <td>{{ customer.net_terms }}</td>
                                    <td class="text-right">
                                        <span class="text-xs text-gray-400">Click to view</span>
                                    </td>
                                </tr>
                                <!-- Row for when there's no data -->
                                <tr v-if="customers.data.length === 0">
                                    <td colspan="6" class="text-center py-4">No customers found</td>
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
                        <p class="text-gray-400">Recent work orders will appear here</p>
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
        PrimaryButton,
        DangerButton,
        AddCustomerButton,
        AddWorkOrder,
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
