<template>
<!-- end:: Header -->
<div class="kt-grid__item kt-grid__item--fluid kt-grid kt-grid--hor" >
	<!-- begin:: Subheader -->
	<div class="kt-subheader   kt-grid__item" id="kt_subheader">
		<div class="kt-subheader__main">
			<h3 class="kt-subheader__title">Dashboard</h3>
			<span class="kt-subheader__separator kt-hidden"></span>
			<div class="kt-subheader__breadcrumbs">
				<router-link :to="{name : 'index'}" class="kt-subheader__breadcrumbs-home"><i class="flaticon2-shelter"></i></router-link>
				<span class="kt-subheader__breadcrumbs-separator"></span>
				<a href="#" class="kt-subheader__breadcrumbs-link">Orders</a>
				<!-- <span class="kt-subheader__breadcrumbs-link kt-subheader__breadcrumbs-link--active">Active link</span> -->
			</div>
		</div>
	</div>
	<!-- end:: Subheader -->
    <!-- begin:: Content -->
	<div class="kt-content  kt-grid__item kt-grid__item--fluid" id="kt_content">
		<div class="kt-portlet kt-portlet--mobile">
				<div class="kt-portlet__head kt-portlet__head--lg">
					<div class="kt-portlet__head-label">
						<span class="kt-portlet__head-icon">
							<i class="kt-font-brand flaticon2-line-chart"></i>
						</span>
						<h3 class="kt-portlet__head-title">
							Order List
						</h3>
					</div>
				</div>
				<div class="kt-portlet__body">
					<!--begin: Search Form -->
					<div class="kt-form kt-form--label-right kt-margin-t-20 kt-margin-b-10">
						<div class="row align-items-center">
							<div class="col-xl-8 order-2 order-xl-1">
								<div class="row align-items-center">
									<div class="col-md-4 kt-margin-b-20-tablet-and-mobile">
										<div class="kt-input-icon kt-input-icon--left">
											<div class="input-group">
												<input type="text" v-model="search" @keyup="searchit" class="form-control" placeholder="Search for...">
												<div class="input-group-append">
													<button class="btn btn-secondary" @click="searchit" type="submit">
														<i class="fas fa-search"></i>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!--end: Search Form -->
				</div>
										
				<!--begin: Datatable -->
				<div class="kt-portlet__body">
					<div class="kt-section">
						<div class="kt-section__content">
							<div class="row">
								<div class="col-12">
									<div class="table-responsive-md">
										<table class="table table-hover">
											<thead class="thead-light">
												<tr>
													<th>S.no</th>
													<th>Order ID</th>
													<th>Order Date</th>
													<th>Total Amount</th>
													<th>Total Items</th>
													<th>Payment Mode</th>
													<th>Action</th>
												</tr>
											</thead>
											<tbody>
												<tr v-for="item in pages.data" :key="item.id">
													<th scope="row">{{ item.id }}</th>
													<td>{{ item.invoice_no }}</td>
													<td>{{ item.created_at }}</td>
													<td>{{ item.total_amount }}</td>
													<td>{{ item.total_items }}</td>
													<td>{{ item.payment_method }}</td>
													<td>
														<a href="#" class="btn btn-outline-warning btn-elevate btn-icon" @click="editPageData(item);fetchOrderData(item.id);"><i class="flaticon-edit"></i></a>
														<button class="btn btn-outline-danger btn-elevate btn-icon" @click="deletePage(item.id)"><i class="flaticon-delete"></i></button>
													</td>
												</tr>
											</tbody>
										</table>
										<table class="table table-hover">
											<tfoot>
												<pagination :data="pages" :limit="1" @pagination-change-page="getResults"></pagination>
												<p>Result Showing {{ pages.to }} of {{ pages.total }}</p>
											</tfoot>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end: Datatable -->			
		</div>
		
		<div class="modal fade" id="kt_modal_4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" style="display: none;" aria-modal="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" v-show="editMode" id="exampleModalLabel">Update Order`s Info</h5>
						<h5 class="modal-title" v-show="!editMode" id="exampleModalLabel">Add Order`s Info</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						</button>
					</div>
					<form @submit.prevent="editMode ? updatePageData() : createPage()">
					<div class="modal-body">
						<div class="row">
								<div class="col-md-12 col-lg-6 form-group">
									<label>Order Status</label>

									<select v-model="form.order_status" name="order_status" class="form-control">
										<option v-for="data_id in order_statuss.data" :key="data_id.id" :value="data_id.id">{{ data_id.name }}</option>
									</select>
								</div>
								<div class="col-md-12 col-lg-6 form-group">
									<p style="margin-bottom: 7px;">&nbsp;</p>
									<button type="submit" v-show="editMode" class="btn btn-success">Update</button>
								</div>
							</div>
							<hr>
							<div class="row">
								<div class="order_details" v-html="order_details">
										{{ order_details }}
								</div>
							</diV>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
					</div>
					</form>
				</div>
			</div>
		</div>
		
	</div>
	<!-- end:: Content -->
</div>
</template>

<script>
import Form from 'vform';
import axios from 'axios';
export default {
	data () {
		return {
			pages : {},
			datas : {},
			order_details : {},
			editMode : false,
			search : '',
			apiAddress : 'api/orders',
            order_statuss : {},
			// Create a new form instance
			form: new Form({
				id: '',
				invoice_no: '',
                order_subtotal: '',
                created_at: '',
                total_amount: '',
                total_items: '',
                payment_method: '',
                order_status: ''
			})
		}
	},
	methods: {
		getResults(page = 1) {
			this.$Progress.start();
			axios.get(this.siteUrl+this.apiAddress+'?page=' + page)
				.then(response => {
					this.pages = response.data;
				});
			this.$Progress.finish();
		},
		loadPageData () {
			this.$Progress.start();
			axios.get(this.siteUrl+this.apiAddress).then(({ data }) => (this.pages = data))
			this.$Progress.finish();
		},
		addPageData () {
			this.editMode = false;
			$('#kt_modal_4').modal('show');
			this.form.reset();
		},
		editPageData (data) {
			this.editMode = true;
			this.form.clear();
			Fire.$emit('load_ids');
			$('#kt_modal_4').modal('show');
			this.form.reset();
			this.form.fill(data);
		},
		fetchOrderData (id) {
			this.$Progress.start();
			axios.get(this.siteUrl+this.apiAddress+'/show/'+id)
			.then(response => {
				this.order_details = response.data;
				console.log(response.data);
			});
			this.$Progress.finish();
		},
		updatePageData () {
			this.$Progress.start();
			this.form.put(this.siteUrl+this.apiAddress+'/'+this.form.id)
			.then(()=>{
				Fire.$emit('AfterCreate');
				$('#kt_modal_4').modal('hide');
				$('.modal-backdrop').remove();

				Toast.fire({
					icon: 'success',
					title: 'Updated Successfully'
				});
				
				this.$Progress.finish();
			})
			.catch(()=>{
				Toast.fire({
					icon: 'error',
					title: 'Error! Order Not Updated'
				});
				this.$Progress.fail();
			})
		},
		deletePage (id) {
			Swal.fire({
			title: 'Are you sure?',
			text: "You won't be able to revert this!",
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!'
			}).then((result) => {
			if (result.value) {
				this.form.delete(this.siteUrl+this.apiAddress+'/'+id)
				.then(()=>{
					Swal.fire(
					'Deleted!',
					'Your file has been deleted.',
					'success'
					)
					Fire.$emit('AfterCreate');
				})
				.catch(()=>{
					Swal.fire(
					'Failed!',
					'Error! Unable to Delete.',
					'warning'
					)
				})
			}
			})
		},
		createPage () {
			this.$Progress.start();
			this.form.post(this.siteUrl+this.apiAddress)
			.then(()=>{
				Fire.$emit('AfterCreate');
				$('#kt_modal_4').modal('hide');
				$('.modal-backdrop').remove();

				Toast.fire({
					icon: 'success',
					title: 'Gender Created Successfully'
				});
				
				this.$Progress.finish();
			})
			.catch(()=>{
				Toast.fire({
					icon: 'error',
					title: 'Error! Gender Not Created'
				});
				this.$Progress.fail();
			})
        },
		searchit: _.debounce(() => {
			Fire.$emit('searching');
		},1000)
	},

    created() {
		Fire.$on('searching',() => {
			this.$Progress.start();
			let query = this.search;
			if(query){
			axios.get(this.siteUrl+this.apiAddress+'/search/' + query)
				.then(({data}) => {
					this.pages = data;
					this.$Progress.finish();
				})
				.catch(()=>{
					Toast.fire({
						icon: 'error',
						title: 'Error!'
					});
					this.$Progress.fail();
				})
			}
			else{
				this.loadPageData();
				this.$Progress.finish();
			}
		});
		this.loadPageData();
		Fire.$on('AfterCreate',() => {
			this.loadPageData();
		});

		Fire.$on('load_ids',() => {
			axios.get(this.siteUrl+'api/OrderStatus').then((order_statuss) => (this.order_statuss = order_statuss));
		});
    }
}
</script>
