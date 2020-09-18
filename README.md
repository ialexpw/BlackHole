# BlackHole
A simple Request Bin style application that allows you to capture, debug and inspect HTTP requests in a human-readable way. It is especially helpful when creating an application API or developing Webhooks.

BlackHole allows you to capture GET, POST, PUT, PATCH, DELETE and more HTTP requests.

## Requirements
BlackHole is lightweight and can be setup within minutes, the only requirements are a webserver and PHP. Both Apache and nginx work without issue and PHP 7+ is recommended. No database is required.

## Installation
1. Clone the repository
1. Ensure the directory has write access
1. Click to generate a random url
1. Send HTTP requests to the given url
1. Refresh the page to see information about the requests

## Screenshot
![alt text](https://req.m0x.org/ReqHole.png "Example HTTP request")

## Development
- [ ] Auto refresh the requests coming in
- [x] An API to view details about bins/holes
- [x] An API to allow you to create bins/holes
- [ ] Ability to export data that is within a bin/hole
- [ ] Make it easier to change the HTML template if needed
