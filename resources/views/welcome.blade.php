<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel EventStream</title>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">

</head>

<body>
    <div class="container w-full mx-auto pt-20">
        <div class="w-full px-4 md:px-0 md:mt-8 mb-16 text-gray-800 leading-normal">

            <div class="flex flex-wrap">
                <div class="w-full md:w-2/2 xl:w-3/3 p-3">
                    <div class="bg-white border rounded shadow p-2">
                        <div class="flex flex-row items-center">
                            <div class="flex-shrink pr-4">
                                <div class="rounded p-3 bg-yellow-600"><i
                                        class="fas fa-user-plus fa-2x fa-fw fa-inverse"></i></div>
                            </div>
                            <div class="flex-1 text-right md:text-center">
                                <h5 class="font-bold uppercase text-gray-500">Latest trade</h5>
                                <h3 class="font-bold text-3xl">
                                    <p>
                                        <span id="name_user"></span>: <span id="latest_trade_user"></span>
                                    </p>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script src="{{ asset('js/app.js') }}"></script>
<script>
    Echo.private('.chat.1')
        .listen('MessageSent', (e) => {
            console.log(e);

            if(e.message.send == 0) {
                 document.getElementById('latest_trade_user').innerText = e.message.message;
                document.getElementById('name_user').innerText = e.client.firstname;
            }else{
                 document.getElementById('latest_trade_user').innerText = e.message.message;
                 document.getElementById('latest_trade_user').style.color = "red";
            document.getElementById('name_user').innerText = "ADMIN";
            }


        });

        Echo.private('.positionDelivery')
        .listen('positionDelivery', (e) => {
            console.log(e);
        });

    let staff = []
    Echo.join('online')
        .here(users => ( users.map((item) => {
            if (item.role === 'delivery') { staff.push(item)}
        })))
        .joining(user => {
            if (user.role === 'delivery') {
                staff.push(user)
            }})
        .leaving(user => (staff = staff.filter(u => (u.id !== user.id))))
        .listen('NewMessage', (e) => {
            console.log(e);
        });
</script>

</html>
