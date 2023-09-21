// resources/assets/js/bootstrap.js

import Echo from "laravel-echo"

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '4afc4dd6a109658f90f7',
    cluster: 'eu',
    encrypted: true
});
