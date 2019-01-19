<template>
    <div class="row">
        <div class="col-4">
            <input id="billing_rate"
                   type="number"
                   step=".01"
                   class="form-control"
                   v-model="form.billing_rate"
                   name="billing_rate"
                   placeholder="Rate" >
            <form-error
                    :text="form.errors.get('billing_rate')"
                    :show="form.errors.has('billing_rate')">
            </form-error>
        </div>
        <div class="col-4">
            <select class="form-control"
                    name="billing_currency"
                    v-model="form.billing_currency"
                    required>
                <option :value="0" selected disabled>Currency</option>
                <option
                        :key="currency.id"
                        :value="currency.id"
                        v-for="currency in currencies">
                    {{ currency.symbol }} {{ currency.name }}
                </option>
            </select>
            <form-error
                    :text="form.errors.get('billing_currency')"
                    :show="form.errors.has('billing_currency')">
            </form-error>
        </div>
        <div class="col-4">
             <select class="form-control"
                     name="billing_currency"
                     v-model="form.billing_type"
                     required>
                <option :value="''" selected disabled>Billing type</option>
                <option
                        :key="index"
                        :value="index"
                        v-for="(billingType, index) in billingTypes">
                    {{ billingType }}
                </option>
            </select>
            <form-error
                    :text="form.errors.get('billing_type')"
                    :show="form.errors.has('billing_type')">
            </form-error>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'BillingForm',
        props: {
            currencies: {
                type: Array,
                required: true
            },
            billingTypes: {
                type: Object|Array,
                required: true
            },
            form: {
                type: Object,
                required: true
            }
        }
    }
</script>
