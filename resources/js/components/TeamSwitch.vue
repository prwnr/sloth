<template>
    <div class="user-panel form-group mt-3 pb-3 mb-3 d-flex">
        <select name="switcher" v-model="currentTeam" class="form-control">
            <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
        </select>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                teams: [],
                currentTeam: this.$user.team.id
            }
        },

        created() {
            this.fetchTeams();
        },

        watch: {
            currentTeam: function() {
                this.switchTeam();
            }
        },

        methods: {
            fetchTeams() {
                axios.get(`/api/users/${this.$user.get('id')}`).then(response => {
                    response.data.teams.forEach(team => {
                        this.teams.push(team);
                    });
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            },

            /**
             * Switch team and reloads user data
             */
            switchTeam() {
                axios.put(`/api/users/${this.$user.get('id')}/switch`, {
                    team: this.currentTeam
                }).then(response => {
                    EventHub.fire('team_change', response.data);
                    this.$router.push({ name: 'dashboard' });
                }).catch(error => {
                    this.$awn.alert(error.message);
                });
            }
        }
    }
</script>

<style scoped>
    select {
        background-color: #e9ecef;
        opacity: 1;
    }
</style>