Vue.component('cms-customer-overview', {
	props: ['id'],
	template: `<b-card>
	<div v-if="customer === null" class="text-center py-5">
		<b-spinner></b-spinner>
	</div>
	<template v-else>
		<b-form @submit="save">
			<div class="row">
				<div class="col-4">
					Jméno:
					<input v-model="customer.firstName" class="form-control">
				</div>
				<div class="col-4">
					Příjmení:
					<input v-model="customer.lastName" class="form-control">
				</div>
				<div class="col-4">
					E-mail:
					<input v-model="customer.email" class="form-control">
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-4">
					Telefon:
					<input v-model="customer.phone" class="form-control">
				</div>
				<div class="col-4">
					Datum registrace:
					<input v-model="customer.insertedDate" class="form-control">
				</div>
				<div class="col-4">
					Newsletter?<br>
					{{ customer.newsletter ? 'ano' : 'ne' }}
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-4">
					Základní sleva na všechny objednávky (v&nbsp;%):
					<input v-model="customer.defaultOrderSale" class="form-control">
				</div>
			</div>
			<div class="mt-3">
				<b-button type="submit" variant="primary">Uložit</b-button>
			</div>
		</b-form>
		<div class="mt-3">
			<h4>Objednávky</h4>
			<table class="table table-sm">
				<tr>
					<th>Číslo</th>
					<th>Cena</th>
					<th>Datum</th>
				</tr>
				<tr v-for="order in customer.orders">
					<td>
						<a :href="link('CmsOrder:detail', {id: order.id})">{{ order.number }}</a>
					</td>
					<td>{{ order.price }}&nbsp;Kč</td>
					<td>{{ order.date }}</td>
				</tr>
			</table>
		</div>
	</template>
</b-card>`,
	data() {
		return {
			customer: null
		}
	},
	created() {
		this.sync();
	},
	methods: {
		sync: function () {
			axiosApi.get(`cms-customer/detail?id=${this.id}`)
				.then(req => {
					this.customer = req.data;
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
				defaultOrderSale: this.customer.defaultOrderSale
			}).then(req => {
				this.sync();
			});
		}
	}
});
