/**
 * Class User
 */
export default class User {

    /**
     * User constructor
     * @param activeUser
     */
    constructor(activeUser) {
        this.data = activeUser.user;
        this.team = activeUser.team;
        this.permissions = activeUser.permissions;
    }

    /**
     * Return user field value
     * @param field
     * @returns {*}
     */
    get(field) {
        return this.data[field];
    }

    /**
     * Check if user has permission
     * @param perm
     * @returns {boolean}
     */
    can(perm) {
        if (!this.permissions) {
            return false;
        }

        if (this.permissions.includes(perm)) {
            return true;
        }

        return false;
    }
}