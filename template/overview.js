Vue.component('cms-customer-overview', {
	props: ['id'],
	template: `<cms-card>
	<div v-if="customer === null" class="text-center py-5">
		<b-spinner></b-spinner>
	</div>
	<template v-else>
		<b-form @submit="save">
			<div class="row">
				<div class="col-4">
					First name:
					<input v-model="customer.firstName" class="form-control">
				</div>
				<div class="col-4">
					Last name:
					<input v-model="customer.lastName" class="form-control">
				</div>
				<div class="col-4">
					E-mail:
					<input v-model="customer.email" class="form-control">
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-4">
					Phone:
					<input v-model="customer.phone" class="form-control">
				</div>
				<div class="col-2">
					Register date:
					<input v-model="customer.insertedDate" class="form-control">
				</div>
				<div class="col-2">
					<span v-b-tooltip title="Preferred user interface language.">Locale</span>
					<b-form-select v-model="customer.locale" :options="locales"></b-form-select>
				</div>
				<div class="col-1">
					Premium?<br>
					<b-form-checkbox v-model="customer.premium"></b-form-checkbox>
				</div>
				<div class="col-1">
					Ban?<br>
					<b-form-checkbox v-model="customer.ban"></b-form-checkbox>
				</div>
				<div class="col-2">
					Newsletter?<br>
					{{ customer.newsletter ? 'yes' : 'no ' }}
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-4">
					Note:
					<b-form-textarea v-model="customer.note" :class="{ 'bg-warning': customer.note }"></b-form-textarea>
				</div>
				<div class="col-4">
					Base discount on all orders (in&nbsp;%):
					<input v-model="customer.defaultOrderSale" class="form-control">
				</div>
			</div>
			<div class="mt-3">
				<b-button type="submit" variant="primary">Save</b-button>
			</div>
		</b-form>
		<div class="mt-3">
			<h4>Past orders</h4>
			<p v-if="customer.orders.length === 0" class="text-center my-5">There are no orders.</p>
			<table v-else class="table table-sm cms-table-no-border-top">
				<tr>
					<th>Number</th>
					<th>Price</th>
					<th>Date</th>
				</tr>
				<tr v-for="order in customer.orders">
					<td>
						<a :href="link('CmsOrder:detail', {id: order.id})">{{ order.number }}</a>
					</td>
					<td v-html="order.price"></td>
					<td>{{ order.date }}</td>
				</tr>
			</table>
		</div>
	</template>
	<b-modal id="modal-change-password" title="Change customer password" hide-footer>
		<p>
			This form will permanently change the customer password.
			The password change is permanent and cannot be undone.
		</p>
		<b-form @submit="changePassword">
			<div class="mb-3">
				New password:
				<b-form-input type="password" v-model="form.password"></b-form-input>
			</div>
			<b-button type="submit" variant="danger">
				<template v-if="form.loading"><b-spinner small></b-spinner></template>
				<template v-else>Change password</template>
			</b-button>
		</b-form>
	</b-modal>
</cms-card>`,
	data() {
		return {
			customer: null,
			locales: [],
			form: {
				loading: false,
				password: ''
			}
		}
	},
	created() {
		this.sync();
	},
	methods: {
		sync: function () {
			axiosApi.get(`cms-customer/detail?id=${this.id}`)
				.then(req => {
					this.customer = req.data.customer;
					this.locales = req.data.locales;
				});
		},
		save(evt) {
			evt.preventDefault();
			axiosApi.post('cms-customer/save', {
				id: this.id,
				email: this.customer.email,
				firstName: this.customer.firstName,
				lastName: this.customer.lastName,
				phone: this.customer.phone,
				locale: this.customer.locale,
				note: this.customer.note,
				premium: this.customer.premium,
				ban: this.customer.ban,
				defaultOrderSale: this.customer.defaultOrderSale
			}).then(() => {
				this.sync();
			});
		},
		changePassword(evt) {
			evt.preventDefault();
			if (!confirm('Really?')) {
				return;
			}
			this.form.loading = true;
			axiosApi.post('cms-customer/save-password', {
				id: this.id,
				password: this.form.password
			}).then(() => {
				this.form.loading = false;
				this.form.password = '';
				this.sync();
			});
		}
	}
});
