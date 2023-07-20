import { user_container, user_adapter } from "./user.js";

class session_manager {
    constructor(apikey = null) {
        this.apikey = apikey;
        this.user_container = null;
    }

    get user() {
        return this.user_container;
    }

    async logged() {
        if (this.user_container !== null) return true;

        if (this.apikey == null || typeof(this.apikey) == 'undefined') {
            return false;
        }

        const adapter = new user_adapter(this.apikey);
        const user = await adapter.get_user();

        if (!(user instanceof user_container)) return false;

        this.user_container = user;

        return true;
    }
}

export { session_manager };
