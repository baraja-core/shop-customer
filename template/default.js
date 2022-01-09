Vue.component('cms-customer-default', {
	template: `<div class="container-fluid">
	<div class="row mt-2">
		<div class="col">
			<h1>Customers</h1>
		</div>
		<div class="col-sm-3 text-right">
			<b-button variant="success" v-b-modal.modal-create-customer>New customer</b-button>
		</div>
	</div>
	<div v-if="items === null" class="text-center py-5">
		<b-spinner></b-spinner>
	</div>
	<template v-else>
		<cms-filter>
			<b-form inline class="w-100">
				<div class="w-100">
					<div class="d-flex flex-column flex-sm-row align-items-sm-center pr-lg-0">
						<b-form-input size="sm" v-model="filter.query" @input="sync" class="mr-3 w-100" style="max-width:400px" placeholder="Search anywhere..."></b-form-input>
					</div>
				</div>
			</b-form>
		</cms-filter>
		<b-card>
			<table class="table table-hover table-sm cms-table-no-border-top">
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>E-mail</th>
					<th class="text-right">Phone</th>
					<th class="text-right">Inserted</th>
				</tr>
				<tr v-for="item in items">
					<td><a :href="link('Customer:detail', {id: item.id})">{{ item.id }}</a></td>
					<td>
						<span v-if="item.premium" v-b-tooltip title="Premium customer.">🌟</span>
						<span v-if="item.ban" v-b-tooltip title="Customer is banned.">🚫</span>
						<a :href="link('Customer:detail', {id: item.id})">{{ item.firstName }} {{ item.lastName }}</a>
					</td>
					<td>{{ item.email }}</td>
					<td class="text-right">{{ item.phone }}</td>
					<td class="text-right">{{ item.insertedDate }}</td>
				</tr>
			</table>
		</b-card>
	</template>
	<b-modal id="modal-create-customer" title="Create a new customer" hide-footer>
		<div class="row">
			<div class="col mb-3">
				First name:
				<b-form-input v-model="createCustomerForm.firstName"></b-form-input>
			</div>
			<div class="col mb-3">
				Last name:
				<b-form-input v-model="createCustomerForm.lastName"></b-form-input>
			</div>
		</div>
		<div class="mb-3">
			E-mail:
			<b-form-input v-model="createCustomerForm.email"></b-form-input>
		</div>
		<b-button variant="primary" @click="createCustomer">Create new customer</b-button>
	</b-modal>
</div>`,
	data() {
		return {
			items: null,
			filter: {
				query: ''
			},
			createCustomerForm: {
				email: '',
				firstName: '',
				lastName: ''
			}
		}
	},
	created() {
		this.sync();
	},
	methods: {
		sync: function () {
			axiosApi.get(`cms-customer?query=${this.filter.query}`)
				.then(req => {
					this.items = req.data.items;
				});
		},
		createCustomer() {
			axiosApi.post('cms-customer/create-customer', {
				email: this.createCustomerForm.email,
				firstName: this.createCustomerForm.firstName,
				lastName: this.createCustomerForm.lastName
			}).then(() => {
				this.createCustomerForm = {
					email: '',
					firstName: '',
					lastName: ''
				};
				this.sync();
			});
		}
	}
});
