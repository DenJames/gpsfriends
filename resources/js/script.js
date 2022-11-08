import './bootstrap.js';

const channel = Echo.private('private.locations');

channel.subscribed(() => {
    console.log("Subscribed");
}).listen('.message', (event) => {
    console.log(event);
});