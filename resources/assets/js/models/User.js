/**
 * Class User
 */
class User {

    /**
     * User constructor
     * @param activeUser
     */
    constructor(activeUser) {
        this.data = activeUser.user;
        this.team = activeUser.team;
        this.projects = activeUser.projects;
        this.roles = activeUser.roles;
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

    /**
     * Check if user has role
     * @param perm
     * @returns {boolean}
     */
    hasRole(role) {
        if (!this.roles) {
            return false;
        }

        if (this.roles.find(item => item.name == role)) {
            return true;
        }

        return false;
    }
}

export default User;