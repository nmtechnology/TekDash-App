<template>
    <AppLayout title="Customers">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Customers
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Search and Add Customer Section -->
                    <div class="flex justify-between items-center mb-6">
                        <div class="w-1/3">
                            <input
                                type="text"
                                v-model="search"
                                @input="performSearch"
                                placeholder="Search customers..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <AddCustomerButton @customer-added="reloadCustomers" />
                    </div>

                    <!-- Customers Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Business Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contact Person
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Net Terms
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="customer in customers.data" :key="customer.id">
                                    <td class="px-6 py-4 whitespace-nowrap">{{ customer.business_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ customer.poc_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ customer.poc_email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ customer.net_terms }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button
                                            @click="editCustomer(customer)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-4"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            @click="confirmDelete(customer)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
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

        <!-- Add/Edit Customer Modal -->
        <Modal :show="showAddModal" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingCustomer ? 'Edit Customer' : 'Add New Customer' }}
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
                        <PrimaryButton type="submit">{{ editingCustomer ? 'Update' : 'Create' }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Delete</h3>
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
            showDeleteModal: false,
            editingCustomer: null,
            customerToDelete: null,
            form: this.getEmptyForm(),
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
            } catch (error) {
                this.toast.error('Error performing search')
            }
        },

        editCustomer(customer) {
            this.editingCustomer = customer
            this.form = { ...customer }
            this.showAddModal = true
        },

        confirmDelete(customer) {
            this.customerToDelete = customer
            this.showDeleteModal = true
        },

        async submitForm() {
            try {
                if (this.editingCustomer) {
                    await axios.put(route('api.customers.update', this.editingCustomer.id), this.form)
                    this.toast.success('Customer updated successfully')
                } else {
                    await axios.post(route('api.customers.store'), this.form)
                    this.toast.success('Customer created successfully')
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
        },
    },
})
</script>
