<template>
    <div class="mb-2 d-inline">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            {{ range | capitalize }}
        </button>
        <ul class="dropdown-menu dropdown-menu-right" x-placement="top-start">
            <a :key="index"
               @click.prevent="changeRange(option)"
               class="dropdown-item text-right"
               href="#"
               v-for="(option, index) in options">
                {{ option | capitalize }}
            </a>

            <div class="dropdown-divider"
                 v-if="allowCustom">
            </div>

            <a class="dropdown-item text-right"
               data-target="#customRange"
               data-toggle="modal"
               href="#"
               v-if="allowCustom">
                Custom
            </a>
        </ul>

        <div aria-hidden="true"
             class="modal fade"
             id="customRange"
             role="dialog"
             tabindex="-1"
             v-if="allowCustom">
            <div class="modal-dialog" role="document">
                <div class="modal-content card-primary card-outline">
                    <div class="modal-header">
                        <h5 class="modal-title">Custom date range</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p>Select your range filter</p>
                        <div class="row">
                            <div class="form-group col-6">
                                <label>From</label>
                                <date-picker
                                        :disabled-dates="{ from: disableFrom }"
                                        :bootstrap-styling="true"
                                        format="yyyy-MM-dd"
                                        v-model="custom.start"
                                        :monday-first="true">
                                </date-picker>
                            </div>

                            <div class="form-group col-6">
                                <label>To</label>
                                <date-picker
                                        :disabled-dates="{ to: disableTo }"
                                        :bootstrap-styling="true"
                                        format="yyyy-MM-dd"
                                        v-model="custom.end"
                                        :monday-first="true">
                                </date-picker>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button :disabled="custom.start == '' || custom.end == ''"
                                @click="discardCustomRange"
                                class="btn btn-secondary"
                                data-dismiss="modal"
                                type="button">
                            Discard
                        </button>
                        <button :disabled="custom.start == '' || custom.end == ''"
                                @click="changeCustomRange"
                                class="btn btn-success"
                                data-dismiss="modal"
                                type="button">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DatePicker from "vuejs-datepicker";

    export default {
        name: 'DateRange',
        components: {
            DatePicker
        },

        props: {
            allowCustom: {
                type: Boolean,
                required: false,
                default: true
            }
        },

        data() {
            return {
                range: 'week',
                custom: {
                    start: '',
                    end: ''
                },
                disableTo: '',
                disableFrom: '',
                options: [
                    'week', 'month', 'year'
                ]
            }
        },

        watch: {
            'custom.start': function () {
                this.disableTo = this.custom.start;
            },
            'custom.end': function () {
                this.disableFrom = this.custom.end;
            }
        },

        methods: {
            /**
             * Change range and emit events about this
             * @param range
             */
            changeRange(range) {
                this.range = range;
                this.custom = {
                    start: '',
                    end: ''
                }

                this.$emit('change', range);
            },

            /**
             * Change range to custom, creates range array
             * and emits event about this
             */
            changeCustomRange() {
                let range = {
                    start: moment(this.custom.start).format('YYYY-MM-DD'),
                    end: moment(this.custom.end).format('YYYY-MM-DD')
                };
                this.range = range.start + ' - ' + range.end;
                this.$emit('change', range);
            },

            /**
             * Reset customer start and end dates
             */
            discardCustomRange() {
                this.custom = {
                    start: '',
                    end: ''
                }

                if (!this.options.includes(this.range)) {
                    this.range = 'week';
                    this.$emit('change', 'week');
                }
            }
        },

        filters: {
            capitalize: function (value) {
                if (!value) {
                    return '';
                }
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1);
            }
        }
    }
</script>

<style scoped>
    .dropdown-menu {
        min-width: 7em !important;
    }

    .modal-dialog {
        margin-top: 5% !important;
    }
</style>