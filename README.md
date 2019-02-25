#WebSocket Server in PHP7

### Run
Start the server with:
`php -q Server.php`

You will find the logic to communicate with the server in the `index.html`.

PHP can also host the .html by running `php -S 0.0.0.0:80 index.html`

### Extend
Implement the `WSHandler` and create your own implementation to use with the WebSocket server.

A Demo used by the index is included: `ChatHandler` 

### Issues
Safari & Chrome work great!

...but Firefox has a bug in the websocket:
websockets are still open after a page close/refresh, even after firefox is closed!
It will work, but clients won't leave.

### Before production
Use wss:// (websocket secure) and host on https.