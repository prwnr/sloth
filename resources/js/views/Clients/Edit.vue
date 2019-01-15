<template>
    <div class="content">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-10">
                        <h1>{{ form.fullname }} - {{ form.company_name }}</h1>
                    </div>
                    <div class="col-md-2">
                        <router-link
                                exact
                                v-if="client.id"
                                :to="{ name: 'clients.show', params: { id: client.id } }"
                                class="btn btn-info btn-block">View
                        </router-link>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <form @submit.prevent="submitForm" @keydown="form.errors.clear($event.target.name)">
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <card-header>Company Details</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="company_name">Company Name</label>
                                    <input id="company_name" type="text"
                                           v-model="form.company_name"
                                           class="form-control"
                                           name="company_name" value=""
                                           placeholder="Company Name" required>
                                    <form-error :text="form.errors.get('company_name')" :show="form.errors.has('company_name')"></form-error>
                                </div>

                                <div class="form-group">
                                    <label for="vat">VAT/TAX Number</label>
                                    <input id="vat" type="text"
                                           v-model="form.vat"
                                           class="form-control"
                                           name="vat" value="" placeholder="VAT/TAX Number"
                                           required>
                                    <form-error :text="form.errors.get('vat')" :show="form.errors.has('vat')"></form-error>
                                </div>

                                <div class="form-group">
                                    <label for="street">Address</label>
                                    <input id="street" type="text"
                                           v-model="form.street"
                                           class="form-control"
                                           name="street" value="" placeholder="Address"
                                           required>
                                    <form-error :text="form.errors.get('street')" :show="form.errors.has('street')"></form-error>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="country">Country</label>
                                            <input id="country" type="text"
                                                   v-model="form.country"
                                                   class="form-control"
                                                   name="country" value="" placeholder="Country"
                                                   required>
                                            <form-error :text="form.errors.get('country')" :show="form.errors.has('country')"></form-error>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="zip">Zip/Postal Code</label>
                                            <input id="zip" type="text"
                                                   v-model="form.zip"
                                                   class="form-control"
                                                   name="zip" value=""
                                                   placeholder="Zip/Postal Code" required>
                                            <form-error :text="form.errors.get('zip')" :show="form.errors.has('zip')"></form-error>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="city">City</label>
                                            <input id="city" type="text"
                                                   v-model="form.city"
                                                   class="form-control"
                                                   name="city" value="" placeholder="City" required>
                                            <form-error :text="form.errors.get('city')" :show="form.errors.has('city')"></form-error>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button class="mt-3 btn btn-success w-25">Save</button>
                    </div>

                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <card-header>Contact Details</card-header>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="fullname">Full Name</label>
                                    <input id="fullname" type="text"
                                           v-model="form.fullname"
                                           class="form-control"
                                           name="fullname" value=""
                                           placeholder="Full Name" required>
                                    <form-error :text="form.errors.get('fullname')" :show="form.errors.has('fullname')"></form-error>
                                </div>
                                <div class="form-group">
                                    <label for="email">Contact Email</label>
                                    <input id="email" type="email"
                                           v-model="form.email"
                                           class="form-control"
                                           name="email" value=""
                                           placeholder="Contact Email" required>
                                    <form-error :text="form.errors.get('email')" :show="form.errors.has('email')"></form-error>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <card-header>Billings</card-header>
                            <div class="card-body">
                                <billings-form v-if="currencies.length > 0 && billingTypes"
                                               :currencies="currencies"
                                               :billingTypes="billingTypes">
                                </billings-form>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
</template>

<script>
    import BillingsForm from '../../components/Billings/Form.vue';

    export default {
        name: 'ClientsEdit',
        components: {
            BillingsForm
        },
        data() {
            return {
                client: {},
                currencies: [],
                billingTypes: null,
                form: new Form({
                    company_name: '',
                    vat: '',
                    street: '',
                    country: '',
                    zip: '',
                    city: '',
                    fullname: '',
                    email: '',
                    billing_rate: '',
                    billing_type: '',
                    billing_currency: 0
                })
            }
        },

        created() {
            this.fetchData();
            this.fetchBillingData();
        },

        methods: {
            submitForm() {
                this.$awn.async(
                    this.form.put('clients/' + this.$route.params.id).then(response => {
                        this.$awn.success('Client updated successfully.');
                    }).catch(error => {
                        this.$awn.alert(error.message);
                    })
                );
            },
            /**
             * Load current client
             */
            fetchData() {
                axios.get('clients/' + this.$route.params.id).then(response => {
                    this.client = response.data.data;
                    this.form.company_name = this.client.company_name;
                    this.form.vat = this.client.street;
                    this.form.street = this.client.street;
                    this.form.country = this.client.country;
                    this.form.zip = this.client.zip;
                    this.form.city = this.client.city;
                    this.form.fullname = this.client.fullname;
                    this.form.email = this.client.email;
                    this.form.billing_rate = this.client.billing.rate;
                    this.form.billing_type = this.client.billing.type;
                    this.form.billing_currency = this.client.billing.currency.id;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },
            /**
             * Load billing data
             */
            fetchBillingData() {
                axios.get('billings/data').then(response => {
                    this.currencies = response.data.currencies;
                    this.billingTypes = response.data.billing_types;
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>

</style>