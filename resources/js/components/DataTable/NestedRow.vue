<template>
    <div class="nested-dsp-row-comp">
        <div class="callout callout-info" style="margin-bottom: 0px !important">
            <h5>Details about log</h5>

            <span class="pr-3"><strong>Member earned</strong>: {{ details.member_salary.amount }} {{ details.member_salary.currency }}</span>
            <span class="pr-3"><strong>Earned on the project</strong>: {{ details.project_salary.amount }} {{ details.project_salary.currency }}</span>
            <span class="pr-3"><strong>Client salary</strong>: {{ details.client_salary.amount }} {{ details.client_salary.currency }}</span>
            <br>
            <strong>Task description</strong>: <span v-if="details.description">{{ details.description }}</span> <span v-else class="small">(none)</span>        </div>
    </div>
</template>

<script>
    export default {
        props: ['row', 'nested'],

        data () {
            return {
                details: this.row.details ? this.row.details : {}
            }
        }
    }
</script>

<style>
    .nested-dsp-row-comp {
        position: relative;
    }
</style>