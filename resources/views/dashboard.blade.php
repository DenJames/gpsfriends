<x-app-layout>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4 pb-6">
            <div class="p-6 bg-white border-b border-gray-200 mt-4">
                My current latitude & longitude:
                <p id="currentLocation"></p>

                <canvas id="locationCanvas" class="w-full border rounded-md bg-gray-100"></canvas>

                <input type="range" id="scaleCanvas" min="0.3" max="8" class="w-full mt-4">
            </div>

            {{-- Chat area --}}
            <div class="p-6 bg-white border-b border-gray-200 mt-4">
                <h2 class="text-2xl mb-4">Chat</h2>
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col">
                        <label for="chatUsers">Select user to chat with</label>
                        <select name="chat_user" id="chatUsers" class="border border-gray-200 rounded-md w-full">
                            <option value="">None</option>
                        </select>
                    </div>

                    <div id="chatArea" class="flex flex-col space-y-4">
                        <div id="chatContainer" class="max-h-[320px] overflow-y-scroll"></div>

                        <textarea name="message" id="message" class="border border-gray-200 rounded-md w-full"></textarea>

                        <button type="submit" id="sendMessage" class="w-full bg-blue-500 hover:bg-blue-600 text-white rounded-md px-4 py-2 flex gap-x-2 justify-center items-center">
                            Send

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                document.getElementById("scaleCanvas").addEventListener("click", scaleCanvas);
                const inputRange = document.getElementById('scaleCanvas');
                inputRange.defaultValue = 1;

                fetchCoords(); // Used to fetch the coords first time around
                setChatUsers(); // Used to set the chat users

                setInterval(() => {
                    fetchCoords();
                }, 5000)

                function fetchCoords() {
                    if (!navigator.geolocation) {
                        x.innerHTML = "Geolocation is not supported by this browser.";

                        return;
                    }

                    navigator.geolocation.getCurrentPosition(fetchPosition);
                }

                function fetchPosition(position) {
                    const x = position.coords.latitude;
                    const y = position.coords.longitude;

                    const el = document.getElementById('currentLocation');
                    el.innerHTML = `Latitude: ${x} <br> Longitude: ${y}`;

                    pushCoordsToDatabase({
                        latitude: x,
                        longitude: y,
                    });
                }

                // Get the canvas element
                const c = document.getElementById("locationCanvas");

                // Set the canvas size
                const content = document.getElementById('locationCanvas');
                c.width = content.offsetWidth
                c.height = 600

                const canvas = c.getContext("2d");
                canvas.beginPath();

                function scaleCanvas() {
                    const num = parseInt(inputRange.value)
                    const minScale = 0.3

                    canvas.clearRect(0, 0, c.width, c.height);

                    // console.log(num);

                    if(minScale >= num) {
                        canvas.scale(0.3, 0.3);
                    } else {
                        canvas.scale(7.3, 7.3);
                    }

                    setCanvasEntries();
                }

                async function setCanvasEntries() {
                    // Clear the canvas
                    canvas.clearRect(0, 0, c.width, c.height);

                    // Fetch users
                    const users = await fetchUsers();

                    users.forEach((user) => {
                        // console.log(user)

                        const offsets = latLonToOffsets(user.latitude, user.longitude, c.width, c.height);

                        let posX = offsets.x
                        let posY = offsets.y

                        if (user.name === '{{ auth()->user()->name }}') {
                            posX = c.width / 2
                            posY = c.height / 2
                        }

                        canvas.beginPath()

                        // Draw circle
                        canvas.arc(posX, posY, 10, 0, 2 * Math.PI);
                        canvas.stroke();

                        // Draw name
                        canvas.fillText(user.name, posX, posY);
                    });
                }

                function pushCoordsToDatabase(coords) {
                    $.ajax({
                        url: '{{ route('location.store') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: coords,
                        success: function() {
                            setCanvasEntries()
                        },
                        error: function(response) {
                            console.log(response)
                        }
                    });
                }

                // Helper methods
                function fetchUsers() {
                    return fetch('/fetch/users')
                        .then((response) => response.json())
                        .then(data => data)
                }

                /**
                 * @param {number} latitude in degrees
                 * @param {number} longitude in degrees
                 * @param {number} mapWidth in pixels
                 * @param {number} mapHeight in pixels
                 */
                function latLonToOffsets(latitude, longitude, mapWidth, mapHeight) {
                    const radius = mapWidth / (2 * Math.PI);
                    const FE = 180; // false easting

                    const lonRad = degreesToRadians(longitude + FE);
                    const x = lonRad * radius;

                    const latRad = degreesToRadians(latitude);
                    const verticalOffsetFromEquator =
                        radius * Math.log(Math.tan(Math.PI / 4 + latRad / 2));
                    const y = mapHeight / 2 - verticalOffsetFromEquator;

                    return { x, y };
                }

                /**
                 * @param {number} degrees
                 */
                function degreesToRadians(degrees) {
                    return (degrees * Math.PI) / 180;
                }

                async function setChatUsers() {
                    const users = await fetchUsers();
                    const filteredUsers = users.filter(user => user.name !== '{{ auth()->user()->name }}')

                    filteredUsers.forEach((user) => {
                        const el = document.getElementById('chatUsers');
                        const option = document.createElement('option');

                        option.value = user.id;
                        option.innerHTML = user.name;

                        el.appendChild(option);
                    });
                }

                // Add event listener to the sendMessage button
                document.getElementById('sendMessage').addEventListener('click', sendMessage);

                const el = document.getElementById('chatUsers');
                const chat = document.getElementById('chatArea');
                if (el.value === '') {
                    chat.style.display = 'none';
                } else {
                    chat.style.display = 'block';
                }

                document.getElementById('chatUsers').addEventListener('change', () => {
                    if (el.value === '') {
                        chat.style.display = 'none';
                    } else {
                        chat.style.display = 'block';

                        setTimeout(() => {
                            const chatContainer = document.getElementById('chatContainer');
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        }, 500);
                    }
                });

                function sendMessage() {
                    const message = document.getElementById('message').value;
                    const user = document.getElementById('chatUsers').value;

                    $.ajax({
                        url: '{{ route('message.store') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            receiver_id: user,
                            message: message,
                        },
                        success: function() {
                            document.getElementById('message').value = '';

                            // Auto scroll to the bottom of the chatContainer div
                            setTimeout(() => {
                                const chatContainer = document.getElementById('chatContainer');
                                chatContainer.scrollTop = chatContainer.scrollHeight;
                            }, 200);

                            Toast.fire({
                                icon: 'success',
                                title: 'Message sent!'
                            })
                        },
                        error: function(response) {
                            const error = response.responseJSON.errors.message[0];
                            Toast.fire({
                                icon: 'error',
                                title: error
                            })
                        }
                    });
                }

               setTimeout(() => {
                   fetchChats();
               }, 1000);

                // Add event listener to check when the select box changes
                document.getElementById('chatUsers').addEventListener('change', fetchChats);
                function fetchChats() {
                    $.ajax({
                        url: '{{ route('messages.fetch') }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            receiver_id: document.getElementById('chatUsers').value,
                        },
                        success: function(response) {
                            const chats = response;

                            // Clear the chat container
                            document.getElementById('chatContainer').innerHTML = '';
                            setTimeout(() => {
                                const chatContainer = document.getElementById('chatContainer');
                                chatContainer.scrollTop = chatContainer.scrollHeight;
                            }, 200);

                            chats.forEach((chat) => {
                                const div = document.createElement('div');

                                if (chat.sender_id === {{ auth()->user()->id }}) {
                                    div.classList.add('w-full', 'mt-2', 'p-4', 'bg-gray-200', 'rounded-md', 'text-gray-700');
                                    div.innerHTML += `<div class="w-full flex gap-x-3 justify-end items-center"><span class="w-[94%] break-words">${chat.message}</span><img src="{{ auth()->user()->profile_picture_url }}" alt="Avatar" class="rounded-full w-12"></div>`;
                                    div.innerHTML += `<p class="text-xs text-gray-500 w-full flex justify-start">{{ auth()->user()->name }}</p>`;
                                } else {
                                    div.classList.add('w-full', 'mt-2', 'p-4', 'bg-gray-200', 'rounded-md', 'text-gray-700');
                                    div.innerHTML += `<div class="w-full flex gap-x-3 justify-start items-center"><img src="${chat.sender.profile_picture_url}" alt="Avatar" class="rounded-full w-12"><span class="w-[94%] break-words">${chat.message}</span></div>`;
                                    div.innerHTML += `<p class="text-xs text-gray-500 w-full flex justify-end">${chat.sender.name}</p>`;
                                }

                                // Append the div to the chat container
                                document.getElementById('chatContainer').appendChild(div);
                            });
                        },
                        error: function(response) {
                            console.log(response)
                        }
                    });
                }

                // Websocket stuff
                const userAuth = Echo.channel('userAuthenticated');
                const userLogout = Echo.channel('userLogout');
                const userChats = Echo.channel('messageSent');

                userAuth.subscribed(() => {
                    console.log("Listening for auth requests");
                }).listen('.user.authenticated', () => {
                    fetchCoords();
                    setChatUsers();
                })

                userLogout.subscribed(() => {
                    console.log("Listening for logout requests");
                }).listen('.user.logout', () => {
                    fetchCoords();
                    setChatUsers();
                })

                userChats.subscribed(() => {
                    console.log("Listening for chats");
                }).listen('.message.sent', () => {
                    fetchChats();
                })
            });
        </script>
    @endpush
</x-app-layout>